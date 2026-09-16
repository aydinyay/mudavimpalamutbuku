<?php
declare(strict_types=1);
require_once __DIR__ . '/autoload.php';
use Mudavim\Core\Auth;
Auth::requireLogin('login.php');
$user = Auth::user();
?>
<!DOCTYPE html>
<html lang="tr" data-bs-theme="light">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no">
<title>Müdavim — Palamutbükü</title>
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
<link rel="stylesheet" href="assets/style.css">
</head>
<body>

<!-- ── NAVBAR ─────────────────────────────────────────────── -->
<nav class="navbar navbar-dark bg-mud-dark sticky-top">
  <div class="container-fluid px-3">
    <span class="navbar-brand mb-0 fw-bold">
      <i class="bi bi-fish me-1"></i>Müdavim
      <small class="text-mud-muted fs-7 ms-1 d-none d-sm-inline">Palamutbükü</small>
    </span>
    <div class="d-flex align-items-center gap-2">
      <button class="btn btn-sm btn-outline-light position-relative" id="btnAlerts" title="Stok Uyarıları">
        <i class="bi bi-bell-fill"></i>
        <span class="badge bg-danger position-absolute top-0 start-100 translate-middle rounded-pill d-none" id="alertBadge">0</span>
      </button>
      <div class="dropdown">
        <button class="btn btn-sm btn-outline-light dropdown-toggle d-flex align-items-center gap-1" type="button" data-bs-toggle="dropdown" aria-expanded="false">
          <i class="bi bi-person-fill"></i>
          <span class="d-none d-sm-inline"><?= htmlspecialchars($user['name']) ?></span>
          <span class="badge bg-mud-role ms-1"><?= htmlspecialchars(strtoupper($user['role'])) ?></span>
        </button>
        <ul class="dropdown-menu dropdown-menu-end">
          <li><span class="dropdown-item-text text-muted small"><?= htmlspecialchars($user['username']) ?></span></li>
          <li><hr class="dropdown-divider"></li>
          <li><a class="dropdown-item" href="/yonetim/"><i class="bi bi-arrow-left-right me-2 text-warning"></i>Yönetim Paneli</a></li>
          <li><hr class="dropdown-divider"></li>
          <li><a class="dropdown-item text-danger" href="logout.php"><i class="bi bi-box-arrow-right me-2"></i>Çıkış Yap</a></li>
        </ul>
      </div>
    </div>
  </div>
</nav>

<!-- ── OFFLİNE BANNER ────────────────────────────────────── -->
<div id="offlineBanner" class="d-none alert alert-warning alert-dismissible rounded-0 mb-0 py-2 text-center small">
  <i class="bi bi-wifi-off me-1"></i>
  <strong>Çevrimdışı:</strong> Girişler kaydedildi, bağlantı gelince gönderilecek.
  <span id="offlineQueueCount" class="ms-1"></span>
</div>

