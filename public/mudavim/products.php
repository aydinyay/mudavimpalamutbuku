<?php
declare(strict_types=1);
require_once __DIR__ . '/autoload.php';
use Mudavim\Core\{Auth, Database};
Auth::requireLogin('login.php');
$user = Auth::user();

$pdo = Database::get();
$categories = $pdo->query("SELECT id, name FROM categories WHERE name NOT LIKE 'Demirbaş%' ORDER BY sort_order, name")->fetchAll();
$units      = $pdo->query("SELECT id, name, abbreviation FROM units ORDER BY id")->fetchAll();
?>
<!DOCTYPE html>
<html lang="tr" data-bs-theme="light">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no">
<title>Ürünler — Müdavim</title>
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
<link rel="stylesheet" href="assets/style.css">
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
        <i class="bi bi-box-seam me-1"></i>Ürünler
      </span>
    </div>
    <button class="btn btn-sm btn-success fw-semibold" id="btnNewProduct">
      <i class="bi bi-plus-lg me-1"></i>Yeni Ürün
    </button>
  </div>
</nav>

<div class="container-fluid px-2 px-sm-3 py-3" style="max-width:900px;margin:0 auto">

  <!-- ARAMA + FİLTRE -->
  <div class="d-flex gap-2 mb-3">
    <div class="input-group">
      <span class="input-group-text bg-white"><i class="bi bi-search text-muted"></i></span>
      <input type="search" class="form-control" id="searchInput" placeholder="Ürün ara…">
    </div>
    <select class="form-select" id="filterCategory" style="max-width:160px">
      <option value="">Tüm Kategoriler</option>
      <?php foreach ($categories as $cat): ?>
      <option value="<?= $cat['id'] ?>"><?= htmlspecialchars($cat['name']) ?></option>
      <?php endforeach; ?>
    </select>
  </div>

  <!-- ÜRÜN LİSTESİ -->
  <div id="productList">
    <div class="text-center py-5 text-muted">
      <div class="spinner-border spinner-border-sm me-2"></div>Yükleniyor…
    </div>
  </div>

</div>

<!-- EKLE / DÜZENLE MODAL -->
<div class="modal fade" id="modalProduct" tabindex="-1" data-bs-backdrop="static">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title fw-bold" id="modalProductTitle">Yeni Ürün</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">
        <input type="hidden" id="prd_id">

        <div class="mb-3">
          <label class="form-label fw-semibold">Ürün Adı <span class="text-danger">*</span></label>
          <input type="text" class="form-control" id="prd_name" placeholder="Levrek, Domates…" autocomplete="off">
        </div>

        <div class="row g-2 mb-3">
          <div class="col-7">
            <label class="form-label fw-semibold">Kategori <span class="text-danger">*</span></label>
            <select class="form-select" id="prd_category">
              <option value="">— Seç —</option>
              <?php foreach ($categories as $cat): ?>
              <option value="<?= $cat['id'] ?>"><?= htmlspecialchars($cat['name']) ?></option>
              <?php endforeach; ?>
            </select>
          </div>
          <div class="col-5">
            <label class="form-label fw-semibold">Birim <span class="text-danger">*</span></label>
            <select class="form-select" id="prd_unit">
              <option value="">— Seç —</option>
              <?php foreach ($units as $u): ?>
              <option value="<?= $u['id'] ?>"><?= htmlspecialchars($u['name']) ?> (<?= htmlspecialchars($u['abbreviation']) ?>)</option>
              <?php endforeach; ?>
            </select>
          </div>
        </div>

        <div class="row g-2 mb-3">
          <div class="col-6">
            <label class="form-label fw-semibold">SKU / Kod <small class="text-muted">(isteğe bağlı)</small></label>
            <input type="text" class="form-control" id="prd_sku" placeholder="ATI-001">
          </div>
          <div class="col-6">
            <label class="form-label fw-semibold">Min. Stok</label>
            <input type="number" class="form-control" id="prd_min_stock" min="0" step="0.1" value="0">
          </div>
        </div>

        <div class="mb-3">
          <label class="form-label fw-semibold">Sayım Sıklığı</label>
          <select class="form-select" id="prd_freq">
            <option value="daily">Her Gün</option>
            <option value="weekly" selected>Her Hafta</option>
            <option value="monthly">Her Ay</option>
            <option value="never">Sayılmaz</option>
          </select>
        </div>

        <div class="form-check form-switch">
          <input class="form-check-input" type="checkbox" id="prd_tracked" checked>
          <label class="form-check-label" for="prd_tracked">Stok takibi aktif</label>
        </div>
      </div>
      <div class="modal-footer flex-column gap-2">
        <div class="d-flex w-100 gap-2">
          <button type="button" class="btn btn-outline-secondary flex-fill" data-bs-dismiss="modal">Vazgeç</button>
          <button type="button" class="btn btn-primary flex-fill fw-bold" id="btnProductSave">
            <i class="bi bi-check-lg me-1"></i>Kaydet
          </button>
        </div>
        <button type="button" class="btn btn-outline-danger w-100 d-none" id="btnProductDelete">
          <i class="bi bi-trash me-1"></i>Ürünü Sil
        </button>
      </div>
    </div>
  </div>
