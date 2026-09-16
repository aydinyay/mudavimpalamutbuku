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
<title>Ürün Yönetimi — Müdavim</title>
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
<style>
body { background:#f8f9fa; }
.nav-top { background:#1a1a2e; }
.nav-top .btn-nav { color:#ccc; border:none; background:none; padding:.4rem .9rem; border-radius:6px; font-size:.85rem; }
.nav-top .btn-nav:hover, .nav-top .btn-nav.active { background:#ffffff22; color:#fff; }
.card-product { border-left:4px solid #dee2e6; transition:border-color .2s; }
.card-product:hover { border-left-color:#0d6efd; }
.badge-freq { font-size:.7rem; }
.stock-badge { font-size:.78rem; min-width:54px; text-align:center; }
th { white-space:nowrap; }
.category-header { background:#e9ecef; font-weight:600; font-size:.8rem; text-transform:uppercase; letter-spacing:.05em; color:#6c757d; }
</style>
</head>
<body>

<!-- NAV -->
<div class="nav-top px-3 py-2 d-flex align-items-center gap-2 flex-wrap">
  <span class="text-white fw-bold me-3" style="font-size:1rem;">🍽 Müdavim</span>
  <a href="dashboard.php"  class="btn-nav">Kasa</a>
  <a href="stok.php"       class="btn-nav">Stok</a>
  <a href="urunler.php"    class="btn-nav active">Ürünler</a>
  <a href="tedarikci.php"  class="btn-nav">Tedarikçi</a>
  <a href="alis.php"       class="btn-nav">Alış</a>
  <a href="sayim.php"      class="btn-nav">Sayım</a>
  <a href="rapor.php"      class="btn-nav">Rapor</a>
  <?php if ($role === 'patron'): ?>
  <a href="demirbaslar.php" class="btn-nav">Demirbaş</a>
  <?php endif; ?>
  <div class="ms-auto d-flex gap-2">
    <a href="logout.php" class="btn-nav">Çıkış</a>
  </div>
</div>

<!-- HEADER -->
<div class="container-fluid px-4 pt-4 pb-2">
  <div class="d-flex align-items-center justify-content-between flex-wrap gap-2">
    <div>
      <h4 class="mb-0 fw-bold">Ürün Yönetimi</h4>
      <small class="text-muted" id="statsLine">Yükleniyor…</small>
    </div>
    <div class="d-flex gap-2">
      <input type="search" id="searchInput" class="form-control form-control-sm" placeholder="Ürün ara…" style="width:200px">
      <select id="catFilter" class="form-select form-select-sm" style="width:160px">
        <option value="">Tüm kategoriler</option>
      </select>
      <?php if ($role === 'patron'): ?>
      <button class="btn btn-primary btn-sm" onclick="openAdd()">+ Yeni Ürün</button>
      <?php endif; ?>
    </div>
  </div>
</div>

<!-- TABLE -->
<div class="container-fluid px-4 pb-5">
  <div class="card shadow-sm border-0">
    <div class="card-body p-0">
      <table class="table table-hover align-middle mb-0" id="productTable">
        <thead class="table-light">
          <tr>
            <th class="ps-3">Ürün</th>
            <th>SKU</th>
            <th>Kategori</th>
            <th>Birim</th>
            <th class="text-end">Stok</th>
            <th class="text-center">Min</th>
            <th class="text-center">Sayım</th>
            <th class="text-center">Takip</th>
            <?php if ($role === 'patron'): ?>
            <th class="text-center pe-3">İşlem</th>
            <?php endif; ?>
          </tr>
        </thead>
        <tbody id="productTbody">
          <tr><td colspan="9" class="text-center text-muted py-4">Yükleniyor…</td></tr>
        </tbody>
      </table>
    </div>
  </div>
</div>

<!-- MODAL: Ekle / Düzenle -->
<div class="modal fade" id="modalProduct" tabindex="-1">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="modalProductTitle">Yeni Ürün</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">
        <input type="hidden" id="pId">
        <div class="row g-3">
          <div class="col-md-8">
            <label class="form-label">Ürün Adı <span class="text-danger">*</span></label>
            <input type="text" id="pName" class="form-control" placeholder="Çipura, Zeytinyağı…">
          </div>
          <div class="col-md-4">
            <label class="form-label">SKU / Kod</label>
            <input type="text" id="pSku" class="form-control" placeholder="Otomatik">
          </div>
          <div class="col-md-6">
            <label class="form-label">Kategori <span class="text-danger">*</span></label>
            <select id="pCat" class="form-select">
              <option value="">— Seçin —</option>
            </select>
          </div>
          <div class="col-md-6">
            <label class="form-label">Birim <span class="text-danger">*</span></label>
            <select id="pUnit" class="form-select">
              <option value="">— Seçin —</option>
            </select>
          </div>
          <div class="col-md-4">
            <label class="form-label">Min. Stok</label>
            <input type="number" id="pMin" class="form-control" value="0" min="0" step="0.1">
          </div>
          <div class="col-md-4">
            <label class="form-label">Sayım Sıklığı</label>
            <select id="pFreq" class="form-select">
              <option value="daily">Günlük</option>
              <option value="weekly" selected>Haftalık</option>
              <option value="monthly">Aylık</option>
              <option value="never">Sayılmaz</option>
            </select>
          </div>
          <div class="col-md-4 d-flex align-items-end">
            <div class="form-check form-switch mb-1">
              <input class="form-check-input" type="checkbox" id="pTracked" checked>
              <label class="form-check-label" for="pTracked">Stok Takibi</label>
            </div>
          </div>
        </div>
      </div>
      <div class="modal-footer">
        <button class="btn btn-secondary" data-bs-dismiss="modal">İptal</button>
        <button class="btn btn-primary" id="btnSaveProduct" onclick="saveProduct()">Kaydet</button>
      </div>
    </div>
  </div>
</div>

<!-- MODAL: Sil Onay -->
<div class="modal fade" id="modalDelete" tabindex="-1">
  <div class="modal-dialog modal-sm">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title text-danger">Ürünü Sil</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">
        <p><strong id="deleteProductName"></strong> silinecek. Stok hareketleri etkilenmez.</p>
      </div>
      <div class="modal-footer">
        <button class="btn btn-secondary btn-sm" data-bs-dismiss="modal">İptal</button>
        <button class="btn btn-danger btn-sm" onclick="confirmDelete()">Sil</button>
      </div>
    </div>
  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
const API = 'api/index.php';
const isPatron = <?= $role === 'patron' ? 'true' : 'false' ?>;

let allProducts = [];
let categories  = [];
let units       = [];
let deleteId    = null;

const modalProduct = new bootstrap.Modal(document.getElementById('modalProduct'));
const modalDelete  = new bootstrap.Modal(document.getElementById('modalDelete'));

async function api(endpoint, method = 'GET', body = null) {
  const opts = { method, headers: { 'Content-Type': 'application/json' } };
  if (body) opts.body = JSON.stringify(body);
  const r = await fetch(`${API}/${endpoint}`, opts);
  return r.json();
}

async function init() {
  const [pRes, cRes, uRes] = await Promise.all([
    api('products/list'),
    api('categories/list'),
    api('units/list'),
  ]);
  allProducts = pRes.data ?? [];
  categories  = cRes.data ?? [];
  units       = uRes.data ?? [];

  // Kategori filtresi
  const catFilter = document.getElementById('catFilter');
  categories.forEach(c => {
    catFilter.insertAdjacentHTML('beforeend', `<option value="${c.id}">${c.name}</option>`);
  });

  // Modal dropdown'ları doldur
  const pCat  = document.getElementById('pCat');
  const pUnit = document.getElementById('pUnit');
  categories.forEach(c => pCat.insertAdjacentHTML('beforeend',  `<option value="${c.id}">${c.name}</option>`));
  units.forEach(u      => pUnit.insertAdjacentHTML('beforeend', `<option value="${u.id}">${u.name} (${u.abbreviation})</option>`));

  renderTable();
  updateStats();
}

function freqLabel(f) {
  return { daily: 'Günlük', weekly: 'Haftalık', monthly: 'Aylık', never: 'Sayılmaz' }[f] ?? f;
}

function stockBadge(qty, min) {
  if (qty <= 0)              return `<span class="badge bg-danger stock-badge">${qty}</span>`;
  if (min > 0 && qty < min) return `<span class="badge bg-warning text-dark stock-badge">${qty}</span>`;
  return                            `<span class="badge bg-success stock-badge">${qty}</span>`;
}

function renderTable() {
  const q   = document.getElementById('searchInput').value.toLowerCase();
  const cat = document.getElementById('catFilter').value;

  let filtered = allProducts.filter(p => {
    if (cat && String(p.category_id) !== cat) return false;
    if (q && !p.name.toLowerCase().includes(q) && !(p.sku ?? '').toLowerCase().includes(q)) return false;
    return true;
  });

  if (!filtered.length) {
    document.getElementById('productTbody').innerHTML =
      `<tr><td colspan="9" class="text-center text-muted py-4">Ürün bulunamadı</td></tr>`;
    return;
  }

  // Kategoriye göre grupla
  const groups = {};
  filtered.forEach(p => {
    const k = p.category_name;
    if (!groups[k]) groups[k] = [];
    groups[k].push(p);
  });

  let html = '';
  Object.entries(groups).sort(([a],[b]) => a.localeCompare(b, 'tr')).forEach(([cat, prods]) => {
    html += `<tr class="category-header"><td colspan="9" class="ps-3 py-2">${cat}</td></tr>`;
    prods.forEach(p => {
      const qty = parseFloat(p.current_stock ?? 0);
      html += `<tr>
        <td class="ps-3 fw-semibold">${p.name}</td>
        <td><code class="text-muted small">${p.sku ?? '—'}</code></td>
        <td><span class="badge bg-light text-dark border">${p.category_name}</span></td>
        <td>${p.stock_unit}</td>
        <td class="text-end">${stockBadge(qty, parseFloat(p.min_stock_qty ?? 0))}</td>
        <td class="text-center text-muted">${p.min_stock_qty > 0 ? p.min_stock_qty : '—'}</td>
        <td class="text-center"><span class="badge bg-secondary badge-freq">${freqLabel(p.count_frequency)}</span></td>
        <td class="text-center">${p.is_tracked ? '<span class="text-success">✔</span>' : '<span class="text-muted">—</span>'}</td>
        ${isPatron ? `<td class="text-center pe-3">
          <button class="btn btn-outline-primary btn-sm me-1" onclick='openEdit(${JSON.stringify(p)})'>Düzenle</button>
          <button class="btn btn-outline-danger btn-sm" onclick="openDelete(${p.id}, '${p.name.replace(/'/g,"\\'")}')">Sil</button>
        </td>` : ''}
      </tr>`;
    });
  });

  document.getElementById('productTbody').innerHTML = html;
}

function updateStats() {
  const total   = allProducts.length;
  const zero    = allProducts.filter(p => parseFloat(p.current_stock) <= 0).length;
  const tracked = allProducts.filter(p => p.is_tracked).length;
  document.getElementById('statsLine').textContent =
    `${total} ürün · ${tracked} takipli · ${zero} stoksuz`;
}

function openAdd() {
  document.getElementById('modalProductTitle').textContent = 'Yeni Ürün';
  document.getElementById('pId').value    = '';
  document.getElementById('pName').value  = '';
  document.getElementById('pSku').value   = '';
  document.getElementById('pCat').value   = '';
  document.getElementById('pUnit').value  = '';
  document.getElementById('pMin').value   = '0';
  document.getElementById('pFreq').value  = 'weekly';
  document.getElementById('pTracked').checked = true;
  modalProduct.show();
}

function openEdit(p) {
  document.getElementById('modalProductTitle').textContent = 'Ürünü Düzenle';
  document.getElementById('pId').value    = p.id;
  document.getElementById('pName').value  = p.name;
  document.getElementById('pSku').value   = p.sku ?? '';
  document.getElementById('pCat').value   = p.category_id;
  document.getElementById('pUnit').value  = p.stock_unit_id;
  document.getElementById('pMin').value   = p.min_stock_qty ?? 0;
  document.getElementById('pFreq').value  = p.count_frequency ?? 'weekly';
  document.getElementById('pTracked').checked = !!parseInt(p.is_tracked);
  modalProduct.show();
}

async function saveProduct() {
  const id   = document.getElementById('pId').value;
  const name = document.getElementById('pName').value.trim();
  const cat  = document.getElementById('pCat').value;
  const unit = document.getElementById('pUnit').value;

  if (!name) { alert('Ürün adı zorunlu'); return; }
  if (!cat)  { alert('Kategori seçin'); return; }
  if (!unit) { alert('Birim seçin'); return; }

  const body = {
    name,
    sku:             document.getElementById('pSku').value.trim() || null,
    category_id:     parseInt(cat),
    unit_id:         parseInt(unit),
    min_stock_qty:   parseFloat(document.getElementById('pMin').value) || 0,
    count_frequency: document.getElementById('pFreq').value,
    is_tracked:      document.getElementById('pTracked').checked ? 1 : 0,
  };

  const btn = document.getElementById('btnSaveProduct');
  btn.disabled = true;

  try {
    let res;
    if (id) {
      res = await api(`products/update/${id}`, 'PUT', body);
    } else {
      res = await api('products/create', 'POST', body);
    }

    if (res.status === 'ok') {
      modalProduct.hide();
      await refreshProducts();
    } else {
      alert(res.data?.message ?? 'Hata oluştu');
    }
  } finally {
    btn.disabled = false;
  }
}

function openDelete(id, name) {
  deleteId = id;
  document.getElementById('deleteProductName').textContent = name;
  modalDelete.show();
}

async function confirmDelete() {
  if (!deleteId) return;
  const res = await api(`products/delete/${deleteId}`, 'DELETE');
  if (res.status === 'ok') {
    modalDelete.hide();
    await refreshProducts();
  } else {
    alert(res.data?.message ?? 'Silinemedi');
  }
}

async function refreshProducts() {
  const res = await api('products/list');
  allProducts = res.data ?? [];
  renderTable();
  updateStats();
}

document.getElementById('searchInput').addEventListener('input', renderTable);
document.getElementById('catFilter').addEventListener('change', renderTable);

init();
</script>
</body>
</html>
