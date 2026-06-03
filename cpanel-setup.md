# cPanel Kurulum Rehberi — Müdavim Şef Restaurant

## 1. Klasör Yapısı

cPanel File Manager'da aşağıdaki yapıyı oluşturun:

```
/home/KULLANICI/
├── public_html/           ← Web root (Apache buraya bakıyor)
│   ├── index.php          ← Değiştirilmiş versiyonu buraya koyun (bakınız Adım 3)
│   ├── .htaccess          ← public/.htaccess'i buraya kopyalayın
│   ├── build/             ← npm run build çıktısı
│   ├── images/            ← Restoran fotoğrafları
│   └── qr-codes/          ← Üretilen QR PNG'leri
│
└── mudavimpalamutbuku/    ← Laravel uygulaması (web root DIŞINDA!)
    ├── app/, config/, ...
    ├── storage/           ← 775 yazma izni verilmeli
    └── .env               ← Asla public_html içinde olmamalı!
```

## 2. Dosyaları FTP ile Yükleyin

1. Tüm proje dosyalarını `/home/KULLANICI/mudavimpalamutbuku/` dizinine yükleyin
   - `public/` klasörü hariç — onları aşağıda ayrıca handle edeceğiz
   - `vendor/` klasörünü yüklemeyin! Composer ile kurun (Adım 4)

2. `public/` içindeki dosyaları `/home/KULLANICI/public_html/` içine kopyalayın:
   - `public/.htaccess` → `public_html/.htaccess`
   - `public/build/` → `public_html/build/`
   - `public/images/` → `public_html/images/`
   - `public/qr-codes/` → `public_html/qr-codes/`

## 3. index.php'yi Düzenleyin

`public_html/index.php` dosyasını şöyle değiştirin:

```php
// Orijinal (1. satır):
$app = require_once __DIR__.'/../bootstrap/app.php';

// Yeni (KULLANICI'yı kendi cPanel kullanıcı adınızla değiştirin):
$app = require_once '/home/KULLANICI/mudavimpalamutbuku/bootstrap/app.php';
```

## 4. Composer Bağımlılıkları

cPanel'in Terminal'i varsa (bazı hosting'lerde bulunur):
```bash
cd ~/mudavimpalamutbuku
composer install --no-dev --optimize-autoloader
```

Terminal yoksa: Yerel makinenizde `vendor/` klasörünü derleyip FTP ile yükleyin.

## 5. .env Dosyası

`/home/KULLANICI/mudavimpalamutbuku/.env` oluşturun:

```ini
APP_NAME="Müdavim Şef Restaurant"
APP_ENV=production
APP_KEY=base64:BURAYA_ANAHTAR_GELECEk
APP_DEBUG=false
APP_URL=https://siteniz.com

APP_LOCALE=tr
APP_FALLBACK_LOCALE=tr

DB_CONNECTION=mysql
DB_HOST=localhost
DB_PORT=3306
DB_DATABASE=KULLANICI_mudavim
DB_USERNAME=KULLANICI_dbuser
DB_PASSWORD=SIFRENIZ

SESSION_DRIVER=file
CACHE_STORE=file
QUEUE_CONNECTION=database
FILESYSTEM_DISK=public

RESTAURANT_SEASON_OPEN=05-01
RESTAURANT_SEASON_CLOSE=10-31
```

## 6. APP_KEY Üretme

Yerel makinenizde:
```bash
php artisan key:generate --show
```
Çıkan `base64:...` değerini `.env`'deki `APP_KEY`'e yapıştırın.

## 7. Storage Symlink

SSH yoksa bu script'i geçici olarak `public_html/` içine yükleyin:

```php
<?php
// symlink_create.php — çalıştırın ve silin!
$target = '/home/KULLANICI/mudavimpalamutbuku/storage/app/public';
$link   = __DIR__ . '/storage';
if (!file_exists($link)) {
    symlink($target, $link);
    echo 'Symlink oluşturuldu!';
} else {
    echo 'Zaten var.';
}
```

Tarayıcıdan açın: `https://siteniz.com/symlink_create.php`
Sonra bu dosyayı silin!

## 8. Migration Çalıştırma (post_deploy.php)

SSH yoksa bu script'i `public_html/` içine yükleyin:

```php
<?php
// post_deploy.php — çalıştırın ve silin!
if ($_GET['secret'] !== 'GIZLI_TOKEN') { http_response_code(403); die('Unauthorized'); }

chdir('/home/KULLANICI/mudavimpalamutbuku');
echo '<pre>';
echo shell_exec('php artisan migrate --force');
echo shell_exec('php artisan db:seed --force');
echo shell_exec('php artisan config:cache');
echo shell_exec('php artisan route:cache');
echo shell_exec('php artisan view:cache');
echo '</pre>';
unlink(__FILE__);
echo 'Tamamlandı ve script silindi.';
```

Açın: `https://siteniz.com/post_deploy.php?secret=GIZLI_TOKEN`

## 9. Storage İzinleri

cPanel File Manager → `mudavimpalamutbuku/storage/` → Sağ tık → Change Permissions:
- `storage/` ve tüm alt klasörlere **755** veya **775** izin verin

## 10. cPanel Cron Job

cPanel → Cron Jobs → Yeni Cron:
```
*/5 * * * * /usr/local/bin/php /home/KULLANICI/mudavimpalamutbuku/artisan schedule:run >> /dev/null 2>&1
```

## 11. Kontrol

1. `https://siteniz.com/` → Ana sayfa açılmalı
2. `https://siteniz.com/menu` → QR Menü görünmeli
3. `https://siteniz.com/login` → Admin giriş
4. `https://siteniz.com/yonetim` → Yönetim paneli (admin@mudavim.com / mudavim2024!)

## 12. İlk Yapılandırma (Güvenlik!)

Admin paneline girdikten sonra **mutlaka** şifreyi değiştirin:
`/yonetim/ayarlar` → Şifre değiştirme
