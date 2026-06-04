<?php
if (($_GET['token'] ?? '') !== 'mudavim2024deploy') {
    die('Yetkisiz erişim.');
}

echo '<pre>';

try {
    define('LARAVEL_START', microtime(true));
    require '/home/mudavimp/mudavimpalamutbuku/vendor/autoload.php';
    echo "✅ autoload OK\n";

    $app = require_once '/home/mudavimp/mudavimpalamutbuku/bootstrap/app.php';
    echo "✅ app bootstrap OK\n";

    $kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
    echo "✅ kernel OK\n";

    $commands = [
        ['migrate', ['--force' => true]],
        ['db:seed', ['--force' => true]],
        ['config:cache', []],
        ['route:cache', []],
        ['view:cache', []],
    ];

    foreach ($commands as [$cmd, $args]) {
        echo "\n▶ php artisan $cmd\n";
        ob_start();
        $status = $kernel->call($cmd, $args);
        $output = ob_get_clean();
        echo $output;
        echo "  Çıkış: $status\n";
    }

    echo "\n✅ Tamamlandı! Bu dosyayı public_html'den SİL.\n";
} catch (\Throwable $e) {
    echo "\n❌ HATA: " . $e->getMessage() . "\n";
    echo "Dosya: " . $e->getFile() . ":" . $e->getLine() . "\n";
}

echo '</pre>';
