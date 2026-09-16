<?php
declare(strict_types=1);

namespace Mudavim\Core;

use PDO;
use PDOException;

final class Database
{
    private static ?PDO $instance = null;

    private function __construct() {}
    private function __clone() {}

    public static function get(): PDO
    {
        if (self::$instance === null) {
            $cfg = require __DIR__ . '/../config/database.php';
            $dsn = "mysql:host={$cfg['host']};dbname={$cfg['dbname']};charset=utf8mb4";
            self::$instance = new PDO($dsn, $cfg['user'], $cfg['pass'], [
                PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES   => false,
                PDO::MYSQL_ATTR_FOUND_ROWS   => true,
            ]);
            self::$instance->exec("SET time_zone = '+03:00'");
        }
        return self::$instance;
    }

    /**
     * Transaction wrapper — Be Truly: tüm kritik işlemler transaction içinde.
     * Hata olursa rollback, aksi takdirde commit.
     */
    public static function transaction(callable $fn): mixed
    {
        $pdo    = self::get();
        $nested = $pdo->inTransaction();

        if (!$nested) $pdo->beginTransaction();
        try {
            $result = $fn($pdo);
            if (!$nested) $pdo->commit();
            return $result;
        } catch (\Throwable $e) {
            if (!$nested) $pdo->rollBack();
            throw $e;
        }
    }
}
