<?php
declare(strict_types=1);
require_once __DIR__ . '/autoload.php';

use Mudavim\Core\Auth;
use Mudavim\Core\Database;

Auth::boot();

// Zaten girişliyse dashboard'a yönlendir
if (Auth::check()) {
    header('Location: dashboard.php');
    exit;
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';

    if ($username && $password) {
        try {
            $pdo = Database::get();
            if (Auth::attempt($username, $password, $pdo)) {
                header('Location: dashboard.php');
                exit;
            }
            $error = 'Kullanıcı adı veya şifre hatalı.';
        } catch (\PDOException $e) {
            $error = 'Veritabanı bağlantısı kurulamadı. config/database.php dosyasını kontrol edin.';
        }
    } else {
        $error = 'Kullanıcı adı ve şifre zorunlu.';
    }
}
?><!DOCTYPE html>
<html lang="tr">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<title>Müdavim — Giriş</title>
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
<style>
  body {
    background: #1a2332;
    min-height: 100vh;
    display: flex;
    align-items: center;
    justify-content: center;
  }
  .login-card {
    width: 100%;
    max-width: 380px;
    background: #fff;
    border-radius: 1rem;
    padding: 2.25rem 2rem;
    box-shadow: 0 8px 32px rgba(0,0,0,.35);
  }
  .login-logo { font-size: 2.5rem; margin-bottom: .25rem; }
  .btn-login {
    background: #0d6efd;
    color: #fff;
    border: none;
    min-height: 52px;
    font-size: 1.05rem;
    font-weight: 700;
    border-radius: .6rem;
    width: 100%;
    margin-top: .5rem;
    transition: background .15s;
  }
  .btn-login:hover { background: #0b5ed7; }
  .form-control { min-height: 48px; border-radius: .5rem; }
  .form-label { font-weight: 600; font-size: .88rem; color: #495057; }
</style>
</head>
<body>
<div class="login-card">
  <div class="text-center mb-3">
    <div class="login-logo">🐟</div>
    <h4 class="fw-bold mb-0">Müdavim</h4>
    <p class="text-muted small mb-0">Palamutbükü Restoran Yönetimi</p>
  </div>

  <?php if ($error): ?>
  <div class="alert alert-danger py-2 small"><?= htmlspecialchars($error) ?></div>
  <?php endif; ?>

  <form method="POST" action="login.php" autocomplete="on">
    <div class="mb-3">
      <label class="form-label">Kullanıcı Adı</label>
      <input type="text" class="form-control" name="username"
             value="<?= htmlspecialchars($_POST['username'] ?? '') ?>"
             autocomplete="username" autofocus required>
    </div>
    <div class="mb-3">
      <label class="form-label">Şifre</label>
      <input type="password" class="form-control" name="password"
             autocomplete="current-password" required>
    </div>
    <button type="submit" class="btn-login">Giriş Yap</button>
  </form>

  <p class="text-center text-muted mt-3 mb-0" style="font-size:.72rem">
    v4 · <?= date('Y') ?> · Palamutbükü
  </p>
</div>
</body>
</html>
