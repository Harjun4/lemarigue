<?php
/**
 * Order Entity Model
 * Handles order creation, code generation (#PRL-YYYYMMDD-XXX), and verification status transitions.
 */

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/Product.php';

class Order {
    private PDO $db;
    private Product $productModel;

    public function __construct() {
        $this->db = Database::getConnection();
        $this->productModel = new Product();
    }

    /**
     * Generates a unique cross-verifiable order code matching #PRL-{YYYYMMDD}-{INDEX}.
     */
    public function generateOrderCode(): string {
        $todayPrefix = '#PRL-' . date('Ymd') . '-';
        $stmt = $this->db->prepare("SELECT order_code FROM orders WHERE order_code LIKE :prefix ORDER BY id DESC LIMIT 1");
        $stmt->execute([':prefix' => $todayPrefix . '%']);
        $lastOrder = $stmt->fetch();

        $index = 1;
        if ($lastOrder && !empty($lastOrder['order_code'])) {
            $parts = explode('-', $lastOrder['order_code']);
            $lastIndex = (int)end($parts);
            $index = $lastIndex + 1;
        }

        return $todayPrefix . str_pad((string)$index, 3, '0', STR_PAD_LEFT);
    }

    /**
     * Creates an order, records items, and holds products for the hold duration.
     */
    public function create(array $orderData, array $itemIds, int $holdMinutes = 60): ?array {
        $this->db->beginTransaction();

        try {
            // Verify all items are currently available
            $placeholders = implode(',', array_fill(0, count($itemIds), '?'));
            $checkStmt = $this->db->prepare("SELECT id, name, price, discount_price, status FROM products WHERE id IN ($placeholders)");
            $checkStmt->execute($itemIds);
            $products = $checkStmt->fetchAll();

            if (count($products) !== count($itemIds)) {
                $this->db->rollBack();
                return null;
            }

            $totalAmount = 0;
            $verifiedItems = [];
            foreach ($products as $p) {
                if ($p['status'] !== 'available') {
                    $this->db->rollBack();
                    return null;
                }
                $effectivePrice = !empty($p['discount_price']) ? (float)$p['discount_price'] : (float)$p['price'];
                $totalAmount += $effectivePrice;
                $verifiedItems[] = [
                    'product_id' => (int)$p['id'],
                    'name' => $p['name'],
                    'price' => $effectivePrice,
                ];
            }

            $orderCode = $this->generateOrderCode();

            $sql = "INSERT INTO orders (order_code, customer_name, customer_phone, shipping_address, courier, total_amount, payment_status, whatsapp_message)
                    VALUES (:order_code, :customer_name, :customer_phone, :shipping_address, :courier, :total_amount, 'waiting_payment', :whatsapp_message)";
            
            $stmt = $this->db->prepare($sql);
            $stmt->execute([
                ':order_code' => $orderCode,
                ':customer_name' => $orderData['customer_name'],
                ':customer_phone' => $orderData['customer_phone'],
                ':shipping_address' => $orderData['shipping_address'],
                ':courier' => $orderData['courier'],
                ':total_amount' => $totalAmount,
                ':whatsapp_message' => $orderData['whatsapp_message'] ?? null,
            ]);

            $orderId = (int)$this->db->lastInsertId();

            // Insert order items and hold products
            $itemInsert = $this->db->prepare("INSERT INTO order_items (order_id, product_id, price_at_purchase) VALUES (:oid, :pid, :price)");
            $holdStmt = $this->db->prepare("UPDATE products SET status = 'booked', hold_expires_at = DATE_ADD(NOW(), INTERVAL :min MINUTE) WHERE id = :pid");

            foreach ($verifiedItems as $item) {
                $itemInsert->execute([
                    ':oid' => $orderId,
                    ':pid' => $item['product_id'],
                    ':price' => $item['price'],
                ]);
                $holdStmt->execute([
                    ':min' => $holdMinutes,
                    ':pid' => $item['product_id'],
                ]);
            }

            $this->db->commit();

            return [
                'id' => $orderId,
                'order_code' => $orderCode,
                'total_amount' => $totalAmount,
                'items' => $verifiedItems,
            ];
        } catch (Exception $e) {
            $this->db->rollBack();
            throw $e;
        }
    }

