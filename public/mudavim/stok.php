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
<title>Stok Durumu — Müdavim</title>
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
<link rel="stylesheet" href="assets/style.css">
<style>
.stock-bar-wrap { height:6px; background:#e9ecef; border-radius:3px; overflow:hidden; margin-top:4px; }
.stock-bar      { height:6px; border-radius:3px; transition:width .3s; }
.status-zero    { border-left:3px solid #dc3545 !important; }
.status-low     { border-left:3px solid #fd7e14 !important; }
.status-ok      { border-left:3px solid #198754 !important; }
.filter-btn.active { background:#0d6efd; color:#fff; border-color:#0d6efd; }
</style>
</head>
<body>

<nav class="navbar navbar-dark bg-mud-dark sticky-top">
  <div class="container-fluid px-3">
    <div class="d-flex align-items-center gap-2">
      <a href="dashboard.php" class="btn btn-sm btn-outline-light">
        <i class="bi bi-arrow-left"></i>
      </a>
      <span class="navbar-brand mb-0 fw-bold ms-1">
        <i class="bi bi-boxes me-1"></i>Stok Durumu
      </span>
    </div>
    <button class="btn btn-sm btn-outline-light" id="btnRefresh">
      <i class="bi bi-arrow-clockwise"></i>
    </button>
  </div>
</nav>

<div class="container-fluid px-2 px-sm-3 py-3" style="max-width:900px;margin:0 auto">

  <!-- ÖZET SAYAÇLAR -->
  <div class="row g-2 mb-3" id="summaryRow">
    <div class="col-4">
      <div class="text-center p-2 rounded border bg-white shadow-sm">
        <div class="fw-bold fs-4 text-danger" id="cntZero">—</div>
        <div class="text-muted" style="font-size:.68rem;text-transform:uppercase;letter-spacing:.06em">Sıfır Stok</div>
      </div>
    </div>
    <div class="col-4">
      <div class="text-center p-2 rounded border bg-white shadow-sm">
        <div class="fw-bold fs-4 text-warning" id="cntLow">—</div>
        <div class="text-muted" style="font-size:.68rem;text-transform:uppercase;letter-spacing:.06em">Kritik</div>
      </div>
    </div>
    <div class="col-4">
      <div class="text-center p-2 rounded border bg-white shadow-sm">
        <div class="fw-bold fs-4 text-success" id="cntOk">—</div>
        <div class="text-muted" style="font-size:.68rem;text-transform:uppercase;letter-spacing:.06em">Yeterli</div>
      </div>
    </div>
  </div>

  <!-- FİLTRE -->
  <div class="d-flex gap-2 mb-3 flex-wrap">
    <input type="search" class="form-control form-control-sm" id="searchInput"
           placeholder="Ürün ara…" style="max-width:200px">
    <div class="d-flex gap-1">
      <button class="btn btn-sm btn-outline-secondary filter-btn active" data-filter="all">Tümü</button>
      <button class="btn btn-sm btn-outline-danger filter-btn"   data-filter="zero">Sıfır</button>
      <button class="btn btn-sm btn-outline-warning filter-btn"  data-filter="low">Kritik</button>
      <button class="btn btn-sm btn-outline-success filter-btn"  data-filter="ok">Yeterli</button>
    </div>
  </div>

  <div id="stockList">
    <div class="text-center py-5 text-muted">
      <div class="spinner-border spinner-border-sm me-2"></div>Yükleniyor…
    </div>
  </div>

</div>

<div class="position-fixed bottom-0 end-0 p-3" style="z-index:1100" id="toastContainer"></div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
async function apiFetch(path) {
  const res  = await fetch(`api/${path}`, { credentials:'same-origin' });
  const json = await res.json().catch(() => ({status:'error',data:{message:'Hata'}}));
  if (!res.ok || json.status === 'error') throw new Error(json?.data?.message || `HTTP ${res.status}`);
  return json.data;
}

function toast(msg, type='danger') {
  const id = `t${Date.now()}`;
  document.getElementById('toastContainer').insertAdjacentHTML('beforeend',`
    <div id="${id}" class="toast align-items-center border-0 shadow-sm" role="alert">
      <div class="d-flex">
        <div class="toast-body fw-semibold"><i class="bi bi-x-circle-fill text-${type} me-2"></i>${msg}</div>
        <button type="button" class="btn-close me-2 m-auto" data-bs-dismiss="toast"></button>
      </div>
    </div>`);
  const el = document.getElementById(id);
  new bootstrap.Toast(el,{delay:3500}).show();
  el.addEventListener('hidden.bs.toast', () => el.remove());
}

function esc(s) { return String(s??'').replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;'); }

function fmtQty(n, unit) {
  return parseFloat(n||0).toLocaleString('tr-TR',{maximumFractionDigits:3}) + ' ' + unit;
}

function fmtDate(dt) {
  if (!dt) return '—';
  return new Date(dt).toLocaleDateString('tr-TR',{day:'2-digit',month:'short'});
}

function getStatus(qty, minQty) {
  qty    = parseFloat(qty || 0);
  minQty = parseFloat(minQty || 0);
  if (qty <= 0)              return 'zero';
  if (minQty > 0 && qty < minQty) return 'low';
  return 'ok';
}

/* ── DATA ── */
let allStock  = [];
let activeFilter = 'all';

async function loadStock() {
  document.getElementById('stockList').innerHTML =
    '<div class="text-center py-5 text-muted"><div class="spinner-border spinner-border-sm me-2"></div>Yükleniyor…</div>';
  try {
    allStock = await apiFetch('stock/status');
    renderSummary();
    renderList();
  } catch(e) {
    toast(e.message);
    document.getElementById('stockList').innerHTML =
      `<div class="alert alert-danger">${esc(e.message)}</div>`;
  }
}

function renderSummary() {
  const zero = allStock.filter(p => getStatus(p.qty, p.min_stock_qty) === 'zero').length;
  const low  = allStock.filter(p => getStatus(p.qty, p.min_stock_qty) === 'low').length;
  const ok   = allStock.filter(p => getStatus(p.qty, p.min_stock_qty) === 'ok').length;
  document.getElementById('cntZero').textContent = zero;
  document.getElementById('cntLow').textContent  = low;
  document.getElementById('cntOk').textContent   = ok;
}

function renderList() {
  const q      = document.getElementById('searchInput').value.trim().toLowerCase();
  let filtered = allStock.filter(p => {
    const matchQ   = !q || p.name.toLowerCase().includes(q) || (p.sku||'').toLowerCase().includes(q);
    const status   = getStatus(p.qty, p.min_stock_qty);
    const matchF   = activeFilter === 'all' || status === activeFilter;
    return matchQ && matchF;
  });

  if (!filtered.length) {
    document.getElementById('stockList').innerHTML =
      '<div class="text-center py-5 text-muted"><i class="bi bi-search fs-1 d-block mb-2"></i>Ürün bulunamadı.</div>';
    return;
  }

  /* Kategoriye göre grupla */
  const groups = {};
  filtered.forEach(p => {
    if (!groups[p.category_name]) groups[p.category_name] = [];
    groups[p.category_name].push(p);
  });

  let html = '';
  for (const [cat, items] of Object.entries(groups)) {
    html += `<h6 class="text-muted fw-semibold text-uppercase small mt-3 mb-2 px-1"
               style="letter-spacing:.07em">${esc(cat)}</h6>
             <div class="list-group shadow-sm mb-2">`;

    items.forEach(p => {
      const qty    = parseFloat(p.qty || 0);
      const minQty = parseFloat(p.min_stock_qty || 0);
      const status = getStatus(qty, minQty);
      const avgCost= parseFloat(p.avg_cost || 0);
      const totalVal = qty * avgCost;

      const statusClass = `status-${status}`;
      const statusBadge = status === 'zero'
        ? '<span class="badge bg-danger">Sıfır</span>'
        : status === 'low'
          ? '<span class="badge bg-warning text-dark">Kritik</span>'
          : '<span class="badge bg-success bg-opacity-10 text-success border border-success">Yeterli</span>';

      // İlerleme çubuğu (min stok varsa)
      let barHtml = '';
      if (minQty > 0 && qty > 0) {
        const pct  = Math.min(100, Math.round(qty / minQty * 100));
        const color= pct >= 100 ? '#198754' : pct >= 50 ? '#fd7e14' : '#dc3545';
        barHtml = `<div class="stock-bar-wrap"><div class="stock-bar" style="width:${pct}%;background:${color}"></div></div>`;
      }

      html += `
        <div class="list-group-item ${statusClass} py-2">
          <div class="d-flex justify-content-between align-items-start gap-2">
            <div class="flex-grow-1 min-width-0">
              <div class="d-flex align-items-center gap-2 flex-wrap">
                <span class="fw-semibold">${esc(p.name)}</span>
                ${statusBadge}
              </div>
              ${barHtml}
              <div class="text-muted mt-1" style="font-size:.73rem">
                ${minQty > 0 ? `Min: ${fmtQty(minQty, p.unit)} &middot; ` : ''}
                Son hareket: ${fmtDate(p.last_movement)}
              </div>
            </div>
            <div class="text-end flex-shrink-0">
              <div class="fw-bold fs-6">${fmtQty(qty, p.unit)}</div>
              ${avgCost > 0 ? `<div class="text-muted small">₺${totalVal.toLocaleString('tr-TR',{maximumFractionDigits:0})}</div>` : ''}
            </div>
          </div>
        </div>`;
    });
    html += '</div>';
  }

  document.getElementById('stockList').innerHTML = html;
}

/* ── FİLTRE ── */
document.querySelectorAll('.filter-btn').forEach(btn => {
  btn.addEventListener('click', () => {
    document.querySelectorAll('.filter-btn').forEach(b => b.classList.remove('active'));
    btn.classList.add('active');
    activeFilter = btn.dataset.filter;
    renderList();
  });
});

document.getElementById('searchInput').addEventListener('input', renderList);
document.getElementById('btnRefresh').addEventListener('click', loadStock);

/* ── BAŞLAT ── */
loadStock();
</script>
</body>
</html>