<!-- ── KAPSAYICI ─────────────────────────────────────────── -->
<div class="container-fluid px-2 px-sm-3 py-3" style="max-width:900px;margin:0 auto;">

  <!-- GÜNLÜK ÖZET ── -->
  <div class="row g-2 mb-3" id="summaryRow">
    <div class="col-3">
      <div class="summary-card text-center">
        <div class="summary-val text-success" id="sumEntries">—</div>
        <div class="summary-lbl">Giriş</div>
      </div>
    </div>
    <div class="col-3">
      <div class="summary-card text-center">
        <div class="summary-val text-primary" id="sumOutlets">—</div>
        <div class="summary-lbl">Çıkış</div>
      </div>
    </div>
    <div class="col-3">
      <div class="summary-card text-center">
        <div class="summary-val text-danger" id="sumWaste">—</div>
        <div class="summary-lbl">Zayi ₺</div>
      </div>
    </div>
    <div class="col-3">
      <div class="summary-card text-center">
        <div class="summary-val text-warning" id="sumCash">—</div>
        <div class="summary-lbl">Kasa ₺</div>
      </div>
    </div>
  </div>

  <!-- HIZLI İŞLEM BAŞLIĞI ── -->
  <div class="d-flex align-items-center justify-content-between mb-2">
    <h6 class="fw-bold mb-0 text-muted text-uppercase fs-7 ls-1">
      <i class="bi bi-lightning-charge-fill text-warning me-1"></i>Hızlı İşlem
    </h6>
    <div class="d-flex gap-1">
      <a href="kasa.php" class="btn btn-sm btn-outline-secondary py-0">
        <i class="bi bi-cash-stack me-1"></i>Kasa
      </a>
      <a href="reports.php" class="btn btn-sm btn-outline-secondary py-0">
        <i class="bi bi-bar-chart-fill me-1"></i>Rapor
      </a>
      <a href="stok.php" class="btn btn-sm btn-outline-secondary py-0">
        <i class="bi bi-boxes me-1"></i>Stok
      </a>
      <a href="tedarikci.php" class="btn btn-sm btn-outline-secondary py-0">
        <i class="bi bi-truck me-1"></i>Tedarikçi
      </a>
      <a href="alis.php" class="btn btn-sm btn-outline-secondary py-0">
        <i class="bi bi-cart3 me-1"></i>Alış
      </a>
      <a href="sayim.php" class="btn btn-sm btn-outline-secondary py-0">
        <i class="bi bi-clipboard2-check me-1"></i>Sayım
      </a>
      <a href="products.php" class="btn btn-sm btn-outline-secondary py-0">
        <i class="bi bi-box-seam me-1"></i>Ürünler
      </a>
      <?php if ($user['role'] === 'patron'): ?>
      <a href="staff.php" class="btn btn-sm btn-outline-secondary py-0">
        <i class="bi bi-people-fill me-1"></i>Personel
      </a>
      <?php endif; ?>
      <button class="btn btn-sm btn-outline-secondary py-0" id="btnShowAll" data-expanded="false">
        <i class="bi bi-grid me-1"></i><span>Tüm İşlemler</span>
      </button>
    </div>
  </div>

  <!-- ÖNERİLEN BUTONLAR (context-aware) ── -->
  <div id="quickActions" class="row g-2 mb-2">
    <!-- JS tarafından doldurulur -->
    <div class="col-12 text-center py-3">
      <div class="spinner-border spinner-border-sm text-secondary" role="status"></div>
    </div>
  </div>

  <!-- TÜM İŞLEMLER (varsayılan gizli) ── -->
  <div id="allActions" class="row g-2 mb-3 d-none">
    <!-- JS tarafından doldurulur -->
  </div>

  <!-- STOK UYARILARI ── -->
  <div id="alertsSection" class="d-none mb-3">
    <h6 class="fw-bold mb-2 text-muted text-uppercase fs-7 ls-1">
      <i class="bi bi-exclamation-triangle-fill text-danger me-1"></i>Stok Uyarıları
    </h6>
    <div id="alertsList"></div>
  </div>

  <!-- DEMİRBAŞ ENVANTERİ ── -->
  <div class="mb-3">
    <div class="d-flex align-items-center justify-content-between mb-2">
      <h6 class="fw-bold mb-0 text-muted text-uppercase fs-7 ls-1">
        <i class="bi bi-box-seam me-1"></i>Demirba&#351; Envanteri
      </h6>
      <div class="d-flex gap-1">
        <button class="btn btn-sm btn-outline-secondary py-0" id="btnAssetHistory" title="Bu ayin kayiplari">
          <i class="bi bi-clock-history me-1"></i><span class="d-none d-sm-inline">Gecmis</span>
        </button>
        <button class="btn btn-sm btn-outline-success py-0" id="btnAssetAdd" title="Yeni demirba&#351; ekle">
          <i class="bi bi-plus-lg me-1"></i><span class="d-none d-sm-inline">Ekle</span>
        </button>
      </div>
    </div>
    <div id="assetGrid" class="row g-2">
      <div class="col-12 text-center py-3">
        <div class="spinner-border spinner-border-sm text-secondary"></div>
      </div>
    </div>
    <!-- Bu ay kayıp özeti -->
    <div id="assetHistoryPanel" class="d-none mt-2 section-card">
      <div class="p-2 border-bottom d-flex align-items-center justify-content-between">
        <span class="fw-semibold small">Bu Ayin Kayiplari</span>
        <button class="btn btn-sm py-0 text-muted" id="btnAssetHistoryClose">
          <i class="bi bi-x-lg"></i>
        </button>
      </div>
      <div id="assetHistoryList" style="max-height:220px;overflow-y:auto"></div>
    </div>
  </div>

  <!-- BUGÜN'ÜN HAREKETLERİ ── -->
  <div class="mb-3">
    <div class="d-flex align-items-center justify-content-between mb-2">
      <h6 class="fw-bold mb-0 text-muted text-uppercase fs-7 ls-1">
        <i class="bi bi-clock-history me-1"></i>Bugün
      </h6>
      <small class="text-muted" id="recentDate"></small>
    </div>
    <div id="recentList">
      <div class="text-center py-3 text-muted small">
        <div class="spinner-border spinner-border-sm" role="status"></div>
      </div>
    </div>
  </div>