</div>

<!-- SİLME ONAY MODAL -->
<div class="modal fade" id="modalConfirmDelete" tabindex="-1">
  <div class="modal-dialog modal-dialog-centered modal-sm">
    <div class="modal-content">
      <div class="modal-body text-center py-4">
        <i class="bi bi-exclamation-triangle-fill text-danger fs-1 d-block mb-3"></i>
        <p class="fw-semibold mb-1" id="deleteConfirmName"></p>
        <p class="text-muted small">Bu ürünü silmek istediğinize emin misiniz?</p>
      </div>
      <div class="modal-footer gap-2">
        <button class="btn btn-outline-secondary flex-fill" data-bs-dismiss="modal">Vazgeç</button>
        <button class="btn btn-danger flex-fill fw-bold" id="btnConfirmDeleteYes">Sil</button>
      </div>
    </div>
  </div>
</div>

<!-- TOAST -->
<div class="position-fixed bottom-0 end-0 p-3" style="z-index:1100" id="toastContainer"></div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
/* ── HELPERS ── */
const API_BASE = 'api';
async function apiFetch(method, path, body) {
  const opts = { method, headers: { 'Content-Type': 'application/json' }, credentials: 'same-origin' };
  if (body) opts.body = JSON.stringify(body);
  const res  = await fetch(`${API_BASE}/${path}`, opts);
  const json = await res.json().catch(() => ({ status: 'error', data: { message: 'Sunucu yanıtı okunamadı' } }));
  if (!res.ok || json.status === 'error') throw new Error(json?.data?.message || `HTTP ${res.status}`);
  return json.data;
}

function toast(msg, type = 'success') {
  const id = `t${Date.now()}`;
  const icons = { success: 'bi-check-circle-fill text-success', danger: 'bi-x-circle-fill text-danger', warning: 'bi-exclamation-triangle-fill text-warning' };
  document.getElementById('toastContainer').insertAdjacentHTML('beforeend', `
    <div id="${id}" class="toast align-items-center border-0 shadow-sm" role="alert">
      <div class="d-flex">
        <div class="toast-body fw-semibold"><i class="bi ${icons[type]||icons.success} me-2"></i>${msg}</div>
        <button type="button" class="btn-close me-2 m-auto" data-bs-dismiss="toast"></button>
      </div>
    </div>`);
  const el = document.getElementById(id);
  new bootstrap.Toast(el, { delay: 3500 }).show();
  el.addEventListener('hidden.bs.toast', () => el.remove());
}

function escHtml(s) {
  return String(s ?? '').replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;');
}

/* ── DATA ── */
let allProducts = [];

