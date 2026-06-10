<?php
if (($_GET['token'] ?? '') !== 'mudavim2024deploy') {
    http_response_code(403);
    die('Yetkisiz erişim.');
}

echo '<pre style="font-family:monospace;font-size:13px;padding:20px;background:#111;color:#0f0;">';
echo "=== Müdavim Post-Deploy ===\n\n";

define('LARAVEL_START', microtime(true));

try {
    require '/home/mudavimp/mudavimpalamutbuku/vendor/autoload.php';
    echo "✅ autoload OK\n";

    $app = require_once '/home/mudavimp/mudavimpalamutbuku/bootstrap/app.php';
    echo "✅ bootstrap OK\n";

    $kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
    echo "✅ kernel OK\n\n";

    $commands = [
        ['migrate',      ['--force' => true]],
        ['config:cache', []],
        ['route:cache',  []],
        ['view:cache',   []],
    ];

    foreach ($commands as [$cmd, $args]) {
        echo "▶ php artisan {$cmd}\n";
        ob_start();
        $status = $kernel->call($cmd, $args);
        $out    = ob_get_clean();
        echo $out ?: "  (çıktı yok)\n";
        echo $status === 0 ? "  ✅ OK\n\n" : "  ⚠️ Exit: {$status}\n\n";
    }

    echo "=== TAMAMLANDI ===\n";
    echo sprintf("Süre: %.2fs\n", microtime(true) - LARAVEL_START);

} catch (\Throwable $e) {
    echo "❌ HATA: " . $e->getMessage() . "\n";
    echo "Dosya: " . $e->getFile() . ":" . $e->getLine() . "\n";
}

echo '</pre>';