</div><!-- /container -->

<!-- ═══════════════════════════════════════════════════════
     MODAL — EVRENSEL İŞLEM FORMU
     (Zayi, İkram, Patron Çıkışı, Stok Düşüm, Alım)
════════════════════════════════════════════════════════ -->
<div class="modal fade" id="modalAction" tabindex="-1" data-bs-backdrop="static">
  <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable">
    <div class="modal-content">
      <div class="modal-header border-0 pb-1">
        <h5 class="modal-title fw-bold" id="modalActionTitle">İşlem</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body pt-1">
        <form id="frmAction" novalidate>
          <input type="hidden" id="frmAction_type" name="type">
          <input type="hidden" id="frmAction_scenario" name="scenario">
          <input type="hidden" id="frmAction_productId" name="product_id">
          <input type="hidden" id="frmAction_locationId" name="location_id" value="<?= (int)$user['location_id'] ?>">

          <!-- ÜRÜN ARAMA ── -->
          <div class="mb-3 position-relative">
            <label class="form-label fw-semibold">Ürün</label>
            <div class="input-group">
              <span class="input-group-text"><i class="bi bi-search"></i></span>
              <input type="text" class="form-control form-control-lg ac-input"
                     id="frmAction_productSearch"
                     placeholder="Ürün adı yazın…"
                     autocomplete="off"
                     data-target="frmAction_productId"
                     data-stock-display="frmAction_stockInfo">
            </div>
            <div class="ac-dropdown d-none" id="frmAction_acDrop"></div>
            <!-- Seçilen ürünün anlık stoğu -->
            <div id="frmAction_stockInfo" class="form-text d-none mt-1"></div>
          </div>

          <!-- MİKTAR ── -->
          <div class="mb-3">
            <label class="form-label fw-semibold">Miktar</label>
            <div class="input-group">
              <button type="button" class="btn btn-outline-secondary qty-dec">
                <i class="bi bi-dash-lg"></i>
              </button>
              <input type="number" class="form-control form-control-lg text-center fw-bold"
                     id="frmAction_qty" name="quantity"
                     min="0" step="0.1" value="" placeholder="0">
              <button type="button" class="btn btn-outline-secondary qty-inc">
                <i class="bi bi-plus-lg"></i>
              </button>
              <span class="input-group-text fw-semibold" id="frmAction_unit" style="min-width:52px">—</span>
            </div>
          </div>

          <!-- ALIM'A ÖZEL: FİYAT + FATURA + TEDARİKÇİ ── -->
          <div id="frmAction_purchaseFields" class="d-none">
            <div class="mb-3">
              <label class="form-label fw-semibold">Birim Fiyat (₺)</label>
              <input type="number" class="form-control" id="frmAction_price" name="unit_price" min="0" step="0.01">
            </div>
            <div class="mb-3">
              <label class="form-label fw-semibold">Fatura No <small class="text-muted">(isteğe bağlı)</small></label>
              <input type="text" class="form-control" id="frmAction_invoice" name="invoice_number">
            </div>
            <div class="mb-3">
              <label class="form-label fw-semibold">Tedarikçi <small class="text-muted">(isteğe bağlı — cari borç oluşturur)</small></label>
              <select class="form-select" id="frmAction_supplier" name="supplier_id">
                <option value="">— Seçin —</option>
              </select>
            </div>
          </div>

          <!-- NOT ── -->
          <div class="mb-1">
            <label class="form-label fw-semibold">Not <small class="text-muted">(isteğe bağlı)</small></label>
            <textarea class="form-control" id="frmAction_note" name="reference_note" rows="2"></textarea>
          </div>
        </form>
      </div>
      <div class="modal-footer border-0 pt-0">
        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Vazgeç</button>
        <button type="button" class="btn btn-mud-primary btn-action-lg" id="btnActionSubmit">
          <span class="btn-text"><i class="bi bi-check2-circle me-1"></i>Kaydet</span>
          <span class="btn-loading d-none"><span class="spinner-border spinner-border-sm me-1"></span>Kaydediliyor…</span>
        </button>
      </div>
    </div>
  </div>
