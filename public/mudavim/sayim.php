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
<title>Fiziksel Sayım — Müdavim</title>
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
<link rel="stylesheet" href="assets/style.css">
<style>
/* EKRANLAR */
#screenList    { display:block; }
#screenCount   { display:none; }
#screenSummary { display:none; }

.product-row { transition:background .15s; }
.product-row.saved      { background:#f0fdf4; }
.product-row.has-neg    { background:#fff5f5; }
.product-row.has-pos    { background:#f0f9ff; }

.qty-display { font-size:1.1rem; font-weight:700; min-width:70px; text-align:right; }
.count-input { width:90px; font-size:1.1rem; text-align:center; }

.variance-neg { color:#dc3545; font-weight:700; }
.variance-pos { color:#198754; font-weight:700; }
.variance-zer { color:#6c757d; }

/* İlerleme çubuğu */
#progressBar { height:4px; background:#0d6efd; transition:width .3s; border-radius:2px; }
#progressWrap { height:4px; background:#e9ecef; border-radius:2px; margin-bottom:.5rem; }

/* Sayım modunda büyük input */
#qtyInput { font-size:2rem; height:3rem; text-align:center; border-radius:10px; }
</style>
</head>
<body>

<nav class="navbar navbar-dark bg-mud-dark sticky-top">
  <div class="container-fluid px-3">
    <div class="d-flex align-items-center gap-2" id="navLeft">
      <a href="dashboard.php" class="btn btn-sm btn-outline-light" id="btnBack">
        <i class="bi bi-arrow-left"></i>
      </a>
      <span class="navbar-brand mb-0 fw-bold ms-1" id="navTitle">
        <i class="bi bi-clipboard2-check me-1"></i>Fiziksel Sayım
      </span>
    </div>
    <div id="navRight"></div>
  </div>
</nav>

<!-- ══════════════ EKRAN 1: SAYIM LİSTESİ ══════════════ -->
<div id="screenList" class="container-fluid px-2 px-sm-3 py-3" style="max-width:860px;margin:0 auto">

  <div class="d-flex justify-content-between align-items-center mb-3">
    <h6 class="fw-bold text-muted text-uppercase mb-0" style="letter-spacing:.06em">
      <i class="bi bi-clock-history me-1"></i>Geçmiş Sayımlar
    </h6>
    <button class="btn btn-success" id="btnNewCount">
      <i class="bi bi-plus-lg me-1"></i>Yeni Sayım
    </button>
  </div>

  <div id="countHistoryList">
    <div class="text-center py-5 text-muted">
      <div class="spinner-border spinner-border-sm me-2"></div>Yükleniyor…
    </div>
  </div>

</div>

<!-- ══════════════ EKRAN 2: SAYIM GİRİŞİ ══════════════ -->
<div id="screenCount" class="container-fluid px-2 px-sm-3 py-2" style="max-width:860px;margin:0 auto">

  <!-- İLERLEME -->
  <div class="mb-2">
    <div class="d-flex justify-content-between align-items-center mb-1">
      <span class="small text-muted">
        Sayılan: <strong id="countedCount">0</strong> / <span id="totalCount">0</span>
      </span>
      <span class="small text-muted" id="countDate"></span>
    </div>
    <div id="progressWrap"><div id="progressBar" style="width:0%"></div></div>
  </div>

  <!-- ARAMA + FİLTRE -->
  <div class="d-flex gap-2 mb-2 flex-wrap">
    <input type="search" class="form-control form-control-sm" id="countSearch"
           placeholder="Ürün ara…" style="max-width:180px">
    <div class="d-flex gap-1">
      <button class="btn btn-sm btn-outline-secondary count-filter active" data-f="all">Tümü</button>
      <button class="btn btn-sm btn-outline-warning count-filter" data-f="pending">Bekleyen</button>
      <button class="btn btn-sm btn-outline-success count-filter" data-f="done">Sayıldı</button>
    </div>
  </div>

  <!-- ÜRÜN LİSTESİ -->
  <div id="productList"></div>

  <!-- TAMAMLA BUTONU -->
  <div class="position-sticky bottom-0 bg-white border-top py-2 mt-2 d-flex gap-2 justify-content-end">
    <button class="btn btn-outline-secondary" id="btnCancelCount">İptal</button>
    <button class="btn btn-primary px-4" id="btnFinishCount">
      <i class="bi bi-check2-all me-1"></i>Sayımı Tamamla
    </button>
  </div>

</div>

<!-- ══════════════ EKRAN 3: ÖZET / ONAY ══════════════ -->
<div id="screenSummary" class="container-fluid px-2 px-sm-3 py-3" style="max-width:860px;margin:0 auto">

  <div class="alert alert-warning d-flex gap-2 align-items-start mb-3">
    <i class="bi bi-exclamation-triangle-fill mt-1 flex-shrink-0"></i>
    <div>
      <strong>Onaylamadan önce kontrol edin.</strong><br>
      <span class="small">Onaylama işlemi stok bakiyelerini günceller ve geri alınamaz.</span>
    </div>
  </div>

  <!-- ÖZET KARTLAR -->
  <div class="row g-2 mb-3">
    <div class="col-4">
      <div class="text-center p-2 rounded border bg-white shadow-sm">
        <div class="fw-bold fs-5" id="sumTotal">—</div>
        <div class="text-muted" style="font-size:.68rem;text-transform:uppercase">Toplam</div>
      </div>
    </div>
    <div class="col-4">
      <div class="text-center p-2 rounded border bg-white shadow-sm">
        <div class="fw-bold fs-5 text-danger" id="sumNeg">—</div>
        <div class="text-muted" style="font-size:.68rem;text-transform:uppercase">Açık</div>
      </div>
    </div>
    <div class="col-4">
      <div class="text-center p-2 rounded border bg-white shadow-sm">
        <div class="fw-bold fs-5 text-success" id="sumPos">—</div>
        <div class="text-muted" style="font-size:.68rem;text-transform:uppercase">Fazla</div>
      </div>
    </div>
  </div>

  <!-- FARK TABLOSU (sadece farklı olanlar) -->
  <h6 class="fw-bold text-muted text-uppercase small mb-2" style="letter-spacing:.06em">Fark Olan Ürünler</h6>
  <div id="summaryVariances" class="list-group shadow-sm mb-3"></div>

  <div class="mb-3">
    <label class="form-label">Not (opsiyonel)</label>
    <textarea class="form-control" id="confirmNotes" rows="2" placeholder="Sayım notu…"></textarea>
  </div>

  <div class="d-flex gap-2">
    <button class="btn btn-outline-secondary flex-grow-1" id="btnBackToCount">
      <i class="bi bi-arrow-left me-1"></i>Geri Dön
    </button>
    <?php if ($user['role'] === 'patron'): ?>
    <button class="btn btn-danger flex-grow-1 px-4" id="btnConfirmCount">
      <i class="bi bi-check-circle-fill me-1"></i>Onayla ve Stoğa İşle
    </button>
    <?php else: ?>
    <div class="alert alert-info flex-grow-1 mb-0 py-2 small text-center">
      Onaylama için patron yetkisi gerekli.
    </div>
    <?php endif; ?>
  </div>

</div>

<!-- GİRİŞ MODALI (hızlı miktar girişi) -->
<div class="modal fade" id="inputModal" tabindex="-1">
  <div class="modal-dialog modal-dialog-centered modal-sm">
    <div class="modal-content">
      <div class="modal-header pb-2">
        <h6 class="modal-title fw-bold" id="inputModalTitle">Miktar Gir</h6>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">
        <div class="text-muted small mb-2" id="inputModalSub"></div>
        <input type="number" id="qtyInput" class="form-control" min="0" step="0.001" placeholder="0">
        <div class="d-flex gap-1 mt-2 flex-wrap">
          <button class="btn btn-outline-secondary btn-sm quick-qty" data-v="0">0</button>
          <button class="btn btn-outline-secondary btn-sm quick-qty" data-v="0.5">½</button>
          <button class="btn btn-outline-secondary btn-sm quick-qty" data-v="1">1</button>
          <button class="btn btn-outline-secondary btn-sm quick-qty" data-v="2">2</button>
          <button class="btn btn-outline-secondary btn-sm quick-qty" data-v="5">5</button>
          <button class="btn btn-outline-secondary btn-sm quick-qty" data-v="10">10</button>
        </div>
      </div>
      <div class="modal-footer pt-2">
        <button class="btn btn-secondary" data-bs-dismiss="modal">İptal</button>
        <button class="btn btn-primary" id="btnSaveQty">Kaydet</button>
      </div>
    </div>
  </div>
</div>

<div class="position-fixed bottom-0 end-0 p-3" style="z-index:1200" id="toastContainer"></div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
/* ── HELPERS ── */
async function apiFetch(path, opts = {}) {
  const res  = await fetch(`api/${path}`, { credentials: 'same-origin', ...opts });
  const json = await res.json().catch(() => ({ status:'error', data:{ message:'Hata' } }));
  if (!res.ok || json.status === 'error') throw new Error(json?.data?.message || `HTTP ${res.status}`);
  return json.data;
}

function toast(msg, type = 'danger') {
  const id = `t${Date.now()}`;
  document.getElementById('toastContainer').insertAdjacentHTML('beforeend', `
    <div id="${id}" class="toast align-items-center border-0 shadow-sm" role="alert">
      <div class="d-flex">
        <div class="toast-body fw-semibold">
          <i class="bi bi-${type==='danger'?'x-circle-fill text-danger':'check-circle-fill text-success'} me-2"></i>${msg}
        </div>
        <button type="button" class="btn-close me-2 m-auto" data-bs-dismiss="toast"></button>
      </div>
    </div>`);
  const el = document.getElementById(id);
  new bootstrap.Toast(el, { delay: 3000 }).show();
  el.addEventListener('hidden.bs.toast', () => el.remove());
}

function esc(s) { return String(s??'').replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;'); }
function fmtQty(n, u) { return parseFloat(n||0).toLocaleString('tr-TR',{maximumFractionDigits:3})+' '+(u||''); }
function fmtMoney(n)  { return '₺'+parseFloat(n||0).toLocaleString('tr-TR',{minimumFractionDigits:2,maximumFractionDigits:2}); }

function showScreen(name) {
  ['screenList','screenCount','screenSummary'].forEach(id =>
    document.getElementById(id).style.display = id === `screen${name}` ? 'block' : 'none'
  );
}

/* ── STATE ── */
let currentCountId = null;
let products       = [];        // [{id, name, unit, category_name, theoretical_qty, avg_cost, counted_qty, ...}]
let savedQty       = {};        // { productId: counted_qty }
let activeFilter   = 'all';
let inputModal, inputProductId;

/* ══════════ EKRAN 1: LİSTE ══════════ */
async function loadHistory() {
  try {
    const list = await apiFetch('stock/count-list');
    if (!list.length) {
      document.getElementById('countHistoryList').innerHTML =
        '<div class="text-center py-5 text-muted"><i class="bi bi-clipboard2 fs-1 d-block mb-2"></i>Henüz sayım yapılmamış.</div>';
      return;
    }
    const statusLabel = { draft:'Taslak', in_progress:'Devam Ediyor', confirmed:'Onaylandı', cancelled:'İptal' };
    const statusCls   = { draft:'secondary', in_progress:'warning text-dark', confirmed:'success', cancelled:'danger' };
    let html = '<div class="list-group shadow-sm">';
    list.forEach(c => {
      const cls = statusCls[c.status] || 'secondary';
      const lbl = statusLabel[c.status] || c.status;
      html += `
        <div class="list-group-item py-2">
          <div class="d-flex justify-content-between align-items-center">
            <div>
              <div class="fw-semibold">${esc(c.count_date)}</div>
              <div class="text-muted small">${esc(c.created_by_name)}
                · ${c.item_count} ürün
                ${c.total_variance_value > 0 ? `· Fark: ${fmtMoney(c.total_variance_value)}` : ''}
              </div>
            </div>
            <div class="d-flex align-items-center gap-2">
              <span class="badge bg-${cls}">${lbl}</span>
              ${c.status === 'in_progress' ? `<button class="btn btn-sm btn-primary" onclick="resumeCount()"><i class="bi bi-play-fill me-1"></i>Devam</button>` : ''}
            </div>
          </div>
        </div>`;
    });
    html += '</div>';
    document.getElementById('countHistoryList').innerHTML = html;
  } catch(e) { toast(e.message); }
}

document.getElementById('btnNewCount').addEventListener('click', startCount);

async function startCount() {
  document.getElementById('btnNewCount').disabled = true;
  try {
    await loadDraft();
    showScreen('Count');
  } catch(e) { toast(e.message); }
  finally { document.getElementById('btnNewCount').disabled = false; }
}

async function resumeCount() {
  try {
    await loadDraft();
    showScreen('Count');
  } catch(e) { toast(e.message); }
}

/* ══════════ EKRAN 2: SAYIM GİRİŞİ ══════════ */
async function loadDraft() {
  document.getElementById('productList').innerHTML =
    '<div class="text-center py-5 text-muted"><div class="spinner-border spinner-border-sm me-2"></div></div>';
  const data = await apiFetch('stock/count-draft');
  currentCountId = data.count.id;
  products       = data.products;

  // Daha önce girilmiş değerleri yükle
  savedQty = {};
  products.forEach(p => {
    if (p.counted_qty !== null && p.counted_qty !== undefined) {
      savedQty[p.id] = parseFloat(p.counted_qty);
    }
  });

  document.getElementById('countDate').textContent = data.count.count_date;
  renderProductList();
}

function renderProductList() {
  const q   = document.getElementById('countSearch').value.trim().toLowerCase();
  let items = products.filter(p => {
    const matchQ  = !q || p.name.toLowerCase().includes(q);
    const isSaved = savedQty[p.id] !== undefined;
    if (activeFilter === 'pending') return matchQ && !isSaved;
    if (activeFilter === 'done')    return matchQ && isSaved;
    return matchQ;
  });

  const counted = Object.keys(savedQty).length;
  document.getElementById('countedCount').textContent = counted;
  document.getElementById('totalCount').textContent   = products.length;
  const pct = products.length ? Math.round(counted / products.length * 100) : 0;
  document.getElementById('progressBar').style.width  = pct + '%';

  if (!items.length) {
    document.getElementById('productList').innerHTML =
      '<div class="text-center py-4 text-muted">Eşleşen ürün yok.</div>';
    return;
  }

  // Kategoriye göre grupla
  const groups = {};
  items.forEach(p => {
    if (!groups[p.category_name]) groups[p.category_name] = [];
    groups[p.category_name].push(p);
  });

  let html = '';
  for (const [cat, catItems] of Object.entries(groups)) {
    html += `<h6 class="text-muted fw-semibold text-uppercase small mt-3 mb-1 px-1"
               style="letter-spacing:.07em;font-size:.67rem">${esc(cat)}</h6>
             <div class="list-group shadow-sm mb-2">`;
    catItems.forEach(p => {
      const qty    = savedQty[p.id];
      const isSaved = qty !== undefined;
      const rowCls  = !isSaved ? '' : ' saved';
      html += `
        <div class="list-group-item product-row py-2${rowCls}" id="prow-${p.id}">
          <div class="d-flex align-items-center gap-2">
            <div class="flex-grow-1 min-width-0">
              <div class="fw-semibold text-truncate">${esc(p.name)}</div>
              ${!isSaved
                ? '<div class="text-muted small">Henüz sayılmadı</div>'
                : `<div class="text-muted small">Sayıldı: <strong>${fmtQty(qty, p.unit)}</strong></div>`
              }
            </div>
            <button class="btn btn-sm ${isSaved ? 'btn-success' : 'btn-outline-primary'} flex-shrink-0"
                    onclick="openInput(${p.id})">
              ${isSaved
                ? `<i class="bi bi-check2 me-1"></i>${fmtQty(qty, p.unit)}`
                : `<i class="bi bi-pencil me-1"></i>Gir`}
            </button>
          </div>
        </div>`;
    });
    html += '</div>';
  }
  document.getElementById('productList').innerHTML = html;
}

/* FİLTRE */
document.querySelectorAll('.count-filter').forEach(btn => {
  btn.addEventListener('click', () => {
    document.querySelectorAll('.count-filter').forEach(b => b.classList.remove('active'));
    btn.classList.add('active');
    activeFilter = btn.dataset.f;
    renderProductList();
  });
});
document.getElementById('countSearch').addEventListener('input', renderProductList);

/* GİRİŞ MODALI */
inputModal = new bootstrap.Modal(document.getElementById('inputModal'));

function openInput(productId) {
  inputProductId = productId;
  const p = products.find(x => x.id == productId);
  document.getElementById('inputModalTitle').textContent = p.name;
  document.getElementById('inputModalSub').textContent   =
    'Sistemdeki miktar: ' + fmtQty(p.theoretical_qty, p.unit);
  document.getElementById('qtyInput').value = savedQty[productId] ?? '';
  inputModal.show();
  setTimeout(() => document.getElementById('qtyInput').focus(), 350);
}

document.getElementById('qtyInput').addEventListener('keydown', e => {
  if (e.key === 'Enter') document.getElementById('btnSaveQty').click();
});

document.querySelectorAll('.quick-qty').forEach(btn => {
  btn.addEventListener('click', () => {
    document.getElementById('qtyInput').value = btn.dataset.v;
  });
});

document.getElementById('btnSaveQty').addEventListener('click', async () => {
  const qty = parseFloat(document.getElementById('qtyInput').value);
  if (isNaN(qty) || qty < 0) { toast('Geçerli bir miktar girin.'); return; }
  const btn = document.getElementById('btnSaveQty');
  btn.disabled = true;
  try {
    const result = await apiFetch(`stock/count-item`, {
      method: 'POST',
      headers: { 'Content-Type':'application/json' },
      body: JSON.stringify({ count_id: currentCountId, product_id: inputProductId, counted_qty: qty }),
    });
    savedQty[inputProductId] = qty;
    inputModal.hide();
    renderProductList();
    // Sonraki sayılmamış ürüne atla
    const pending = products.filter(p => savedQty[p.id] === undefined);
    if (pending.length > 0) {
      toast(`Kaydedildi. Kalan: ${pending.length}`, 'success');
    } else {
      toast('Tüm ürünler sayıldı!', 'success');
    }
  } catch(e) { toast(e.message); }
  finally { btn.disabled = false; }
});

document.getElementById('btnCancelCount').addEventListener('click', () => {
  if (!confirm('Sayımı iptal etmek istediğinize emin misiniz? Girilen değerler kaybolmayacak.')) return;
  showScreen('List');
  loadHistory();
});

document.getElementById('btnFinishCount').addEventListener('click', () => {
  const saved  = Object.keys(savedQty).length;
  const pending = products.length - saved;
  if (pending > 0 && !confirm(`${pending} ürün henüz sayılmadı. Yine de devam edilsin mi?`)) return;
  buildSummary();
  showScreen('Summary');
});

/* ══════════ EKRAN 3: ÖZET ══════════ */
function buildSummary() {
  let negCount = 0, posCount = 0;
  let variances = [];

  products.forEach(p => {
    if (savedQty[p.id] === undefined) return;
    const theoretical = parseFloat(p.theoretical_qty || 0);
    const counted     = savedQty[p.id];
    const variance    = counted - theoretical;
    if (Math.abs(variance) < 0.0001) return;
    const varianceVal = variance * parseFloat(p.avg_cost || 0);
    if (variance < 0) negCount++;
    else               posCount++;
    variances.push({ ...p, counted, theoretical, variance, varianceVal });
  });

  const saved = Object.keys(savedQty).length;
  document.getElementById('sumTotal').textContent = saved;
  document.getElementById('sumNeg').textContent   = negCount;
  document.getElementById('sumPos').textContent   = posCount;

  if (!variances.length) {
    document.getElementById('summaryVariances').innerHTML =
      '<div class="list-group-item text-center text-success py-3">' +
      '<i class="bi bi-check-circle-fill fs-4 d-block mb-1"></i>Tüm ürünler sistemle uyumlu!</div>';
    return;
  }

  variances.sort((a,b) => a.variance - b.variance);
  let html = '';
  variances.forEach(v => {
    const cls   = v.variance < 0 ? 'variance-neg' : 'variance-pos';
    const icon  = v.variance < 0 ? 'bi-arrow-down-circle-fill text-danger' : 'bi-arrow-up-circle-fill text-success';
    const sign  = v.variance > 0 ? '+' : '';
    html += `
      <div class="list-group-item py-2">
        <div class="d-flex justify-content-between align-items-center">
          <div>
            <i class="bi ${icon} me-1"></i>
            <span class="fw-semibold">${esc(v.name)}</span>
            <div class="text-muted small">
              Sistem: ${fmtQty(v.theoretical, v.unit)} → Sayılan: ${fmtQty(v.counted, v.unit)}
            </div>
          </div>
          <div class="text-end">
            <div class="${cls}">${sign}${fmtQty(v.variance, v.unit)}</div>
            ${v.varianceVal !== 0 ? `<div class="${cls}" style="font-size:.8rem">${sign}${fmtMoney(v.varianceVal)}</div>` : ''}
          </div>
        </div>
      </div>`;
  });
  document.getElementById('summaryVariances').innerHTML = html;
}

document.getElementById('btnBackToCount').addEventListener('click', () => showScreen('Count'));

<?php if ($user['role'] === 'patron'): ?>
document.getElementById('btnConfirmCount').addEventListener('click', async () => {
  if (!confirm('Sayım onaylanacak ve stok bakiyeleri güncellenecek. Emin misiniz?')) return;
  const btn = document.getElementById('btnConfirmCount');
  btn.disabled = true;
  btn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>İşleniyor…';
  try {
    const result = await apiFetch('stock/count-confirm', {
      method: 'POST',
      headers: { 'Content-Type':'application/json' },
      body: JSON.stringify({
        count_id: currentCountId,
        notes: document.getElementById('confirmNotes').value.trim() || null,
      }),
    });
    toast(`Sayım onaylandı. ${result.items_applied} ürün güncellendi.`, 'success');
    currentCountId = null;
    savedQty = {};
    showScreen('List');
    loadHistory();
  } catch(e) {
    toast(e.message);
    btn.disabled = false;
    btn.innerHTML = '<i class="bi bi-check-circle-fill me-1"></i>Onayla ve Stoğa İşle';
  }
});
<?php endif; ?>

/* ── BAŞLAT ── */
loadHistory();
</script>
</body>
</html>
