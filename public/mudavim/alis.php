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
<title>Alış Listesi — Müdavim</title>
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
<link rel="stylesheet" href="assets/style.css">
<style>
.supplier-section { border:1px solid #e2e8f0; border-radius:10px; overflow:hidden; background:#fff; }
.supplier-header  { background:#f8fafc; padding:.6rem 1rem; border-bottom:1px solid #e2e8f0;
                    display:flex; align-items:center; justify-content:space-between; }
.item-row         { display:flex; align-items:center; gap:.5rem; padding:.5rem 1rem;
                    border-bottom:1px solid #f1f5f9; }
.item-row:last-child { border-bottom:none; }
.item-row.unchecked { opacity:.45; }
.status-zero { color:#dc3545; }
.status-low  { color:#fd7e14; }
.qty-input   { width:80px; text-align:center; }
@media print {
  nav, .no-print { display:none !important; }
  .supplier-section { break-inside:avoid; }
  body { font-size:13px; }
}
</style>
</head>
<body>

<nav class="navbar navbar-dark bg-mud-dark sticky-top no-print">
  <div class="container-fluid px-3">
    <div class="d-flex align-items-center gap-2">
      <a href="dashboard.php" class="btn btn-sm btn-outline-light">
        <i class="bi bi-arrow-left"></i>
      </a>
      <span class="navbar-brand mb-0 fw-bold ms-1">
        <i class="bi bi-cart3 me-1"></i>Alış Listesi
      </span>
    </div>
    <div class="d-flex gap-2">
      <button class="btn btn-sm btn-outline-light" id="btnRefresh" title="Yenile">
        <i class="bi bi-arrow-clockwise"></i>
      </button>
      <button class="btn btn-sm btn-outline-light" onclick="window.print()" title="Yazdır">
        <i class="bi bi-printer"></i>
      </button>
    </div>
  </div>
</nav>

<div class="container-fluid px-2 px-sm-3 py-3" style="max-width:860px;margin:0 auto">

  <!-- ÖZET + FİLTRE -->
  <div class="d-flex flex-wrap align-items-center justify-content-between gap-2 mb-3 no-print">
    <div class="d-flex gap-1">
      <button class="btn btn-sm btn-outline-secondary filter-btn active" data-filter="all">
        Tümü <span class="badge bg-secondary ms-1" id="cntAll">—</span>
      </button>
      <button class="btn btn-sm btn-outline-danger filter-btn" data-filter="zero">
        Sıfır <span class="badge bg-danger ms-1" id="cntZero">—</span>
      </button>
      <button class="btn btn-sm btn-outline-warning filter-btn" data-filter="low">
        Kritik <span class="badge bg-warning text-dark ms-1" id="cntLow">—</span>
      </button>
    </div>
    <div class="d-flex gap-1 align-items-center">
      <input type="search" class="form-control form-control-sm" id="searchInput"
             placeholder="Ürün ara…" style="width:150px">
      <button class="btn btn-sm btn-outline-secondary" id="btnSelectAll">Tümünü Seç</button>
      <button class="btn btn-sm btn-outline-secondary" id="btnSelectNone">Temizle</button>
    </div>
  </div>

  <!-- TOPLAM -->
  <div class="alert alert-light border d-flex justify-content-between align-items-center py-2 mb-3 no-print" id="totalBar">
    <span class="text-muted small">Seçili kalem: <strong id="selectedCount">0</strong></span>
    <span>Tahmini toplam: <strong class="text-primary" id="totalEstimate">₺0,00</strong></span>
  </div>

  <div id="listWrap">
    <div class="text-center py-5 text-muted">
      <div class="spinner-border spinner-border-sm me-2"></div>Yükleniyor…
    </div>
  </div>

</div>

<div class="position-fixed bottom-0 end-0 p-3 no-print" style="z-index:1100" id="toastContainer"></div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
/* ── HELPERS ── */
async function apiFetch(path) {
  const res  = await fetch(`api/${path}`, { credentials: 'same-origin' });
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
function fmtQty(n, u) { return parseFloat(n||0).toLocaleString('tr-TR',{maximumFractionDigits:3})+' '+u; }
function fmtMoney(n)  { return '₺'+parseFloat(n||0).toLocaleString('tr-TR',{minimumFractionDigits:2,maximumFractionDigits:2}); }

/* ── STATE ── */
let allItems     = [];   // flat list from API
let activeFilter = 'all';
let orderQty     = {};   // { productId: qty }
let checked      = {};   // { productId: bool }

/* ── LOAD ── */
async function loadList() {
  document.getElementById('listWrap').innerHTML =
    '<div class="text-center py-5 text-muted"><div class="spinner-border spinner-border-sm me-2"></div>Yükleniyor…</div>';
  try {
    allItems = await apiFetch('stock/shopping-list');

    // Varsayılan sipariş miktarı: min_stock - current (en az 1)
    orderQty = {};
    checked  = {};
    allItems.forEach(p => {
      const cur = parseFloat(p.current_qty || 0);
      const min = parseFloat(p.min_stock_qty || 0);
      const sug = min > 0 ? Math.max(1, Math.ceil(min - cur)) : 1;
      orderQty[p.id] = sug;
      checked[p.id]  = true;
    });

    updateCounts();
    render();
  } catch(e) {
    toast(e.message);
    document.getElementById('listWrap').innerHTML =
      `<div class="alert alert-danger">${esc(e.message)}</div>`;
  }
}

function updateCounts() {
  const zero = allItems.filter(p => parseFloat(p.current_qty||0) <= 0).length;
  const low  = allItems.filter(p => parseFloat(p.current_qty||0) > 0).length;
  document.getElementById('cntAll').textContent  = allItems.length;
  document.getElementById('cntZero').textContent = zero;
  document.getElementById('cntLow').textContent  = low;
}

function getStatus(p) {
  return parseFloat(p.current_qty||0) <= 0 ? 'zero' : 'low';
}

function render() {
  const q = document.getElementById('searchInput').value.trim().toLowerCase();

  let filtered = allItems.filter(p => {
    const matchQ = !q || p.name.toLowerCase().includes(q) || (p.sku||'').toLowerCase().includes(q);
    const matchF = activeFilter === 'all' || getStatus(p) === activeFilter;
    return matchQ && matchF;
  });

  if (!filtered.length) {
    document.getElementById('listWrap').innerHTML =
      '<div class="text-center py-5 text-muted"><i class="bi bi-check2-circle fs-1 d-block mb-2 text-success"></i>' +
      (allItems.length ? 'Filtre eşleşmedi.' : 'Tüm ürünler yeterli stokta!') + '</div>';
    recalcTotal();
    return;
  }

  // Tedarikçiye göre grupla
  const groups = {};
  filtered.forEach(p => {
    const key = p.supplier_id ? `${p.supplier_id}` : '__none__';
    if (!groups[key]) groups[key] = { name: p.supplier_name, phone: p.supplier_phone, items: [] };
    groups[key].items.push(p);
  });

  let html = '<div class="d-flex flex-column gap-3">';

  for (const [key, g] of Object.entries(groups)) {
    const supplierLabel = g.name
      ? `<span class="fw-bold">${esc(g.name)}</span>${g.phone ? `<span class="text-muted small ms-2"><i class="bi bi-telephone me-1"></i>${esc(g.phone)}</span>` : ''}`
      : '<span class="text-muted fst-italic">Tedarikçi atanmamış</span>';

    html += `<div class="supplier-section shadow-sm">
      <div class="supplier-header">
        <div>${supplierLabel}</div>
        <span class="badge bg-secondary">${g.items.length} ürün</span>
      </div>`;

    g.items.forEach(p => {
      const cur      = parseFloat(p.current_qty || 0);
      const min      = parseFloat(p.min_stock_qty || 0);
      const status   = getStatus(p);
      const stCls    = status === 'zero' ? 'status-zero' : 'status-low';
      const stIcon   = status === 'zero' ? 'bi-exclamation-circle-fill' : 'bi-exclamation-triangle-fill';
      const price    = parseFloat(p.last_price || p.avg_cost || 0);
      const isChk    = checked[p.id] !== false;

      html += `
        <div class="item-row${isChk ? '' : ' unchecked'}" id="row-${p.id}">
          <input type="checkbox" class="form-check-input flex-shrink-0 item-chk"
                 data-id="${p.id}" ${isChk ? 'checked' : ''}>
          <i class="bi ${stIcon} ${stCls} flex-shrink-0"></i>
          <div class="flex-grow-1 min-width-0">
            <div class="fw-semibold text-truncate">${esc(p.name)}</div>
            <div class="text-muted" style="font-size:.72rem">
              Mevcut: <span class="${stCls} fw-semibold">${fmtQty(cur, p.unit)}</span>
              ${min > 0 ? ` · Min: ${fmtQty(min, p.unit)}` : ''}
              ${price > 0 ? ` · Son fiyat: ${fmtMoney(price)}` : ''}
            </div>
          </div>
          <div class="d-flex align-items-center gap-1 flex-shrink-0">
            <input type="number" class="form-control form-control-sm qty-input item-qty"
                   data-id="${p.id}" value="${orderQty[p.id] ?? 1}"
                   min="0.001" step="1" style="width:75px;text-align:center">
            <span class="text-muted small">${esc(p.unit)}</span>
          </div>
          ${price > 0 ? `<div class="text-end flex-shrink-0" style="min-width:70px">
            <span class="small fw-semibold item-line-total" data-id="${p.id}" data-price="${price}">
              ${fmtMoney((orderQty[p.id]??1) * price)}
            </span></div>` : `<div style="min-width:70px"></div>`}
        </div>`;
    });

    html += '</div>';
  }
  html += '</div>';

  document.getElementById('listWrap').innerHTML = html;

  // Checkbox events
  document.querySelectorAll('.item-chk').forEach(el => {
    el.addEventListener('change', () => {
      const id     = el.dataset.id;
      checked[id]  = el.checked;
      const row    = document.getElementById(`row-${id}`);
      row.classList.toggle('unchecked', !el.checked);
      recalcTotal();
    });
  });

  // Qty events
  document.querySelectorAll('.item-qty').forEach(el => {
    el.addEventListener('input', () => {
      const id  = el.dataset.id;
      const qty = parseFloat(el.value) || 0;
      orderQty[id] = qty;
      // Satır toplamı güncelle
      const lt = document.querySelector(`.item-line-total[data-id="${id}"]`);
      if (lt) lt.textContent = fmtMoney(qty * parseFloat(lt.dataset.price));
      recalcTotal();
    });
  });

  recalcTotal();
}

function recalcTotal() {
  let count = 0, total = 0;
  allItems.forEach(p => {
    if (checked[p.id] === false) return;
    const qty   = parseFloat(orderQty[p.id] || 0);
    const price = parseFloat(p.last_price || p.avg_cost || 0);
    count++;
    total += qty * price;
  });
  document.getElementById('selectedCount').textContent = count;
  document.getElementById('totalEstimate').textContent = fmtMoney(total);
}

/* ── FİLTRE ── */
document.querySelectorAll('.filter-btn').forEach(btn => {
  btn.addEventListener('click', () => {
    document.querySelectorAll('.filter-btn').forEach(b => b.classList.remove('active'));
    btn.classList.add('active');
    activeFilter = btn.dataset.filter;
    render();
  });
});

document.getElementById('searchInput').addEventListener('input', render);

document.getElementById('btnSelectAll').addEventListener('click', () => {
  allItems.forEach(p => { checked[p.id] = true; });
  render();
});
document.getElementById('btnSelectNone').addEventListener('click', () => {
  allItems.forEach(p => { checked[p.id] = false; });
  render();
});

document.getElementById('btnRefresh').addEventListener('click', loadList);

/* ── BAŞLAT ── */
loadList();
</script>
</body>
</html>