</div>

<!-- ═══════════════════════════════════════════════════════
     MODAL — PIN DOĞRULAMA
     (Kasadan masraf + patron işlemleri için)
════════════════════════════════════════════════════════ -->
<div class="modal fade" id="modalPIN" tabindex="-1" data-bs-backdrop="static">
  <div class="modal-dialog modal-dialog-centered modal-sm">
    <div class="modal-content">
      <div class="modal-header border-0 pb-0">
        <h5 class="modal-title fw-bold">
          <i class="bi bi-shield-lock-fill text-warning me-2"></i>PIN Doğrulama
        </h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body text-center">
        <p class="text-muted small mb-3">Bu işlem için yetkili PIN gerekli.</p>

        <!-- PIN görsel kutuları ── -->
        <div class="pin-display mb-3" id="pinDisplay">
          <span class="pin-dot"></span><span class="pin-dot"></span>
          <span class="pin-dot"></span><span class="pin-dot"></span>
        </div>
        <div id="pinError" class="text-danger small mb-2 d-none">
          <i class="bi bi-x-circle me-1"></i>Yanlış PIN
        </div>

        <!-- Tuş takımı ── -->
        <div class="pin-keypad">
          <?php for ($i = 1; $i <= 9; $i++): ?>
          <button class="btn btn-outline-secondary pin-key fs-4 fw-bold" data-digit="<?= $i ?>"><?= $i ?></button>
          <?php endfor; ?>
          <button class="btn btn-outline-danger pin-key" data-action="clear">
            <i class="bi bi-x-lg"></i>
          </button>
          <button class="btn btn-outline-secondary pin-key fs-4 fw-bold" data-digit="0">0</button>
          <button class="btn btn-outline-secondary pin-key" data-action="backspace">
            <i class="bi bi-backspace"></i>
          </button>
        </div>
      </div>
    </div>
  </div>
</div>

<!-- ═══════════════════════════════════════════════════════
     MODAL — FİZİKSEL SAYIM (tam ekran wizard)
