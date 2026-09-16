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
<title>Rapor — Müdavim</title>
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
<style>
body { background:#f8f9fa; }
.nav-top { background:#1a1a2e; }
.nav-top .btn-nav { color:#ccc; border:none; background:none; padding:.4rem .9rem; border-radius:6px; font-size:.85rem; }
.nav-top .btn-nav:hover, .nav-top .btn-nav.active { background:#ffffff22; color:#fff; }
.stat-card { border:none; border-radius:12px; }
.stat-card .val { font-size:1.6rem; font-weight:700; }
.stat-card .lbl { font-size:.78rem; color:#6c757d; }
.kind-entry  { border-left:3px solid #198754; }
.kind-outlet { border-left:3px solid #dc3545; }
.kind-waste  { border-left:3px solid #fd7e14; }
.tab-btn { border:none; background:none; padding:.4rem 1rem; border-radius:6px; font-size:.88rem; color:#6c757d; }
.tab-btn.active { background:#0d6efd; color:#fff; }
</style>
</head>
<body>

<!-- NAV -->
<div class="nav-top px-3 py-2 d-flex align-items-center gap-2 flex-wrap">
  <span class="text-white fw-bold me-3" style="font-size:1rem;">🍽 Müdavim</span>
  <a href="dashboard.php"  class="btn-nav">Kasa</a>
  <a href="stok.php"       class="btn-nav">Stok</a>
  <a href="urunler.php"    class="btn-nav">Ürünler</a>
  <a href="tedarikci.php"  class="btn-nav">Tedarikçi</a>
  <a href="alis.php"       class="btn-nav">Alış</a>
  <a href="sayim.php"      class="btn-nav">Sayım</a>
  <a href="rapor.php"      class="btn-nav active">Rapor</a>
  <?php if ($role === 'patron'): ?>
  <a href="demirbaslar.php" class="btn-nav">Demirbaş</a>
  <?php endif; ?>
  <div class="ms-auto">
    <a href="logout.php" class="btn-nav">Çıkış</a>
  </div>
</div>

<!-- HEADER & FİLTRE -->
<div class="container-fluid px-4 pt-4 pb-3">
  <div class="d-flex align-items-center justify-content-between flex-wrap gap-3">
    <h4 class="mb-0 fw-bold">Raporlar</h4>
    <div class="d-flex gap-2 align-items-center flex-wrap">
      <input type="date" id="dateStart" class="form-control form-control-sm" style="width:145px">
      <span class="text-muted">—</span>
      <input type="date" id="dateEnd"   class="form-control form-control-sm" style="width:145px">
      <button class="btn btn-primary btn-sm" onclick="loadAll()">Uygula</button>
      <button class="btn btn-outline-secondary btn-sm" onclick="setPreset('month')">Bu Ay</button>
      <button class="btn btn-outline-secondary btn-sm" onclick="setPreset('week')">Bu Hafta</button>
      <button class="btn btn-outline-secondary btn-sm" onclick="setPreset('today')">Bugün</button>
    </div>
  </div>
</div>

<!-- ÖZET KARTLAR -->
<div class="container-fluid px-4 mb-4">
  <div class="row g-3">
    <div class="col-6 col-md-3">
      <div class="card stat-card shadow-sm p-3" style="border-left:4px solid #198754">
        <div class="val text-success" id="sEntryTotal">—</div>
        <div class="lbl">Toplam Alış (₺)</div>
        <div class="text-muted" style="font-size:.75rem" id="sEntryCount">— giriş</div>
      </div>
    </div>
    <div class="col-6 col-md-3">
      <div class="card stat-card shadow-sm p-3" style="border-left:4px solid #dc3545">
        <div class="val text-danger" id="sOutletTotal">—</div>
        <div class="lbl">Toplam Çıkış (₺)</div>
        <div class="text-muted" style="font-size:.75rem" id="sOutletCount">— çıkış</div>
      </div>
    </div>
    <div class="col-6 col-md-3">
      <div class="card stat-card shadow-sm p-3" style="border-left:4px solid #fd7e14">
        <div class="val text-warning" id="sWasteTotal">—</div>
        <div class="lbl">Fire / Kayıp (₺)</div>
        <div class="text-muted" style="font-size:.75rem">fire & sayım açığı</div>
      </div>
    </div>
    <div class="col-6 col-md-3">
      <div class="card stat-card shadow-sm p-3" style="border-left:4px solid #0dcaf0">
        <div class="val text-info" id="sCash">—</div>
        <div class="lbl">Kasa Bakiyesi (₺)</div>
        <div class="text-muted" style="font-size:.75rem">anlık</div>
      </div>
    </div>
  </div>
</div>

<!-- TABS -->
<div class="container-fluid px-4 mb-3">
  <div class="d-flex gap-1">
    <button class="tab-btn active" id="tabMovements" onclick="showTab('movements')">Hareketler</button>
    <button class="tab-btn" id="tabExpenses"  onclick="showTab('expenses')">Gider Kalemleri</button>
  </div>
  <hr class="mt-2 mb-0">
</div>

<!-- HAREKETler -->
<div class="container-fluid px-4 pb-5" id="panelMovements">
  <div class="d-flex gap-2 mb-3 flex-wrap">
    <button class="btn btn-sm btn-outline-secondary active" id="fAll"   onclick="filterMov('all')">Tümü</button>
    <button class="btn btn-sm btn-outline-success"         id="fEntry" onclick="filterMov('entry')">Girişler</button>
    <button class="btn btn-sm btn-outline-danger"          id="fOut"   onclick="filterMov('outlet')">Çıkışlar</button>
    <input type="search" id="movSearch" class="form-control form-control-sm ms-auto" placeholder="Ürün ara…" style="max-width:200px" oninput="renderMovements()">
  </div>
  <div class="card shadow-sm border-0">
    <div class="card-body p-0">
      <table class="table table-hover align-middle mb-0 small">
        <thead class="table-light">
          <tr>
            <th class="ps-3">Tarih</th>
            <th>Tür</th>
            <th>Ürün</th>
            <th>Kategori</th>
            <th class="text-end">Miktar</th>
            <th class="text-end">Tutar (₺)</th>
            <th>Personel</th>
          </tr>
        </thead>
        <tbody id="movTbody">
          <tr><td colspan="7" class="text-center text-muted py-4">Yükleniyor…</td></tr>
        </tbody>
      </table>
    </div>
  </div>
  <div class="text-muted small mt-2" id="movCount"></div>
</div>

<!-- GİDER KALEMLERİ -->
<div class="container-fluid px-4 pb-5 d-none" id="panelExpenses">
  <div class="card shadow-sm border-0">
    <div class="card-body p-0">
      <table class="table table-hover align-middle mb-0 small">
        <thead class="table-light">
          <tr>
            <th class="ps-3">Ürün</th>
            <th>Kategori</th>
            <th class="text-end">Toplam Alış (₺)</th>
            <th class="text-end">Toplam Çıkış (₺)</th>
            <th class="text-end">Fire (₺)</th>
          </tr>
        </thead>
        <tbody id="expTbody">
          <tr><td colspan="5" class="text-center text-muted py-4">Yükleniyor…</td></tr>
        </tbody>
      </table>
    </div>
  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
const API = 'api/index.php';

let allMovements = [];
let movFilter    = 'all';

async function apiFetch(ep) {
  const r = await fetch(`${API}/${ep}`);
  const j = await r.json();
  return j.data ?? [];
}

function fmt(n) { return parseFloat(n || 0).toLocaleString('tr-TR', { minimumFractionDigits: 2, maximumFractionDigits: 2 }); }

function setPreset(p) {
  const now   = new Date();
  const pad   = n => String(n).padStart(2,'0');
  const ymd   = d => `${d.getFullYear()}-${pad(d.getMonth()+1)}-${pad(d.getDate())}`;
  const today = ymd(now);

  if (p === 'today') {
    document.getElementById('dateStart').value = today;
    document.getElementById('dateEnd').value   = today;
  } else if (p === 'week') {
    const mon = new Date(now); mon.setDate(now.getDate() - now.getDay() + 1);
    document.getElementById('dateStart').value = ymd(mon);
    document.getElementById('dateEnd').value   = today;
  } else {
    document.getElementById('dateStart').value = `${now.getFullYear()}-${pad(now.getMonth()+1)}-01`;
    document.getElementById('dateEnd').value   = today;
  }
  loadAll();
}

async function loadAll() {
  const s = document.getElementById('dateStart').value;
  const e = document.getElementById('dateEnd').value;

  const [summary, movements] = await Promise.all([
    apiFetch(`report/summary?start=${s}&end=${e}`),
    apiFetch(`report/movements?start=${s}&end=${e}&limit=500`),
  ]);

  // Özet kartlar
  if (summary && !Array.isArray(summary)) {
    document.getElementById('sEntryTotal').textContent  = fmt(summary.entry_total);
    document.getElementById('sEntryCount').textContent  = `${summary.entry_count} giriş`;
    document.getElementById('sOutletTotal').textContent = fmt(summary.outlet_total);
    document.getElementById('sOutletCount').textContent = `${summary.outlet_count} çıkış`;
    document.getElementById('sWasteTotal').textContent  = fmt(summary.waste_value);
    document.getElementById('sCash').textContent        = fmt(summary.cash_balance);
  }

  allMovements = Array.isArray(movements) ? movements : [];
  renderMovements();
  renderExpenses();
}

function filterMov(f) {
  movFilter = f;
  ['fAll','fEntry','fOut'].forEach(id => document.getElementById(id).classList.remove('active'));
  document.getElementById(f === 'all' ? 'fAll' : f === 'entry' ? 'fEntry' : 'fOut').classList.add('active');
  renderMovements();
}

function renderMovements() {
  const q = document.getElementById('movSearch').value.toLowerCase();
  let rows = allMovements.filter(r => {
    if (movFilter !== 'all' && r.kind !== movFilter) return false;
    if (q && !r.product_name.toLowerCase().includes(q)) return false;
    return true;
  });

  if (!rows.length) {
    document.getElementById('movTbody').innerHTML = `<tr><td colspan="7" class="text-center text-muted py-4">Hareket bulunamadı</td></tr>`;
    document.getElementById('movCount').textContent = '';
    return;
  }

  const html = rows.map(r => {
    const isEntry = r.kind === 'entry';
    const cls     = isEntry ? 'kind-entry' : (r.accounting_category === 'waste_loss' ? 'kind-waste' : 'kind-outlet');
    const icon    = isEntry ? '↓' : '↑';
    const color   = isEntry ? 'text-success' : (r.accounting_category === 'waste_loss' ? 'text-warning' : 'text-danger');
    return `<tr class="${cls}">
      <td class="ps-3">${r.txn_date}</td>
      <td><span class="badge bg-light text-dark border">${r.type_name}</span></td>
      <td class="fw-semibold">${r.product_name}</td>
      <td class="text-muted">${r.category_name}</td>
      <td class="text-end">${parseFloat(r.qty).toLocaleString('tr-TR')} ${r.unit}</td>
      <td class="text-end ${color} fw-semibold">${icon} ${fmt(r.total_cost)}</td>
      <td class="text-muted">${r.user_name}</td>
    </tr>`;
  }).join('');

  document.getElementById('movTbody').innerHTML = html;
  document.getElementById('movCount').textContent = `${rows.length} hareket gösteriliyor`;
}

function renderExpenses() {
  // Ürün bazında özet
  const byProduct = {};
  allMovements.forEach(r => {
    const key = r.product_name;
    if (!byProduct[key]) byProduct[key] = { name: key, cat: r.category_name, entry: 0, outlet: 0, waste: 0 };
    const cost = parseFloat(r.total_cost || 0);
    if (r.kind === 'entry') {
      byProduct[key].entry += cost;
    } else {
      byProduct[key].outlet += cost;
      if (r.accounting_category === 'waste_loss') byProduct[key].waste += cost;
    }
  });

  const rows = Object.values(byProduct).sort((a,b) => b.entry - a.entry);

  if (!rows.length) {
    document.getElementById('expTbody').innerHTML = `<tr><td colspan="5" class="text-center text-muted py-4">Veri yok</td></tr>`;
    return;
  }

  document.getElementById('expTbody').innerHTML = rows.map(r => `
    <tr>
      <td class="ps-3 fw-semibold">${r.name}</td>
      <td class="text-muted">${r.cat}</td>
      <td class="text-end text-success">${fmt(r.entry)}</td>
      <td class="text-end text-danger">${fmt(r.outlet)}</td>
      <td class="text-end text-warning">${fmt(r.waste)}</td>
    </tr>
  `).join('');
}

function showTab(t) {
  document.getElementById('panelMovements').classList.toggle('d-none', t !== 'movements');
  document.getElementById('panelExpenses').classList.toggle('d-none',  t !== 'expenses');
  document.getElementById('tabMovements').classList.toggle('active', t === 'movements');
  document.getElementById('tabExpenses').classList.toggle('active',  t === 'expenses');
}

// İlk yük: bu ay
setPreset('month');
</script>
</body>
</html>
