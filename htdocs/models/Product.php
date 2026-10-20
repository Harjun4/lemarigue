<?php
/**
 * Product Entity Model
 * Handles preloved one-of-a-kind inventory, status states, and hold expirations.
 */

require_once __DIR__ . '/../config/database.php';

class Product {
    private PDO $db;

    public function __construct() {
        $this->db = Database::getConnection();
    }

    /**
     * Releases expired booking locks dynamically.
     * Preloved items reserved over the timeout without verification revert to available.
     */
    public function releaseExpiredHolds(): int {
        $sql = "UPDATE products 
                SET status = 'available', hold_expires_at = NULL 
                WHERE status = 'booked' 
                  AND hold_expires_at IS NOT NULL 
                  AND hold_expires_at <= NOW()";
        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        return $stmt->rowCount();
    }

    /**
     * Fetches products matching optional filters for catalog and admin views.
     */
    public function getAll(array $filters = []): array {
        $this->releaseExpiredHolds();

        $sql = "SELECT p.*, 
                       (SELECT image_path FROM product_images WHERE product_id = p.id ORDER BY is_primary DESC, sort_order ASC, id ASC LIMIT 1) as primary_image
                FROM products p 
                WHERE 1=1";
        $params = [];

        if (!empty($filters['category'])) {
            $sql .= " AND p.category = :category";
            $params[':category'] = $filters['category'];
        }

        if (!empty($filters['size'])) {
            $sql .= " AND p.size = :size";
            $params[':size'] = $filters['size'];
        }

        if (!empty($filters['condition_grade'])) {
            $sql .= " AND p.condition_grade = :condition_grade";
            $params[':condition_grade'] = $filters['condition_grade'];
        }

        if (!empty($filters['status'])) {
            $sql .= " AND p.status = :status";
            $params[':status'] = $filters['status'];
        }

        if (!empty($filters['search'])) {
            $sql .= " AND (p.name LIKE :search OR p.condition_notes LIKE :searchNotes)";
            $params[':search'] = '%' . $filters['search'] . '%';
            $params[':searchNotes'] = '%' . $filters['search'] . '%';
        }

        if (isset($filters['min_price']) && is_numeric($filters['min_price'])) {
            $sql .= " AND (COALESCE(p.discount_price, p.price) >= :min_price)";
            $params[':min_price'] = (float)$filters['min_price'];
        }

        if (isset($filters['max_price']) && is_numeric($filters['max_price'])) {
            $sql .= " AND (COALESCE(p.discount_price, p.price) <= :max_price)";
            $params[':max_price'] = (float)$filters['max_price'];
        }

        // Sorting: default available first, then newest
        $sql .= " ORDER BY FIELD(p.status, 'available', 'booked', 'sold'), p.id DESC";

        if (!empty($filters['limit'])) {
            $sql .= " LIMIT " . (int)$filters['limit'];
            if (!empty($filters['offset'])) {
                $sql .= " OFFSET " . (int)$filters['offset'];
            }
        }

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    public function getById(int $id): ?array {
        $this->releaseExpiredHolds();

        $stmt = $this->db->prepare("SELECT * FROM products WHERE id = :id LIMIT 1");
        $stmt->execute([':id' => $id]);
        $product = $stmt->fetch();
        if (!$product) {
            return null;
        }

        $product['images'] = $this->getImages($id);
        return $product;
    }

    public function getBySlug(string $slug): ?array {
        $this->releaseExpiredHolds();

        $stmt = $this->db->prepare("SELECT * FROM products WHERE slug = :slug LIMIT 1");
        $stmt->execute([':slug' => $slug]);
        $product = $stmt->fetch();
        if (!$product) {
            return null;
        }

        $product['images'] = $this->getImages((int)$product['id']);
        return $product;
    }

    public function getImages(int $productId): array {
        $stmt = $this->db->prepare("SELECT * FROM product_images WHERE product_id = :product_id ORDER BY is_primary DESC, sort_order ASC, id ASC");
        $stmt->execute([':product_id' => $productId]);
        return $stmt->fetchAll();
    }

    public function create(array $data): int {
        $sql = "INSERT INTO products (name, slug, category, size, dimensions, condition_grade, condition_notes, price, discount_price, status)
                VALUES (:name, :slug, :category, :size, :dimensions, :condition_grade, :condition_notes, :price, :discount_price, :status)";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            ':name' => $data['name'],
            ':slug' => $data['slug'],
            ':category' => $data['category'],
            ':size' => $data['size'],
            ':dimensions' => $data['dimensions'] ?? null,
            ':condition_grade' => $data['condition_grade'] ?? 'good',
            ':condition_notes' => $data['condition_notes'] ?? null,
            ':price' => $data['price'],
            ':discount_price' => !empty($data['discount_price']) ? $data['discount_price'] : null,
            ':status' => $data['status'] ?? 'available',
        ]);

