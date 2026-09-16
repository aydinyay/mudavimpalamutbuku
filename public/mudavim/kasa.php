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
<title>Kasa — Müdavim</title>
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
<link rel="stylesheet" href="assets/style.css">
<style>
.balance-card { border-radius:1rem; padding:1.5rem; text-align:center; }
.balance-val  { font-size:2.5rem; font-weight:800; letter-spacing:-.02em; }
.txn-row { border-left:3px solid transparent; }
.txn-in  { border-left-color:#198754; }
.txn-out { border-left-color:#dc3545; }
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
        <i class="bi bi-cash-stack me-1"></i>Kasa
      </span>
    </div>
    <?php if ($user['role'] === 'patron'): ?>
    <button class="btn btn-sm btn-outline-warning fw-semibold" id="btnZReport">
      <i class="bi bi-file-earmark-text me-1"></i>Z-Rapor
    </button>
    <?php endif; ?>
  </div>
</nav>

<div class="container-fluid px-2 px-sm-3 py-3" style="max-width:700px;margin:0 auto">

  <!-- BAKİYE -->
  <div class="balance-card bg-dark text-white mb-3 shadow">
    <div class="text-white-50 small text-uppercase fw-semibold mb-1" style="letter-spacing:.08em">Güncel Kasa Bakiyesi</div>
    <div class="balance-val" id="balanceDisplay">—</div>
    <div class="text-white-50 small mt-1" id="balanceDate"></div>
  </div>

  <!-- HIZLI İŞLEM -->
  <div class="row g-2 mb-3">
    <div class="col-6">
      <button class="btn btn-success w-100 py-3 fw-bold" id="btnDeposit">
        <i class="bi bi-plus-circle-fill fs-4 d-block mb-1"></i>
        Para Yatır
      </button>
    </div>
    <div class="col-6">
      <button class="btn btn-danger w-100 py-3 fw-bold" id="btnExpense">
        <i class="bi bi-dash-circle-fill fs-4 d-block mb-1"></i>
        Masraf Çıkar
      </button>
    </div>
    <?php if ($user['role'] === 'patron'): ?>
    <div class="col-12">
      <button class="btn btn-outline-secondary w-100 fw-semibold" id="btnAdjust">
        <i class="bi bi-sliders me-1"></i>Bakiye Düzelt
      </button>
    </div>
    <?php endif; ?>
  </div>

  <!-- TARİH SEÇİCİ + HAREKET LİSTESİ -->
  <div class="d-flex align-items-center gap-2 mb-2">
    <h6 class="fw-bold mb-0 text-muted text-uppercase small" style="letter-spacing:.06em">
      <i class="bi bi-list-ul me-1"></i>Günlük Hareketler
    </h6>
    <input type="date" class="form-control form-control-sm ms-auto" id="dateInput" style="max-width:160px">
  </div>

  <div id="txnList">
    <div class="text-center py-4 text-muted"><div class="spinner-border spinner-border-sm"></div></div>
  </div>

</div>

<!-- PARA YATIR MODAL -->
<div class="modal fade" id="modalDeposit" tabindex="-1" data-bs-backdrop="static">
  <div class="modal-dialog modal-dialog-centered modal-sm">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title fw-bold"><i class="bi bi-plus-circle-fill text-success me-2"></i>Para Yatır</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">
        <div class="mb-3">
          <label class="form-label fw-semibold">Tutar (₺) <span class="text-danger">*</span></label>
          <input type="number" class="form-control form-control-lg text-center fw-bold"
                 id="depAmount" min="0" step="10" placeholder="0">
        </div>
        <div class="mb-3">
          <label class="form-label fw-semibold">Açıklama</label>
          <input type="text" class="form-control" id="depDesc" placeholder="Satış hasılatı, kasa açılış…">
        </div>
        <div class="mb-0">
          <label class="form-label fw-semibold">Tarih</label>
          <input type="date" class="form-control" id="depDate">
        </div>
      </div>
      <div class="modal-footer">
        <button class="btn btn-outline-secondary flex-fill" data-bs-dismiss="modal">Vazgeç</button>
        <button class="btn btn-success flex-fill fw-bold" id="btnDepositSave">
          <i class="bi bi-check-lg me-1"></i>Kaydet
        </button>
      </div>
    </div>
  </div>
</div>

<!-- MASRAF ÇIKAR MODAL -->
<div class="modal fade" id="modalExpense" tabindex="-1" data-bs-backdrop="static">
  <div class="modal-dialog modal-dialog-centered modal-sm">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title fw-bold"><i class="bi bi-dash-circle-fill text-danger me-2"></i>Masraf Çıkar</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">
        <div class="mb-3">
          <label class="form-label fw-semibold">Tutar (₺) <span class="text-danger">*</span></label>
          <input type="number" class="form-control form-control-lg text-center fw-bold"
                 id="expAmount" min="0" step="10" placeholder="0">
        </div>
        <div class="mb-3">
          <label class="form-label fw-semibold">Açıklama</label>
          <input type="text" class="form-control" id="expDesc" placeholder="Market, nakliye…">
        </div>
        <div class="mb-3">
          <label class="form-label fw-semibold">Tarih</label>
          <input type="date" class="form-control" id="expDate">
        </div>
        <div class="mb-0">
          <label class="form-label fw-semibold">PIN <span class="text-danger">*</span></label>
          <input type="password" class="form-control text-center fw-bold ls-2"
                 id="expPin" maxlength="4" inputmode="numeric" placeholder="••••">
        </div>
      </div>
      <div class="modal-footer">
        <button class="btn btn-outline-secondary flex-fill" data-bs-dismiss="modal">Vazgeç</button>
        <button class="btn btn-danger flex-fill fw-bold" id="btnExpenseSave">
          <i class="bi bi-check-lg me-1"></i>Kaydet
        </button>
      </div>
    </div>
  </div>
</div>

<!-- BAKİYE DÜZELT MODAL (patron) -->
<div class="modal fade" id="modalAdjust" tabindex="-1" data-bs-backdrop="static">
  <div class="modal-dialog modal-dialog-centered modal-sm">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title fw-bold"><i class="bi bi-sliders me-2"></i>Bakiye Düzelt</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">
        <p class="text-muted small">Kasayı sayıp gerçek tutarı girin. Fark otomatik kaydedilir.</p>
        <div class="mb-3">
          <label class="form-label fw-semibold">Gerçek Bakiye (₺) <span class="text-danger">*</span></label>
          <input type="number" class="form-control form-control-lg text-center fw-bold"
                 id="adjBalance" min="0" step="10" placeholder="0">
        </div>
        <div class="mb-0">
          <label class="form-label fw-semibold">Not</label>
          <input type="text" class="form-control" id="adjDesc" placeholder="Sayım farkı…">
        </div>
      </div>
      <div class="modal-footer">
        <button class="btn btn-outline-secondary flex-fill" data-bs-dismiss="modal">Vazgeç</button>
        <button class="btn btn-warning flex-fill fw-bold" id="btnAdjustSave">Düzelt</button>
      </div>
    </div>
  </div>
</div>

<!-- TOAST -->
<div class="position-fixed bottom-0 end-0 p-3" style="z-index:1100" id="toastContainer"></div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
const IS_PATRON = <?= json_encode($user['role'] === 'patron') ?>;

async function apiFetch(method, path, body) {
  const opts = { method, headers:{'Content-Type':'application/json'}, credentials:'same-origin' };
  if (body) opts.body = JSON.stringify(body);
  const res  = await fetch(`api/${path}`, opts);
  const json = await res.json().catch(() => ({status:'error',data:{message:'Sunucu hatası'}}));
  if (!res.ok || json.status === 'error') throw new Error(json?.data?.message || `HTTP ${res.status}`);
  return json.data;
}

function toast(msg, type='success') {
  const id = `t${Date.now()}`;
  const icons = {success:'bi-check-circle-fill text-success',danger:'bi-x-circle-fill text-danger',warning:'bi-exclamation-triangle-fill text-warning'};
  document.getElementById('toastContainer').insertAdjacentHTML('beforeend',`
    <div id="${id}" class="toast align-items-center border-0 shadow-sm" role="alert">
      <div class="d-flex">
        <div class="toast-body fw-semibold"><i class="bi ${icons[type]||icons.success} me-2"></i>${msg}</div>
        <button type="button" class="btn-close me-2 m-auto" data-bs-dismiss="toast"></button>
      </div>
    </div>`);
  const el = document.getElementById(id);
  new bootstrap.Toast(el,{delay:3500}).show();
  el.addEventListener('hidden.bs.toast', () => el.remove());
}

function fmtTL(n) {
  const v = parseFloat(n||0);
  return (v >= 0 ? '₺' : '-₺') + Math.abs(v).toLocaleString('tr-TR',{minimumFractionDigits:2,maximumFractionDigits:2});
}

function esc(s) { return String(s??'').replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;'); }

function fmtTime(dt) {
  if (!dt) return '';
  return new Date(dt).toLocaleTimeString('tr-TR',{hour:'2-digit',minute:'2-digit'});
}

const TYPE_LABELS = {
  opening:'Açılış', purchase:'Satın Alma', patron_withdraw:'Patron Çekimi',
  cash_expense:'Masraf', staff_expense:'Personel Gideri', deposit:'Para Yatırma',
  z_report:'Z-Rapor', manual_adjust:'Manuel Düzeltme', other:'Diğer',
};

/* ── TARIH ── */
const dateInput = document.getElementById('dateInput');
dateInput.value = new Date().toISOString().slice(0,10);
dateInput.addEventListener('change', loadDay);

/* ── LOAD ── */
async function loadDay() {
  const date = dateInput.value;
  document.getElementById('balanceDate').textContent = new Date(date+'T12:00:00').toLocaleDateString('tr-TR',{weekday:'long',day:'numeric',month:'long',year:'numeric'});
  document.getElementById('txnList').innerHTML = '<div class="text-center py-3 text-muted"><div class="spinner-border spinner-border-sm"></div></div>';

  try {
    const d = await apiFetch('GET', `cash/summary?date=${date}`);
    renderBalance(d.balance);
    renderTxns(d.transactions, d.z_report);
  } catch(e) {
    toast(e.message,'danger');
    document.getElementById('txnList').innerHTML = `<div class="alert alert-danger">${esc(e.message)}</div>`;
  }
}

function renderBalance(bal) {
  const el = document.getElementById('balanceDisplay');
  el.textContent  = fmtTL(bal);
  el.style.color  = bal >= 0 ? '#6ee7b7' : '#fca5a5';
}

function renderTxns(txns, zReport) {
  const el = document.getElementById('txnList');
  if (!txns.length) {
    el.innerHTML = '<div class="text-center py-5 text-muted"><i class="bi bi-inbox fs-1 d-block mb-2"></i>Bu gün işlem yok.</div>';
    return;
  }

  // Günlük özet
  const totalIn  = txns.filter(t=>t.amount>0).reduce((s,t)=>s+parseFloat(t.amount),0);
  const totalOut = txns.filter(t=>t.amount<0).reduce((s,t)=>s+Math.abs(parseFloat(t.amount)),0);

  let html = `
    <div class="row g-2 mb-3">
      <div class="col-6">
        <div class="bg-success bg-opacity-10 border border-success rounded p-2 text-center">
          <div class="fw-bold text-success">${fmtTL(totalIn)}</div>
          <div class="text-muted" style="font-size:.7rem">Toplam Giriş</div>
        </div>
      </div>
      <div class="col-6">
        <div class="bg-danger bg-opacity-10 border border-danger rounded p-2 text-center">
          <div class="fw-bold text-danger">${fmtTL(totalOut)}</div>
          <div class="text-muted" style="font-size:.7rem">Toplam Çıkış</div>
        </div>
      </div>
    </div>
    <div class="list-group shadow-sm">`;

  txns.forEach(t => {
    const isIn    = parseFloat(t.amount) >= 0;
    const rowCls  = isIn ? 'txn-in' : 'txn-out';
    const amtCls  = isIn ? 'text-success' : 'text-danger';
    const amtSign = isIn ? '+' : '';
    html += `
      <div class="list-group-item txn-row ${rowCls} py-2">
        <div class="d-flex justify-content-between align-items-center">
          <div>
            <div class="fw-semibold small">${esc(TYPE_LABELS[t.type] || t.type)}</div>
            ${t.description ? `<div class="text-muted" style="font-size:.75rem">${esc(t.description)}</div>` : ''}
            <div class="text-muted" style="font-size:.7rem"><i class="bi bi-person me-1"></i>${esc(t.user_name)} · ${fmtTime(t.created_at)}</div>
          </div>
          <div class="fw-bold ${amtCls} ms-3 flex-shrink-0">${amtSign}${fmtTL(Math.abs(t.amount))}</div>
        </div>
      </div>`;
  });
  html += '</div>';

  if (zReport) {
    html += `
      <div class="alert alert-info mt-3 py-2 small">
        <i class="bi bi-file-earmark-check me-2"></i>
        <strong>Z-Raporu kesildi</strong> — Kapanış: ${fmtTL(zReport.closing_balance)}
      </div>`;
  } else if (IS_PATRON && dateInput.value === new Date().toISOString().slice(0,10)) {
    html += `
      <div class="text-center mt-3">
        <button class="btn btn-outline-dark btn-sm" id="btnZReportInline">
          <i class="bi bi-file-earmark-text me-1"></i>Bugün Z-Raporu Kes
        </button>
      </div>`;
  }

  el.innerHTML = html;
  document.getElementById('btnZReportInline')?.addEventListener('click', doZReport);
}

/* ── MODALLAR ── */
const modalDeposit = new bootstrap.Modal(document.getElementById('modalDeposit'));
const modalExpense = new bootstrap.Modal(document.getElementById('modalExpense'));
const modalAdjust  = IS_PATRON ? new bootstrap.Modal(document.getElementById('modalAdjust')) : null;

document.getElementById('btnDeposit').addEventListener('click', () => {
  document.getElementById('depAmount').value = '';
  document.getElementById('depDesc').value   = '';
  document.getElementById('depDate').value   = dateInput.value;
  modalDeposit.show();
  setTimeout(() => document.getElementById('depAmount').focus(), 300);
});

document.getElementById('btnExpense').addEventListener('click', () => {
  document.getElementById('expAmount').value = '';
  document.getElementById('expDesc').value   = '';
  document.getElementById('expPin').value    = '';
  document.getElementById('expDate').value   = dateInput.value;
  modalExpense.show();
  setTimeout(() => document.getElementById('expAmount').focus(), 300);
});

document.getElementById('btnAdjust')?.addEventListener('click', () => {
  document.getElementById('adjBalance').value = '';
  document.getElementById('adjDesc').value    = '';
  modalAdjust?.show();
});

async function saveWithBtn(btnId, fn) {
  const btn = document.getElementById(btnId);
  btn.disabled = true;
  btn.innerHTML = '<span class="spinner-border spinner-border-sm"></span>';
  try {
    await fn();
  } catch(e) {
    toast(e.message, 'danger');
  } finally {
    btn.disabled = false;
    btn.innerHTML = '<i class="bi bi-check-lg me-1"></i>Kaydet';
  }
}

document.getElementById('btnDepositSave').addEventListener('click', () => saveWithBtn('btnDepositSave', async () => {
  const amount = parseFloat(document.getElementById('depAmount').value);
  if (!amount || amount <= 0) { toast('Tutar girin','warning'); return; }
  const d = await apiFetch('POST', 'cash/deposit', {
    amount, description: document.getElementById('depDesc').value.trim() || 'Nakit yatırma',
    date: document.getElementById('depDate').value,
  });
  renderBalance(d.balance);
  modalDeposit.hide();
  toast('Para yatırıldı');
  loadDay();
}));

document.getElementById('btnExpenseSave').addEventListener('click', () => saveWithBtn('btnExpenseSave', async () => {
  const amount = parseFloat(document.getElementById('expAmount').value);
  const pin    = document.getElementById('expPin').value;
  if (!amount || amount <= 0) { toast('Tutar girin','warning'); return; }
  if (!/^\d{4}$/.test(pin))   { toast('PIN 4 rakam olmalı','warning'); return; }
  const d = await apiFetch('POST', 'cash/expense', {
    amount, pin,
    description: document.getElementById('expDesc').value.trim() || 'Masraf',
    date: document.getElementById('expDate').value,
  });
  renderBalance(d.balance);
  modalExpense.hide();
  document.getElementById('expPin').value = '';
  toast('Masraf kaydedildi');
  loadDay();
}));

document.getElementById('btnAdjustSave')?.addEventListener('click', async () => {
  const bal = parseFloat(document.getElementById('adjBalance').value);
  if (isNaN(bal)) { toast('Bakiye girin','warning'); return; }
  try {
    const d = await apiFetch('POST', 'cash/adjust', {
      new_balance: bal, description: document.getElementById('adjDesc').value.trim() || 'Manuel düzeltme',
    });
    renderBalance(d.balance);
    modalAdjust?.hide();
    toast(`Bakiye düzeltildi. Fark: ${fmtTL(d.diff)}`);
    loadDay();
  } catch(e) { toast(e.message,'danger'); }
});

async function doZReport() {
  if (!confirm('Bugün için Z-Raporu kesilecek. Emin misiniz?')) return;
  try {
    const d = await apiFetch('POST', 'cash/z-report', { date: dateInput.value });
    toast(`Z-Raporu kesildi — Kapanış: ${fmtTL(d.closing_balance)}`);
    loadDay();
  } catch(e) { toast(e.message,'danger'); }
}

document.getElementById('btnZReport')?.addEventListener('click', doZReport);

/* ── BAŞLAT ── */
loadDay();
</script>
</body>
</html>