════════════════════════════════════════════════════════ -->
<div class="modal fade" id="modalCount" tabindex="-1" data-bs-backdrop="static" data-bs-keyboard="false">
  <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable" style="max-width:500px">
    <div class="modal-content" style="min-height:520px">

      <!-- BAŞLIK (dinamik) -->
      <div class="modal-header border-0 pb-1">
        <div class="w-100">
          <div class="d-flex align-items-center justify-content-between">
            <h5 class="modal-title fw-bold mb-0" id="cntModalTitle">
              <i class="bi bi-clipboard2-check-fill me-2 text-dark"></i>Fiziksel Sayım
            </h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
          </div>
          <!-- İlerleme çubuğu (sayım sırasında görünür) -->
          <div id="cntProgressWrap" class="d-none mt-2">
            <div class="d-flex justify-content-between small text-muted mb-1">
              <span id="cntProgressLabel">0 / 0 ürün</span>
              <span id="cntProgressPct">0%</span>
            </div>
            <div class="progress" style="height:6px">
              <div class="progress-bar bg-dark" id="cntProgressBar" style="width:0%;transition:width .4s"></div>
            </div>
          </div>
        </div>
      </div>

      <div class="modal-body pt-1 pb-2" id="cntBody">

        <!-- ── SAYFA 1: BAŞLAT / DEVAM ET ─────────────────── -->
        <div id="cntPageStart">
          <div class="text-center py-3">
            <i class="bi bi-clipboard2-check display-3 text-secondary mb-3 d-block"></i>
            <p class="text-muted mb-1">Bugünün sayım listesi hazırlanıyor.</p>
            <p class="text-muted small mb-4">Öncelik: en yüksek değerli ürünler ilk sırada.</p>
            <button class="btn btn-dark btn-action-lg w-100 mb-2" id="btnStartNewCount">
              <i class="bi bi-play-circle-fill me-2"></i>Yeni Sayım Başlat
            </button>
            <div id="cntResumeWrap" class="d-none">
              <div class="alert alert-info py-2 small text-start mb-2">
                <i class="bi bi-clock-history me-1"></i>
                Bugün <strong id="cntResumeProgress">—</strong> ürün sayıldı. Kaldığın yerden devam edebilirsin.
              </div>
              <button class="btn btn-outline-dark btn-action-lg w-100" id="btnResumeCount">
                <i class="bi bi-arrow-clockwise me-2"></i>Kaldığım Yerden Devam Et
              </button>
            </div>
          </div>
        </div>

        <!-- ── SAYFA 2: ÜRÜN KARTI ───────────────────────── -->
        <div id="cntPageCard" class="d-none">
          <!-- Be Truly: Ürün adı ve birim gösterilir, teorik miktar GİZLİDİR -->
          <div class="cnt-product-card text-center py-3">
            <div class="badge bg-secondary text-white mb-2" id="cntProductFreq" style="font-size:.7rem;letter-spacing:.06em"></div>
            <h3 class="fw-bold mb-1" id="cntProductName">—</h3>
            <p class="text-muted mb-4 small" id="cntProductHint">Depoda kaç <span class="cnt-unit fw-semibold"></span> var?</p>

            <div class="input-group input-group-lg justify-content-center mb-2" style="max-width:260px;margin:0 auto">
              <button class="btn btn-outline-secondary cnt-qty-dec" type="button">
                <i class="bi bi-dash-lg"></i>
              </button>
              <input type="number" class="form-control text-center fw-bold fs-3 border-dark"
                     id="cntQtyInput"
                     min="0" step="0.1"
                     placeholder="0"
                     inputmode="decimal"
                     style="max-width:120px">
              <span class="input-group-text fw-bold bg-dark text-white cnt-unit" id="cntUnitLabel" style="min-width:50px">—</span>
            </div>

            <p class="text-muted small mt-1 mb-0">
              <i class="bi bi-eye-slash me-1"></i>Teorik miktar bilmeden sayın — tarafsız sonuç için
            </p>
          </div>
        </div>

        <!-- ── SAYFA 3: FARK / NEDEN ─────────────────────── -->
        <div id="cntPageVariance" class="d-none">
          <div class="text-center pt-2 pb-3">
            <h5 class="fw-bold mb-3" id="cntVarianceProduct">—</h5>

            <!-- Karşılaştırma -->
            <div class="d-flex gap-3 justify-content-center mb-3">
              <div class="cnt-compare-box border rounded-3 p-3 flex-fill">
                <div class="text-muted small mb-1">Teorik</div>
                <div class="fw-bold fs-4" id="cntVarianceTheoretical">—</div>
                <div class="text-muted small cnt-unit"></div>
              </div>
              <div class="cnt-compare-box border rounded-3 p-3 flex-fill" id="cntVarianceCountedBox">
                <div class="text-muted small mb-1">Sayılan</div>
                <div class="fw-bold fs-4" id="cntVarianceCounted">—</div>
                <div class="text-muted small cnt-unit"></div>
              </div>
            </div>

            <!-- Fark balonu -->
            <div id="cntVarianceDiff" class="cnt-diff-badge mb-4 d-none">
              <i class="bi me-1" id="cntVarianceDiffIcon"></i>
              <span id="cntVarianceDiffText"></span>
            </div>
            <div id="cntVarianceOk" class="d-none mb-4">
              <span class="badge bg-success fs-6 px-3 py-2">
                <i class="bi bi-check-circle-fill me-1"></i> Stok eşleşiyor
              </span>
            </div>

            <!-- Neden? (sadece fark varsa) -->
            <div id="cntReasonPicker" class="d-none">
              <p class="text-muted small mb-2 fw-semibold">Fark nedeni nedir?</p>
              <div class="d-grid gap-2">
                <button class="btn btn-outline-secondary cnt-reason-btn" data-reason="entry_error">
                  <i class="bi bi-pencil me-1"></i>Giriş Hatası / Kayıt Eksik
                </button>
                <button class="btn btn-outline-secondary cnt-reason-btn" data-reason="natural_shrinkage">
                  <i class="bi bi-droplet me-1"></i>Doğal Fire / Kuruma
                </button>
                <button class="btn btn-outline-secondary cnt-reason-btn" data-reason="theft_suspected">
                  <i class="bi bi-exclamation-triangle me-1 text-danger"></i>Hırsızlık Şüphesi
                </button>
                <button class="btn btn-outline-secondary cnt-reason-btn" data-reason="unknown">
                  <i class="bi bi-question-circle me-1"></i>Bilinmiyor
                </button>
              </div>
            </div>
          </div>
        </div>

        <!-- ── SAYFA 4: ÖZET ─────────────────────────────── -->
        <div id="cntPageSummary" class="d-none">
          <div class="mb-3">
            <div class="d-flex justify-content-between align-items-center mb-2">
              <h6 class="fw-bold mb-0">Sayım Özeti</h6>
              <span class="badge bg-dark" id="cntSummaryDate">—</span>
            </div>
            <!-- Toplam fark değeri -->
            <div id="cntSummaryTotals" class="row g-2 mb-3 text-center">
              <div class="col-6">
                <div class="summary-card">
                  <div class="summary-val text-danger" id="cntTotalShortage">₺0</div>
                  <div class="summary-lbl">Toplam Açık</div>
                </div>
              </div>
              <div class="col-6">
                <div class="summary-card">
                  <div class="summary-val text-success" id="cntTotalSurplus">₺0</div>
                  <div class="summary-lbl">Toplam Fazla</div>
                </div>
              </div>
            </div>
            <!-- Onay uyarısı (büyük fark) -->
            <div id="cntApprovalWarn" class="alert alert-warning small py-2 d-none mb-2">
              <i class="bi bi-shield-exclamation me-1"></i>
              Toplam fark <strong>₺500</strong>'ü aştığı için <strong>müdür onayı</strong> gerekebilir.
            </div>
            <!-- Fark tablosu -->
            <div class="section-card">
              <div id="cntSummaryList" style="max-height:260px;overflow-y:auto">
                <!-- JS ile doldurulur -->
              </div>
            </div>
          </div>
        </div>

      </div><!-- /modal-body -->

      <!-- FOOTER (dinamik butonlar) -->
      <div class="modal-footer border-0 pt-0 d-flex gap-2" id="cntFooter">
        <button type="button" class="btn btn-outline-secondary flex-fill d-none" id="cntBtnBack">
          <i class="bi bi-arrow-left me-1"></i>Geri
        </button>
        <button type="button" class="btn btn-outline-secondary flex-fill d-none" id="cntBtnSkip">
          Geç <i class="bi bi-arrow-right ms-1"></i>
        </button>
        <button type="button" class="btn btn-dark flex-fill d-none" id="cntBtnNext">
          İleri <i class="bi bi-arrow-right ms-1"></i>
        </button>
        <button type="button" class="btn btn-success flex-fill d-none" id="cntBtnConfirm">
          <span class="btn-text"><i class="bi bi-check2-all me-1"></i>Onayla ve Kaydet</span>
          <span class="btn-loading d-none"><span class="spinner-border spinner-border-sm me-1"></span>Kaydediliyor…</span>
        </button>
      </div>

    </div><!-- /modal-content -->
  </div>
