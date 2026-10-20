<?php
/**
 * Setting Entity Model
 * Reads and updates key-value store configurations.
 */

require_once __DIR__ . '/../config/database.php';

class Setting {
    private PDO $db;
    private static array $cache = [];

    public function __construct() {
        $this->db = Database::getConnection();
    }

    public function get(string $key, string $default = ''): string {
        if (isset(self::$cache[$key])) {
            return self::$cache[$key];
        }

        $stmt = $this->db->prepare("SELECT setting_value FROM settings WHERE setting_key = :k LIMIT 1");
        $stmt->execute([':k' => $key]);
        $row = $stmt->fetch();
        $val = $row ? (string)$row['setting_value'] : $default;
        self::$cache[$key] = $val;
        return $val;
    }

    public function set(string $key, string $value): bool {
        self::$cache[$key] = $value;
        $stmt = $this->db->prepare("INSERT INTO settings (setting_key, setting_value) VALUES (:k, :v)
                                    ON DUPLICATE KEY UPDATE setting_value = :v2");
        return $stmt->execute([':k' => $key, ':v' => $value, ':v2' => $value]);
    }

    public function getAll(): array {
        $stmt = $this->db->query("SELECT setting_key, setting_value FROM settings");
        $results = [];
        while ($row = $stmt->fetch()) {
            $results[$row['setting_key']] = $row['setting_value'];
            self::$cache[$row['setting_key']] = $row['setting_value'];
        }
        return $results;
    }
}
