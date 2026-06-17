# Müdavim Şef Restaurant — Claude Hafıza Dosyası

## Proje Özeti
Datça/Palamutbükü'nde denize sıfır konumda hizmet veren **Müdavim Şef Restaurant** için Laravel 11 modüler yönetim sistemi.

- **Canlı site:** https://mudavimpalamutbuku.com
- **Admin panel:** https://mudavimpalamutbuku.com/yonetim
- **Stok sistemi:** https://mudavimpalamutbuku.com/mudavim/ (AYRI sistem, bu repoda değil)
- **Repo:** aydinyay/mudavimpalamutbuku
- **Deploy branch:** main → GitHub Actions FTP → cPanel

---

## KRİTİK KURALLAR

1. **SSH YOK, terminal YOK** — Hosting hayalhost.com cPanel paylaşımlı. shell_exec da KAPALI.
2. **Asla tahmini fiyat veya veri kullanma** — Fiyatlar kolaybu.com'dan doğrulanır.
3. **Deploy = GitHub Actions FTP** — main'e push → otomatik deploy.
4. **Seeder otomatik çalışmaz** — `post_deploy.php` elle tetiklenir.
5. **Push öncesi kullanıcıdan onay al.**

---

## Hosting & Deploy

```
Hosting: hayalhost.com cPanel (SSH YOK, terminal YOK, shell_exec KAPALI)
FTP: deploy@mudavimpalamutbuku.com
App dizini: /home/mudavimp/mudavimpalamutbuku/
Web root: /home/mudavimp/public_html/
Deploy: GitHub Actions → SamKirkland/FTP-Deploy-Action@v4.3.5
```

**post_deploy.php:**
- `public_html/post_deploy.php?token=mudavim2024deploy` adresinden çalıştırılır
- migrate + seed + config:cache + route:cache + view:cache yapar
- Çalıştırdıktan sonra dosya silinmeli

**.env (production) özeti:**
```
APP_URL=https://mudavimpalamutbuku.com
DB_DATABASE=mudavimp_mudavimp_db
DB_USERNAME=mudavimp_mudavimp_user
SESSION_DRIVER=file
CACHE_STORE=file
```

---

## Mimari

**Modüler monolit** — `app/Modules/` altında her modül bağımsız.

```
app/Modules/
├── Core/          # RestaurantSetting modeli, paylaşılan altyapı
├── Admin/         # Auth + dashboard, prefix: /yonetim
├── Website/       # Public site (TR varsayılan, /en/, /de/)
├── Menu/          # QR menü + admin menü yönetimi
├── TablePlan/     # Masa ve şezlong layout
├── Reservation/   # Rezervasyon formu + yönetimi
└── QrCode/        # QR üretimi + print
```

**config/modules.php** ile modüller açılıp kapatılır.

---

## URL Yapısı

```
/                    → Türkçe ana sayfa
/hakkimizda          → Hakkımızda
/iletisim            → İletişim
/galeri              → Galeri (boş, fotoğraf bekleniyor)
/en/                 → İngilizce
/de/                 → Almanca
/yonetim             → Admin panel (auth gerekli)
/menu/masa/{code}    → QR menü (mobil-first)
```

---

## Veritabanı

**Tablo sırası:** restaurant_settings → areas → tables/loungers → menu_categories → menu_items → menu_item_translations → menu_item_allergens → reservations → qr_codes

**Önemli alanlar:**
- `reservations.source` → website/admin/phone/walkin/**boat** (tekne ile gelenler)
- `menu_items.is_featured` → ana sayfada gösterilir
- `menu_items.is_available` → AJAX toggle ile değiştirilebilir

---

## Menü Verileri

**MenuSeeder.php** hazır — ~79 ürün, 15 kategori. Fiyatlar kolaybu.com'dan alındı.

Kategoriler: Atıştırmalıklar, Salatalar, Mezeler, Deniz Mezeleri, Ara Sıcaklar, Et Ürünleri, Deniz Ürünleri, Tatlılar, Soğuk İçecekler, Biralar, Rakı, Şarap, İthal İçecekler, Sıcak İçecekler

**Rakı fiyatları:**
- Yeni Rakı: Tek 500₺, Double 600₺, 20cl 1500₺, 35cl 2000₺, 70cl 3000₺
- Efe Gold: 20cl 1700₺, 35cl 2250₺, 70cl 3400₺
- Beylerbeyi Göbek: 20cl 1900₺, 35cl 2400₺, 70cl 3750₺

**Şarap:** Kadeh 650₺, Angora 2100₺, Kav 2500₺, Sarafin SB/CS 3700₺, Sarafin Chardonnay 3900₺, Datça Wineyard Blush 2500₺

---

## Teknik Notlar

**PHP 8.4** — nullable type deprecation için `?string` kullan, `string = null` değil.

**Paketler:**
- `simplesoftwareio/simple-qrcode` → QR PNG üretimi
- `intervention/image` v3 → fotoğraf resize
- `barryvdh/laravel-dompdf` → QR sheet PDF

**JS:** interact.js (drag-drop masa), Sortable.js (kategori sıralama), vanilla JS (AJAX toggle, availability check)

---

## Yapılacaklar (Öncelik Sırası)

### Acil
- [ ] `post_deploy.php` çalıştır → seed veritabanına işlensin
- [ ] Galeri fotoğrafları ekle (`/galeri` sayfası boş)

### Yakın Vade
- [ ] Floating WhatsApp butonu (tüm sayfalarda, +905544427748)
- [ ] Yemek fotoğrafları (MenuItem'a image_path kolonu)
- [ ] "Bugün Ne Var?" bölümü (admin'den güncellenen günlük balık/özel)
- [ ] Rezervasyon formu (3 adım: tarih → masa seç → misafir bilgisi)

### Orta Vade
- [ ] Müşteri geri bildirimi (QR'dan 5 yıldız)
- [ ] Canlı kamera snapshot (10 dakikada bir, XMeye Pro kamera)
- [ ] Google İşletme senkronizasyonu

---

## Restoran Bilgileri

```
Ad: Müdavim Şef Restaurant
Adres: Cumalı Mahallesi, Palamutbükü Sokak No:50, Datça
Telefon: +90 554 442 77 48
WhatsApp: +90 554 442 77 48
Çalışma: 12:00–01:00 (her gün)
Sezon: Mayıs–Ekim
Özellikler: Denize sıfır, ücretsiz WiFi, evcil hayvan dostu, şezlong
Hedef kitle: TR tatilci, Alman turist, tekne ile gelenler
```

---

## Ayrı Sistem: /mudavim/ Stok Uygulaması

`mudavimpalamutbuku.com/mudavim/` adresinde çalışan **ayrı bir stok/envanter sistemi** var.
- Bu repoda değil, başka bir Claude session'ı tarafından yapıldı ve FTP ile yüklendi
- Modüller: Kasa, Rapor, Stok, Tedarikçi, Alış, Sayım, Demirbaş Envanteri, Ürün Alımı, Stok Düşüm
- Bu session'dan erişilemiyor