</div>

<!-- ═══════════════════════════════════════════════════════
     MODAL — PATRON NAKİT ÇEKİMİ
     (Ürün yok, sadece tutar)
════════════════════════════════════════════════════════ -->
<div class="modal fade" id="modalCashWithdraw" tabindex="-1" data-bs-backdrop="static">
  <div class="modal-dialog modal-dialog-centered modal-sm">
    <div class="modal-content">
      <div class="modal-header border-0 pb-1">
        <h5 class="modal-title fw-bold">
          <i class="bi bi-cash-stack me-2 text-warning"></i>Patron Nakit Çekimi
        </h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">
        <form id="frmCashWithdraw" novalidate>
          <div class="mb-3">
            <label class="form-label fw-semibold">Tutar (₺)</label>
            <div class="input-group input-group-lg">
              <span class="input-group-text">₺</span>
              <input type="number" class="form-control fw-bold" id="cwAmount" name="amount" min="1" step="1" placeholder="0">
            </div>
          </div>
          <div class="mb-1">
            <label class="form-label fw-semibold">Not</label>
            <input type="text" class="form-control" id="cwNote" name="note">
          </div>
        </form>
      </div>
      <div class="modal-footer border-0 pt-0">
        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Vazgeç</button>
        <button type="button" class="btn btn-warning btn-action-lg text-dark fw-bold" id="btnCashWithdrawSubmit">
          <span class="btn-text"><i class="bi bi-check2 me-1"></i>Onayla</span>
          <span class="btn-loading d-none"><span class="spinner-border spinner-border-sm me-1"></span></span>
        </button>
      </div>
    </div>
  </div>