const CAT_COLORS = ['primary','success','danger','warning','info','secondary','dark'];
const catColorMap = {};
let colorIdx = 0;
function catColor(catId) {
  if (!catColorMap[catId]) { catColorMap[catId] = CAT_COLORS[colorIdx++ % CAT_COLORS.length]; }
  return catColorMap[catId];
}

const FREQ_LABEL = { daily: 'Günlük', weekly: 'Haftalık', monthly: 'Aylık', never: 'Sayılmaz' };

async function loadProducts() {
  try {
    allProducts = await apiFetch('GET', 'products/list');
    renderList();
  } catch (e) {
    document.getElementById('productList').innerHTML =
      `<div class="alert alert-danger"><i class="bi bi-x-circle me-2"></i>${escHtml(e.message)}</div>`;
  }
}

function renderList() {
  const q       = document.getElementById('searchInput').value.trim().toLowerCase();
  const catFilt = document.getElementById('filterCategory').value;

  const filtered = allProducts.filter(p => {
    const matchQ   = !q || p.name.toLowerCase().includes(q) || (p.sku||'').toLowerCase().includes(q);
    const matchCat = !catFilt || String(p.category_id) === catFilt;
    return matchQ && matchCat;
  });

  if (!filtered.length) {
    document.getElementById('productList').innerHTML =
      `<div class="text-center py-5 text-muted"><i class="bi bi-search fs-1 d-block mb-2"></i>Ürün bulunamadı.</div>`;
    return;
  }

  /* Kategoriye göre grupla */
  const groups = {};
  filtered.forEach(p => {
    if (!groups[p.category_name]) groups[p.category_name] = [];
    groups[p.category_name].push(p);
  });

  let html = '';
  for (const [catName, products] of Object.entries(groups)) {
    html += `<h6 class="text-muted fw-semibold text-uppercase small mt-3 mb-2 px-1"
               style="letter-spacing:.07em">${escHtml(catName)}</h6>
             <div class="list-group mb-2 shadow-sm">`;
    products.forEach(p => {
      const stock = parseFloat(p.current_stock);
      const stockBadge = p.is_tracked
        ? `<span class="badge ${stock <= 0 ? 'bg-danger' : 'bg-success bg-opacity-10 text-success border border-success'} ms-1">
             ${stock.toLocaleString('tr-TR',{maximumFractionDigits:2})} ${escHtml(p.stock_unit)}
           </span>`
        : `<span class="badge bg-secondary bg-opacity-10 text-secondary ms-1">takipsiz</span>`;
      html += `
        <button type="button" class="list-group-item list-group-item-action d-flex justify-content-between align-items-center py-3"
                onclick="openEdit(${p.id})">
          <div>
            <div class="fw-semibold">${escHtml(p.name)}</div>
            <div class="mt-1">
              ${p.sku ? `<span class="badge bg-light text-dark border me-1" style="font-size:.65rem">${escHtml(p.sku)}</span>` : ''}
              <span class="badge text-bg-${catColor(p.category_id)} bg-opacity-75" style="font-size:.65rem">${escHtml(p.category_name)}</span>
              ${stockBadge}
            </div>
          </div>
          <i class="bi bi-pencil-square text-muted ms-3 flex-shrink-0"></i>
        </button>`;
    });
    html += `</div>`;
  }

  document.getElementById('productList').innerHTML = html;
}

/* ── MODAL ── */
const modalEl  = document.getElementById('modalProduct');
const modal    = new bootstrap.Modal(modalEl);
const confirmDeleteModal = new bootstrap.Modal(document.getElementById('modalConfirmDelete'));

let deleteTargetId   = null;
let deleteTargetName = '';

