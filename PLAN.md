# PLAN v3 (final, konsolide): Araç Takip Modülü

## Core Focus
Aydın'ın takip ettiği birden fazla aracın (Müdavim'in kullandığı ama HK adına kayıtlı Citroen Berlingo 26AEV158; Aydın'ın kendi aracı; eşi Yıldız'ın aracı; ileride eklenecekler) tüm masraf/bakım/arıza/kaza geçmişini tek admin panelden kaydedip, üç katmanda göstermek: (1) admin — tam yetki, (2) araç sahibi — plaka+şifre ile salt-okunur finansal defter, (3) opsiyonel herkese açık vitrin/ilan — satış gibi senaryolarda paylaşılabilir, finansal detay içermeyen özet. **Zengin veri modeline rağmen günlük kullanım sade ve hızlı olmalı.**

## Context / mevcut kod tabanı konvansiyonları (doğrulandı)
- Laravel 11, modüler monolit: `app/Modules/{Core,Admin,Website,Menu,TablePlan,Reservation,QrCode,Reviews}`.
- Modeller: Core/Menu/QrCode/Reservation/TablePlan → `app/Modules/*/Models` (orijinal/doğru konvansiyon, takip ediyoruz).
- Admin route deseni: `Route::prefix('yonetim')->middleware(['auth'])->name('admin.')->group(...)`.
- Modül aktivasyonu: `config/modules.php` + `AppServiceProvider` kaydı zorunlu, yoksa modül yüklenmez.
- Deploy (doğrulandı, `.github/workflows/deploy.yml`): GitHub Actions → SSH → VPS → `git pull` + `composer install` + `npm run build` + `artisan migrate --force` OTOMATİK. Repodaki `post_deploy.php` vb. eski cPanel çöpü, göz ardı edilir.
- Kural: fiyat/ölçüm/bakım aralığı gibi veriler tahmin edilmez, elle girilir.

## Kapsam
1. **Çoklu araç**, `/yonetim/arac` listesi, HK/Müdavim'e özgü hiçbir varsayım yok.
2. **Künye:** plaka + normalize `plate_key` (unique), marka, model/tip, model yılı, şase no, motor no, renk, yakıt cinsi, güncel km, sonraki bakım km/tarihi (elle), sonraki muayene tarihi, sigorta bitiş tarihi, önceki sahipler notu (serbest metin), **donanım/özellikler** (klima/vites tipi/ABS/elektrikli cam vb. — checkbox + serbest metin, JSON kolon).
   - "Satarken tek tıkla ilan" = ayrı bir "satılık" kavramı DEĞİL, aşağıdaki `vitrin_acik` anahtarının ta kendisi (künye+donanım+foto+bakım geçmişini otomatik ilan haline getirir).
3. **Masraf kaydı:** tarih, açıklama, tutar (decimal 12,2, min 0), kategori (yakit/bakim/yag_degisimi/lastik/sigorta/vergi/parca/iscilik/diger), ödeyen taraf + borçlu taraf (FK → `vehicle_parties`), mahsup durumu, km, parça meta (marka/üretim yılı/nereden alındı/garanti bitişi), opsiyonel `vitrinde_goster` + `public_summary`. İsteğe bağlı bir `vehicle_issue`'a bağlanabilir.
4. **Sorun/görev/kaza kaydı:** başlık, açıklama, kategori (bakim/ariza/kaza/gorev/diger — "vize randevusu" gibi genel görevler de burada), durum, öncelik, bildirilme/çözülme tarihi (tamamlandı → çözülme tarihi zorunlu), yapan firma, km. Maliyet burada TUTULMAZ, ilişkili `vehicle_expenses`'ten okunur.
5. **Medya (foto+video):** araca/masrafa/soruna bağlanabilir, kontrollü `role`, `tur`, serbest `caption`.
6. **"Yaklaşan Tarihler" paneli:** muayene/sigorta/bakım/garanti bitişleri — 30 gün veya 1000 km kala sarı, geçmişse kırmızı.
7. **UX ilkesi:** masraf/sorun ekleme formu varsayılan KISA (tarih+açıklama+tutar/başlık, 3-4 alan); parça/garanti/km/medya "detay ekle" ile açılır. Taraf seçimi dropdown + formu terk etmeden inline-taraf-ekleme.

