<?php
require_once __DIR__ . '/autoload.php';
use Mudavim\Core\Auth;
Auth::boot();
if (!Auth::check()) { header('Location: login.php'); exit; }
$role = Auth::role();
if ($role !== 'patron') { header('Location: dashboard.php'); exit; }
?>
<!DOCTYPE html>
<html lang="tr">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Demirbaşlar — Müdavim</title>
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
<style>
body { background:#f8f9fa; }
.nav-top { background:#1a1a2e; }
.nav-top .btn-nav { color:#ccc; border:none; background:none; padding:.4rem .9rem; border-radius:6px; font-size:.85rem; }
.nav-top .btn-nav:hover, .nav-top .btn-nav.active { background:#ffffff22; color:#fff; }
.stat-card { border:none; border-radius:12px; }
.stat-card .val { font-size:1.5rem; font-weight:700; }
.stat-card .lbl { font-size:.78rem; color:#6c757d; }
.category-header { background:#e9ecef; font-weight:600; font-size:.8rem; text-transform:uppercase; letter-spacing:.05em; color:#6c757d; }
.qty-badge { font-size:.78rem; min-width:40px; text-align:center; }
</style>
</head>
<body>

<!-- NAV -->
<div class="nav-top px-3 py-2 d-flex align-items-center gap-2 flex-wrap">
  <span class="text-white fw-bold me-3" style="font-size:1rem;">🍽 Müdavim</span>
  <a href="dashboard.php"   class="btn-nav">Kasa</a>
  <a href="stok.php"        class="btn-nav">Stok</a>
  <a href="urunler.php"     class="btn-nav">Ürünler</a>
  <a href="tedarikci.php"   class="btn-nav">Tedarikçi</a>
  <a href="alis.php"        class="btn-nav">Alış</a>
  <a href="sayim.php"       class="btn-nav">Sayım</a>
  <a href="rapor.php"       class="btn-nav">Rapor</a>
  <a href="demirbaslar.php" class="btn-nav active">Demirbaş</a>
  <div class="ms-auto">
    <a href="logout.php" class="btn-nav">Çıkış</a>
  </div>
</div>

<!-- HEADER -->
<div class="container-fluid px-4 pt-4 pb-2">
  <div class="d-flex align-items-center justify-content-between flex-wrap gap-2">
    <div>
      <h4 class="mb-0 fw-bold">Demirbaş Envanteri</h4>
      <small class="text-muted" id="statsLine">Yükleniyor…</small>
    </div>
    <div class="d-flex gap-2">
      <input type="search" id="searchInput" class="form-control form-control-sm" placeholder="Ara…" style="width:180px">
      <button class="btn btn-success btn-sm"  onclick="openAdd()">+ Giriş</button>
      <button class="btn btn-danger btn-sm"   onclick="openLoss()">Kayıp / Kırık</button>
      <button class="btn btn-outline-secondary btn-sm" onclick="openHistory()">Geçmiş</button>
    </div>
  </div>
</div>

<!-- ÖZET KARTLAR -->
<div class="container-fluid px-4 py-3">
  <div class="row g-3">
    <div class="col-6 col-md-3">
      <div class="card stat-card shadow-sm p-3" style="border-left:4px solid #0d6efd">
        <div class="val text-primary" id="sTotalItems">—</div>
        <div class="lbl">Toplam Kalem</div>
      </div>
    </div>
    <div class="col-6 col-md-3">
      <div class="card stat-card shadow-sm p-3" style="border-left:4px solid #198754">
        <div class="val text-success" id="sTotalQty">—</div>
        <div class="lbl">Toplam Adet</div>
      </div>
    </div>
    <div class="col-6 col-md-3">
      <div class="card stat-card shadow-sm p-3" style="border-left:4px solid #dc3545">
        <div class="val text-danger" id="sZeroItems">—</div>
        <div class="lbl">Stoksuz Kalem</div>
      </div>
    </div>
    <div class="col-6 col-md-3">
      <div class="card stat-card shadow-sm p-3" style="border-left:4px solid #fd7e14">
        <div class="val text-warning" id="sLossTotal">—</div>
        <div class="lbl">Bu Ay Kayıp (₺)</div>
      </div>
    </div>
  </div>
</div>

<!-- TABLO -->
<div class="container-fluid px-4 pb-5">
  <div class="card shadow-sm border-0">
    <div class="card-body p-0">
      <table class="table table-hover align-middle mb-0 small">
        <thead class="table-light">
          <tr>
            <th class="ps-3">Demirbaş</th>
            <th>SKU</th>
            <th class="text-center">Adet</th>
            <th class="text-center">Min</th>
            <th>Son Güncelleme</th>
            <th class="text-center pe-3">İşlem</th>
          </tr>
        </thead>
        <tbody id="assetTbody">
          <tr><td colspan="6" class="text-center text-muted py-4">Yükleniyor…</td></tr>
        </tbody>
      </table>
    </div>
  </div>
</div>

<!-- MODAL: Giriş -->
<div class="modal fade" id="modalAdd" tabindex="-1">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">Demirbaş Girişi</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">
        <div class="mb-3">
          <label class="form-label">Demirbaş <span class="text-danger">*</span></label>
          <select id="addProductId" class="form-select">
            <option value="">— Seçin —</option>
          </select>
        </div>
        <div class="row g-3">
          <div class="col-6">
            <label class="form-label">Adet <span class="text-danger">*</span></label>
            <input type="number" id="addQty" class="form-control" value="1" min="1">
          </div>
          <div class="col-6">
            <label class="form-label">Birim Fiyat (₺)</label>
            <input type="number" id="addPrice" class="form-control" value="0" min="0" step="0.01">
          </div>
        </div>
        <div class="mt-3">
          <label class="form-label">Not</label>
          <input type="text" id="addNote" class="form-control" placeholder="Fatura no, satıcı…">
        </div>
      </div>
      <div class="modal-footer">
        <button class="btn btn-secondary" data-bs-dismiss="modal">İptal</button>
        <button class="btn btn-success" onclick="saveAdd()">Kaydet</button>
      </div>
    </div>
  </div>
</div>

<!-- MODAL: Kayıp/Kırık -->
<div class="modal fade" id="modalLoss" tabindex="-1">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title text-danger">Kayıp / Kırık Kaydı</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">
        <div class="mb-3">
          <label class="form-label">Demirbaş <span class="text-danger">*</span></label>
          <select id="lossProductId" class="form-select">
            <option value="">— Seçin —</option>
          </select>
        </div>
        <div class="row g-3">
          <div class="col-6">
            <label class="form-label">Adet <span class="text-danger">*</span></label>
            <input type="number" id="lossQty" class="form-control" value="1" min="1">
          </div>
          <div class="col-6">
            <label class="form-label">Tür</label>
            <select id="lossType" class="form-select">
              <option value="BREAKAGE">Kırık / Hasar</option>
              <option value="ASSET_LOST">Kayıp / Çalıntı</option>
            </select>
          </div>
        </div>
        <div class="mt-3">
          <label class="form-label">Not</label>
          <input type="text" id="lossNote" class="form-control" placeholder="Açıklama…">
        </div>
      </div>
      <div class="modal-footer">
        <button class="btn btn-secondary" data-bs-dismiss="modal">İptal</button>
        <button class="btn btn-danger" onclick="saveLoss()">Kaydet</button>
      </div>
    </div>
  </div>
</div>

<!-- MODAL: Geçmiş -->
<div class="modal fade" id="modalHistory" tabindex="-1">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">Son 30 Gün Kayıp / Kırık Geçmişi</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body p-0">
        <table class="table table-hover small mb-0">
          <thead class="table-light">
            <tr>
              <th class="ps-3">Tarih</th>
              <th>Demirbaş</th>
              <th>Tür</th>
              <th class="text-end">Adet</th>
              <th class="text-end">Tutar (₺)</th>
              <th>Personel</th>
            </tr>
          </thead>
          <tbody id="historyTbody">
            <tr><td colspan="6" class="text-center text-muted py-3">Yükleniyor…</td></tr>
          </tbody>
        </table>
      </div>
    </div>
  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
const API = 'api/index.php';
const LOCATION_ID = 1;

let allAssets  = [];
let assetProds = [];

const modalAdd     = new bootstrap.Modal(document.getElementById('modalAdd'));
const modalLoss    = new bootstrap.Modal(document.getElementById('modalLoss'));
const modalHistory = new bootstrap.Modal(document.getElementById('modalHistory'));

async function apiFetch(ep, method = 'GET', body = null) {
  const opts = { method, headers: { 'Content-Type': 'application/json' } };
  if (body) opts.body = JSON.stringify(body);
  const r = await fetch(`${API}/${ep}`, opts);
  return r.json();
}

function fmt(n) { return parseFloat(n || 0).toLocaleString('tr-TR', { minimumFractionDigits: 2 }); }

async function init() {
  const [assetsRes, prodsRes, lossRes] = await Promise.all([
    apiFetch(`assets/list?location_id=${LOCATION_ID}`),
    apiFetch('products/list'),
    apiFetch('assets/history?months=1'),
  ]);

  allAssets  = assetsRes.data ?? [];

  // Sadece demirbaş kategorisindeki ürünler
  const allProds = prodsRes.data ?? [];
  assetProds = allProds.filter(p => p.category_name === 'Demirbaş');

  // Dropdown doldur
  const opts = assetProds.map(p => `<option value="${p.id}">${p.name}</option>`).join('');
  document.getElementById('addProductId').insertAdjacentHTML('beforeend', opts);
  document.getElementById('lossProductId').insertAdjacentHTML('beforeend', opts);

  const lossRows = lossRes.data ?? [];
  const lossTotal = lossRows.reduce((s, r) => s + parseFloat(r.total_cost || 0), 0);
  document.getElementById('sLossTotal').textContent = fmt(lossTotal);

  renderTable();
}

function renderTable() {
  const q = document.getElementById('searchInput').value.toLowerCase();

  let filtered = allAssets.filter(a => !q || a.product_name.toLowerCase().includes(q));

  if (!filtered.length) {
    document.getElementById('assetTbody').innerHTML =
      `<tr><td colspan="6" class="text-center text-muted py-4">Demirbaş bulunamadı</td></tr>`;
    updateStats([]);
    return;
  }

  // Kategoriye göre grupla
  const groups = {};
  filtered.forEach(a => {
    const k = a.category_name ?? 'Demirbaş';
    if (!groups[k]) groups[k] = [];
    groups[k].push(a);
  });

  let html = '';
  Object.entries(groups).sort(([a],[b]) => a.localeCompare(b, 'tr')).forEach(([cat, items]) => {
    html += `<tr class="category-header"><td colspan="6" class="ps-3 py-2">${cat}</td></tr>`;
    items.forEach(a => {
      const qty = parseFloat(a.net_qty ?? 0);
      const min = parseFloat(a.min_stock_qty ?? 0);
      let qBadge;
      if (qty <= 0)             qBadge = `<span class="badge bg-danger qty-badge">${qty}</span>`;
      else if (min>0 && qty<min) qBadge = `<span class="badge bg-warning text-dark qty-badge">${qty}</span>`;
      else                      qBadge = `<span class="badge bg-success qty-badge">${qty}</span>`;

      const lastUpd = a.updated_at ? a.updated_at.substring(0,10) : '—';

      html += `<tr>
        <td class="ps-3 fw-semibold">${a.product_name}</td>
        <td><code class="text-muted">${a.sku ?? '—'}</code></td>
        <td class="text-center">${qBadge}</td>
        <td class="text-center text-muted">${min > 0 ? min : '—'}</td>
        <td class="text-muted">${lastUpd}</td>
        <td class="text-center pe-3">
          <button class="btn btn-sm btn-outline-success me-1" onclick="quickAdd(${a.product_id})">+ Giriş</button>
          <button class="btn btn-sm btn-outline-danger"       onclick="quickLoss(${a.product_id})">Kayıp</button>
        </td>
      </tr>`;
    });
  });

  document.getElementById('assetTbody').innerHTML = html;
  updateStats(filtered);
}

function updateStats(rows) {
  const total = rows.length;
  const qty   = rows.reduce((s, r) => s + parseFloat(r.net_qty || 0), 0);
  const zero  = rows.filter(r => parseFloat(r.net_qty || 0) <= 0).length;
  document.getElementById('sTotalItems').textContent = total;
  document.getElementById('sTotalQty').textContent   = qty;
  document.getElementById('sZeroItems').textContent  = zero;
}

function openAdd() {
  document.getElementById('addProductId').value = '';
  document.getElementById('addQty').value       = '1';
  document.getElementById('addPrice').value     = '0';
  document.getElementById('addNote').value      = '';
  modalAdd.show();
}

function quickAdd(productId) {
  document.getElementById('addProductId').value = productId;
  document.getElementById('addQty').value       = '1';
  document.getElementById('addPrice').value     = '0';
  document.getElementById('addNote').value      = '';
  modalAdd.show();
}

async function saveAdd() {
  const pid   = parseInt(document.getElementById('addProductId').value);
  const qty   = parseFloat(document.getElementById('addQty').value);
  const price = parseFloat(document.getElementById('addPrice').value) || 0;
  const note  = document.getElementById('addNote').value.trim();

  if (!pid || qty < 1) { alert('Demirbaş ve adet zorunlu'); return; }

  const res = await apiFetch('assets/add', 'POST', {
    product_id:  pid,
    quantity:    qty,
    location_id: LOCATION_ID,
    unit_price:  price,
    note,
  });

  if (res.status === 'ok') {
    modalAdd.hide();
    await refreshAssets();
  } else {
    alert(res.data?.message ?? 'Hata oluştu');
  }
}

function openLoss() {
  document.getElementById('lossProductId').value = '';
  document.getElementById('lossQty').value       = '1';
  document.getElementById('lossType').value      = 'BREAKAGE';
  document.getElementById('lossNote').value      = '';
  modalLoss.show();
}

function quickLoss(productId) {
  document.getElementById('lossProductId').value = productId;
  document.getElementById('lossQty').value       = '1';
  document.getElementById('lossType').value      = 'BREAKAGE';
  document.getElementById('lossNote').value      = '';
  modalLoss.show();
}

async function saveLoss() {
  const pid  = parseInt(document.getElementById('lossProductId').value);
  const qty  = parseFloat(document.getElementById('lossQty').value);
  const type = document.getElementById('lossType').value;
  const note = document.getElementById('lossNote').value.trim();

  if (!pid || qty < 1) { alert('Demirbaş ve adet zorunlu'); return; }

  const res = await apiFetch('assets/loss', 'POST', {
    product_id:  pid,
    quantity:    qty,
    loss_type:   type,
    location_id: LOCATION_ID,
    note,
  });

  if (res.status === 'ok') {
    modalLoss.hide();
    await refreshAssets();
  } else {
    alert(res.data?.message ?? 'Hata oluştu');
  }
}

async function openHistory() {
  modalHistory.show();
  const res  = await apiFetch('assets/history?months=1');
  const rows = res.data ?? [];

  if (!rows.length) {
    document.getElementById('historyTbody').innerHTML =
      `<tr><td colspan="6" class="text-center text-muted py-3">Kayıt yok</td></tr>`;
    return;
  }

  document.getElementById('historyTbody').innerHTML = rows.map(r => `
    <tr>
      <td class="ps-3">${r.outlet_date ?? '—'}</td>
      <td class="fw-semibold">${r.product_name}</td>
      <td><span class="badge bg-${r.type_code === 'BREAKAGE' ? 'warning text-dark' : 'danger'}">${r.type_name}</span></td>
      <td class="text-end">${parseFloat(r.quantity)}</td>
      <td class="text-end">${fmt(r.total_cost)}</td>
      <td class="text-muted">${r.user_name}</td>
    </tr>
  `).join('');
}

async function refreshAssets() {
  const res = await apiFetch(`assets/list?location_id=${LOCATION_ID}`);
  allAssets = res.data ?? [];
  renderTable();
}

document.getElementById('searchInput').addEventListener('input', renderTable);

init();
</script>
</body>
</html>
