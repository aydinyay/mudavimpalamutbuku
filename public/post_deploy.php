<?php
// Güvenlik tokeni — çalıştırdıktan sonra bu dosyayı SİL
if (($_GET['token'] ?? '') !== 'mudavim2024deploy') {
    die('Yetkisiz erişim.');
}

define('LARAVEL_START', microtime(true));
require '/home/mudavimp/mudavimpalamutbuku/vendor/autoload.php';

$app = require_once '/home/mudavimp/mudavimpalamutbuku/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);

$commands = [
    'migrate --force',
    'db:seed --force',
    'config:cache',
    'route:cache',
    'view:cache',
];

echo '<pre>';
foreach ($commands as $cmd) {
    echo "\n▶ php artisan $cmd\n";
    $status = $kernel->call($cmd);
    echo "  Çıkış kodu: $status\n";
}
echo "\n✅ Tamamlandı. Bu dosyayı public_html'den SİL!\n";
echo '</pre>';