        return (int)$this->db->lastInsertId();
    }

    public function update(int $id, array $data): bool {
        $sql = "UPDATE products SET 
                    name = :name,
                    slug = :slug,
                    category = :category,
                    size = :size,
                    dimensions = :dimensions,
                    condition_grade = :condition_grade,
                    condition_notes = :condition_notes,
                    price = :price,
                    discount_price = :discount_price,
                    status = :status
                WHERE id = :id";

        $stmt = $this->db->prepare($sql);
        return $stmt->execute([
            ':id' => $id,
            ':name' => $data['name'],
            ':slug' => $data['slug'],
            ':category' => $data['category'],
            ':size' => $data['size'],
            ':dimensions' => $data['dimensions'] ?? null,
            ':condition_grade' => $data['condition_grade'],
            ':condition_notes' => $data['condition_notes'] ?? null,
            ':price' => $data['price'],
            ':discount_price' => !empty($data['discount_price']) ? $data['discount_price'] : null,
            ':status' => $data['status'],
        ]);
    }

    public function delete(int $id): bool {
        $images = $this->getImages($id);
        foreach ($images as $img) {
            $fullPath = ROOT_PATH . '/' . ltrim($img['image_path'], '/');
            if (file_exists($fullPath) && !str_starts_with($img['image_path'], 'assets/')) {
                @unlink($fullPath);
            }
        }

        $stmt = $this->db->prepare("DELETE FROM products WHERE id = :id");
        return $stmt->execute([':id' => $id]);
    }

    public function addImage(int $productId, string $imagePath, bool $isPrimary = false, int $sortOrder = 0): int {
        if ($isPrimary) {
            $unset = $this->db->prepare("UPDATE product_images SET is_primary = 0 WHERE product_id = :product_id");
            $unset->execute([':product_id' => $productId]);
        }

        $stmt = $this->db->prepare("INSERT INTO product_images (product_id, image_path, is_primary, sort_order) VALUES (:pid, :path, :primary, :sort)");
        $stmt->execute([
            ':pid' => $productId,
            ':path' => $imagePath,
            ':primary' => $isPrimary ? 1 : 0,
            ':sort' => $sortOrder,
        ]);
        return (int)$this->db->lastInsertId();
    }

    public function deleteImage(int $imageId): bool {
        $stmt = $this->db->prepare("SELECT * FROM product_images WHERE id = :id");
        $stmt->execute([':id' => $imageId]);
        $image = $stmt->fetch();
        if ($image) {
            $fullPath = ROOT_PATH . '/' . ltrim($image['image_path'], '/');
            if (file_exists($fullPath) && !str_starts_with($image['image_path'], 'assets/')) {
                @unlink($fullPath);
            }
            $del = $this->db->prepare("DELETE FROM product_images WHERE id = :id");
            return $del->execute([':id' => $imageId]);
        }
        return false;
    }

    public function setPrimaryImage(int $productId, int $imageId): bool {
        $unset = $this->db->prepare("UPDATE product_images SET is_primary = 0 WHERE product_id = :pid");
        $unset->execute([':pid' => $productId]);

        $set = $this->db->prepare("UPDATE product_images SET is_primary = 1 WHERE id = :id AND product_id = :pid");
        return $set->execute([':id' => $imageId, ':pid' => $productId]);
    }

    public function holdProduct(int $id, int $minutes = 60): bool {
        $stmt = $this->db->prepare("UPDATE products SET status = 'booked', hold_expires_at = DATE_ADD(NOW(), INTERVAL :min MINUTE) WHERE id = :id AND status = 'available'");
        $stmt->execute([':id' => $id, ':min' => $minutes]);
        return $stmt->rowCount() > 0;
    }

    public function markAsSold(int $id): bool {
        $stmt = $this->db->prepare("UPDATE products SET status = 'sold', hold_expires_at = NULL WHERE id = :id");
        return $stmt->execute([':id' => $id]);
    }

    public function releaseToAvailable(int $id): bool {
        $stmt = $this->db->prepare("UPDATE products SET status = 'available', hold_expires_at = NULL WHERE id = :id");
        return $stmt->execute([':id' => $id]);
    }

    public function getInventoryStats(): array {
        $this->releaseExpiredHolds();

        $stmt = $this->db->query("SELECT 
            COUNT(*) as total,
            SUM(CASE WHEN status = 'available' THEN 1 ELSE 0 END) as available,
            SUM(CASE WHEN status = 'booked' THEN 1 ELSE 0 END) as booked,
            SUM(CASE WHEN status = 'sold' THEN 1 ELSE 0 END) as sold
        FROM products");
        return $stmt->fetch() ?: ['total' => 0, 'available' => 0, 'booked' => 0, 'sold' => 0];
    }
}
