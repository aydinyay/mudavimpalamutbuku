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
<title>Tedarikçiler — Müdavim</title>
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
<link rel="stylesheet" href="assets/style.css">
<style>
.supplier-card { border-radius:10px; border:1px solid #e2e8f0; background:#fff; transition:box-shadow .15s; }
.supplier-card:hover { box-shadow:0 2px 10px rgba(0,0,0,.08); }
.balance-pos { color:#dc3545; font-weight:700; }  /* biz borçluyuz */
.balance-neg { color:#198754; font-weight:700; }  /* bize borçlar  */
.balance-zer { color:#6c757d; }
.txn-borc  { border-left:3px solid #dc3545; }
.txn-odeme { border-left:3px solid #198754; }
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
        <i class="bi bi-truck me-1"></i>Tedarikçiler
      </span>
    </div>
    <button class="btn btn-sm btn-success" id="btnAddSupplier">
      <i class="bi bi-plus-lg me-1"></i>Ekle
    </button>
  </div>
</nav>

<div class="container-fluid px-2 px-sm-3 py-3" style="max-width:860px;margin:0 auto">

  <!-- ÖZET -->
  <div class="row g-2 mb-3">
    <div class="col-4">
      <div class="text-center p-2 rounded border bg-white shadow-sm">
        <div class="fw-bold fs-5 text-danger" id="sumDebt">—</div>
        <div class="text-muted" style="font-size:.68rem;text-transform:uppercase;letter-spacing:.06em">Toplam Borç</div>
      </div>
    </div>
    <div class="col-4">
      <div class="text-center p-2 rounded border bg-white shadow-sm">
        <div class="fw-bold fs-5 text-success" id="sumCredit">—</div>
        <div class="text-muted" style="font-size:.68rem;text-transform:uppercase;letter-spacing:.06em">Alacak</div>
      </div>
    </div>
    <div class="col-4">
      <div class="text-center p-2 rounded border bg-white shadow-sm">
        <div class="fw-bold fs-5" id="sumCount">—</div>
        <div class="text-muted" style="font-size:.68rem;text-transform:uppercase;letter-spacing:.06em">Tedarikçi</div>
      </div>
    </div>
  </div>

  <!-- ARAMA -->
  <input type="search" class="form-control form-control-sm mb-3" id="searchInput" placeholder="Tedarikçi ara…">

  <div id="supplierList">
    <div class="text-center py-5 text-muted">
      <div class="spinner-border spinner-border-sm me-2"></div>Yükleniyor…
    </div>
  </div>
</div>

<!-- ── ADD / EDIT MODAL ─────────────────────────────── -->
<div class="modal fade" id="supplierModal" tabindex="-1">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="supplierModalTitle">Yeni Tedarikçi</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">
        <input type="hidden" id="supplierId">
        <div class="mb-3">
          <label class="form-label fw-semibold">Firma Adı <span class="text-danger">*</span></label>
          <input type="text" class="form-control" id="sName" placeholder="Balıkçı Mehmet">
        </div>
        <div class="row g-2">
          <div class="col-6">
            <label class="form-label">Yetkili</label>
            <input type="text" class="form-control" id="sContact" placeholder="Ad Soyad">
          </div>
          <div class="col-6">
            <label class="form-label">Telefon</label>
            <input type="tel" class="form-control" id="sPhone" placeholder="0532…">
          </div>
        </div>
        <div class="row g-2 mt-1">
          <div class="col-6">
            <label class="form-label">Vergi No</label>
            <input type="text" class="form-control" id="sTaxNo" placeholder="1234567890">
          </div>
          <div class="col-6">
            <label class="form-label">Vade (gün)</label>
            <input type="number" class="form-control" id="sTerms" value="0" min="0" max="180">
          </div>
        </div>
        <div class="mt-2">
          <label class="form-label">Notlar</label>
          <textarea class="form-control" id="sNotes" rows="2"></textarea>
        </div>
        <div class="form-check form-switch mt-2" id="sActiveWrap">
          <input class="form-check-input" type="checkbox" id="sActive" checked>
          <label class="form-check-label" for="sActive">Aktif</label>
        </div>
        <div class="text-danger small mt-2 d-none" id="supplierErr"></div>
      </div>
      <div class="modal-footer">
        <button class="btn btn-secondary" data-bs-dismiss="modal">İptal</button>
        <button class="btn btn-primary" id="btnSaveSupplier">Kaydet</button>
      </div>
    </div>
  </div>
</div>

<!-- ── CARİ MODAL ────────────────────────────────────── -->
<div class="modal fade" id="cariModal" tabindex="-1">
  <div class="modal-dialog modal-dialog-centered modal-lg">
    <div class="modal-content">
      <div class="modal-header">
        <div>
          <h5 class="modal-title" id="cariModalTitle">Cari Hesap</h5>
          <div class="small text-muted">
            Bakiye: <span id="cariBalance" class="fw-bold">—</span>
            <span class="ms-1 text-muted" style="font-size:.75rem" id="cariBalanceNote"></span>
          </div>
        </div>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body p-2">
        <input type="hidden" id="cariSupplierId">

        <!-- YENİ İŞLEM -->
        <div class="card mb-3">
          <div class="card-body p-3">
            <div class="d-flex gap-2 flex-wrap">
              <div class="btn-group btn-group-sm" id="cariTypeGroup">
                <button class="btn btn-outline-danger active" data-type="borc">
                  <i class="bi bi-arrow-up-circle me-1"></i>Borç Ekle
                </button>
                <button class="btn btn-outline-success" data-type="odeme">
                  <i class="bi bi-arrow-down-circle me-1"></i>Ödeme Yap
                </button>
              </div>
            </div>
            <div class="row g-2 mt-2">
              <div class="col-sm-4">
                <input type="number" class="form-control form-control-sm" id="cariAmount"
                       placeholder="Tutar (₺)" min="0.01" step="0.01">
              </div>
              <div class="col-sm-4">
                <input type="date" class="form-control form-control-sm" id="cariDate">
              </div>
              <div class="col-sm-4">
                <input type="text" class="form-control form-control-sm" id="cariDesc" placeholder="Açıklama">
              </div>
            </div>
            <div class="text-danger small mt-1 d-none" id="cariErr"></div>
            <button class="btn btn-primary btn-sm mt-2 w-100" id="btnSaveCari">Kaydet</button>
          </div>
        </div>

        <!-- İŞLEM LİSTESİ -->
        <div id="cariTxnList">
          <div class="text-center py-4 text-muted">
            <div class="spinner-border spinner-border-sm"></div>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>

<!-- ── SİL MODAL ─────────────────────────────────────── -->
<div class="modal fade" id="deleteModal" tabindex="-1">
  <div class="modal-dialog modal-dialog-centered modal-sm">
    <div class="modal-content">
      <div class="modal-body text-center py-4">
        <i class="bi bi-exclamation-triangle-fill text-danger fs-1 d-block mb-2"></i>
        <p class="mb-0"><strong id="deleteModalName"></strong> silinsin mi?</p>
        <p class="small text-muted">Bu işlem geri alınamaz.</p>
      </div>
      <div class="modal-footer justify-content-center">
        <button class="btn btn-secondary" data-bs-dismiss="modal">İptal</button>
        <button class="btn btn-danger" id="btnConfirmDelete">Sil</button>
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
  const json = await res.json().catch(() => ({ status: 'error', data: { message: 'Hata' } }));
  if (!res.ok || json.status === 'error') throw new Error(json?.data?.message || `HTTP ${res.status}`);
  return json.data;
}

function toast(msg, type = 'danger') {
  const id = `t${Date.now()}`;
  document.getElementById('toastContainer').insertAdjacentHTML('beforeend', `
    <div id="${id}" class="toast align-items-center border-0 shadow-sm" role="alert">
      <div class="d-flex">
        <div class="toast-body fw-semibold">
          <i class="bi bi-${type === 'danger' ? 'x-circle-fill text-danger' : 'check-circle-fill text-success'} me-2"></i>${msg}
        </div>
        <button type="button" class="btn-close me-2 m-auto" data-bs-dismiss="toast"></button>
      </div>
    </div>`);
  const el = document.getElementById(id);
  new bootstrap.Toast(el, { delay: 3000 }).show();
  el.addEventListener('hidden.bs.toast', () => el.remove());
}

function esc(s) { return String(s ?? '').replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;'); }
function fmtMoney(n) { return '₺' + parseFloat(n||0).toLocaleString('tr-TR',{minimumFractionDigits:2,maximumFractionDigits:2}); }
function fmtDate(d) { return d ? new Date(d+'T00:00:00').toLocaleDateString('tr-TR',{day:'2-digit',month:'short',year:'numeric'}) : '—'; }

/* ── DATA ── */
let allSuppliers = [];

async function loadSuppliers() {
  try {
    allSuppliers = await apiFetch('supplier/list');
    renderSummary();
    renderList();
  } catch(e) {
    toast(e.message);
  }
}

function renderSummary() {
  let debt = 0, credit = 0;
  allSuppliers.forEach(s => {
    const b = parseFloat(s.balance || 0);
    if (b > 0) debt   += b;
    if (b < 0) credit += Math.abs(b);
  });
  document.getElementById('sumDebt').textContent   = fmtMoney(debt);
  document.getElementById('sumCredit').textContent  = fmtMoney(credit);
  document.getElementById('sumCount').textContent   = allSuppliers.length;
}

function renderList() {
  const q = document.getElementById('searchInput').value.trim().toLowerCase();
  const filtered = allSuppliers.filter(s =>
    !q || s.name.toLowerCase().includes(q) || (s.contact_name || '').toLowerCase().includes(q)
  );

  if (!filtered.length) {
    document.getElementById('supplierList').innerHTML =
      '<div class="text-center py-5 text-muted"><i class="bi bi-search fs-1 d-block mb-2"></i>Tedarikçi bulunamadı.</div>';
    return;
  }

  let html = '<div class="row g-2">';
  filtered.forEach(s => {
    const bal     = parseFloat(s.balance || 0);
    const balCls  = bal > 0 ? 'balance-pos' : bal < 0 ? 'balance-neg' : 'balance-zer';
    const balNote = bal > 0 ? '(Borcumuz var)' : bal < 0 ? '(Alacaklıyız)' : '';
    html += `
      <div class="col-12 col-sm-6">
        <div class="supplier-card p-3">
          <div class="d-flex justify-content-between align-items-start">
            <div class="flex-grow-1 min-width-0 me-2">
              <div class="fw-bold">${esc(s.name)}</div>
              ${s.contact_name ? `<div class="text-muted small"><i class="bi bi-person me-1"></i>${esc(s.contact_name)}</div>` : ''}
              ${s.phone ? `<div class="text-muted small"><i class="bi bi-telephone me-1"></i>${esc(s.phone)}</div>` : ''}
              ${s.payment_terms > 0 ? `<div class="text-muted small"><i class="bi bi-calendar me-1"></i>${s.payment_terms} gün vade</div>` : ''}
            </div>
            <div class="text-end flex-shrink-0">
              <div class="${balCls}">${fmtMoney(Math.abs(bal))}</div>
              <div class="text-muted" style="font-size:.7rem">${balNote}</div>
            </div>
          </div>
          <div class="d-flex gap-2 mt-2">
            <button class="btn btn-sm btn-outline-primary flex-grow-1" onclick="openCari(${s.id}, '${esc(s.name)}')">
              <i class="bi bi-journal-text me-1"></i>Cari
            </button>
            <button class="btn btn-sm btn-outline-secondary" onclick="openEdit(${s.id})">
              <i class="bi bi-pencil"></i>
            </button>
            <button class="btn btn-sm btn-outline-danger" onclick="openDelete(${s.id}, '${esc(s.name)}')">
              <i class="bi bi-trash"></i>
            </button>
          </div>
        </div>
      </div>`;
  });
  html += '</div>';
  document.getElementById('supplierList').innerHTML = html;
}

document.getElementById('searchInput').addEventListener('input', renderList);

/* ── ADD / EDIT ── */
const supplierModal = new bootstrap.Modal(document.getElementById('supplierModal'));

document.getElementById('btnAddSupplier').addEventListener('click', () => {
  document.getElementById('supplierModalTitle').textContent = 'Yeni Tedarikçi';
  document.getElementById('supplierId').value  = '';
  document.getElementById('sName').value       = '';
  document.getElementById('sContact').value    = '';
  document.getElementById('sPhone').value      = '';
  document.getElementById('sTaxNo').value      = '';
  document.getElementById('sTerms').value      = '0';
  document.getElementById('sNotes').value      = '';
  document.getElementById('sActive').checked   = true;
  document.getElementById('sActiveWrap').classList.add('d-none');
  document.getElementById('supplierErr').classList.add('d-none');
  supplierModal.show();
});

function openEdit(id) {
  const s = allSuppliers.find(x => x.id == id);
  if (!s) return;
  document.getElementById('supplierModalTitle').textContent = 'Tedarikçi Düzenle';
  document.getElementById('supplierId').value  = s.id;
  document.getElementById('sName').value       = s.name;
  document.getElementById('sContact').value    = s.contact_name || '';
  document.getElementById('sPhone').value      = s.phone || '';
  document.getElementById('sTaxNo').value      = s.tax_number || '';
  document.getElementById('sTerms').value      = s.payment_terms || 0;
  document.getElementById('sNotes').value      = s.notes || '';
  document.getElementById('sActive').checked   = !!s.is_active;
  document.getElementById('sActiveWrap').classList.remove('d-none');
  document.getElementById('supplierErr').classList.add('d-none');
  supplierModal.show();
}

document.getElementById('btnSaveSupplier').addEventListener('click', async () => {
  const id   = document.getElementById('supplierId').value;
  const name = document.getElementById('sName').value.trim();
  if (!name) {
    document.getElementById('supplierErr').textContent = 'Firma adı zorunlu.';
    document.getElementById('supplierErr').classList.remove('d-none');
    return;
  }
  const body = {
    name,
    contact_name:  document.getElementById('sContact').value.trim() || null,
    phone:         document.getElementById('sPhone').value.trim()   || null,
    tax_number:    document.getElementById('sTaxNo').value.trim()   || null,
    payment_terms: parseInt(document.getElementById('sTerms').value) || 0,
    notes:         document.getElementById('sNotes').value.trim()   || null,
    is_active:     document.getElementById('sActive').checked ? 1 : 0,
  };
  const btn = document.getElementById('btnSaveSupplier');
  btn.disabled = true;
  try {
    if (id) {
      await apiFetch(`supplier/update/${id}`, {
        method: 'PUT', headers: {'Content-Type':'application/json'}, body: JSON.stringify(body)
      });
    } else {
      await apiFetch('supplier/create', {
        method: 'POST', headers: {'Content-Type':'application/json'}, body: JSON.stringify(body)
      });
    }
    supplierModal.hide();
    toast('Kaydedildi.', 'success');
    await loadSuppliers();
  } catch(e) {
    document.getElementById('supplierErr').textContent = e.message;
    document.getElementById('supplierErr').classList.remove('d-none');
  } finally { btn.disabled = false; }
});

/* ── DELETE ── */
const deleteModal = new bootstrap.Modal(document.getElementById('deleteModal'));
let deleteTargetId = null;

function openDelete(id, name) {
  deleteTargetId = id;
  document.getElementById('deleteModalName').textContent = name;
  deleteModal.show();
}

document.getElementById('btnConfirmDelete').addEventListener('click', async () => {
  if (!deleteTargetId) return;
  const btn = document.getElementById('btnConfirmDelete');
  btn.disabled = true;
  try {
    await apiFetch(`supplier/delete/${deleteTargetId}`, { method: 'DELETE' });
    deleteModal.hide();
    toast('Silindi.', 'success');
    await loadSuppliers();
  } catch(e) { toast(e.message); }
  finally { btn.disabled = false; deleteTargetId = null; }
});

/* ── CARİ ── */
const cariModal    = new bootstrap.Modal(document.getElementById('cariModal'));
let cariType       = 'borc';
let cariSupplierId = null;

document.querySelectorAll('#cariTypeGroup button').forEach(btn => {
  btn.addEventListener('click', () => {
    document.querySelectorAll('#cariTypeGroup button').forEach(b => b.classList.remove('active'));
    btn.classList.add('active');
    cariType = btn.dataset.type;
  });
});

async function openCari(supplierId, name) {
  cariSupplierId = supplierId;
  document.getElementById('cariSupplierId').value   = supplierId;
  document.getElementById('cariModalTitle').textContent = name + ' — Cari Hesap';
  document.getElementById('cariAmount').value       = '';
  document.getElementById('cariDate').value         = new Date().toISOString().slice(0,10);
  document.getElementById('cariDesc').value         = '';
  document.getElementById('cariErr').classList.add('d-none');
  // default: borç
  document.querySelectorAll('#cariTypeGroup button').forEach(b => b.classList.remove('active'));
  document.querySelector('#cariTypeGroup button[data-type="borc"]').classList.add('active');
  cariType = 'borc';
  cariModal.show();
  await loadCariTxns(supplierId);
}

async function loadCariTxns(supplierId) {
  document.getElementById('cariTxnList').innerHTML =
    '<div class="text-center py-3 text-muted"><div class="spinner-border spinner-border-sm"></div></div>';
  try {
    const data = await apiFetch(`supplier/cari/${supplierId}`);
    const bal  = parseFloat(data.balance || 0);
    const balEl = document.getElementById('cariBalance');
    balEl.textContent = fmtMoney(Math.abs(bal));
    balEl.className   = 'fw-bold ' + (bal > 0 ? 'text-danger' : bal < 0 ? 'text-success' : 'text-muted');
    document.getElementById('cariBalanceNote').textContent =
      bal > 0 ? '(Borcumuz var)' : bal < 0 ? '(Alacaklıyız)' : '';

    if (!data.transactions.length) {
      document.getElementById('cariTxnList').innerHTML =
        '<div class="text-center py-4 text-muted"><i class="bi bi-journal-x fs-1 d-block mb-2"></i>Henüz işlem yok.</div>';
      return;
    }

    let html = '<div class="list-group shadow-sm">';
    data.transactions.forEach(t => {
      const amt    = parseFloat(t.amount);
      const isBorc = amt > 0;
      html += `
        <div class="list-group-item py-2 ${isBorc ? 'txn-borc' : 'txn-odeme'}">
          <div class="d-flex justify-content-between align-items-center">
            <div>
              <span class="badge ${isBorc ? 'bg-danger' : 'bg-success'} me-1">
                ${isBorc ? 'Borç' : 'Ödeme'}
              </span>
              <span class="small">${esc(t.description)}</span>
              <div class="text-muted" style="font-size:.72rem">${fmtDate(t.transaction_date)} · ${esc(t.created_by_name)}</div>
            </div>
            <div class="d-flex align-items-center gap-2">
              <span class="fw-bold ${isBorc ? 'text-danger' : 'text-success'}">${fmtMoney(Math.abs(amt))}</span>
              <button class="btn btn-sm btn-outline-danger py-0 px-1" onclick="deleteCariTxn(${t.id})" title="Sil">
                <i class="bi bi-x-lg"></i>
              </button>
            </div>
          </div>
        </div>`;
    });
    html += '</div>';
    document.getElementById('cariTxnList').innerHTML = html;
  } catch(e) {
    toast(e.message);
    document.getElementById('cariTxnList').innerHTML = `<div class="alert alert-danger">${esc(e.message)}</div>`;
  }
}

document.getElementById('btnSaveCari').addEventListener('click', async () => {
  const supplierId = document.getElementById('cariSupplierId').value;
  const amount     = parseFloat(document.getElementById('cariAmount').value);
  if (!amount || amount <= 0) {
    document.getElementById('cariErr').textContent = 'Geçerli bir tutar girin.';
    document.getElementById('cariErr').classList.remove('d-none');
    return;
  }
  document.getElementById('cariErr').classList.add('d-none');
  const btn = document.getElementById('btnSaveCari');
  btn.disabled = true;
  try {
    await apiFetch(`supplier/cari/${supplierId}`, {
      method: 'POST',
      headers: {'Content-Type':'application/json'},
      body: JSON.stringify({
        type:        cariType,
        amount,
        date:        document.getElementById('cariDate').value,
        description: document.getElementById('cariDesc').value.trim() || (cariType === 'borc' ? 'Borç' : 'Ödeme'),
      })
    });
    document.getElementById('cariAmount').value = '';
    document.getElementById('cariDesc').value   = '';
    toast('Kaydedildi.', 'success');
    await loadCariTxns(supplierId);
    await loadSuppliers();
  } catch(e) {
    document.getElementById('cariErr').textContent = e.message;
    document.getElementById('cariErr').classList.remove('d-none');
  } finally { btn.disabled = false; }
});

async function deleteCariTxn(txnId) {
  if (!confirm('Bu işlem silinsin mi?')) return;
  try {
    await apiFetch(`supplier/cari-delete/${txnId}`, { method: 'DELETE' });
    toast('Silindi.', 'success');
    await loadCariTxns(cariSupplierId);
    await loadSuppliers();
  } catch(e) { toast(e.message); }
}

/* ── BAŞLAT ── */
loadSuppliers();
</script>
</body>
</html>