    public function getAll(array $filters = []): array {
        $sql = "SELECT o.*, 
                       COUNT(oi.id) as item_count
                FROM orders o
                LEFT JOIN order_items oi ON o.id = oi.order_id
                WHERE 1=1";
        $params = [];

        if (!empty($filters['payment_status'])) {
            $sql .= " AND o.payment_status = :status";
            $params[':status'] = $filters['payment_status'];
        }

        if (!empty($filters['search'])) {
            $sql .= " AND (o.order_code LIKE :search OR o.customer_name LIKE :searchName OR o.customer_phone LIKE :searchPhone)";
            $params[':search'] = '%' . $filters['search'] . '%';
            $params[':searchName'] = '%' . $filters['search'] . '%';
            $params[':searchPhone'] = '%' . $filters['search'] . '%';
        }

        $sql .= " GROUP BY o.id ORDER BY o.id DESC";

        if (!empty($filters['limit'])) {
            $sql .= " LIMIT " . (int)$filters['limit'];
        }

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    public function getById(int $id): ?array {
        $stmt = $this->db->prepare("SELECT * FROM orders WHERE id = :id LIMIT 1");
        $stmt->execute([':id' => $id]);
        $order = $stmt->fetch();
        if (!$order) {
            return null;
        }

        $order['items'] = $this->getOrderItems($id);
        return $order;
    }

    public function getByCode(string $orderCode): ?array {
        $stmt = $this->db->prepare("SELECT * FROM orders WHERE order_code = :code LIMIT 1");
        $stmt->execute([':code' => $orderCode]);
        $order = $stmt->fetch();
        if (!$order) {
            return null;
        }

        $order['items'] = $this->getOrderItems((int)$order['id']);
        return $order;
    }

    public function getOrderItems(int $orderId): array {
        $sql = "SELECT oi.*, p.name as product_name, p.slug as product_slug, p.size, p.condition_grade,
                       (SELECT image_path FROM product_images WHERE product_id = p.id ORDER BY is_primary DESC, sort_order ASC LIMIT 1) as product_image
                FROM order_items oi
                JOIN products p ON oi.product_id = p.id
                WHERE oi.order_id = :oid";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':oid' => $orderId]);
        return $stmt->fetchAll();
    }

    /**
     * Updates payment status. Automatically transitions products to 'sold' or back to 'available'.
     */
    public function updateStatus(int $orderId, string $newStatus): bool {
        $validStatuses = ['waiting_payment', 'verified', 'cancelled'];
        if (!in_array($newStatus, $validStatuses, true)) {
            return false;
        }

        $order = $this->getById($orderId);
        if (!$order) {
            return false;
        }

        $this->db->beginTransaction();

        try {
            $stmt = $this->db->prepare("UPDATE orders SET payment_status = :status WHERE id = :id");
            $stmt->execute([':status' => $newStatus, ':id' => $orderId]);

            $items = $order['items'];
            foreach ($items as $item) {
                $pid = (int)$item['product_id'];
                if ($newStatus === 'verified') {
                    // Verifying marks inventory permanently as sold
                    $this->productModel->markAsSold($pid);
                } elseif ($newStatus === 'cancelled') {
                    // Cancelling restores inventory to available
                    $this->productModel->releaseToAvailable($pid);
                }
            }

            $this->db->commit();
            return true;
        } catch (Exception $e) {
            $this->db->rollBack();
            throw $e;
        }
    }

    public function getDashboardStats(): array {
        $stmt = $this->db->query("SELECT 
            COUNT(*) as total_orders,
            COALESCE(SUM(CASE WHEN payment_status = 'verified' THEN total_amount ELSE 0 END), 0) as total_revenue,
            SUM(CASE WHEN payment_status = 'waiting_payment' THEN 1 ELSE 0 END) as waiting_orders,
            SUM(CASE WHEN payment_status = 'verified' THEN 1 ELSE 0 END) as verified_orders
        FROM orders");
        return $stmt->fetch() ?: ['total_orders' => 0, 'total_revenue' => 0, 'waiting_orders' => 0, 'verified_orders' => 0];
    }
}
