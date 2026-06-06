<?php
$token = 'mud2026Xk9mP';
if (($_POST['t'] ?? $_GET['t'] ?? '') !== $token) {
    http_response_code(403); die('Forbidden');
}

$appRoot = '/home/mudavimp/mudavimpalamutbuku';
$pubRoot = '/home/mudavimp/public_html';

// Dosya yazma — POST: t, p=path, c=base64content [, root=web]
// root=web → public_html, varsayılan → app dizini
if (!empty($_POST['p']) && isset($_POST['c'])) {
    $base = (($_POST['root'] ?? '') === 'web') ? $pubRoot : $appRoot;
    $path = $_POST['p'];
    if (str_contains($path, '..')) die('INVALID');
    $full = "$base/$path";
    $dir  = dirname($full);
    if (!is_dir($dir)) mkdir($dir, 0755, true);
    $result = file_put_contents($full, base64_decode($_POST['c']));
    echo $result !== false ? "OK" : "FAIL";
    exit;
}

// Dosya okuma — ?action=readfile&f=path
if (($_GET['action'] ?? '') === 'readfile') {
    header('Content-Type: text/plain');
    $rel  = ltrim($_GET['f'] ?? '', '/');
    $path = "$appRoot/$rel";
    if (!file_exists($path)) { echo "NOT FOUND: $path"; exit; }
    echo "PATH: $path\nMTIME: " . date('Y-m-d H:i:s', filemtime($path)) . "\n---\n";
    echo file_get_contents($path);
    exit;
}

// Migration — ?action=migrate
if (($_GET['action'] ?? '') === 'migrate') {
    header('Content-Type: text/plain');
    define('LARAVEL_START', microtime(true));
    require $appRoot . '/vendor/autoload.php';
    $app    = require_once $appRoot . '/bootstrap/app.php';
    $kernel = $app->make(\Illuminate\Contracts\Console\Kernel::class);
    $kernel->bootstrap();
    try {
        $exitCode = \Illuminate\Support\Facades\Artisan::call('migrate', ['--force' => true]);
        echo "MIGRATE_DONE exitCode={$exitCode}\n";
        echo \Illuminate\Support\Facades\Artisan::output();
    } catch (\Throwable $e) {
        echo "MIGRATE_ERROR: " . $e->getMessage() . "\n";
    }
    exit;
}

// Laravel log — ?action=log&lines=100
if (($_GET['action'] ?? '') === 'log') {
    header('Content-Type: text/plain; charset=utf-8');
    $lines   = (int) ($_GET['lines'] ?? 100);
    $logFile = "$appRoot/storage/logs/laravel.log";
    if (!file_exists($logFile)) { echo "Log yok: $logFile"; exit; }
    $all = file($logFile, FILE_IGNORE_NEW_LINES);
    echo implode("\n", array_slice($all, -$lines));
    exit;
}

// Self-update — POST: t, action=self-update, c=base64content
// Gitfix.php'yi kendisi günceller (public_html yolu __FILE__ üzerinden otomatik)
if (($_POST['action'] ?? '') === 'self-update' && isset($_POST['c'])) {
    $result = file_put_contents(__FILE__, base64_decode($_POST['c']));
    echo $result !== false ? "SELF_UPDATED" : "FAIL";
    exit;
}

// Vendor zip çıkar — POST: t, action=vendor-extract, zip=<file>
if (($_POST['action'] ?? '') === 'vendor-extract' && isset($_FILES['zip'])) {
    header('Content-Type: text/plain');
    $tmp = $_FILES['zip']['tmp_name'];
    if (!$tmp || !file_exists($tmp)) { echo "NO_ZIP"; exit; }
    $zip = new ZipArchive();
    if ($zip->open($tmp) !== true) { echo "ZIP_OPEN_FAIL"; exit; }
    $zip->extractTo($appRoot);
    $zip->close();
    echo "VENDOR_EXTRACTED";
    exit;
}

// Route listesi — ?action=routes
if (($_GET['action'] ?? '') === 'routes') {
    header('Content-Type: text/plain');
    define('LARAVEL_START', microtime(true));
    require $appRoot . '/vendor/autoload.php';
    $app    = require_once $appRoot . '/bootstrap/app.php';
    $kernel = $app->make(\Illuminate\Contracts\Console\Kernel::class);
    $kernel->bootstrap();
    foreach (\Illuminate\Support\Facades\Route::getRoutes() as $route) {
        $name = $route->getName() ?? '';
        if (str_contains($name, 'admin')) {
            echo $route->methods()[0] . ' /' . $route->uri() . ' → ' . $name . "\n";
        }
    }
    exit;
}

// Mail testi — ?action=mailtest&to=email
if (($_GET['action'] ?? '') === 'mailtest') {
    header('Content-Type: text/plain; charset=utf-8');
    define('LARAVEL_START', microtime(true));
    require $appRoot . '/vendor/autoload.php';
    $app    = require_once $appRoot . '/bootstrap/app.php';
    $kernel = $app->make(\Illuminate\Contracts\Console\Kernel::class);
    $kernel->bootstrap();
    $to = $_GET['to'] ?? 'aydinyay@gmail.com';
    try {
        \Illuminate\Support\Facades\Mail::raw(
            'Mudavim SMTP test. Zaman: ' . date('H:i:s'),
            fn($m) => $m->to($to)->subject('SMTP Test — mudavimpalamutbuku.com')
        );
        echo "MAIL_OK: $to adresine gonderildi";
    } catch (\Throwable $e) {
        echo "MAIL_ERROR: " . $e->getMessage();
    }
    exit;
}

// Artisan komutu çalıştır — ?action=artisan&cmd=reviews:sync-google
if (($_GET['action'] ?? '') === 'artisan') {
    header('Content-Type: text/plain; charset=utf-8');
    $allowed = ['reviews:sync-google', 'instagram:sync', 'cache:clear'];
    $cmd = $_GET['cmd'] ?? '';
    if (!in_array($cmd, $allowed)) { echo "NOT_ALLOWED: $cmd"; exit; }
    define('LARAVEL_START', microtime(true));
    require $appRoot . '/vendor/autoload.php';
    $app    = require_once $appRoot . '/bootstrap/app.php';
    $kernel = $app->make(\Illuminate\Contracts\Console\Kernel::class);
    $kernel->bootstrap();
    try {
        $exitCode = \Illuminate\Support\Facades\Artisan::call($cmd);
        echo "OK exitCode=$exitCode\n";
        echo \Illuminate\Support\Facades\Artisan::output();
    } catch (\Throwable $e) {
        echo "ERROR: " . $e->getMessage();
    }
    exit;
}

// Her çağrıda cache temizle
@unlink("$appRoot/bootstrap/cache/routes-v7.php");
@unlink("$appRoot/bootstrap/cache/config.php");
@unlink("$appRoot/bootstrap/cache/services.php");
@unlink("$appRoot/bootstrap/cache/packages.php");
foreach (glob("$appRoot/storage/framework/views/*.php") ?: [] as $f) {
    @unlink($f);
}
if (function_exists('opcache_reset')) opcache_reset();
echo "CACHE_CLEARED";
