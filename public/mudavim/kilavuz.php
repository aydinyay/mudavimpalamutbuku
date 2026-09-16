<?php
require_once __DIR__ . '/autoload.php';
use Mudavim\Core\Auth;
Auth::boot();
if (!Auth::check()) { header('Location: login.php'); exit; }
$role = Auth::role();
?>
<!DOCTYPE html>
<html lang="tr">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Kullanım Kılavuzu — Müdavim v4</title>
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
<style>
body { background:#f8f9fa; font-size:.95rem; line-height:1.7; }
.nav-top { background:#1a1a2e; position:sticky; top:0; z-index:1000; }
.nav-top .btn-nav { color:#ccc; border:none; background:none; padding:.4rem .9rem; border-radius:6px; font-size:.85rem; }
.nav-top .btn-nav:hover { background:#ffffff22; color:#fff; }
.sidebar { position:sticky; top:58px; max-height:calc(100vh - 70px); overflow-y:auto; padding-bottom:2rem; }
.sidebar .nav-link { color:#495057; font-size:.82rem; padding:.25rem .75rem; border-radius:5px; }
.sidebar .nav-link:hover { background:#e9ecef; }
.sidebar .nav-link.active { background:#0d6efd; color:#fff; }
.sidebar .section-label { font-size:.68rem; font-weight:700; text-transform:uppercase; letter-spacing:.07em; color:#adb5bd; padding:.6rem .75rem .15rem; margin-top:.4rem; }
h2 { font-size:1.4rem; font-weight:700; border-bottom:3px solid #0d6efd; padding-bottom:.4rem; margin-top:2.8rem; color:#1a1a2e; }
h3 { font-size:1.05rem; font-weight:700; margin-top:1.6rem; color:#0d6efd; }
h4 { font-size:.95rem; font-weight:600; margin-top:1.2rem; color:#343a40; }
.tip  { background:#e8f4fd; border-left:4px solid #0d6efd; border-radius:0 6px 6px 0; padding:.65rem 1rem; margin:.8rem 0; font-size:.88rem; }
.warn { background:#fff3cd; border-left:4px solid #ffc107; border-radius:0 6px 6px 0; padding:.65rem 1rem; margin:.8rem 0; font-size:.88rem; }
.danger-box { background:#f8d7da; border-left:4px solid #dc3545; border-radius:0 6px 6px 0; padding:.65rem 1rem; margin:.8rem 0; font-size:.88rem; }
.step { display:flex; gap:.75rem; margin:.45rem 0; align-items:flex-start; }
.step-num { background:#0d6efd; color:#fff; border-radius:50%; width:22px; height:22px; min-width:22px; display:flex; align-items:center; justify-content:center; font-size:.72rem; font-weight:700; margin-top:2px; }
.badge-role { font-size:.7rem; vertical-align:middle; }
section { scroll-margin-top:65px; }
table.fields td:first-child { font-weight:600; white-space:nowrap; width:30%; }
.screen-label { display:inline-block; background:#1a1a2e; color:#fff; border-radius:5px; font-size:.75rem; padding:.1rem .5rem; margin-bottom:.3rem; }
@media (max-width:768px) { .sidebar-col { display:none; } }
@media print {
  .nav-top, .sidebar-col, .no-print { display:none !important; }
  .content-col { padding:0 1rem !important; }
  h2 { page-break-before:always; border:none; border-bottom:2px solid #000; }
  h2:first-of-type { page-break-before:auto; }
  .tip, .warn, .danger-box { border:1px solid #ccc; }
}
</style>
</head>
<body>

<div class="nav-top px-3 py-2 d-flex align-items-center gap-2 flex-wrap no-print">
  <span class="text-white fw-bold me-3">🍽 Müdavim</span>
  <a href="dashboard.php"  class="btn-nav">Kasa</a>
  <a href="stok.php"       class="btn-nav">Stok</a>
  <a href="urunler.php"    class="btn-nav">Ürünler</a>
  <a href="tedarikci.php"  class="btn-nav">Tedarikçi</a>
  <a href="alis.php"       class="btn-nav">Alış</a>
  <a href="sayim.php"      class="btn-nav">Sayım</a>
  <a href="rapor.php"      class="btn-nav">Rapor</a>
  <?php if ($role === 'patron'): ?>
  <a href="demirbaslar.php" class="btn-nav">Demirbaş</a>
  <?php endif; ?>
  <div class="ms-auto d-flex gap-2">
    <button class="btn-nav" onclick="window.print()">🖨 Yazdır / PDF</button>
    <a href="logout.php" class="btn-nav">Çıkış</a>
  </div>
</div>

<div class="container-fluid px-3 pt-3">
<div class="row g-0">

<!-- SIDEBAR -->
<div class="col-lg-2 sidebar-col pe-3 no-print">
  <div class="sidebar">
    <div class="section-label">Başlangıç</div>
    <a class="nav-link" href="#genel">Genel Bakış</a>
    <a class="nav-link" href="#giris">Giriş & Oturum</a>
    <a class="nav-link" href="#roller">Kullanıcı Rolleri</a>
    <a class="nav-link" href="#nav">Navigasyon</a>
    <div class="section-label">Modüller</div>
    <a class="nav-link" href="#kasa">Kasa (Dashboard)</a>
    <a class="nav-link" href="#stok-durumu">Stok Durumu</a>
    <a class="nav-link" href="#urunler">Ürün Yönetimi</a>
    <a class="nav-link" href="#tedarikci">Tedarikçi & Cari</a>
    <a class="nav-link" href="#alis">Alış Listesi</a>
    <a class="nav-link" href="#sayim">Fiziksel Sayım</a>
    <a class="nav-link" href="#rapor">Rapor</a>
    <a class="nav-link" href="#demirbas">Demirbaş</a>
    <a class="nav-link" href="#personel">Personel</a>
    <div class="section-label">Yardım</div>
    <a class="nav-link" href="#renkler">Renk Sistemi</a>
    <a class="nav-link" href="#sikcasorulan">Sık Sorulan</a>
    <a class="nav-link" href="#hata">Hata Mesajları</a>
  </div>
</div>

<!-- İÇERİK -->
<div class="col-lg-10 content-col pb-5 ps-2">

  <div class="d-flex align-items-start justify-content-between flex-wrap gap-2 mb-2">
    <div>
      <h1 class="fw-bold mb-0" style="font-size:1.8rem;">Müdavim Restoran Yönetim Sistemi</h1>
      <p class="text-muted mb-0">Tam Kullanım Kılavuzu — Sürüm 4 &nbsp;·&nbsp; <?= date('d F Y') ?></p>
    </div>
    <span class="badge bg-dark fs-6">mudavimpalamutbuku.com/mudavim/</span>
  </div>
  <hr>

  <!-- ======================== GENEL BAKIŞ ======================== -->
  <section id="genel">
    <h2>1. Genel Bakış</h2>
    <p>Müdavim; stok takibi, kasa yönetimi, tedarikçi cari hesapları, personel takibi ve demirbaş envanterini tek çatı altında toplayan bir restoran yönetim sistemidir. Özel yazılım veya kurulum gerektirmez — internet tarayıcısı olan her cihazdan (bilgisayar, tablet, telefon) erişilebilir.</p>

    <h4>Sistemin Kapsadığı Alanlar</h4>
    <ul>
      <li>Günlük kasa işlemleri (satış, masraf, patron çekimi, nakit yatırma)</li>
      <li>Stok giriş ve çıkışları; anlık bakiye takibi</li>
      <li>Ürün kataloğu yönetimi (ekle / düzenle / sil)</li>
      <li>Tedarikçi ve cari hesap takibi (borç / ödeme geçmişi)</li>
      <li>Otomatik alış listesi (stok kritik olanlar)</li>
      <li>Fiziksel sayım ve stok düzeltme</li>
      <li>Alış / çıkış / fire raporları</li>
      <li>Demirbaş envanteri (ekipman, mobilya, elektronik)</li>
      <li>Personel yönetimi (maaş, avans, gün sonu ödemesi)</li>
    </ul>
  </section>

  <!-- ======================== GİRİŞ ======================== -->
  <section id="giris">
    <h2>2. Sisteme Giriş & Oturum</h2>

    <h3>İlk Kez Giriş</h3>
    <div class="step"><div class="step-num">1</div><div>Tarayıcıya <code>mudavimpalamutbuku.com/mudavim/</code> adresini yazın.</div></div>
    <div class="step"><div class="step-num">2</div><div>Kullanıcı adı ve şifrenizi girin; <strong>Giriş Yap</strong>'a basın.</div></div>
    <div class="step"><div class="step-num">3</div><div>Giriş başarılıysa otomatik olarak <strong>Kasa (Dashboard)</strong> ekranına yönlendirilirsiniz.</div></div>
    <div class="tip">Sayfayı favorilere ekleyin. Adres çubuğuna her seferinde yazmak zorunda kalmazsınız.</div>

    <h3>Yanlış Şifre</h3>
    <p>Şifrenizi unuttuysanız <span class="badge bg-danger badge-role">Patron</span>'a bildirin. Patron, Personel yönetiminden şifrenizi sıfırlayabilir.</p>

    <h3>Oturumu Kapatma</h3>
    <p>Sağ üstteki <strong>Çıkış</strong> bağlantısına tıklayın. Oturum sonlandırılır ve giriş sayfasına yönlendirilirsiniz.</p>
    <div class="warn">Başka biri aynı bilgisayarı kullanacaksa mutlaka çıkış yapın. Tarayıcıyı kapatmak oturumu her zaman sonlandırmaz.</div>

    <h3>Aynı Anda Birden Fazla Cihazdan Giriş</h3>
    <p>Aynı kullanıcı hesabıyla farklı cihazlardan aynı anda giriş yapılabilir. Giriş çakışması olmaz.</p>
  </section>

  <!-- ======================== ROLLER ======================== -->
  <section id="roller">
    <h2>3. Kullanıcı Rolleri & Yetkiler</h2>
    <p>Her kullanıcı hesabına bir rol atanır. Rol, o kullanıcının hangi işlemleri yapabileceğini belirler.</p>

    <table class="table table-bordered small">
      <thead class="table-dark">
        <tr>
          <th>İşlem</th>
          <th class="text-center"><span class="badge bg-danger">Patron</span></th>
          <th class="text-center"><span class="badge bg-warning text-dark">Kasiyer</span></th>
          <th class="text-center"><span class="badge bg-info text-dark">Garson</span></th>
        </tr>
      </thead>
      <tbody>
        <tr><td>Kasa: satış girişi, nakit yatır</td><td class="text-center">✔</td><td class="text-center">✔</td><td class="text-center">—</td></tr>
        <tr><td>Kasa: masraf (PIN ile)</td><td class="text-center">✔</td><td class="text-center">✔</td><td class="text-center">—</td></tr>
        <tr><td>Kasa: patron çekimi</td><td class="text-center">✔</td><td class="text-center">—</td><td class="text-center">—</td></tr>
        <tr><td>Kasa: bakiye düzeltme</td><td class="text-center">✔</td><td class="text-center">—</td><td class="text-center">—</td></tr>
        <tr><td>Kasa: Z-Raporu</td><td class="text-center">✔</td><td class="text-center">—</td><td class="text-center">—</td></tr>
        <tr><td>Stok girişi (alım)</td><td class="text-center">✔</td><td class="text-center">✔</td><td class="text-center">—</td></tr>
        <tr><td>Stok çıkışı (fire, kayıp…)</td><td class="text-center">✔</td><td class="text-center">✔</td><td class="text-center">✔</td></tr>
        <tr><td>Stok durumu görüntüleme</td><td class="text-center">✔</td><td class="text-center">✔</td><td class="text-center">✔</td></tr>
        <tr><td>Ürün ekleme / düzenleme / silme</td><td class="text-center">✔</td><td class="text-center">—</td><td class="text-center">—</td></tr>
        <tr><td>Tedarikçi yönetimi</td><td class="text-center">✔</td><td class="text-center">✔</td><td class="text-center">—</td></tr>
        <tr><td>Cari işlemleri</td><td class="text-center">✔</td><td class="text-center">✔</td><td class="text-center">—</td></tr>
        <tr><td>Alış listesi görüntüleme</td><td class="text-center">✔</td><td class="text-center">✔</td><td class="text-center">✔</td></tr>
        <tr><td>Fiziksel sayım: giriş</td><td class="text-center">✔</td><td class="text-center">✔</td><td class="text-center">✔</td></tr>
        <tr><td>Fiziksel sayım: onaylama</td><td class="text-center">✔</td><td class="text-center">—</td><td class="text-center">—</td></tr>
        <tr><td>Rapor görüntüleme</td><td class="text-center">✔</td><td class="text-center">✔</td><td class="text-center">—</td></tr>
        <tr><td>Demirbaş envanteri</td><td class="text-center">✔</td><td class="text-center">—</td><td class="text-center">—</td></tr>
        <tr><td>Personel yönetimi</td><td class="text-center">✔</td><td class="text-center">—</td><td class="text-center">—</td></tr>
      </tbody>
    </table>
  </section>

  <!-- ======================== NAVİGASYON ======================== -->
  <section id="nav">
    <h2>4. Navigasyon (Sayfa Geçişleri)</h2>
    <p>Ekranın en üstünde koyu arka planlı bir menü çubuğu bulunur. Tüm modüllere buradan ulaşılır.</p>
    <table class="table table-sm table-bordered small">
      <thead class="table-light"><tr><th>Menü</th><th>Ne Açar</th></tr></thead>
      <tbody>
        <tr><td><strong>Kasa</strong></td><td>Ana dashboard — günlük işlemler, kasa bakiyesi</td></tr>
        <tr><td><strong>Stok</strong></td><td>Tüm ürünlerin anlık stok durumu</td></tr>
        <tr><td><strong>Ürünler</strong></td><td>Ürün kataloğu yönetimi (ekle/düzenle/sil)</td></tr>
        <tr><td><strong>Tedarikçi</strong></td><td>Tedarikçi listesi ve cari hesaplar</td></tr>
        <tr><td><strong>Alış</strong></td><td>Sipariş verilmesi gereken ürünlerin listesi</td></tr>
        <tr><td><strong>Sayım</strong></td><td>Fiziksel stok sayımı</td></tr>
        <tr><td><strong>Rapor</strong></td><td>Alış / çıkış / fire raporları</td></tr>
        <tr><td><strong>Demirbaş</strong></td><td>Ekipman ve sabit varlık takibi (sadece Patron)</td></tr>
      </tbody>
    </table>
    <div class="tip">Telefonda menü butonları küçük görünebilir. Yatay (landscape) moda döndürerek daha rahat kullanabilirsiniz.</div>
  </section>

  <!-- ======================== KASA ======================== -->
  <section id="kasa">
    <h2>5. Kasa (Dashboard)</h2>
    <p>Sisteme girişte ilk açılan ekrandır. Günlük tüm nakit ve stok işlemleri buradan yapılır.</p>

    <h3>5.1 Üst Bilgi Paneli</h3>
    <p>Ekranın üst bölümünde dört adet özet kart bulunur:</p>
    <ul>
      <li><strong>Kasa Bakiyesi:</strong> Anlık nakit miktarı</li>
      <li><strong>Bugün Giriş:</strong> Bugün yapılan stok giriş sayısı</li>
      <li><strong>Bugün Çıkış:</strong> Bugün yapılan stok çıkış sayısı</li>
      <li><strong>Uyarılar:</strong> Stok kritik seviye ve sıfır bakiye uyarı sayısı</li>
    </ul>

    <h3>5.2 Hızlı İşlem Butonları</h3>
    <p>Ana kartların altında büyük renkli butonlar yer alır. Bunlar en sık kullanılan işlemlere tek tıkla erişimi sağlar. Sistem kullandıkça hangi saatte hangi işlemleri yaptığınızı öğrenir ve en çok kullandıklarınızı öne çıkarır.</p>

    <table class="table table-sm table-bordered small">
      <thead class="table-light"><tr><th>Buton</th><th>Ne Yapar</th><th>Kim Kullanabilir</th></tr></thead>
      <tbody>
        <tr><td><strong>Stok Girişi</strong></td><td>Yeni malzeme alımı kaydeder, stok artar</td><td>Patron, Kasiyer</td></tr>
        <tr><td><strong>Stok Çıkışı</strong></td><td>Stoktan çıkış kaydeder (fire, kayıp, kullanım)</td><td>Patron, Kasiyer, Garson</td></tr>
        <tr><td><strong>Satış Girişi</strong></td><td>Günlük satış gelirini kasaya ekler</td><td>Patron, Kasiyer</td></tr>
        <tr><td><strong>Masraf</strong></td><td>Kasadan gider çıkışı (PIN gerektirir)</td><td>Patron, Kasiyer</td></tr>
        <tr><td><strong>Patron Eve Götürdü</strong></td><td>Patronun kasadan aldığı tutarı kaydeder</td><td>Patron</td></tr>
        <tr><td><strong>Nakit Yatır</strong></td><td>Dışarıdan gelen nakit kasaya eklenir</td><td>Patron, Kasiyer</td></tr>
        <tr><td><strong>Kasa Düzelt</strong></td><td>Fiili kasa ile sistem arasındaki farkı kapatır</td><td>Patron</td></tr>
        <tr><td><strong>Z-Raporu</strong></td><td>Günlük kapanış raporu alır</td><td>Patron</td></tr>
      </tbody>
    </table>

    <h3>5.3 Stok Girişi (Malzeme Alımı)</h3>
    <p>Marketten, tedarikçiden veya toptancıdan malzeme alındığında kullanılır.</p>
    <div class="step"><div class="step-num">1</div><div><strong>Stok Girişi</strong> butonuna basın.</div></div>
    <div class="step"><div class="step-num">2</div><div>Açılan formda ürünü seçin (arama kutusuna yazmaya başlayın, liste daraltılır).</div></div>
    <div class="step"><div class="step-num">3</div><div>Giriş tipini seçin: <em>Resmi Alım, Kasa Alımı, İade,</em> vb.</div></div>
    <div class="step"><div class="step-num">4</div><div>Miktarı ve birim fiyatı girin. Toplam tutar otomatik hesaplanır.</div></div>
    <div class="step"><div class="step-num">5</div><div><em>(İsteğe bağlı)</em> Tedarikçi seçin — seçilirse borç otomatik cari hesaba işlenir.</div></div>
    <div class="step"><div class="step-num">6</div><div><strong>Kaydet</strong>'e basın. Stok anında artar.</div></div>
    <div class="tip">Tedarikçi seçerseniz ayrıca Tedarikçi sayfasından borç girmenize gerek yok — iki kez işlem yapmayın.</div>

    <h4>Stok Giriş Tipleri</h4>
    <table class="table table-sm table-bordered small">
      <thead class="table-light"><tr><th>Tip</th><th>Ne Zaman Kullanılır</th></tr></thead>
      <tbody>
        <tr><td>Resmi Alım</td><td>Faturalı tedarikçi alımı</td></tr>
        <tr><td>Kasa Alımı</td><td>Marketten nakit alınan malzeme</td></tr>
        <tr><td>İade</td><td>Tedarikçiye iade edilen ve geri gelen ürün</td></tr>
        <tr><td>Sayım Düzeltme</td><td>Fiziksel sayım sonrası fark düzeltmesi</td></tr>
        <tr><td>Diğer</td><td>Yukarıdakilerle tanımlanamayan girişler</td></tr>
      </tbody>
    </table>

    <h3>5.4 Stok Çıkışı</h3>
    <p>Stoktan bir ürün çekildiğinde, fire verildiğinde ya da kayıp oluştuğunda kullanılır.</p>
    <div class="step"><div class="step-num">1</div><div><strong>Stok Çıkışı</strong> butonuna basın.</div></div>
    <div class="step"><div class="step-num">2</div><div>Ürünü seçin.</div></div>
    <div class="step"><div class="step-num">3</div><div>Çıkış tipini seçin (aşağıdaki tabloya bakın).</div></div>
    <div class="step"><div class="step-num">4</div><div>Miktarı girin ve kaydedin. Stok azalır.</div></div>

    <h4>Stok Çıkış Tipleri</h4>
    <table class="table table-sm table-bordered small">
      <thead class="table-light"><tr><th>Tip</th><th>Ne Zaman Kullanılır</th></tr></thead>
      <tbody>
        <tr><td>Satış / Kullanım</td><td>Yemeğe kullanılan malzeme</td></tr>
        <tr><td>Fire</td><td>Bozulan, çürüyen, kullanılamaz hale gelen ürün</td></tr>
        <tr><td>Kayıp</td><td>Kayıp veya çalınan ürün</td></tr>
        <tr><td>İade</td><td>Tedarikçiye geri gönderilen ürün</td></tr>
        <tr><td>Personel Tüketimi</td><td>Personele verilen yemek/içecek</td></tr>
        <tr><td>Numune</td><td>Müşteriye tattırılan/verilen ürün</td></tr>
        <tr><td>Sayım Farkı</td><td>Fiziksel sayımda bulunan eksi fark</td></tr>
      </tbody>
    </table>

    <h3>5.5 Satış Girişi</h3>
    <p>Günlük satış tutarını kasaya işlemek için kullanılır.</p>
    <div class="step"><div class="step-num">1</div><div><strong>Satış Girişi</strong> butonuna basın.</div></div>
    <div class="step"><div class="step-num">2</div><div>Tutarı girin (örnek: 3.500).</div></div>
    <div class="step"><div class="step-num">3</div><div>Açıklama ekleyin (isteğe bağlı: "Akşam servisi" vb.).</div></div>
    <div class="step"><div class="step-num">4</div><div>Kaydedin. Kasa bakiyesi artar.</div></div>

    <h3>5.6 Masraf Kaydı</h3>
    <p>Kasadan yapılan her türlü gider için kullanılır (elektrik, su, market alışverişi vb.).</p>
    <div class="step"><div class="step-num">1</div><div><strong>Masraf</strong> butonuna basın.</div></div>
    <div class="step"><div class="step-num">2</div><div>Tutarı ve açıklamayı girin.</div></div>
    <div class="step"><div class="step-num">3</div><div>4 haneli PIN'inizi girin (güvenlik doğrulaması).</div></div>
    <div class="step"><div class="step-num">4</div><div>Kaydedin. Kasa bakiyesi azalır.</div></div>
    <div class="tip">PIN'inizi unuttuysanız Patron size Personel sayfasından yeni PIN tanımlayabilir.</div>

    <h3>5.7 Patron Eve Götürdü</h3>
    <p>Patronun günlük olarak kasadan kendi payını çekmesi için kullanılır.</p>
    <div class="step"><div class="step-num">1</div><div><strong>Patron Eve Götürdü</strong> butonuna basın.</div></div>
    <div class="step"><div class="step-num">2</div><div>Tutarı girin.</div></div>
    <div class="step"><div class="step-num">3</div><div>Kaydedin. Kasa bakiyesinden düşer, ayrı kategoride raporlanır.</div></div>

    <h3>5.8 Nakit Yatırma</h3>
    <p>Dışarıdan getirilen nakdin kasaya eklenmesi için kullanılır (sabah açılış bakiyesi, banka çekimi vb.).</p>
    <div class="step"><div class="step-num">1</div><div><strong>Nakit Yatır</strong> butonuna basın.</div></div>
    <div class="step"><div class="step-num">2</div><div>Tutarı ve açıklamayı girin.</div></div>
    <div class="step"><div class="step-num">3</div><div>Kaydedin.</div></div>

    <h3>5.9 Kasa Bakiyesi Düzeltme <span class="badge bg-danger badge-role">Patron</span></h3>
    <p>Fiili kasadaki para ile sistemdeki bakiye arasında fark oluştuğunda kullanılır.</p>
    <div class="step"><div class="step-num">1</div><div><strong>Kasa Düzelt</strong> butonuna basın.</div></div>
    <div class="step"><div class="step-num">2</div><div>Kasada gerçekte kaç lira olduğunu girin.</div></div>
    <div class="step"><div class="step-num">3</div><div>Kaydedin. Fark "Manuel Düzeltme" olarak kaydedilir ve raporda görünür.</div></div>
    <div class="warn">Bu işlem denetim izi bırakır. Fark büyükse önce sebebini araştırın.</div>

    <h3>5.10 Z-Raporu (Gün Sonu Kapanışı) <span class="badge bg-danger badge-role">Patron</span></h3>
    <p>Her iş günü sonunda bir kez alınır. Günün toplam satış, masraf ve bakiye özetini kayıt altına alır.</p>
    <div class="step"><div class="step-num">1</div><div>Günün tüm işlemleri tamamlandıktan sonra <strong>Z-Raporu</strong> butonuna basın.</div></div>
    <div class="step"><div class="step-num">2</div><div>Sistem özeti gösterir (açılış bakiyesi, toplam satış, masraflar, kapanış bakiyesi).</div></div>
    <div class="step"><div class="step-num">3</div><div>Onaylayın. Rapor saklanır.</div></div>
    <div class="danger-box"><strong>Önemli:</strong> Z-Raporu aynı gün için iki kez alınırsa birincisinin üzerine yazar. Gün bitmeden almayın.</div>

    <h3>5.11 Son İşlemler Listesi</h3>
    <p>Dashboard'un alt kısmında bugün yapılan son 20 işlem listelenir: stok girişleri, çıkışlar ve kasa işlemleri. Tarih, tür, ürün adı, miktar ve kimin yaptığı gösterilir.</p>
  </section>

  <!-- ======================== STOK DURUMU ======================== -->
  <section id="stok-durumu">
    <h2>6. Stok Durumu</h2>
    <p>Sistemde takip edilen tüm ürünlerin anlık miktarını, kategori bazında gruplandırarak gösterir.</p>

    <h3>6.1 Özet Kartlar</h3>
    <p>Sayfanın üstünde üç kart bulunur:</p>
    <ul>
      <li><span class="badge bg-danger">Sıfır Stok</span> — Miktarı 0 veya negatif olan ürün sayısı</li>
      <li><span class="badge bg-warning text-dark">Kritik</span> — Miktarı 0'ın üstünde ama minimum seviyenin altında olan ürün sayısı</li>
      <li><span class="badge bg-success">Yeterli</span> — Yeterli stokta olan ürün sayısı</li>
    </ul>

    <h3>6.2 Ürün Listesi</h3>
    <p>Ürünler kategoriye göre gruplandırılır. Her satırda şunlar görünür:</p>
    <ul>
      <li>Ürün adı ve SKU kodu</li>
      <li>Anlık stok miktarı ve birimi (renk kodlu badge ile)</li>
      <li>Ortalama maliyet (son alışların ağırlıklı ortalaması)</li>
      <li>Son hareket tarihi</li>
    </ul>

    <h3>6.3 Arama ve Filtreleme</h3>
    <ul>
      <li><strong>Arama kutusu:</strong> Ürün adına veya SKU'ya göre anlık filtreler</li>
      <li><strong>Sıfır / Kritik / Yeterli butonları:</strong> Stok durumuna göre filtreler</li>
    </ul>
  </section>

  <!-- ======================== ÜRÜN YÖNETİMİ ======================== -->
  <section id="urunler">
    <h2>7. Ürün Yönetimi <span class="badge bg-danger badge-role">Patron</span></h2>
    <p>Sistemdeki tüm ürünlerin yönetildiği sayfa. Yeni ürün eklenebilir, mevcutlar düzenlenebilir veya silinebilir.</p>

    <h3>7.1 Ürün Listesi</h3>
    <p>Ürünler kategoriye göre gruplandırılır. Her satırda şunlar görünür: ürün adı, SKU, kategori, birim, anlık stok, minimum stok, sayım sıklığı ve stok takibi durumu.</p>

    <h3>7.2 Yeni Ürün Ekleme</h3>
    <div class="step"><div class="step-num">1</div><div><strong>+ Yeni Ürün</strong> butonuna basın.</div></div>
    <div class="step"><div class="step-num">2</div><div>Aşağıdaki alanları doldurun.</div></div>
    <div class="step"><div class="step-num">3</div><div><strong>Kaydet</strong>'e basın.</div></div>

    <h4>Ürün Alanları</h4>
    <table class="table table-bordered small fields">
      <thead class="table-light"><tr><th>Alan</th><th>Açıklama</th><th>Zorunlu</th></tr></thead>
      <tbody>
        <tr><td>Ürün Adı</td><td>Sistemde görünecek ad. Kısa ve açık olsun (örn: "Çipura", "Zeytinyağı 5 lt").</td><td>Evet</td></tr>
        <tr><td>SKU / Kod</td><td>Stok kodu. Otomatik atanır ama elle de girilebilir.</td><td>Hayır</td></tr>
        <tr><td>Kategori</td><td>Ürünün ait olduğu kategori (Balık, İçecek, Temizlik vb.)</td><td>Evet</td></tr>
        <tr><td>Birim</td><td>Stok takibinde kullanılacak birim (kg, lt, adet, gr vb.)</td><td>Evet</td></tr>
        <tr><td>Min. Stok</td><td>Bu miktarın altına düşünce ürün "Kritik" olur ve Alış Listesi'ne girer. 0 girilirse minimum kontrol yapılmaz.</td><td>Hayır</td></tr>
        <tr><td>Sayım Sıklığı</td><td>Fiziksel sayımlarda bu ürünün ne sıklıkla sayılacağı.</td><td>Hayır</td></tr>
        <tr><td>Stok Takibi</td><td>Açıksa ürün sayım, alış listesi ve stok raporlarına dahil edilir. Kapatılırsa sistem bu ürünü takip etmez.</td><td>Hayır</td></tr>
      </tbody>
    </table>

    <h4>Sayım Sıklığı Seçenekleri</h4>
    <table class="table table-sm table-bordered small">
      <tr><td><strong>Günlük</strong></td><td>Balık, et, taze meyve/sebze gibi çabuk bozulabilir ürünler</td></tr>
      <tr><td><strong>Haftalık</strong></td><td>İçecek, kuru gıda, baharat</td></tr>
      <tr><td><strong>Aylık</strong></td><td>Temizlik malzemeleri, ambalaj, demirbaş</td></tr>
      <tr><td><strong>Sayılmaz</strong></td><td>Stok takibi istenmeyen kalemler</td></tr>
    </table>

    <h3>7.3 Ürün Düzenleme</h3>
    <p>İlgili ürün satırındaki <strong>Düzenle</strong> butonuna basın. Aynı form açılır, mevcut değerler dolu gelir. Değişiklikleri yapıp <strong>Kaydet</strong>'e basın.</p>

    <h3>7.4 Ürün Silme</h3>
    <p><strong>Sil</strong> butonuna basın, onay kutusunda adı doğrulayıp onaylayın.</p>
    <div class="danger-box"><strong>Dikkat:</strong> Ürün silinince stok durumundan, alış listesinden ve sayım listesinden kalkar. Geçmiş hareketler korunur ama yeni işlem yapılamaz. Kullanılmayan ürünü silmek yerine <em>Stok Takibi</em>'ni kapatmayı düşünün — bu daha güvenlidir.</div>

    <h3>7.5 Arama ve Filtreleme</h3>
    <ul>
      <li><strong>Arama kutusu:</strong> Ürün adı veya SKU'ya göre anında filtreler</li>
      <li><strong>Kategori filtresi:</strong> Belirli bir kategorideki ürünleri gösterir</li>
    </ul>
  </section>

  <!-- ======================== TEDARİKÇİ ======================== -->
  <section id="tedarikci">
    <h2>8. Tedarikçi & Cari Hesap</h2>
    <p>Malzeme alındığı tedarikçilerin yönetildiği, borç ve ödeme takibinin yapıldığı modüldür. Her tedarikçiye otomatik bir <strong>cari hesap</strong> açılır.</p>

    <h3>8.1 Tedarikçi Listesi</h3>
    <p>Sayfanın üstünde üç özet kart bulunur:</p>
    <ul>
      <li><strong>Toplam Borç:</strong> Tüm tedarikçilere toplam borcumuz</li>
      <li><strong>Alacak:</strong> Tedarikçilerin bize olan borcu (fazla ödeme durumu)</li>
      <li><strong>Tedarikçi Sayısı:</strong> Aktif tedarikçi sayısı</li>
    </ul>
    <p>Alt tabloda her tedarikçi için ad, iletişim bilgisi, vade günü ve mevcut cari bakiye görünür.</p>

    <h3>8.2 Yeni Tedarikçi Ekleme</h3>
    <div class="step"><div class="step-num">1</div><div><strong>+ Yeni Tedarikçi</strong> butonuna basın.</div></div>
    <div class="step"><div class="step-num">2</div><div>Alanları doldurun:</div></div>

    <table class="table table-bordered small fields ms-3" style="max-width:600px">
      <tr><td>Ad</td><td>Tedarikçi firma veya kişi adı <em>(zorunlu)</em></td></tr>
      <tr><td>Yetkili</td><td>İletişim kurduğunuz kişi adı</td></tr>
      <tr><td>Telefon</td><td>Cep veya sabit hat</td></tr>
      <tr><td>Vergi No</td><td>Fatura için (isteğe bağlı)</td></tr>
      <tr><td>Vade Günü</td><td>Kaç günde ödeme yapılıyor (örn: 30)</td></tr>
      <tr><td>Notlar</td><td>Özel hatırlatmalar</td></tr>
      <tr><td>Aktif</td><td>Pasif yapılırsa listede görünmez</td></tr>
    </table>

    <div class="step"><div class="step-num">3</div><div>Kaydedin. Cari hesap otomatik oluşturulur.</div></div>

    <h3>8.3 Cari Hesap İşlemleri</h3>
    <p>Tedarikçi satırındaki <strong>Cari</strong> butonuna basın. Açılan pencerede:</p>

    <h4>Borç Ekleme</h4>
    <p>Tedarikçiden mal aldığınızda (nakit alım veya fatura) borç kaydı girin.</p>
    <div class="step"><div class="step-num">1</div><div>İşlem Türü: <strong>Borç</strong> seçin.</div></div>
    <div class="step"><div class="step-num">2</div><div>Tutarı girin.</div></div>
    <div class="step"><div class="step-num">3</div><div>Tarih ve açıklama ekleyin (fatura no, ürün adı vb.).</div></div>
    <div class="step"><div class="step-num">4</div><div>Kaydedin. Bakiye artar.</div></div>
    <div class="tip">Stok girişinde tedarikçi seçildiyse borç OTOMATIK işlenir. Bu ekrandan ayrıca girilmez.</div>

    <h4>Ödeme Kaydetme</h4>
    <p>Tedarikçiye ödeme yaptığınızda:</p>
    <div class="step"><div class="step-num">1</div><div>İşlem Türü: <strong>Ödeme</strong> seçin.</div></div>
    <div class="step"><div class="step-num">2</div><div>Ödenen tutarı girin.</div></div>
    <div class="step"><div class="step-num">3</div><div>Tarihi ve açıklamayı doldurun.</div></div>
    <div class="step"><div class="step-num">4</div><div>Kaydedin. Bakiye azalır.</div></div>

    <h4>İşlem Silme</h4>
    <p>Yanlış girilen bir işlemi silmek için işlem satırının yanındaki <strong>Sil</strong> (çöp kutusu) ikonuna basın. Silme işlemi bakiyeyi otomatik günceller.</p>

    <h3>8.4 Bakiye Yorumlama</h3>
    <table class="table table-sm table-bordered small">
      <tr><td class="text-danger fw-semibold">+ Pozitif Bakiye</td><td>Restoranın tedarikçiye borcu var</td></tr>
      <tr><td class="text-success fw-semibold">- Negatif Bakiye</td><td>Tedarikçi restorana borçlu (fazla ödeme yapıldı)</td></tr>
      <tr><td>0</td><td>Hesap dengede</td></tr>
    </table>

    <h3>8.5 Tedarikçi Düzenleme ve Silme</h3>
    <p>Satır üzerindeki <strong>Düzenle</strong> butonuyla bilgiler güncellenebilir. <strong>Sil</strong> ile tedarikçi pasif hale getirilir (cari geçmişi korunur).</p>
  </section>

  <!-- ======================== ALIS LİSTESİ ======================== -->
  <section id="alis">
    <h2>9. Alış Listesi</h2>
    <p>Stoku sıfırın altında veya minimum seviyenin altında olan ürünlerin otomatik olarak listelendiği, sipariş planlaması yapılan ekrandır.</p>

    <h3>9.1 Liste Nasıl Oluşur?</h3>
    <p>Sayfa açıldığında sistem şu koşullara uyan ürünleri otomatik getirir:</p>
    <ul>
      <li>Mevcut stok ≤ 0 <strong>veya</strong></li>
      <li>Min. stok tanımlıysa ve mevcut stok < min. stok</li>
    </ul>
    <p>Ürünler tedarikçiye göre gruplandırılır. Tedarikçisi tanımlı olmayan ürünler sonda listelenir.</p>

    <h3>9.2 Kullanım Adımları</h3>
    <div class="step"><div class="step-num">1</div><div>Sayfa açılır, eksik ürünler otomatik gelir.</div></div>
    <div class="step"><div class="step-num">2</div><div>Her ürün için <strong>sipariş miktarını</strong> kontrol edin. Varsayılan değer: mevcut stoku minimum seviyeye tamamlayacak miktar (minimum tanımlı değilse 1).</div></div>
    <div class="step"><div class="step-num">3</div><div>Sipariş vermeyeceğiniz ürünlerin solundaki <strong>kutusunu kaldırın</strong>.</div></div>
    <div class="step"><div class="step-num">4</div><div>Sayfanın altındaki <strong>Tahmini Toplam</strong> işaretli ürünlerin toplam maliyetini gösterir.</div></div>
    <div class="step"><div class="step-num">5</div><div><strong>Yazdır</strong> butonuyla listeyi çıktı alın veya ekran görüntüsünü tedarikçiye gönderin.</div></div>

    <div class="tip">Alış listesi yalnızca görüntüleme ve planlama içindir. Fiili alım yapıldığında Kasa → <strong>Stok Girişi</strong> üzerinden kayıt oluşturun.</div>
    <div class="tip">Son fiyat, o ürün için en son yapılan alışın birim fiyatı olarak gösterilir. Fiyat değişmişse stok girişinde güncel fiyatı yazmanız yeterli — sistem günceller.</div>
  </section>

  <!-- ======================== FİZİKSEL SAYIM ======================== -->
  <section id="sayim">
    <h2>10. Fiziksel Sayım</h2>
    <p>Depodaki ürünleri gerçek anlamda sayıp sistem kayıtlarıyla karşılaştırmanızı sağlar. Farklar tespit edilir ve stok düzeltilir.</p>

    <h3>10.1 Sayım Listesi (Ana Ekran)</h3>
    <p>Sayım sayfası ilk açıldığında geçmiş sayımlar listelenir. Her sayım için tarih, durum ve toplam fark değeri görünür.</p>
    <ul>
      <li><strong>Devam Ediyor:</strong> Henüz onaylanmamış, üzerinde çalışılan sayım</li>
      <li><strong>Onaylandı:</strong> Tamamlanmış ve stoğa işlenmiş sayım</li>
    </ul>

    <h3>10.2 Yeni Sayım Başlatma</h3>
    <div class="step"><div class="step-num">1</div><div><strong>Yeni Sayım</strong> butonuna basın.</div></div>
    <div class="step"><div class="step-num">2</div><div>Sistem otomatik olarak tüm takipli ürünlerin listesini açar.</div></div>
    <div class="step"><div class="step-num">3</div><div>Üst çubukta ilerleme durumu görünür: <em>Sayılan X / Toplam Y</em></div></div>
    <div class="tip">Sistemde zaten açık (devam eden) bir sayım varsa otomatik olarak o sayıma bağlanırsınız — yeni oluşturulmaz.</div>

    <h3>10.3 Ürün Sayma</h3>
    <div class="step"><div class="step-num">1</div><div>Saymak istediğiniz ürünün yanındaki <strong>Gir</strong> butonuna basın.</div></div>
    <div class="step"><div class="step-num">2</div><div>Açılan pencerede hızlı tuşları (0, ½, 1, 2, 5, 10) veya klavyeyi kullanarak miktarı girin.</div></div>
    <div class="step"><div class="step-num">3</div><div><strong>Kaydet</strong>'e basın. Ürün yeşil arka plana döner ve "Sayıldı" olarak işaretlenir.</div></div>
    <div class="step"><div class="step-num">4</div><div>Yanılırsanız aynı ürüne tekrar <strong>Gir</strong> basarak değeri düzeltebilirsiniz.</div></div>

    <h4>Filtreler</h4>
    <ul>
      <li><strong>Tümü:</strong> Tüm ürünleri göster</li>
      <li><strong>Bekleyen:</strong> Henüz sayılmamış ürünleri göster</li>
      <li><strong>Sayıldı:</strong> Girilen ürünleri göster</li>
    </ul>
    <div class="tip">Büyük listelerde <strong>Bekleyen</strong> filtresini kullanarak atladığınız ürünleri kolayca bulun.</div>
    <div class="tip">Sayım yarıda kalabilir. Tarayıcıyı kapatıp döndüğünüzde kaldığınız yerden devam edebilirsiniz — girdiğiniz veriler kaybolmaz.</div>

    <h3>10.4 Sayım Özeti ve Onay</h3>
    <div class="step"><div class="step-num">1</div><div>Tüm ürünler sayıldıktan sonra <strong>Sayımı Tamamla</strong> butonuna basın.</div></div>
    <div class="step"><div class="step-num">2</div><div>Özet ekranı açılır:
      <ul>
        <li><span class="badge bg-warning text-dark">Fazla</span> — Sistemdeki miktardan gerçekte fazla olan ürünler</li>
        <li><span class="badge bg-danger">Açık</span> — Sistemdeki miktardan gerçekte az olan ürünler</li>
      </ul>
    </div></div>
    <div class="step"><div class="step-num">3</div><div>Farkları gözden geçirin.</div></div>
    <div class="step"><div class="step-num">4</div><div><span class="badge bg-danger badge-role">Patron</span> <strong>Onayla</strong> butonuna basarak stoğu günceller.</div></div>

    <h4>Onaylama Sonrası Ne Olur?</h4>
    <ul>
      <li>Tüm ürünlerin stok bakiyesi sayılan miktarla güncellenir.</li>
      <li>Eksik (açık) ürünler için "Sayım Farkı" çıkışı otomatik oluşturulur.</li>
      <li>Sayım durumu "Onaylandı" olarak işaretlenir.</li>
    </ul>
    <div class="danger-box"><strong>Dikkat:</strong> Sayım onayı geri alınamaz. Onaylamadan önce özeti dikkatlice kontrol edin.</div>

    <h3>10.5 Sayım Tavsiyeleri</h3>
    <ul>
      <li>Sayımı mutfak kapalıyken veya mesai dışında yapın — aktif kullanım sırasında stok hareketi değişebilir.</li>
      <li>Büyük mutfaklarda sayımı kategoriye göre bölümlere ayırın (önce et, sonra sebze vb.).</li>
      <li>Sayım sırasında yeni stok girişi veya çıkışı yapılmamasına dikkat edin.</li>
      <li>Haftalık veya iki haftada bir düzenli sayım yapılması önerilir.</li>
    </ul>
  </section>

  <!-- ======================== RAPOR ======================== -->
  <section id="rapor">
    <h2>11. Rapor</h2>
    <p>Seçilen tarih aralığındaki tüm stok ve kasa hareketlerinin özetlendiği modüldür.</p>

    <h3>11.1 Tarih Aralığı Seçimi</h3>
    <p>Sağ üstteki <strong>Başlangıç</strong> ve <strong>Bitiş</strong> tarih alanlarından aralık girin, ardından <strong>Uygula</strong>'ya basın. Ya da hazır butonları kullanın:</p>
    <ul>
      <li><strong>Bu Ay:</strong> Ayın 1'inden bugüne</li>
      <li><strong>Bu Hafta:</strong> Pazartesiden bugüne</li>
      <li><strong>Bugün:</strong> Sadece bugün</li>
    </ul>

    <h3>11.2 Özet Kartlar</h3>
    <table class="table table-sm table-bordered small">
      <thead class="table-light"><tr><th>Kart</th><th>Gösterilen Değer</th></tr></thead>
      <tbody>
        <tr><td class="text-success fw-semibold">Toplam Alış</td><td>Seçilen dönemde satın alınan malzemelerin toplam maliyeti (₺) ve giriş sayısı</td></tr>
        <tr><td class="text-danger fw-semibold">Toplam Çıkış</td><td>Tüm çıkış tiplerinin toplam maliyeti ve çıkış sayısı</td></tr>
        <tr><td class="text-warning fw-semibold">Fire / Kayıp</td><td>Yalnızca fire, kayıp ve sayım açığı olarak kaydedilen tutar</td></tr>
        <tr><td class="text-info fw-semibold">Kasa Bakiyesi</td><td>Anlık nakit bakiye (tarih aralığından bağımsız)</td></tr>
      </tbody>
    </table>

    <h3>11.3 Hareketler Sekmesi</h3>
    <p>Dönem içindeki her işlemi sıralı listeler. Sütunlar: tarih, hareket türü, ürün adı, kategori, miktar, tutar, personel.</p>

    <h4>Filtreleme</h4>
    <ul>
      <li><strong>Tümü / Girişler / Çıkışlar:</strong> Hareket tipine göre süzer</li>
      <li><strong>Ürün ara:</strong> Belirli bir ürünün hareketlerini bulur</li>
    </ul>

    <h4>Renk Kodları (Hareketler Listesi)</h4>
    <ul>
      <li><span style="border-left:3px solid #198754; padding-left:6px">Yeşil kenarlı satır</span> — Stok girişi</li>
      <li><span style="border-left:3px solid #dc3545; padding-left:6px">Kırmızı kenarlı satır</span> — Stok çıkışı</li>
      <li><span style="border-left:3px solid #fd7e14; padding-left:6px">Turuncu kenarlı satır</span> — Fire / kayıp</li>
    </ul>

    <h3>11.4 Gider Kalemleri Sekmesi</h3>
    <p>Ürün bazında toplam alış, çıkış ve fire tutarlarını tek tabloda gösterir. En yüksek alış maliyetinden sıralar. Hangi ürüne ne kadar harcandığını görmek için kullanılır.</p>

    <h3>11.5 Raporu Yazdırma / Dışa Aktarma</h3>
    <p>Tarayıcının <strong>Yazdır</strong> (Ctrl+P) fonksiyonuyla raporu yazdırabilir veya PDF olarak kaydedebilirsiniz. Kılavuz sayfasındaki yazdır düğmesi de aynı şekilde çalışır.</p>
  </section>

  <!-- ======================== DEMİRBAŞ ======================== -->
  <section id="demirbas">
    <h2>12. Demirbaş Envanteri <span class="badge bg-danger badge-role">Patron</span></h2>
    <p>Mutfak ekipmanları, mobilya, elektronik cihazlar ve diğer sabit varlıkların takip edildiği modüldür. Sadece Patron rolü erişebilir.</p>

    <h3>12.1 Özet Kartlar</h3>
    <ul>
      <li><strong>Toplam Kalem:</strong> Demirbaş listesindeki ürün türü sayısı</li>
      <li><strong>Toplam Adet:</strong> Sistemdeki tüm demirbaş adedi</li>
      <li><strong>Stoksuz Kalem:</strong> Adet 0 veya altına düşen ürün sayısı</li>
      <li><strong>Bu Ay Kayıp (₺):</strong> Son 30 gündeki kırık ve kayıp miktarlarının toplam maliyeti</li>
    </ul>

    <h3>12.2 Demirbaş Girişi</h3>
    <p>Yeni ekipman satın alındığında veya envantere eklenecek bir varlık olduğunda kullanılır.</p>
    <div class="step"><div class="step-num">1</div><div><strong>+ Giriş</strong> butonuna basın (veya ürün satırındaki Giriş butonunu kullanın).</div></div>
    <div class="step"><div class="step-num">2</div><div>Demirbaş türünü seçin (dropdown listeden).</div></div>
    <div class="step"><div class="step-num">3</div><div>Adet girin (ör: 2 yeni sandalye).</div></div>
    <div class="step"><div class="step-num">4</div><div>Birim fiyat girin (raporlarda maliyet hesabı için).</div></div>
    <div class="step"><div class="step-num">5</div><div>Not alanına fatura numarası, satıcı bilgisi vb. ekleyin.</div></div>
    <div class="step"><div class="step-num">6</div><div>Kaydedin. Adet artar.</div></div>

    <h3>12.3 Kayıp / Kırık Kaydı</h3>
    <p>Ekipman kırıldığında, çalındığında veya hurdaya ayrıldığında kullanılır.</p>
    <div class="step"><div class="step-num">1</div><div><strong>Kayıp / Kırık</strong> butonuna basın (veya ürün satırındaki Kayıp butonunu).</div></div>
    <div class="step"><div class="step-num">2</div><div>Demirbaş türünü seçin.</div></div>
    <div class="step"><div class="step-num">3</div><div>Tür seçin: <em>Kırık/Hasar</em> veya <em>Kayıp/Çalıntı</em>.</div></div>
    <div class="step"><div class="step-num">4</div><div>Adet girin.</div></div>
    <div class="step"><div class="step-num">5</div><div>Açıklama ekleyin (ne oldu, nerede vb.).</div></div>
    <div class="step"><div class="step-num">6</div><div>Kaydedin. Adet azalır ve kayıp raporda görünür.</div></div>

    <h3>12.4 Geçmiş Kayıtlar</h3>
    <p><strong>Geçmiş</strong> butonuyla son 30 günün tüm kayıp ve kırık kayıtları listelenir. Tarih, ürün, tür, adet, maliyet ve kaydı yapan personel görünür.</div>

    <h3>12.5 Listede Olmayan Demirbaş Türü Ekleme</h3>
    <p>Dropdown'da görmediğiniz bir ekipman türü varsa:</p>
    <div class="step"><div class="step-num">1</div><div><strong>Ürün Yönetimi</strong> sayfasına gidin.</div></div>
    <div class="step"><div class="step-num">2</div><div><strong>+ Yeni Ürün</strong>'e basın.</div></div>
    <div class="step"><div class="step-num">3</div><div>Kategori olarak <strong>Demirbaş</strong> seçin.</div></div>
    <div class="step"><div class="step-num">4</div><div>Ürünü kaydedin. Artık Demirbaş giriş/kayıp formlarında görünecektir.</div></div>

    <h3>12.6 Sistemde Önceden Tanımlı Demirbaş Türleri</h3>
    <p>Sistem kurulumunda aşağıdaki gruplar ve ürünler önceden yüklenmiştir:</p>
    <ul>
      <li>Mutfak Makineleri (buzdolabı, dondurucular, buz makinası, bulaşık makinası, fritöz, fırın, ızgara vb.)</li>
      <li>Tencere & Tava (endüstriyel boyutlar, GN kaplar, bıçak setleri vb.)</li>
      <li>Servis Tabak & Kase (tüm boy ve tipler)</li>
      <li>Bardak & İçecek (su, şarap, rakı, çay, kahve, bira bardakları)</li>
      <li>Çatal-Bıçak-Kaşık (tüm tipler)</li>
      <li>Masa & Salon (masa, sandalye, örtü, mumluk, şemsiye vb.)</li>
      <li>Servis Ekipmanları (tepsi, menü, tirbuşon, buz kovası vb.)</li>
      <li>Teknoloji & Ofis (POS, tablet, kamera, müzik sistemi vb.)</li>
      <li>Depolama & Taşıma</li>
      <li>Temizlik & Bakım</li>
    </ul>
  </section>

  <!-- ======================== PERSONEL ======================== -->
  <section id="personel">
    <h2>13. Personel Yönetimi <span class="badge bg-danger badge-role">Patron</span></h2>
    <p>Sistem kullanıcılarının eklenip yönetildiği ve maaş/avans takibinin yapıldığı modüldür. Sadece Patron erişebilir.</p>

    <h3>13.1 Personel Listesi</h3>
    <p>Tüm aktif ve pasif personel listelenir. Her satırda: ad soyad, kullanıcı adı, rol, aktiflik durumu ve son giriş tarihi görünür.</p>

    <h3>13.2 Yeni Personel Ekleme</h3>
    <div class="step"><div class="step-num">1</div><div><strong>+ Yeni Personel</strong>'e basın.</div></div>
    <div class="step"><div class="step-num">2</div><div>Alanları doldurun:</div></div>
    <table class="table table-bordered small fields ms-3" style="max-width:600px">
      <tr><td>Ad Soyad</td><td>Tam adı <em>(zorunlu)</em></td></tr>
      <tr><td>Kullanıcı Adı</td><td>Sisteme giriş için kullanılır <em>(zorunlu, benzersiz)</em></td></tr>
      <tr><td>Şifre</td><td>En az 6 karakter <em>(zorunlu)</em></td></tr>
      <tr><td>Rol</td><td>Patron / Kasiyer / Garson <em>(zorunlu)</em></td></tr>
      <tr><td>PIN</td><td>4 rakam — Masraf işlemleri için kullanılır <em>(isteğe bağlı)</em></td></tr>
    </table>
    <div class="step"><div class="step-num">3</div><div>Kaydedin. Personel artık sisteme giriş yapabilir.</div></div>
    <div class="tip">Yeni personele geçici bir şifre verin ve sisteme ilk girişte değiştirmelerini isteyin. (Şifre değiştirme şu an yalnızca Patron tarafından yapılabilir.)</div>

    <h3>13.3 Personel Bilgisi Güncelleme</h3>
    <p><strong>Düzenle</strong> butonuyla ad, kullanıcı adı, rol ve aktiflik durumu güncellenebilir. Şifre ve PIN değiştirilmek istenirse ilgili alanlara yeni değer girilir; boş bırakılırsa değişmez.</p>

    <h3>13.4 Maaş Tanımlama</h3>
    <div class="step"><div class="step-num">1</div><div>Personel satırındaki <strong>Finans</strong> butonuna basın.</div></div>
    <div class="step"><div class="step-num">2</div><div><strong>Maaş Tanımla</strong> bölümünde tutarı ve başlangıç ayını girin.</div></div>
    <div class="step"><div class="step-num">3</div><div>Kaydedin. Bu tutar, o aydan itibaren geçerli olur.</div></div>
    <div class="tip">Maaş geçmişi tutulur. Zamlar için yeni ay girilirse eski değer korunur.</div>

    <h3>13.5 Avans Kaydı</h3>
    <div class="step"><div class="step-num">1</div><div>Finans sayfasında <strong>Avans Ekle</strong>'ye basın.</div></div>
    <div class="step"><div class="step-num">2</div><div>Tutarı ve tarihi girin.</div></div>
    <div class="step"><div class="step-num">3</div><div>Kaydedin. Ay sonu ödemesinde otomatik düşülür.</div></div>
    <p>Yanlış girilen avansı silmek için çöp kutusu ikonuna basın.</p>

    <h3>13.6 Ay Sonu Ödeme</h3>
    <div class="step"><div class="step-num">1</div><div>Finans sayfasında ayı seçin (varsayılan: bu ay).</div></div>
    <div class="step"><div class="step-num">2</div><div>Sistem brüt maaş ve toplam avansı gösterir; net ödeme = brüt − avanslar.</div></div>
    <div class="step"><div class="step-num">3</div><div><strong>Ödemeyi Kaydet</strong>'e basın.</div></div>
    <div class="step"><div class="step-num">4</div><div>Ödeme tarihi ve not ekleyebilirsiniz.</div></div>

    <h3>13.7 Personeli Pasif Yapma</h3>
    <p>İşten ayrılan personeli <strong>silmeyin</strong> — Düzenle ekranında <em>Aktif</em> anahtarını kapatın. Personel artık sisteme giremez ama geçmiş kayıtları korunur.</p>
  </section>

  <!-- ======================== RENK SİSTEMİ ======================== -->
  <section id="renkler">
    <h2>14. Renk Sistemi</h2>
    <p>Sistem genelinde tutarlı renk kodlaması kullanılır:</p>
    <table class="table table-bordered small">
      <thead class="table-light"><tr><th>Renk</th><th>Anlam</th><th>Nerede Görülür</th></tr></thead>
      <tbody>
        <tr><td><span class="badge bg-danger">Kırmızı</span></td><td>Kritik / Sıfır / Hata / Silme</td><td>Stok, uyarılar, silme butonları</td></tr>
        <tr><td><span class="badge bg-warning text-dark">Sarı</span></td><td>Dikkat / Minimum altı</td><td>Kritik stok, uyarılar</td></tr>
        <tr><td><span class="badge bg-success">Yeşil</span></td><td>Yeterli / Tamam / Giriş</td><td>Yeterli stok, başarı mesajları, stok girişleri</td></tr>
        <tr><td><span class="badge bg-primary">Mavi</span></td><td>Bilgi / Eylem / Vurgu</td><td>Kaydet butonları, sayım onayı</td></tr>
        <tr><td><span class="badge bg-secondary">Gri</span></td><td>İptal / Pasif / Nötr</td><td>İptal butonları, pasif durumlar</td></tr>
        <tr><td><span class="badge bg-info text-dark">Açık Mavi</span></td><td>Garson rolü / Bilgilendirme</td><td>Rol etiketleri, bilgi kartları</td></tr>
      </tbody>
    </table>
  </section>

  <!-- ======================== SIK SORULAN ======================== -->
  <section id="sikcasorulan">
    <h2>15. Sık Sorulan Sorular</h2>

    <h3>Stok miktarı yanlış görünüyor, ne yapabilirim?</h3>
    <p>Fiziksel sayım modülünü kullanın. Ürünleri sayın, onayladığınızda sistem bakiyesi gerçek miktara eşitlenir. Küçük bir düzeltme için Stok Çıkışı (fazla düşürmek) veya Stok Girişi (eksik ise eklemek) de kullanılabilir.</p>

    <h3>Yanlış stok girişi yaptım, nasıl düzeltebilirim?</h3>
    <p>Girişi silme özelliği şu an mevcut değildir. Miktarı dengelemek için ters yönde bir kayıt girin: fazla girdiyseniz aynı miktarda çıkış yapın, az girdiyseniz tekrar giriş yapın.</p>

    <h3>Tedarikçi cari bakiyesi yanlış, nasıl düzeltirim?</h3>
    <p>Tedarikçi sayfasından ilgili tedarikçinin Cari butonuna basın. Yanlış işlemi silebilir veya düzeltme amaçlı yeni bir işlem ekleyebilirsiniz.</p>

    <h3>Ürün stok girişinde listede görünmüyor?</h3>
    <p>Ürün Yönetimi'nde ürünün <em>Stok Takibi</em> özelliğinin açık olduğunu kontrol edin. Kapalıysa Düzenle ile açın.</p>

    <h3>Sayım ekranında bir ürün görünmüyor?</h3>
    <p>Fiziksel sayım yalnızca <em>Stok Takibi</em> açık ürünleri listeler. Ürün Yönetimi'nde takibi açın.</p>

    <h3>Demirbaş listesinde istediğim ekipman yok?</h3>
    <p>Ürün Yönetimi'nde + Yeni Ürün ile ekleyin; kategori olarak <em>Demirbaş</em> seçin. Kaydettiğinizde Demirbaş sayfasında görünür.</p>

    <h3>Şifremi veya PIN'imi unuttum?</h3>
    <p>Patron, Personel yönetimindeki Düzenle ekranından yeni şifre veya PIN tanımlayabilir.</p>

    <h3>Alış listesinde ürün var ama stokta yeterli görünüyor?</h3>
    <p>Sayfa önbelleğini yenileyin (F5 veya sayfayı yenile). Hâlâ görünüyorsa ürünün Minimum Stok değerini Ürün Yönetimi'nden kontrol edin — min. değer mevcut stoktan yüksek olabilir.</p>

    <h3>Kasa bakiyesi ile fiili kasa arasında fark var?</h3>
    <p>Patron, Kasa Düzelt işlemiyle bakiyeyi doğru değere getirebilir. Sebebini bulmak için o günün hareketlerini Rapor sayfasından inceleyin.</p>

    <h3>Sistem yavaş açılıyor?</h3>
    <p>İnternet bağlantınızı kontrol edin. Yavaşlık devam ederse tarayıcı önbelleğini temizleyin: Chrome'da Ctrl+Shift+Del → Önbelleğe alınmış görüntüler → Temizle.</p>

    <h3>Mobil cihazda düzgün açılmıyor?</h3>
    <p>Sistem tüm modern tarayıcılarda çalışır. Telefonda Chrome veya Safari kullanmanız önerilir. Sayfayı tam ekran modunda (masaüstüne ekle) kullanmak daha rahat bir deneyim sağlar.</p>
  </section>

  <!-- ======================== HATA MESAJLARI ======================== -->
  <section id="hata">
    <h2>16. Hata Mesajları</h2>
    <table class="table table-bordered small">
      <thead class="table-dark"><tr><th>Mesaj</th><th>Anlamı</th><th>Çözüm</th></tr></thead>
      <tbody>
        <tr><td>Oturum açmanız gerekiyor</td><td>Oturum süresi dolmuş</td><td>Sayfayı yenileyin, tekrar giriş yapın</td></tr>
        <tr><td>Yetki yok</td><td>Rolünüz bu işlemi yapamaz</td><td>Patron'a başvurun</td></tr>
        <tr><td>PIN hatalı</td><td>Girilen PIN yanlış</td><td>Doğru PIN'i girin veya Patron'a başvurun</td></tr>
        <tr><td>… zorunlu</td><td>Boş bırakılamaz alan var</td><td>Formdaki eksik alanı doldurun</td></tr>
        <tr><td>Sayım açık değil</td><td>Sayıma bağlanılamadı</td><td>Sayım sayfasından yeni sayım başlatın</td></tr>
        <tr><td>Sayımda hiç ürün girilmemiş</td><td>Onaylamaya çalışıldı ama ürün sayılmamış</td><td>En az bir ürün sayın</td></tr>
        <tr><td>Sunucu hatası</td><td>Geçici teknik sorun</td><td>Birkaç saniye bekleyip tekrar deneyin</td></tr>
        <tr><td>Endpoint bulunamadı</td><td>Eski sayfa önbelleğe alınmış</td><td>Ctrl+Shift+R ile sayfayı sıfırlayarak yenileyin</td></tr>
      </tbody>
    </table>
  </section>

  <hr class="my-5">
  <p class="text-muted small text-center">
    Müdavim Restoran Yönetim Sistemi v4 &nbsp;·&nbsp; mudavimpalamutbuku.com &nbsp;·&nbsp; <?= date('Y') ?><br>
    Bu kılavuz sisteme giriş yapan tüm kullanıcılar tarafından erişilebilir.
  </p>

</div><!-- /content-col -->
</div><!-- /row -->
</div><!-- /container -->

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
const sections = document.querySelectorAll('section[id]');
const links    = document.querySelectorAll('#tocNav .nav-link');
window.addEventListener('scroll', () => {
  let current = '';
  sections.forEach(s => { if (window.scrollY >= s.offsetTop - 80) current = s.id; });
  links.forEach(l => l.classList.toggle('active', l.getAttribute('href') === '#' + current));
}, { passive: true });
</script>
</body>
</html>
