<?php
/**
 * Database Connection via PDO
 * Handles MySQL (Primary for InfinityFree) & SQLite Fallback
 */

class Database {
    private static ?PDO $instance = null;

    public static function getConnection(): PDO {
        if (self::$instance !== null) {
            return self::$instance;
        }

        // Allow overriding via config file if present
        $customConfig = __DIR__ . '/db_config.php';
        if (file_exists($customConfig)) {
            $cfg = require $customConfig;
        } else {
            $cfg = [];
        }

        $host = $cfg['host'] ?? getenv('DB_HOST') ?: '127.0.0.1';
        $port = $cfg['port'] ?? getenv('DB_PORT') ?: '3306';
        $dbname = $cfg['dbname'] ?? getenv('DB_NAME') ?: 'isi_lemarigue';
        $user = $cfg['user'] ?? getenv('DB_USER') ?: 'root';
        $pass = $cfg['pass'] ?? (getenv('DB_PASS') !== false ? getenv('DB_PASS') : '');
        $driver = $cfg['driver'] ?? getenv('DB_DRIVER') ?: 'mysql';

        if ($driver === 'mysql') {
            try {
                // Try connecting directly to the database
                $dsn = "mysql:host={$host};port={$port};dbname={$dbname};charset=utf8mb4";
                $options = [
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                    PDO::ATTR_EMULATE_PREPARES => false,
                ];
                self::$instance = new PDO($dsn, $user, $pass, $options);
            } catch (PDOException $e) {
                // If database doesn't exist yet, attempt to connect to server and create it
                if (str_contains($e->getMessage(), 'Unknown database') || str_contains($e->getMessage(), '1049')) {
                    try {
                        $serverDsn = "mysql:host={$host};port={$port};charset=utf8mb4";
                        $serverPdo = new PDO($serverDsn, $user, $pass, [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);
                        $serverPdo->exec("CREATE DATABASE IF NOT EXISTS `{$dbname}` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
                        unset($serverPdo);

                        self::$instance = new PDO($dsn, $user, $pass, $options);
                        self::initializeSchema(self::$instance);
                    } catch (Exception) {
                        throw $e;
                    }
                } else {
                    throw $e;
                }
            }

            // Verify if tables exist; if not, initialize them from schema.sql
            self::checkAndInitialize(self::$instance);
            return self::$instance;
        }

        // SQLite fallback option
        $sqliteFile = ROOT_PATH . '/database.sqlite';
        $isNew = !file_exists($sqliteFile);
        self::$instance = new PDO("sqlite:" . $sqliteFile, null, null, [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        ]);
        if ($isNew) {
            self::initializeSchema(self::$instance);
        }
        return self::$instance;
    }

    private static function checkAndInitialize(PDO $pdo): void {
        try {
            $check = $pdo->query("SHOW TABLES LIKE 'products'")->fetch();
            if (!$check) {
                self::initializeSchema($pdo);
            }
        } catch (Exception) {
            // Ignore check error
        }
    }

    private static function initializeSchema(PDO $pdo): void {
        $schemaFile = ROOT_PATH . '/schema.sql';
        if (file_exists($schemaFile)) {
            $sql = file_get_contents($schemaFile);
            if ($sql) {
                // Remove MySQL specific session settings if running under SQLite
                $isSqlite = $pdo->getAttribute(PDO::ATTR_DRIVER_NAME) === 'sqlite';
                if ($isSqlite) {
                    $sql = preg_replace('/SET FOREIGN_KEY_CHECKS\s*=\s*[01];/i', '', $sql);
                    $sql = preg_replace('/ENGINE=InnoDB/i', '', $sql);
                    $sql = preg_replace('/DEFAULT CHARSET=utf8mb4/i', '', $sql);
                    $sql = preg_replace('/COLLATE=utf8mb4_unicode_ci/i', '', $sql);
                }
                $pdo->exec($sql);
            }
        }
    }
}
