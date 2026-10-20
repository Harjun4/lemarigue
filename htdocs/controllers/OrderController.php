<?php
/**
 * Order Controller
 * Processes checkout, locks inventory, generates the structured WhatsApp message contract.
 */

require_once __DIR__ . '/../config/constants.php';
require_once __DIR__ . '/../models/Order.php';
require_once __DIR__ . '/../models/Product.php';
require_once __DIR__ . '/../models/Setting.php';

class OrderController {
    private Order $orderModel;
    private Product $productModel;
    private Setting $settingModel;

    public function __construct() {
        $this->orderModel = new Order();
        $this->productModel = new Product();
        $this->settingModel = new Setting();
    }

    /**
     * Renders the receipt-style checkout page.
     */
    public function checkout(): void {
        $holdDuration = (int)$this->settingModel->get('booking_hold_duration_minutes', '60');
        $couriers = ['JNE Regular', 'J&T Express', 'SiCepat', 'AnterAja', 'POS Indonesia'];

        require_once ROOT_PATH . '/views/storefront/checkout.php';
    }

    /**
     * Handles checkout form submission.
     */
    public function process(): void {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            redirect('checkout');
        }

        $customerName = trim($_POST['customer_name'] ?? '');
        $customerPhone = trim($_POST['customer_phone'] ?? '');
        $shippingAddress = trim($_POST['shipping_address'] ?? '');
        $courier = trim($_POST['courier'] ?? '');
        $notes = trim($_POST['notes'] ?? '');
        $rawItemIds = $_POST['item_ids'] ?? [];

        // Validation
        if (empty($customerName) || empty($customerPhone) || empty($shippingAddress) || empty($courier)) {
            set_flash('error', 'Mohon lengkapi seluruh kolom formulir pengiriman.');
            redirect('checkout');
        }

        if (is_string($rawItemIds)) {
            $rawItemIds = json_decode($rawItemIds, true) ?: [];
        }

        $itemIds = array_map('intval', array_filter($rawItemIds, 'is_numeric'));
        if (empty($itemIds)) {
            set_flash('error', 'Keranjang belanja Anda kosong atau item tidak valid.');
            redirect('checkout');
        }

        // Standardize recipient phone
        $adminPhone = $this->settingModel->get('whatsapp_number', '6281234567890');
        $adminPhoneClean = preg_replace('/[^0-9]/', '', $adminPhone);
        if (str_starts_with($adminPhoneClean, '0')) {
            $adminPhoneClean = '62' . substr($adminPhoneClean, 1);
        }

        $holdMinutes = (int)$this->settingModel->get('booking_hold_duration_minutes', '60');

        try {
            $created = $this->orderModel->create([
                'customer_name' => $customerName,
                'customer_phone' => $customerPhone,
                'shipping_address' => $shippingAddress . ($notes ? "\nCatatan: " . $notes : ""),
                'courier' => $courier,
                'whatsapp_message' => '',
            ], $itemIds, $holdMinutes);

            if (!$created) {
                set_flash('error', 'Maaf, salah satu atau beberapa barang pesanan baru saja dipesan oleh pembeli lain.');
                redirect('checkout');
            }

            // Build structured WhatsApp contract message
            $orderCode = $created['order_code'];
            $items = $created['items'];
            $subtotal = $created['total_amount'];

            $itemsText = "";
            $idx = 1;
            foreach ($items as $item) {
                $product = $this->productModel->getById($item['product_id']);
                $size = $product ? $product['size'] : '';
                $itemsText .= "{$idx}. {$item['name']} - Ukuran {$size} (" . format_rupiah($item['price']) . ")\n";
                $idx++;
            }

            $waMessage = "Halo Min, saya mau order barang ini:\n\n"
                       . "Kode Order: {$orderCode}\n"
                       . "Nama: {$customerName}\n"
                       . "No. HP: {$customerPhone}\n"
                       . "Alamat: {$shippingAddress}\n\n"
                       . "Barang:\n"
                       . trim($itemsText) . "\n\n"
                       . "Subtotal: " . format_rupiah($subtotal) . "\n"
                       . "Ekspedisi: {$courier}\n";

            if (!empty($notes)) {
                $waMessage .= "Catatan: {$notes}\n";
            }

            $waMessage .= "\nMohon total ongkir dan rekening pembayarannya ya min. Terima kasih!";

            // Update order record with generated message
            $db = Database::getConnection();
            $up = $db->prepare("UPDATE orders SET whatsapp_message = :msg WHERE id = :id");
            $up->execute([':msg' => $waMessage, ':id' => $created['id']]);

            // Save order code to session and redirect to confirmation receipt
            $_SESSION['recent_order'] = [
                'order_code' => $orderCode,
                'wa_url' => "https://wa.me/{$adminPhoneClean}?text=" . rawurlencode($waMessage),
                'wa_message' => $waMessage,
            ];

            redirect('order/success/' . urlencode($orderCode));
        } catch (Exception $e) {
            set_flash('error', 'Terjadi kendala saat memproses pesanan: ' . $e->getMessage());
            redirect('checkout');
        }
    }

    /**
     * Renders order success receipt with WhatsApp link and instructions.
     */
    public function success(string $orderCode): void {
        $order = $this->orderModel->getByCode($orderCode);
        if (!$order) {
            redirect('');
        }

        $adminPhone = $this->settingModel->get('whatsapp_number', '6281345678');
        $adminPhoneClean = preg_replace('/[^0-9]/', '', $adminPhone);
        if (str_starts_with($adminPhoneClean, '0')) {
            $adminPhoneClean = '62' . substr($adminPhoneClean, 1);
        }

        $bankDetails = $this->settingModel->get('bank_payment_details', '');
        $holdDuration = $this->settingModel->get('booking_hold_duration_minutes', '60');
        $waMessage = $order['whatsapp_message'] ?? '';
        $waUrl = "https://wa.me/{$adminPhoneClean}?text=" . rawurlencode($waMessage);

        require_once ROOT_PATH . '/views/storefront/success.php';
    }
}