</div>

<!-- ═══════════════════════════════════════════════════════
     MODAL — DEMİRBAŞ KAYIP / KIRIK
════════════════════════════════════════════════════════ -->
<div class="modal fade" id="modalAssetLoss" tabindex="-1" data-bs-backdrop="static">
  <div class="modal-dialog modal-dialog-centered modal-sm">
    <div class="modal-content">
      <div class="modal-header border-0 pb-1">
        <h5 class="modal-title fw-bold" id="assetLossTitle">
          <i class="bi bi-emoji-frown me-2 text-danger"></i>Kayıp Bildir
        </h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">
        <input type="hidden" id="assetLoss_productId">
        <input type="hidden" id="assetLoss_lossType">

        <div class="alert alert-secondary py-2 small mb-3 fw-semibold" id="assetLoss_productName">—</div>

        <div class="mb-3">
          <label class="form-label fw-semibold small">Neden?</label>
          <div class="d-grid gap-2">
            <button type="button" class="btn btn-outline-warning asset-loss-type-btn fw-semibold"
                    data-loss="BREAKAGE">
              <i class="bi bi-stars me-1"></i>Kirlik / Hasar Gordu
            </button>
            <button type="button" class="btn btn-outline-danger asset-loss-type-btn fw-semibold"
                    data-loss="ASSET_LOST">
              <i class="bi bi-question-circle me-1"></i>Kayip / Bulunamiyor
            </button>
          </div>
        </div>

        <div class="mb-3">
          <label class="form-label fw-semibold small">Kac Adet?</label>
          <div class="input-group input-group-lg">
            <button class="btn btn-outline-secondary" type="button" id="assetLossQtyDec">
              <i class="bi bi-dash-lg"></i>
            </button>
            <input type="number" class="form-control text-center fw-bold"
                   id="assetLoss_qty" min="1" step="1" value="1">
            <span class="input-group-text fw-bold">adet</span>
            <button class="btn btn-outline-secondary" type="button" id="assetLossQtyInc">
              <i class="bi bi-plus-lg"></i>
            </button>
          </div>
        </div>

        <div class="mb-1">
          <label class="form-label fw-semibold small">Not <span class="text-muted">(istege bagli)</span></label>
          <input type="text" class="form-control" id="assetLoss_note" placeholder="Masa numarasi vb.">
        </div>
      </div>
      <div class="modal-footer border-0 pt-0">
        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Vazgec</button>
        <button type="button" class="btn btn-danger btn-action-lg" id="btnAssetLossSubmit" disabled>
          <span class="btn-text"><i class="bi bi-check2 me-1"></i>Kaydet</span>
          <span class="btn-loading d-none"><span class="spinner-border spinner-border-sm me-1"></span></span>
        </button>
      </div>
    </div>
  </div>
