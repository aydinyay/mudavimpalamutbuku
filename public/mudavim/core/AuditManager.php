<?php
declare(strict_types=1);

namespace Mudavim\Core;

/**
 * Her tablo değişikliğini audit_log'a kaydeder.
 * Hiçbir veri kalıcı silinmez; soft delete + audit zorunlu.
 */
final class AuditManager
{
    private static ?int $currentUserId = null;
    private static ?string $currentIp  = null;

    public static function init(int $userId, string $ip): void
    {
        self::$currentUserId = $userId;
        self::$currentIp     = $ip;
    }

    public static function log(
        string $table,
        int    $recordId,
        string $action,
        ?array $oldData = null,
        ?array $newData = null
    ): void {
        $pdo = Database::get();
        $stmt = $pdo->prepare("
            INSERT INTO audit_log
                (table_name, record_id, action, old_data, new_data, user_id, ip_address, user_agent)
            VALUES
                (:table, :rid, :action, :old, :new, :uid, :ip, :ua)
        ");
        $stmt->execute([
            ':table'  => $table,
            ':rid'    => $recordId,
            ':action' => $action,
            ':old'    => $oldData ? json_encode($oldData, JSON_UNESCAPED_UNICODE) : null,
            ':new'    => $newData ? json_encode($newData, JSON_UNESCAPED_UNICODE) : null,
            ':uid'    => self::$currentUserId,
            ':ip'     => self::$currentIp ?? ($_SERVER['REMOTE_ADDR'] ?? null),
            ':ua'     => $_SERVER['HTTP_USER_AGENT'] ?? null,
        ]);
    }

    /**
     * Soft delete — deleted_at ve deleted_by set eder, audit yazar.
     */
    public static function softDelete(string $table, int $id): bool
    {
        $pdo   = Database::get();
        $fetch = $pdo->prepare("SELECT * FROM `{$table}` WHERE id = ?");
        $fetch->execute([$id]);
        $old = $fetch->fetch();

        if (!$old || !is_null($old['deleted_at'] ?? null)) {
            return false; // Zaten silinmiş
        }

        $stmt = $pdo->prepare("
            UPDATE `{$table}` SET deleted_at = NOW(), deleted_by = ? WHERE id = ?
        ");
        $stmt->execute([self::$currentUserId, $id]);

        self::log($table, $id, 'DELETE', $old);
        return true;
    }
}
