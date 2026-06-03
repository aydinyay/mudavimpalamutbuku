<?php
// Güvenlik token kontrolü
if (($_GET['token'] ?? '') !== 'mudavim2024deploy') {
    http_response_code(403);
    die('Yetkisiz erişim.');
}

chdir('/home/mudavimp/mudavimpalamutbuku');

echo '<pre style="font-family:monospace;padding:20px;">';
echo "=== Müdavim Deploy Script ===\n\n";

echo ">> php artisan migrate --force\n";
echo shell_exec('php artisan migrate --force 2>&1');

echo "\n>> php artisan db:seed --force\n";
echo shell_exec('php artisan db:seed --force 2>&1');

echo "\n>> php artisan config:cache\n";
echo shell_exec('php artisan config:cache 2>&1');

echo "\n>> php artisan route:cache\n";
echo shell_exec('php artisan route:cache 2>&1');

echo "\n>> php artisan view:cache\n";
echo shell_exec('php artisan view:cache 2>&1');

echo "\n>> php artisan storage:link\n";
echo shell_exec('php artisan storage:link 2>&1');

echo "\n\n=== TAMAMLANDI ===\n";
echo '</pre>';

// Script kendini siler
unlink(__FILE__);
echo '<p style="color:green;font-weight:bold;">Script silindi. Bu sayfayı kapatabilirsiniz.</p>';
