# SAME-PAGE-LOG — Araç Takip Modülü

## Round 1
### Integrator findings (Codex, verbatim)
Bkz. `/tmp/rf.uqMknN/out-r1.txt` (repo dışı, geçici). Özet: 28 FIX + 1 CLARIFY + 1 DEFER. VERDICT: NOT YET.

### Visionary response (Fable)
- REJECTED: "Deploy FTP/cPanel, migration elle" → Doğrudan `.github/workflows/deploy.yml` dosyasını okudum: appleboy/ssh-action ile VPS'e SSH bağlanıp `git pull` + `migrate --force` OTOMATİK çalışıyor. Codex muhtemelen repodaki eski cPanel-dönemi çöp dosyalardan (post_deploy.php, public_html/migrate_run.php vb.) yanılmış. Deploy/Proof bölümü zaten doğruydu, değişmedi; bu çöp dosyaları görmezden gel notu eklendi.
- ACCEPTED: Token kalıntıları (`/arac/{token}`) → zaten 3 katmanlı erişim tasarımıyla (aşağıda) tamamen değişiyor.
- ACCEPTED: `config/modules.php` + provider kaydı eksik → build kapsamına eklendi.
- ACCEPTED: Modeller `app/Models` değil `app/Modules/Vehicle/Models` altında olmalı → doğruladım (Core/Menu/QrCode/Reservation/TablePlan modülleri gerçekten Modules/*/Models kullanıyor; Reviews/Admin gibi sonradan eklenenler App\Models'e kaymış — bu bir sürüklenme, orijinal/doğru konvansiyonu takip ediyoruz).
- ACCEPTED: MenuItem yükleme metodu genel amaçlı değil (private, 800x600'e sabit, video yok) → yeniden kullanma iddiası kaldırıldı, ayrı bir medya servisi yazılacak.
- ACCEPTED: Ruhsat/poliçe/fatura public disk'te olmamalı → private disk + controller üzerinden yetkilendirilmiş servis.
- ACCEPTED: Video validasyonu (mime/boyut/format/rastgele ad) tanımsızdı → eklendi.
- ACCEPTED: 6 haneli şifre + dakikada 5 throttle zayıf → daha yüksek entropili şifre + IP+plaka bazlı artan gecikme.
- ACCEPTED: Yanlış plaka/şifre zamanlama farkı bilgi sızdırır → aynı hata mesajı + dummy hash kontrolü.
- ACCEPTED: Session TTL/regenerate/araç-eşleşme tanımsız → login'de session regenerate, credential_version, TTL, middleware kontrolü.
- ACCEPTED: Şifre resetleme eski session'ları iptal etmiyor → credential_version alanı.
- ACCEPTED: Logout + no-store/noindex header'ları eksik → eklendi.
- ACCEPTED: Hash'lenmiş şifre sonradan gösterilemez → sadece oluşturma/reset anında bir kez gösterilir.
- ACCEPTED: Plaka normalizasyon + unique `plate_key` eksik → eklendi.
- ACCEPTED: Public/owner URL sözleşmesi belirsiz → 3 katmanlı erişim tasarımıyla netleşti (aşağıda).
- ACCEPTED: Nested masraf/sorun {id} route'ları araca scope edilmeli → ilişki üzerinden scoped binding.
- ACCEPTED (basitleştirilerek): `vehicle_media` polymorphic yerine nullable doğrudan FK'ler (`vehicle_expense_id`, `vehicle_issue_id`) + `vehicle_id`, "en fazla biri dolu" kuralı — Codex'in önerdiği daha basit alternatif kabul edildi.
- ACCEPTED: Silinen expense/issue'da yetim medya kalması → cascade delete + dosya temizliği.
- ACCEPTED: `ruhsat_gorseli` ayrı yaşam döngüsü → `vehicle_media` içine `role=ruhsat` olarak taşındı (sigorta/muayene de aynı şekilde `role` ile).
- ACCEPTED: Serbest metin etiket (önce/sonra/hasar) tutarsız → kontrollü `role` enum + ayrı serbest `caption`.
- ACCEPTED: `sahip_adi`/`odeyen` serbest metin kişi ayrışmasını bozar → hafif `vehicle_parties` tablosu (ad, opsiyonel telefon/not), FK ile bağlanıyor.
- ACCEPTED: "alınacak X₺" kimden kime belirsiz → `odeyen_party_id` + `borclu_party_id` alanları.
- ACCEPTED: "kapandı" durumunda tarih/kapatan/tutar kaybı → `settled_at`, `settled_note` eklendi (kısmi ödeme geçmişi bu turda NON-GOAL, açıkça belirtildi).
- ACCEPTED: Sorun tablosundaki parça/işçilik tutarı masrafla çift kayıt yaratıyor → `vehicle_issues`'tan parca_tutari/iscilik_tutari kaldırıldı, bunun yerine bir soruna bağlı `vehicle_expenses` kayıtları (nullable `vehicle_issue_id` FK) oluşturuluyor — tek finansal kaynak `vehicle_expenses`.
- ACCEPTED: Para kolonlarında precision/scale/negatif kural yok → `decimal(12,2)`, `min:0`.
- RESOLVED (CLARIFY): "Bakım yaklaştı" eşiği → varsayılan sabit: 30 gün VEYA 1000 km kala sarı, geçmişse/aşılmışsa kırmızı. Bu turda kullanıcıya soru değil, makul varsayılan (ileride ayarlanabilir yapmak Issues List'e).
- ACCEPTED: Güncel km geriye gitmemeli → `guncel_km` sadece artabilir (kasıtlı düzeltme için açık onay adımı), geçmiş kayıt km'leri (masraf/sorun) serbest.
- REJECTED: Sigorta/muayene paneli tamamen ertelensin → Kullanıcı AÇIKÇA ve TEKRAR ("sigorta poliçesini vize muayene belgelerini ekleyelim", sonra tekrar "sigorta poliçesi vs vb bunları da düşün") istedi. Kapsam KÜÇÜK tutuluyor: sadece 2 tarih alanı (vehicles.sonraki_muayene_tarihi, sigorta_bitis_tarihi) + vehicle_media'da role=sigorta_police/muayene_belgesi — ayrı bir "belge hatırlatma" alt sistemi YOK, Codex'in asıl endişesi (scope şişmesi) zaten karşılanıyor.
- ACCEPTED: Araç silme hard-delete olmamalı → soft delete (`deleted_at`), finansal geçmiş korunuyor.
- ACCEPTED (hafifletilerek): Tam audit log yerine — şimdilik tek admin kullanıcısı (Aydın) olduğundan, sadece soft delete + timestamps + minimal log (Laravel Log facade, şifre içermeden) yeterli; tam audit-trail tablosu bu turda NON-GOAL.
- ACCEPTED: Güvenlik olay logu (başarılı/başarısız giriş) yok → eklendi.
- ACCEPTED: Proof zayıf, sadece migrate + temel CRUD → feature testleri (auth, throttle, session izolasyonu, iki araç arası veri izolasyonu, bakiye hesabı) eklendi.
- REJECTED: "Proof gerçek deploy'u kanıtlamıyor, FTP akışına göre düzelt" → Deploy zaten SSH/otomatik migration (yukarıda doğrulandı), bu haliyle Proof zaten doğruydu.
- ACCEPTED: Admin nav'da "Araçlar" linki yok → admin.blade.php'ye eklenecek, teslimat kapsamında.

## Round 2
### Integrator findings (Codex, verbatim)
Bkz. `/tmp/rf.uqMknN/out-r2.txt`. Round 1'deki 2 reddim doğrulandı (deploy gerçekten SSH/otomatik migration; sigorta/muayene kapsamı dar tutulmuş kabul edildi). 22 yeni FIX bulgusu. VERDICT: NOT YET.

### Visionary response (Fable) — hepsi kabul edildi, yapısal değil inceltme
- Migration sırası: parties → vehicles → issues → expenses → media.
- Session TTL: absolute 24 saat (idle-sliding pencere YOK, basitlik için).
- Şifre entropisi: 10 karakter değil, 12 karakter uniform alfasayısal (~71 bit, ≥64 bit hedefini karşılar).
- Rate-limit: `sleep()` ile "artan gecikme" DEĞİL — Laravel `RateLimiter` facade + cache tabanlı sayaç, sabit kilit süresi.
- `vehicle_parties` için minimal CRUD (VehiclePartyController) veya form içi doğrulanmış inline-create akışı eklendi.
- Taraflar soft-delete; kullanımdaki taraf hard-delete edilemez (restrict).
- `mahsup_durumu` geçişleri domain validation ile kısıtlanıyor: `alinacak`→borclu_party_id zorunlu, `kapandi`→settled_at zorunlu.
- "Kapatan kişi" için ayrı `settled_by_party_id` alanı yerine daha basit invariant: kapatan taraf her zaman `borclu_party_id`'dir (Occam — gereksiz alan açılmadı).
- Soft delete `vehicle_expenses` ve `vehicle_issues`'a da eklendi (sadece vehicles'ta değil) — finansal geçmiş asla hard-delete olmaz.
- Issue silme: soft-delete, bağlı masrafı varsa hard-delete tamamen engellenir.
- `vehicle_media`'da "en fazla bir alt FK dolu" kuralı artık hem uygulama seviyesinde hem MySQL CHECK constraint ile.
- Medya temizliği DB cascade'e bırakılmıyor — parent silinmeden önce uygulama kodu/observer dosyaları ve kayıtları açıkça siliyor (Eloquent event'leri tetiklenir).
- Resize+WebP SADECE genel galeri fotoğraflarına uygulanıyor; belge rolleri (ruhsat/sigorta_police/muayene_belgesi/fatura) orijinal çözünürlükte, agresif sıkıştırma YOK (okunabilirlik korunur).
- Video servis: bellekte tam dosya okumak yerine Range-destekli stream response (Symfony BinaryFileResponse).
- Vitrin künye alanları allowlist: sadece plaka/marka/model/model_yılı/renk/km — şase no, motor no, sahip telefonu, iç notlar KESİNLİKLE dışarıda.
- Vitrin medya rolleri allowlist: sadece genel/bolum/hasar_once/hasar_sonra — ruhsat/sigorta/muayene/fatura vitrin'de asla gösterilmez.
- Vitrin servis geçmişi artık masrafın ham `aciklama`sını göstermiyor — her masrafta opsiyonel `vitrinde_goster` (bool) + ayrı, kısa, curated `public_summary` alanı; varsayılan gösterilmez.
- Proof'a: vitrin'de eşsiz test verisiyle (belirli tutar/taraf adı) finansal/hassas verinin GERÇEKTEN yokluğunu doğrulayan testler + admin/doğru-owner-session/yanlış-owner-session/vitrin için ayrı yetkilendirme matrisi testleri eklendi.
- Km düşürme: checkbox değil, zorunlu "düzeltme nedeni" alanı + eski/yeni değer gösterimi + loglanır.
- Issue durum/tarih tutarlılığı: `tamamlandi` durumu `cozulme_tarihi`yi zorunlu kılar (validation).

### Kullanıcının bu round sırasında eklediği yeni kapsam (plana işlendi)
- **3 katmanlı erişim** (kullanıcının açık isteği): (1) Admin — tam CRUD. (2) Plaka+şifre ile "sahip görünümü" — finansal defter dahil salt-okunur. (3) OPSİYONEL, araç bazlı açılabilir "herkese açık vitrin" — şifresiz, paylaşılabilir link, araç satışı gibi senaryolar için; künye+fotoğraf+bakım geçmişi gösterir ama kim-kime-ne-borçlu bilgisini GÖSTERMEZ (alıcıyı ilgilendirmez, hassas).
- Modül adı: "Araç Takip" (kullanıcının "araç takibi" önerisiyle uyumlu, admin nav'da bu isimle görünecek).

## Round 3
### Integrator findings (Codex, verbatim)
Ana bulgu: plan gövdesi hâlâ eski kararları taşıyor, Round 2 bölümü sadece sonda geçersiz kılıyor — bu dağınıklık bizzat bir FIX olarak işaretlendi. 9 ek FIX (rate-limit sayıları, çapraz-araç medya enjeksiyonu, soft-delete/medya temizliği çakışması, dosya-silme transaction sırası, soft-deleted taraf withTrashed, mahsup durum matrisinin eksikliği, party CRUD belirsizliği, finansal audit hâlâ cevapsız, orijinal-çözünürlük belge/video güvenli header'ları). VERDICT: NOT YET.

### Visionary response (Fable)
Hepsi kabul edildi. Bunun üzerine PLAN.md TAMAMEN yeniden yazıldı (v3, tek konsolide sürüm — artık ek/addenda yok, her karar ana bölümlerde). Detaylar PLAN.md'nin ilgili bölümlerinde: migration sırası, rate-limit kesin sayıları (5/15dk IP+plaka, 20/saat IP), medya çapraz-araç doğrulaması, soft-delete sırasında medyanın KORUNMASI (sadece force-delete'te temizlik, transaction-sonrası retryable job), withTrashed ilişkiler, tam mahsup durum matrisi, gerçek VehiclePartyController CRUD, `vehicle_change_logs` tablosu (hafif finansal audit), MIME allowlist + nosniff + Content-Disposition.

## Round 4
### Integrator findings (Codex, verbatim)
"Round-3 bulgularının tamamı mimari olarak karşılanmış; kalanlar yerel uygulama netleştirmeleri ve SAME PAGE kararını engellemiyor." 5 küçük FIX (taraf-bazlı bakiye formülü net değildi, vehicle_change_logs'ta araç seviyesi km düzeltmesi için zorunlu vehicle_id eksikti, vitrinde_goster=true iken public_summary zorunlu değildi, force-delete'te log FK davranışı belirsizdi, soft-deleted issue→expense ilişkisi withTrashed değildi). **VERDICT: SAME PAGE.**

### Visionary response (Fable)
5 bulgu da kabul edildi ve PLAN.md'ye işlendi (bakiye hesaplama kuralı, vehicle_change_logs.vehicle_id zorunlu + nullOnDelete, public_summary koşullu zorunlu validation, withTrashed issue ilişkisi). **Same Page Meeting SONA ERDİ — VERDICT: SAME PAGE.**