## Veritabanı (migration sırası zorunlu: parties → vehicles → issues → expenses → media)

- **`vehicle_parties`**: id, ad, telefon (nullable), notlar (nullable), `deleted_at` (soft delete — kullanımdaki taraf hard-delete edilemez, uygulama seviyesinde restrict), timestamps.
- **`vehicles`**: id, plaka, plate_key (unique), marka, model, model_yili, sase_no, motor_no, renk, yakit_cinsi, sahip_party_id (FK, nullable), guncel_km, sonraki_bakim_km (nullable), sonraki_bakim_tarihi (nullable), sonraki_muayene_tarihi (nullable), sigorta_bitis_tarihi (nullable), onceki_sahipler_notu (nullable text), donanimlar (JSON, nullable), erisim_sifresi_hash, credential_version (uint, default 1), vitrin_acik (bool, default false), notlar (nullable text), `deleted_at`, timestamps.
- **`vehicle_issues`**: id, vehicle_id (FK), baslik, aciklama (nullable), kategori (enum), durum (enum), oncelik (enum, default normal), bildirilme_tarihi, cozulme_tarihi (nullable — `tamamlandi` durumunda DB-seviyesinde değil ama form-validation seviyesinde zorunlu), yapan_firma (nullable), km (nullable), notlar (nullable), `deleted_at` (soft delete — bağlı masrafı varsa hard-delete tamamen engellenir), timestamps.
- **`vehicle_expenses`**: id, vehicle_id (FK), vehicle_issue_id (nullable FK), tarih, aciklama, tutar (decimal 12,2, min 0), kategori (enum), odeyen_party_id (FK), borclu_party_id (nullable FK), mahsup_durumu (enum: borc_yok/alinacak/kapandi), settled_at (nullable), km (nullable), parca_markasi/uretim_yili/nereden_alindi/garanti_bitis_tarihi (hepsi nullable), vitrinde_goster (bool, default false), public_summary (nullable, kısa — **`vitrinde_goster=true` iken ZORUNLU**, boş vitrin satırı çıkmasın), notlar (nullable), `deleted_at`, timestamps.
  - **Bakiye hesaplama:** sadece soft-delete edilMEMİŞ + `mahsup_durumu='alinacak'` kayıtlar, (odeyen_party_id, borclu_party_id) çiftine göre gruplanıp toplanır — kapalı/silinmiş kayıtlar bakiyeye girmez (feature test: çok taraflı + kapalı kayıt karışık senaryo).
  - **Mahsup durum matrisi (validation, tam):** `borc_yok` → borclu_party_id NULL + settled_at NULL. `alinacak` → borclu_party_id ZORUNLU + settled_at NULL. `kapandi` → borclu_party_id ZORUNLU + settled_at ZORUNLU. Kapatan taraf her zaman `borclu_party_id`'dir (ayrı `settled_by` alanı yok).
- **`vehicle_media`**: id, vehicle_id (FK, zorunlu — servis katmanında bağlı expense/issue'nun KENDİ vehicle_id'siyle eşleştiği ayrıca doğrulanır, çapraz araç enjeksiyonu engellenir), vehicle_expense_id (nullable FK), vehicle_issue_id (nullable FK), tur (foto/video), role (genel/bolum/hasar_once/hasar_sonra/parca/ruhsat/sigorta_police/muayene_belgesi/fatura/diger), dosya_yolu (private disk), mime_type, caption (nullable), timestamps.
  - **"En fazla bir alt FK dolu" kuralı:** uygulama validasyonu + MySQL CHECK constraint (defense in depth) — ama CHECK sadece "ikisi birden dolu olamaz"ı garanti eder, expense/issue'nun gerçekten bu vehicle_id'ye ait olduğunu servis katmanı ayrıca doğrular (feature test: çapraz araç FK enjeksiyonu reddedilir).
