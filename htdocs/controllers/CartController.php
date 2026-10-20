<?php
/**
 * Cart Controller
 * Validates availability and current pricing of cart items stored in browser LocalStorage.
 */

require_once __DIR__ . '/../config/constants.php';
require_once __DIR__ . '/../models/Product.php';

class CartController {
    private Product $productModel;

    public function __construct() {
        $this->productModel = new Product();
    }

    /**
     * Endpoint /api/cart/validate: accepts JSON array of product IDs and returns current statuses.
     */
    public function validate(): void {
        header('Content-Type: application/json; charset=utf-8');

        $input = json_decode(file_get_contents('php://input'), true);
        $ids = $input['ids'] ?? [];

        if (!is_array($ids) || empty($ids)) {
            echo json_encode([
                'success' => true,
                'items' => [],
                'unavailable_count' => 0,
                'subtotal' => 0,
                'subtotal_formatted' => format_rupiah(0),
            ]);
            return;
        }

        $cleanIds = array_map('intval', array_filter($ids, 'is_numeric'));
        if (empty($cleanIds)) {
            echo json_encode(['success' => true, 'items' => [], 'unavailable_count' => 0, 'subtotal' => 0, 'subtotal_formatted' => format_rupiah(0)]);
            return;
        }

        $items = [];
        $unavailableCount = 0;
        $subtotal = 0;

        foreach ($cleanIds as $id) {
            $product = $this->productModel->getById($id);
            if (!$product) {
                $unavailableCount++;
                continue;
            }

            $effectivePrice = !empty($product['discount_price']) ? (float)$product['discount_price'] : (float)$product['price'];
            $isAvailable = ($product['status'] === 'available');

            if (!$isAvailable) {
                $unavailableCount++;
            } else {
                $subtotal += $effectivePrice;
            }

            $primaryImg = 'assets/images/placeholder.svg';
            if (!empty($product['images'][0]['image_path'])) {
                $primaryImg = $product['images'][0]['image_path'];
            }

            $items[] = [
                'id' => (int)$product['id'],
                'name' => $product['name'],
                'slug' => $product['slug'],
                'size' => $product['size'],
                'condition_grade' => $product['condition_grade'],
                'price' => (float)$product['price'],
                'discount_price' => $product['discount_price'] ? (float)$product['discount_price'] : null,
                'effective_price' => $effectivePrice,
                'price_formatted' => format_rupiah($effectivePrice),
                'status' => $product['status'],
                'is_available' => $isAvailable,
                'image' => asset($primaryImg),
            ];
        }

        echo json_encode([
            'success' => true,
            'items' => $items,
            'unavailable_count' => $unavailableCount,
            'subtotal' => $subtotal,
            'subtotal_formatted' => format_rupiah($subtotal),
        ]);
    }
}
