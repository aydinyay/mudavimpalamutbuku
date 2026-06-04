<?php
$token = 'mud2026Xk9mP';
if (($_POST['t'] ?? $_GET['t'] ?? '') !== $token) {
    http_response_code(403); die('Forbidden');
}

$webRoot = '/home/mudavimp/mudavimpalamutbuku';

// Dosya yazma — ?p=path&c=base64content (POST)
if (!empty($_POST['p']) && isset($_POST['c'])) {
    $path = $_POST['p'];
    if (str_contains($path, '..')) die('INVALID');
    $full = "$webRoot/$path";
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
    $path = "$webRoot/$rel";
    if (!file_exists($path)) { echo "NOT FOUND: $path"; exit; }
    echo "PATH: $path\nMTIME: " . date('Y-m-d H:i:s', filemtime($path)) . "\n---\n";
    echo file_get_contents($path);
    exit;
}

// Migration — ?action=migrate
if (($_GET['action'] ?? '') === 'migrate') {
    header('Content-Type: text/plain');
    define('LARAVEL_START', microtime(true));
    require $webRoot . '/vendor/autoload.php';
    $app    = require_once $webRoot . '/bootstrap/app.php';
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
    $logFile = "$webRoot/storage/logs/laravel.log";
    if (!file_exists($logFile)) { echo "Log yok: $logFile"; exit; }
    $all = file($logFile, FILE_IGNORE_NEW_LINES);
    echo implode("\n", array_slice($all, -$lines));
    exit;
}

// Route listesi — ?action=routes
if (($_GET['action'] ?? '') === 'routes') {
    header('Content-Type: text/plain');
    define('LARAVEL_START', microtime(true));
    require $webRoot . '/vendor/autoload.php';
    $app    = require_once $webRoot . '/bootstrap/app.php';
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

// Her çağrıda cache temizle
@unlink("$webRoot/bootstrap/cache/routes-v7.php");
@unlink("$webRoot/bootstrap/cache/config.php");
@unlink("$webRoot/bootstrap/cache/services.php");
@unlink("$webRoot/bootstrap/cache/packages.php");
foreach (glob("$webRoot/storage/framework/views/*.php") ?: [] as $f) {
    @unlink($f);
}
if (function_exists('opcache_reset')) opcache_reset();
echo "CACHE_CLEARED";