- **`vehicle_change_logs`** (hafif finansal değişiklik geçmişi): id, **vehicle_id (FK, ZORUNLU — araç seviyesindeki km düzeltmesi gibi kayıtlar da hangi araca ait olduğunu kaybetmesin)**, vehicle_expense_id (nullable FK, `nullOnDelete` — expense purge edilirse log kalır ama bağlantısı boşa düşer, log asla silinmez), alan_adi, eski_deger, yeni_deger, aciklama (nullable), created_at. Değişiklikle log kaydı AYNI transaction'da yazılır. Normal admin UI'da sadece soft-delete sunulur; bilinçli kalıcı silme (purge) ayrı, açıkça yetkilendirilmiş bir işlemdir.

**Soft-delete + medya ilişkisi (kritik):** Bir expense/issue soft-delete edildiğinde bağlı medya DOKUNULMAZ (dosyalar silinmez — soft-delete geri alınabilir olmalı). Fiziksel dosya silme SADECE (a) doğrudan "medyayı sil" işleminde veya (b) admin'in bilinçli force-delete işleminde çalışır; DB kaydı transaction içinde silinir, dosya temizliği commit SONRASI yeniden-denenebilir bir job olarak çalışır (DB başarısız olursa dosyaya dokunulmaz; job başarısız olursa retry).

**Soft-deleted taraflar:** Finansal/sahiplik ilişkileri (`odeyen`, `borclu`, `sahip`) `withTrashed()` kullanır — arşivlenmiş bir taraf olsa bile geçmiş kayıtlarda adı görünmeye devam eder.

**Soft-deleted issue → expense ilişkisi:** Bir sorun soft-delete edildiğinde, ona bağlı masrafların `issue` ilişkisi de `withTrashed()` ile çözülür — tarihsel expense→issue bağlantısı kaybolmaz.

## Backend
- Yeni modül: `app/Modules/Vehicle/` — `routes.php`, `Models/{Vehicle,VehicleExpense,VehicleIssue,VehicleMedia,VehicleParty,VehicleChangeLog}.php`, `Controllers/Admin/{VehicleAdminController,VehicleExpenseController,VehicleIssueController,VehicleMediaController,VehiclePartyController}.php` (VehiclePartyController: gerçek CRUD — index/store/update/destroy-with-restrict, "minimal mi inline mi" belirsizliği kapatıldı), `Controllers/VehicleAccessController.php` (plaka+şifre giriş/çıkış), `Controllers/VehicleOwnerController.php` (`/aracim`), `Controllers/VehicleShowcaseController.php` (vitrin).
- `config/modules.php` + provider kaydı — modül kapalıyken hiçbir route kayıt olmaz (feature test).
- Medya servisi: `VehicleMediaService` — foto (genel galeri) → resize+WebP; belge rolleri (ruhsat/sigorta_police/muayene_belgesi/fatura) → orijinal çözünürlük, sıkıştırma yok; video → mime/boyut/format doğrulama + rastgele dosya adı. Tüm dosyalar PRIVATE disk'te, controller üzerinden yetkiyle sunulur: sıkı MIME allowlist, `X-Content-Type-Options: nosniff`, role'e göre güvenli `Content-Disposition` (resim inline, video/belge attachment ya da güvenli inline). Video sunumu Range-destekli stream (tam dosya belleğe alınmaz).
- Route scoping: masraf/sorun/medya alt route'ları `$vehicle->expenses()/issues()/media()` ilişkisi üzerinden çözülür.
- Güncel km düşürme: checkbox değil — zorunlu "düzeltme nedeni" metni + eski/yeni değer gösterimi + `vehicle_change_logs`'a kayıt.

