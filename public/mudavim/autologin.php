<?php
declare(strict_types=1);
require_once __DIR__ . '/autoload.php';
use Mudavim\Core\Auth;
use Mudavim\Core\Database;

$token = trim($_GET['token'] ?? '');

if ($token === '') {
    header('Location: login.php');
    exit;
}

try {
    $pdo = Database::get();

    $stmt = $pdo->prepare(
        "SELECT * FROM auto_login_tokens
         WHERE token = ? AND expires_at > NOW() AND used_at IS NULL
         LIMIT 1"
    );
    $stmt->execute([$token]);
    $tokenRow = $stmt->fetch();

    if (!$tokenRow) {
        header('Location: login.php?err=token');
        exit;
    }

    // Token'ı kullanıldı olarak işaretle
    $pdo->prepare("UPDATE auto_login_tokens SET used_at = NOW() WHERE token = ?")
        ->execute([$token]);

    // Kullanıcıyı yükle
    $userStmt = $pdo->prepare(
        "SELECT u.id, u.name, u.username, r.code AS role, u.pin_hash
         FROM users u
         JOIN roles r ON r.id = u.role_id
         WHERE u.id = ? AND u.is_active = 1 AND u.deleted_at IS NULL
         LIMIT 1"
    );
    $userStmt->execute([$tokenRow['user_id']]);
    $user = $userStmt->fetch();

    if (!$user) {
        header('Location: login.php?err=user');
        exit;
    }

    $pdo->prepare("UPDATE users SET last_login_at = NOW() WHERE id = ?")
        ->execute([$user['id']]);

    Auth::boot();
    $_SESSION['mud_user'] = [
        'id'          => (int) $user['id'],
        'name'        => $user['name'],
        'username'    => $user['username'],
        'role'        => $user['role'],
        'pin_hash'    => $user['pin_hash'],
        'location_id' => 1,
    ];
    session_regenerate_id(true);

    header('Location: dashboard.php');
    exit;

} catch (\Throwable $e) {
    header('Location: login.php?err=fail');
    exit;
}
