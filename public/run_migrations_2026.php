<?php
if (($_GET['k'] ?? '') !== 'mud9x2026') { http_response_code(403); die('Yetkisiz.'); }

echo '<pre style="background:#111;color:#0f0;padding:20px;font-family:monospace;font-size:13px;">';
echo "=== Migration Runner ===\n\n";
define('LARAVEL_START', microtime(true));
try {
    require '/home/mudavimp/mudavimpalamutbuku/vendor/autoload.php';
    echo "autoload OK\n";
    $app    = require '/home/mudavimp/mudavimpalamutbuku/bootstrap/app.php';
    $kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
    echo "kernel OK\n\n";

    foreach (['migrate --force', 'config:cache', 'route:cache', 'view:cache'] as $cmd) {
        echo "▶ $cmd\n";
        $status = $kernel->call(explode(' ', $cmd)[0], ['--force' => true]);
        echo "  exit: $status\n\n";
    }
    echo "=== TAMAM === (" . round(microtime(true) - LARAVEL_START, 2) . "s)\n";
} catch (\Throwable $e) {
    echo "HATA: " . $e->getMessage() . "\n" . $e->getFile() . ":" . $e->getLine() . "\n";
}
echo '</pre>';