## Erişim modeli — 3 katman
1. **Admin** (`/yonetim/arac/*`, `auth` middleware): tam CRUD, tek kullanıcı (Aydın).
2. **Araç sahibi** (plaka+şifre):
   - `GET/POST /arac-sorgula` → doğrulama → session regenerate, `vehicle_id`+`credential_version` session'a yazılır, **absolute TTL 24 saat** (idle-sliding yok) → `GET /aracim` (session middleware korumalı) tam finansal defter.
   - Şifre: **12 karakter uniform alfasayısal** (~71 bit), sadece oluşturma/reset anında bir kez gösterilir, sonra hash'li — admin'de tekrar gösterilemez, sadece "yeniden oluştur".
   - Şifre resetlenince `credential_version` artar, eski session'lar geçersiz.
   - **Rate limit (kesin sayılar):** `RateLimiter` facade + cache — (IP+plate_key) başına 5 başarısız denemede 15 dakika kilit; ayrıca IP başına saatte 20 deneme genel tavan (plaka tarama saldırısına karşı). `sleep()` KULLANILMAZ.
   - Yanlış plaka VE yanlış şifre aynı hata mesajı/süresi (bilinmeyen plaka için dummy hash kontrolü).
   - Çıkış: `POST /arac-cikis`; tüm owner/vitrin cevaplarında `Cache-Control: no-store`, `X-Robots-Tag: noindex`.
3. **Vitrin** (opsiyonel, `vitrin_acik` anahtarı): `GET /arac/vitrin/{plate_key}` — şifresiz.
   - **Künye allowlist:** SADECE plaka/marka/model/model_yılı/renk/km/donanım — şase no, motor no, sahip telefonu, iç notlar KESİNLİKLE yok.
   - **Medya allowlist:** SADECE role=genel/bolum/hasar_once/hasar_sonra — ruhsat/sigorta/muayene/fatura asla yok.
   - **Servis geçmişi:** ham `aciklama` DEĞİL, sadece `vitrinde_goster=true` olan masrafların `public_summary`'si.
   - Finansal alan (tutar/taraf/mahsup) hiçbir koşulda vitrin response'unda yer almaz.

## Frontend
- Admin araç listesi: kart görünümü, plaka/sahip/bakiye özeti/yaklaşan-tarih rozeti.
- Admin araç dashboard: künye+donanım+ruhsat, "Yaklaşan Tarihler" paneli, bakiye özeti (taraf bazlı), masraf/sorun tabloları (kısa hızlı-ekle formu + "detay ekle" genişletme), medya galerisi, erişim şifresi durumu, vitrin aç/kapat + link.
- `/aracim`: kartvizit referansına yakın temiz tasarım — künye, masraf defteri, bakiye kutusu, sorun/görev listesi, medya galerisi.
- `/arac/vitrin/{plate_key}`: aynı temiz tasarım, SADECE allowlist'teki alanlar (satış ilanı gibi kullanılabilir).
- Admin nav'a "Araçlar" linki.

## Non-Goals (bu round için, açıkça)
- Bakım aralığının marka/model bazlı otomatik hesaplanması.
- Bakım tipine özel ayrı sayaç sistemi (kategori filtresiyle geçmişten okunuyor).
- Kısmi ödeme planı (sadece tek `settled_at`).
- Tam genel-amaçlı audit-trail sistemi (sadece finansal alanlar için hafif `vehicle_change_logs`).
- Araç sahipleri için ayrı kullanıcı/login sistemi (plaka+şifre yeterli).
- Vitrin'in bilerek SEO-indexlenmesi (varsayılan noindex).

## Proof
- `php artisan migrate` hatasız; mevcut deploy pipeline değişmez.
- Modül `config/modules.php`'de kapalıyken route kayıt olmaz, açıkken olur (feature test).
- Admin: araç ekle→listede görünür→dashboard CRUD çalışır→nav linki var.
- Sahip görünümü: doğru plaka+şifre→`/aracim`; yanlış kombinasyon→throttle sonrası kilit (5/15dk + IP/saat 20 testleri); başka aracın verisine URL manipülasyonuyla erişilemez; şifre resetlenince eski session geçersiz.
- Medya: çapraz araç FK enjeksiyonu reddedilir (feature test); admin/doğru-owner-session/yanlış-owner-session/vitrin için ayrı yetkilendirme matrisi testleri.
- Vitrin: `vitrin_acik=false`→404; `true`→eşsiz test verisiyle (belirli tutar/taraf adı) finansal/hassas verinin response'ta GERÇEKTEN yokluğu + hassas medya yollarının yokluğu doğrulanır.
- Soft-delete: expense/issue silindiğinde medya dosyaları KORUNUR (test); force-delete'te dosyalar temizlenir (retryable job testi).
- İki araç arasında veri izolasyonu (masraf/sorun/medya karışmaz).