</div>

<!-- ═══════════════════════════════════════════════════════
     MODAL — YENİ DEMİRBAŞ GİRİŞİ
════════════════════════════════════════════════════════ -->
<div class="modal fade" id="modalAssetAdd" tabindex="-1" data-bs-backdrop="static">
  <div class="modal-dialog modal-dialog-centered modal-sm">
    <div class="modal-content">
      <div class="modal-header border-0 pb-1">
        <h5 class="modal-title fw-bold">
          <i class="bi bi-plus-circle-fill me-2 text-success"></i>Yeni Demirba&#351; Girisi
        </h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">
        <div class="mb-3">
          <label class="form-label fw-semibold small">Urun</label>
          <select class="form-select" id="assetAdd_productId">
            <option value="">Secin...</option>
          </select>
        </div>
        <div class="mb-3">
          <label class="form-label fw-semibold small">Adet</label>
          <input type="number" class="form-control form-control-lg fw-bold text-center"
                 id="assetAdd_qty" min="1" step="1" value="1">
        </div>
        <div class="mb-3">
          <label class="form-label fw-semibold small">Birim Fiyat (&#8378;) <span class="text-muted small">(istege bagli)</span></label>
          <input type="number" class="form-control" id="assetAdd_price" min="0" step="0.01" placeholder="0.00">
        </div>
        <div class="mb-1">
          <label class="form-label fw-semibold small">Not</label>
          <input type="text" class="form-control" id="assetAdd_note">
        </div>
      </div>
      <div class="modal-footer border-0 pt-0">
        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Vazgec</button>
        <button type="button" class="btn btn-success btn-action-lg" id="btnAssetAddSubmit">
          <span class="btn-text"><i class="bi bi-check2 me-1"></i>Kaydet</span>
          <span class="btn-loading d-none"><span class="spinner-border spinner-border-sm me-1"></span></span>
        </button>
      </div>
    </div>
  </div>
</div>

<!-- ═══════════════════════════════════════════════════════
     TOAST KONTEYNER
════════════════════════════════════════════════════════ -->
<div class="toast-container position-fixed bottom-0 end-0 p-3" id="toastContainer"></div>

<!-- ── KULLANICI VERİSİ (PHP → JS) ── -->
<script>
window.MUD_USER = <?= json_encode([
  'id'          => $user['id'],
  'name'        => $user['name'],
  'role'        => $user['role'],
  'location_id' => $user['location_id'],
], JSON_HEX_TAG) ?>;
window.MUD_API   = '/mudavim/api';
</script>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="assets/app.js"></script>
</body>
</html>