function openNew() {
  document.getElementById('modalProductTitle').textContent = 'Yeni Ürün';
  document.getElementById('prd_id').value       = '';
  document.getElementById('prd_name').value     = '';
  document.getElementById('prd_category').value = '';
  document.getElementById('prd_unit').value     = '';
  document.getElementById('prd_sku').value      = '';
  document.getElementById('prd_min_stock').value= '0';
  document.getElementById('prd_freq').value     = 'weekly';
  document.getElementById('prd_tracked').checked= true;
  document.getElementById('btnProductDelete').classList.add('d-none');
  modal.show();
  setTimeout(() => document.getElementById('prd_name').focus(), 300);
}

function openEdit(id) {
  const p = allProducts.find(x => x.id === id);
  if (!p) return;
  document.getElementById('modalProductTitle').textContent = 'Ürünü Düzenle';
  document.getElementById('prd_id').value        = p.id;
  document.getElementById('prd_name').value      = p.name;
  document.getElementById('prd_category').value  = p.category_id;
  document.getElementById('prd_unit').value      = p.stock_unit_id;
  document.getElementById('prd_sku').value       = p.sku || '';
  document.getElementById('prd_min_stock').value = p.min_stock_qty;
  document.getElementById('prd_freq').value      = p.count_frequency;
  document.getElementById('prd_tracked').checked = !!p.is_tracked;
  document.getElementById('btnProductDelete').classList.remove('d-none');
  modal.show();
}

document.getElementById('btnNewProduct').addEventListener('click', openNew);

document.getElementById('btnProductSave').addEventListener('click', async () => {
  const id       = document.getElementById('prd_id').value;
  const name     = document.getElementById('prd_name').value.trim();
  const catId    = document.getElementById('prd_category').value;
  const unitId   = document.getElementById('prd_unit').value;

  if (!name)   { toast('Ürün adı zorunlu', 'warning'); return; }
  if (!catId)  { toast('Kategori seçin', 'warning'); return; }
  if (!unitId) { toast('Birim seçin', 'warning'); return; }

  const payload = {
    name:            name,
    category_id:     catId,
    unit_id:         unitId,
    sku:             document.getElementById('prd_sku').value.trim() || null,
    min_stock_qty:   parseFloat(document.getElementById('prd_min_stock').value) || 0,
    count_frequency: document.getElementById('prd_freq').value,
    is_tracked:      document.getElementById('prd_tracked').checked ? 1 : 0,
  };

  const btn = document.getElementById('btnProductSave');
  btn.disabled = true;
  btn.innerHTML = '<span class="spinner-border spinner-border-sm"></span>';

  try {
    if (id) {
      await apiFetch('PUT', `products/update/${id}`, payload);
      toast('Ürün güncellendi');
    } else {
      await apiFetch('POST', 'products/create', payload);
      toast('Ürün eklendi');
    }
    modal.hide();
    await loadProducts();
  } catch (e) {
    toast(e.message, 'danger');
  } finally {
    btn.disabled = false;
    btn.innerHTML = '<i class="bi bi-check-lg me-1"></i>Kaydet';
  }
});

document.getElementById('btnProductDelete').addEventListener('click', () => {
  const id   = document.getElementById('prd_id').value;
  const name = document.getElementById('prd_name').value;
  deleteTargetId   = id;
  deleteTargetName = name;
  document.getElementById('deleteConfirmName').textContent = `"${name}"`;
  modal.hide();
  setTimeout(() => confirmDeleteModal.show(), 300);
});

document.getElementById('btnConfirmDeleteYes').addEventListener('click', async () => {
  const btn = document.getElementById('btnConfirmDeleteYes');
  btn.disabled = true;
  try {
    await apiFetch('DELETE', `products/delete/${deleteTargetId}`);
    toast(`"${deleteTargetName}" silindi`);
    confirmDeleteModal.hide();
    await loadProducts();
  } catch (e) {
    toast(e.message, 'danger');
  } finally {
    btn.disabled = false;
  }
});

/* ── FİLTRE ── */
document.getElementById('searchInput').addEventListener('input', renderList);
document.getElementById('filterCategory').addEventListener('change', renderList);

/* ── BAŞLAT ── */
loadProducts();
</script>
</body>
</html>
