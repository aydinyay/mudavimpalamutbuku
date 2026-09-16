<?php
declare(strict_types=1);
namespace Mudavim\Core;

class Auth
{
    public static function boot(): void
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_name('mud_sess');
            session_set_cookie_params([
                'lifetime' => 0,
                'path'     => '/',
                'secure'   => isset($_SERVER['HTTPS']),
                'httponly' => true,
                'samesite' => 'Lax',
            ]);
            session_start();
        }
    }

    public static function check(): bool
    {
        self::boot();
        return isset($_SESSION['mud_user']['id']);
    }

    public static function user(): ?array
    {
        self::boot();
        return $_SESSION['mud_user'] ?? null;
    }

    public static function id(): ?int
    {
        return self::user()['id'] ?? null;
    }

    public static function role(): string
    {
        return self::user()['role'] ?? 'guest';
    }

    public static function locationId(): int
    {
        return (int) (self::user()['location_id'] ?? 1);
    }

    public static function attempt(string $username, string $password, \PDO $pdo): bool
    {
        $stmt = $pdo->prepare("
            SELECT u.id, u.name, u.username, u.password_hash, u.is_active,
                   r.code AS role, u.pin_hash, 1 AS location_id
            FROM users u
            JOIN roles r ON r.id = u.role_id
            WHERE u.username = ? AND u.deleted_at IS NULL
            LIMIT 1
        ");
        $stmt->execute([$username]);
        $row = $stmt->fetch(\PDO::FETCH_ASSOC);

        if (!$row || !$row['is_active']) return false;
        if (!password_verify($password, $row['password_hash'])) return false;

        // Son giriş güncelle
        $pdo->prepare("UPDATE users SET last_login_at = NOW() WHERE id = ?")
            ->execute([$row['id']]);

        self::boot();
        $_SESSION['mud_user'] = [
            'id'          => (int) $row['id'],
            'name'        => $row['name'],
            'username'    => $row['username'],
            'role'        => $row['role'],
            'pin_hash'    => $row['pin_hash'],
            'location_id' => (int) $row['location_id'],
        ];
        session_regenerate_id(true);
        return true;
    }

    public static function logout(): void
    {
        self::boot();
        $_SESSION = [];
        if (ini_get('session.use_cookies')) {
            $p = session_get_cookie_params();
            setcookie(session_name(), '', time() - 42000,
                $p['path'], $p['domain'], $p['secure'], $p['httponly']);
        }
        session_destroy();
    }

    public static function requireLogin(string $redirectTo = 'login.php'): void
    {
        if (!self::check()) {
            header('Location: ' . $redirectTo);
            exit;
        }
    }

    public static function verifyPin(string $pin): bool
    {
        $hash = self::user()['pin_hash'] ?? null;
        if (!$hash) return false;
        return password_verify($pin, $hash);
    }
}
