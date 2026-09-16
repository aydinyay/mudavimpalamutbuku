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
<title>Raporlar — Müdavim</title>
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
<link rel="stylesheet" href="assets/style.css">
<style>
.stat-card { background:#fff; border:1px solid #dee2e6; border-radius:.75rem; padding:1rem; box-shadow:0 1px 4px rgba(0,0,0,.06); }
.stat-val  { font-size:1.5rem; font-weight:700; line-height:1.1; }
.stat-lbl  { font-size:.68rem; color:#6c757d; text-transform:uppercase; letter-spacing:.06em; margin-top:.2rem; }
.mvmt-row  { border-left:3px solid transparent; }
.mvmt-entry { border-left-color:#198754; }
.mvmt-waste { border-left-color:#dc3545; }
.mvmt-outlet{ border-left-color:#0d6efd; }
.preset-btn.active { background:#0d6efd; color:#fff; border-color:#0d6efd; }
</style>
</head>
<body>

<!-- NAVBAR -->
<nav class="navbar navbar-dark bg-mud-dark sticky-top">
  <div class="container-fluid px-3">
    <div class="d-flex align-items-center gap-2">
      <a href="dashboard.php" class="btn btn-sm btn-outline-light">
        <i class="bi bi-arrow-left"></i>
      </a>
      <span class="navbar-brand mb-0 fw-bold ms-1">
        <i class="bi bi-bar-chart-fill me-1"></i>Raporlar
      </span>
    </div>
  </div>
</nav>

<div class="container-fluid px-2 px-sm-3 py-3" style="max-width:900px;margin:0 auto">

  <!-- TARİH ARALIĞI -->
  <div class="card shadow-sm mb-3">
    <div class="card-body pb-2">
      <div class="d-flex gap-1 flex-wrap mb-2">
        <button class="btn btn-sm btn-outline-secondary preset-btn" data-preset="today">Bugün</button>
        <button class="btn btn-sm btn-outline-secondary preset-btn" data-preset="week">Bu Hafta</button>
        <button class="btn btn-sm btn-outline-secondary preset-btn active" data-preset="month">Bu Ay</button>
        <button class="btn btn-sm btn-outline-secondary preset-btn" data-preset="lastmonth">Geçen Ay</button>
      </div>
      <div class="row g-2 align-items-end">
        <div class="col">
          <label class="form-label small fw-semibold mb-1">Başlangıç</label>
          <input type="date" class="form-control form-control-sm" id="dateStart">
        </div>
        <div class="col">
          <label class="form-label small fw-semibold mb-1">Bitiş</label>
          <input type="date" class="form-control form-control-sm" id="dateEnd">
        </div>
        <div class="col-auto">
          <button class="btn btn-primary btn-sm px-3" id="btnLoad">
            <i class="bi bi-search"></i>
          </button>
        </div>
      </div>
    </div>
  </div>

  <!-- ÖZET KARTLAR -->
  <div class="row g-2 mb-3" id="summaryCards">
    <div class="col-6 col-sm-3">
      <div class="stat-card">
        <div class="stat-val text-success" id="statEntryTotal">—</div>
        <div class="stat-lbl">Alım Tutarı</div>
        <div class="text-muted mt-1" style="font-size:.75rem"><span id="statEntryCount">—</span> işlem</div>
      </div>
    </div>
    <div class="col-6 col-sm-3">
      <div class="stat-card">
        <div class="stat-val text-primary" id="statOutletTotal">—</div>
        <div class="stat-lbl">Çıkış Tutarı</div>
        <div class="text-muted mt-1" style="font-size:.75rem"><span id="statOutletCount">—</span> işlem</div>
      </div>
    </div>
    <div class="col-6 col-sm-3">
      <div class="stat-card">
        <div class="stat-val text-danger" id="statWaste">—</div>
        <div class="stat-lbl">Zayi / Fire</div>
      </div>
    </div>
    <div class="col-6 col-sm-3">
      <div class="stat-card">
        <div class="stat-val text-warning" id="statCash">—</div>
        <div class="stat-lbl">Kasa Bakiyesi</div>
      </div>
    </div>
  </div>

  <!-- FİLTRE + LİSTE -->
  <div class="d-flex gap-2 mb-2 align-items-center">
    <h6 class="fw-bold mb-0 text-muted text-uppercase small" style="letter-spacing:.06em">
      <i class="bi bi-list-ul me-1"></i>Hareketler
    </h6>
    <div class="ms-auto d-flex gap-1">
      <button class="btn btn-xs btn-outline-secondary kind-filter active" data-kind="all"
              style="font-size:.72rem;padding:.2rem .5rem">Tümü</button>
      <button class="btn btn-xs btn-outline-success kind-filter" data-kind="entry"
              style="font-size:.72rem;padding:.2rem .5rem">Giriş</button>
      <button class="btn btn-xs btn-outline-primary kind-filter" data-kind="outlet"
              style="font-size:.72rem;padding:.2rem .5rem">Çıkış</button>
      <button class="btn btn-xs btn-outline-danger kind-filter" data-kind="waste"
              style="font-size:.72rem;padding:.2rem .5rem">Zayi</button>
    </div>
  </div>

  <div id="movementList">
    <div class="text-center py-4 text-muted small">Tarih aralığı seçip yükleyin.</div>
  </div>

</div>

<!-- TOAST -->
<div class="position-fixed bottom-0 end-0 p-3" style="z-index:1100" id="toastContainer"></div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
async function apiFetch(path) {
  const res  = await fetch(`api/${path}`, { credentials: 'same-origin' });
  const json = await res.json().catch(() => ({ status:'error', data:{ message:'Hata' }}));
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
  new bootstrap.Toast(el, {delay:4000}).show();
  el.addEventListener('hidden.bs.toast', () => el.remove());
}

function fmt(n)  { return (n||0).toLocaleString('tr-TR',{minimumFractionDigits:0,maximumFractionDigits:0}); }
function fmtTL(n){ return '₺' + fmt(n); }
function esc(s)  { return String(s??'').replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;'); }

function fmtDateTime(dt) {
  if (!dt) return '—';
  const d = new Date(dt);
  return d.toLocaleDateString('tr-TR',{day:'2-digit',month:'short'}) + ' ' +
         d.toLocaleTimeString('tr-TR',{hour:'2-digit',minute:'2-digit'});
}

/* ── DATE PRESETS ── */
function today()     { return new Date().toISOString().slice(0,10); }
function daysAgo(n)  { const d=new Date(); d.setDate(d.getDate()-n); return d.toISOString().slice(0,10); }

const presets = {
  today:     () => ({ s: today(),                e: today() }),
  week:      () => ({ s: daysAgo(6),             e: today() }),
  month:     () => {
    const d = new Date(); d.setDate(1);
    return { s: d.toISOString().slice(0,10),     e: today() };
  },
  lastmonth: () => {
    const s = new Date(); s.setDate(1); s.setMonth(s.getMonth()-1);
    const e = new Date(); e.setDate(0);
    return { s: s.toISOString().slice(0,10), e: e.toISOString().slice(0,10) };
  },
};

// Başlangıç: Bu Ay
const initRange = presets.month();
document.getElementById('dateStart').value = initRange.s;
document.getElementById('dateEnd').value   = initRange.e;

document.querySelectorAll('.preset-btn').forEach(btn => {
  btn.addEventListener('click', () => {
    document.querySelectorAll('.preset-btn').forEach(b => b.classList.remove('active'));
    btn.classList.add('active');
    const r = presets[btn.dataset.preset]();
    document.getElementById('dateStart').value = r.s;
    document.getElementById('dateEnd').value   = r.e;
    loadAll();
  });
});

document.getElementById('btnLoad').addEventListener('click', () => {
  document.querySelectorAll('.preset-btn').forEach(b => b.classList.remove('active'));
  loadAll();
});

/* ── DATA ── */
let allMovements = [];
let kindFilter   = 'all';

async function loadAll() {
  const start = document.getElementById('dateStart').value;
  const end   = document.getElementById('dateEnd').value;
  if (!start || !end) { toast('Tarih aralığı seçin','warning'); return; }

  document.getElementById('movementList').innerHTML =
    '<div class="text-center py-4 text-muted"><div class="spinner-border spinner-border-sm me-2"></div>Yükleniyor…</div>';

  try {
    const [summary, movements] = await Promise.all([
      apiFetch(`report/summary?start=${start}&end=${end}`),
      apiFetch(`report/movements?start=${start}&end=${end}`),
    ]);
    renderSummary(summary);
    allMovements = movements;
    renderMovements();
  } catch(e) {
    toast(e.message);
    document.getElementById('movementList').innerHTML =
      `<div class="alert alert-danger">${esc(e.message)}</div>`;
  }
}

function renderSummary(s) {
  document.getElementById('statEntryTotal').textContent  = fmtTL(s.entry_total);
  document.getElementById('statEntryCount').textContent  = s.entry_count;
  document.getElementById('statOutletTotal').textContent = fmtTL(s.outlet_total);
  document.getElementById('statOutletCount').textContent = s.outlet_count;
  document.getElementById('statWaste').textContent       = fmtTL(s.waste_value);
  document.getElementById('statCash').textContent        = s.cash_balance !== null ? fmtTL(s.cash_balance) : '—';
}

function renderMovements() {
  let rows = allMovements;

  if (kindFilter === 'entry')  rows = rows.filter(r => r.kind === 'entry');
  else if (kindFilter === 'waste')  rows = rows.filter(r => r.kind === 'outlet' && r.accounting_category === 'waste_loss');
  else if (kindFilter === 'outlet') rows = rows.filter(r => r.kind === 'outlet' && r.accounting_category !== 'waste_loss');

  if (!rows.length) {
    document.getElementById('movementList').innerHTML =
      '<div class="text-center py-5 text-muted"><i class="bi bi-inbox fs-1 d-block mb-2"></i>Bu aralıkta kayıt yok.</div>';
    return;
  }

  let html = '<div class="list-group shadow-sm">';
  rows.forEach(r => {
    const isWaste  = r.kind === 'outlet' && r.accounting_category === 'waste_loss';
    const rowClass = r.kind === 'entry' ? 'mvmt-entry' : (isWaste ? 'mvmt-waste' : 'mvmt-outlet');
    const badge    = r.kind === 'entry'
      ? '<span class="badge bg-success">GİRİŞ</span>'
      : (isWaste
        ? '<span class="badge bg-danger">ZAYİ</span>'
        : '<span class="badge bg-primary">ÇIKIŞ</span>');
    const cost = parseFloat(r.total_cost||0);
    html += `
      <div class="list-group-item mvmt-row ${rowClass} py-2">
        <div class="d-flex justify-content-between align-items-start gap-2">
          <div class="flex-grow-1 min-width-0">
            <div class="d-flex align-items-center gap-2 flex-wrap">
              ${badge}
              <span class="fw-semibold">${esc(r.product_name)}</span>
            </div>
            <div class="text-muted small mt-1">
              ${esc(r.type_name)}
              &middot; ${(+r.qty).toLocaleString('tr-TR',{maximumFractionDigits:3})} ${esc(r.unit)}
              &middot; <i class="bi bi-person me-1"></i>${esc(r.user_name)}
            </div>
          </div>
          <div class="text-end flex-shrink-0">
            ${cost > 0 ? `<div class="fw-semibold ${r.kind==='entry'?'text-success':'text-danger'}">${fmtTL(cost)}</div>` : ''}
            <div class="text-muted" style="font-size:.72rem">${fmtDateTime(r.created_at)}</div>
          </div>
        </div>
      </div>`;
  });
  html += '</div>';
  document.getElementById('movementList').innerHTML = html;
}

/* ── KİND FİLTRE ── */
document.querySelectorAll('.kind-filter').forEach(btn => {
  btn.addEventListener('click', () => {
    document.querySelectorAll('.kind-filter').forEach(b => b.classList.remove('active'));
    btn.classList.add('active');
    kindFilter = btn.dataset.kind;
    renderMovements();
  });
});

/* ── BAŞLAT ── */
loadAll();
</script>
</body>
</html>
