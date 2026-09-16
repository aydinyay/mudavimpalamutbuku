<?php
declare(strict_types=1);
require_once __DIR__ . '/autoload.php';
use Mudavim\Core\{Auth, Database};
Auth::requireLogin('login.php');
$user = Auth::user();
if ($user['role'] !== 'patron') {
    header('Location: dashboard.php');
    exit;
}

$roles = Database::get()->query("SELECT code, name FROM roles ORDER BY id")->fetchAll();
?>
<!DOCTYPE html>
<html lang="tr" data-bs-theme="light">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no">
<title>Personel — Müdavim</title>
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
<link rel="stylesheet" href="assets/style.css">
<style>
.staff-avatar {
  width: 44px; height: 44px; border-radius: 50%;
  display: flex; align-items: center; justify-content: center;
  font-weight: 700; font-size: 1rem; flex-shrink: 0;
}
.role-patron  { background:#fff3cd; color:#856404; }
.role-manager { background:#cff4fc; color:#055160; }
.role-chef    { background:#d1e7dd; color:#0f5132; }
.role-barman  { background:#e2d9f3; color:#432874; }
.role-waiter  { background:#f8d7da; color:#842029; }
.role-default { background:#e9ecef; color:#495057; }
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
        <i class="bi bi-people-fill me-1"></i>Personel
      </span>
    </div>
    <button class="btn btn-sm btn-success fw-semibold" id="btnNewStaff">
      <i class="bi bi-person-plus-fill me-1"></i>Yeni Personel
    </button>
  </div>
</nav>

<div class="container-fluid px-2 px-sm-3 py-3" style="max-width:700px;margin:0 auto">
  <div id="staffList">
    <div class="text-center py-5 text-muted">
      <div class="spinner-border spinner-border-sm me-2"></div>Yükleniyor…
    </div>
  </div>
</div>

<!-- EKLE / DÜZENLE MODAL -->
<div class="modal fade" id="modalStaff" tabindex="-1" data-bs-backdrop="static">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title fw-bold" id="modalStaffTitle">Yeni Personel</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">
        <input type="hidden" id="stf_id">

        <div class="row g-2 mb-3">
          <div class="col-7">
            <label class="form-label fw-semibold">Ad Soyad <span class="text-danger">*</span></label>
            <input type="text" class="form-control" id="stf_name" placeholder="Ahmet Yılmaz" autocomplete="off">
          </div>
          <div class="col-5">
            <label class="form-label fw-semibold">Kullanıcı Adı <span class="text-danger">*</span></label>
            <input type="text" class="form-control" id="stf_username" placeholder="ahmet" autocomplete="off">
          </div>
        </div>

        <div class="mb-3">
          <label class="form-label fw-semibold">Rol <span class="text-danger">*</span></label>
          <select class="form-select" id="stf_role">
            <option value="">— Seç —</option>
            <?php foreach ($roles as $r): ?>
            <option value="<?= htmlspecialchars($r['code']) ?>"><?= htmlspecialchars($r['name']) ?></option>
            <?php endforeach; ?>
          </select>
        </div>

        <hr class="my-3">
        <p class="small text-muted mb-2" id="stf_pass_hint">Şifre ve PIN zorunlu.</p>

        <div class="row g-2 mb-3">
          <div class="col-7">
            <label class="form-label fw-semibold">Şifre <small class="text-muted" id="stf_pass_label">(min 6 karakter)</small></label>
            <div class="input-group">
              <input type="password" class="form-control" id="stf_password" autocomplete="new-password" placeholder="••••••">
              <button class="btn btn-outline-secondary" type="button" id="btnTogglePass">
                <i class="bi bi-eye"></i>
              </button>
            </div>
          </div>
          <div class="col-5">
            <label class="form-label fw-semibold">PIN <small class="text-muted">(4 rakam)</small></label>
            <input type="password" class="form-control text-center fw-bold ls-2" id="stf_pin"
                   maxlength="4" inputmode="numeric" pattern="\d{4}" placeholder="••••">
          </div>
        </div>

        <div class="form-check form-switch d-none" id="stf_active_wrap">
          <input class="form-check-input" type="checkbox" id="stf_active" checked>
          <label class="form-check-label" for="stf_active">Hesap aktif</label>
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-outline-secondary flex-fill" data-bs-dismiss="modal">Vazgeç</button>
        <button type="button" class="btn btn-primary flex-fill fw-bold" id="btnStaffSave">
          <i class="bi bi-check-lg me-1"></i>Kaydet
        </button>
      </div>
    </div>
  </div>
</div>

<!-- FİNANS MODAL -->
<div class="modal fade" id="modalFinance" tabindex="-1">
  <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable">
    <div class="modal-content">
      <div class="modal-header">
        <div>
          <h5 class="modal-title fw-bold mb-0" id="finTitle">Personel Finans</h5>
          <small class="text-muted" id="finSubtitle"></small>
        </div>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body p-0">

        <!-- AY SEÇİCİ -->
        <div class="px-3 pt-3 pb-2 border-bottom bg-light d-flex align-items-center gap-2">
          <button class="btn btn-sm btn-outline-secondary" id="finPrevMonth"><i class="bi bi-chevron-left"></i></button>
          <div class="fw-bold text-center flex-grow-1" id="finMonthLabel"></div>
          <button class="btn btn-sm btn-outline-secondary" id="finNextMonth"><i class="bi bi-chevron-right"></i></button>
        </div>

        <div class="px-3 py-3">

          <!-- MAAŞ -->
          <div class="d-flex justify-content-between align-items-center mb-3">
            <div>
              <div class="text-muted small text-uppercase fw-semibold" style="letter-spacing:.06em">Aylık Maaş</div>
              <div class="fw-bold fs-5" id="finSalaryAmount">—</div>
            </div>
            <button class="btn btn-sm btn-outline-primary" id="btnEditSalary">
              <i class="bi bi-pencil me-1"></i>Düzenle
            </button>
          </div>

          <!-- MAAŞ DÜZENLE (gizli) -->
          <div class="d-none mb-3 p-2 border rounded bg-light" id="salaryEditBox">
            <label class="form-label small fw-semibold mb-1">Bu aydan itibaren maaş (₺)</label>
            <div class="input-group input-group-sm">
              <input type="number" class="form-control" id="finSalaryInput" min="0" step="100" placeholder="15000">
              <button class="btn btn-success" id="btnSalarySave">Kaydet</button>
              <button class="btn btn-outline-secondary" id="btnSalaryCancel">İptal</button>
            </div>
          </div>

          <!-- AVANS LİSTESİ -->
          <div class="d-flex justify-content-between align-items-center mb-2">
            <div class="text-muted small text-uppercase fw-semibold" style="letter-spacing:.06em">Avanslar</div>
            <button class="btn btn-sm btn-outline-success" id="btnAddAdvance">
              <i class="bi bi-plus-lg me-1"></i>Avans Ekle
            </button>
          </div>

          <!-- AVANS EKLE (gizli) -->
          <div class="d-none mb-2 p-2 border rounded bg-light" id="advanceAddBox">
            <div class="row g-2">
              <div class="col-5">
                <input type="number" class="form-control form-control-sm" id="advAmount" placeholder="Tutar ₺" min="0" step="50">
              </div>
              <div class="col-4">
                <input type="date" class="form-control form-control-sm" id="advDate">
              </div>
              <div class="col-3">
                <button class="btn btn-success btn-sm w-100" id="btnAdvanceSave">Ekle</button>
              </div>
              <div class="col-12">
                <input type="text" class="form-control form-control-sm" id="advNote" placeholder="Not (isteğe bağlı)">
              </div>
            </div>
          </div>

          <div id="finAdvanceList" class="mb-3"></div>

          <!-- ÖZET -->
          <div class="rounded p-3 mb-3" style="background:#f8f9fa;border:1px solid #dee2e6">
            <div class="d-flex justify-content-between mb-1">
              <span class="text-muted">Brüt Maaş</span>
              <span class="fw-semibold" id="sumGross">₺0</span>
            </div>
            <div class="d-flex justify-content-between mb-1">
              <span class="text-muted">Toplam Avans</span>
              <span class="fw-semibold text-danger" id="sumAdvances">₺0</span>
            </div>
            <hr class="my-2">
            <div class="d-flex justify-content-between">
              <span class="fw-bold">Net Ödeme</span>
              <span class="fw-bold fs-5 text-success" id="sumNet">₺0</span>
            </div>
          </div>

          <!-- ÖDEME DURUMU -->
          <div id="paymentStatus"></div>

          <!-- ÖDEME GEÇMİŞİ -->
          <div id="finHistory" class="mt-3"></div>

        </div>
      </div>
    </div>
  </div>
</div>

<!-- TOAST -->
<div class="position-fixed bottom-0 end-0 p-3" style="z-index:1100" id="toastContainer"></div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
const ME = <?= json_encode($user['id']) ?>;

async function apiFetch(method, path, body) {
  const opts = { method, headers: {'Content-Type':'application/json'}, credentials:'same-origin' };
  if (body) opts.body = JSON.stringify(body);
  const res  = await fetch(`api/${path}`, opts);
  const json = await res.json().catch(() => ({status:'error',data:{message:'Sunucu yanıtı okunamadı'}}));
  if (!res.ok || json.status === 'error') throw new Error(json?.data?.message || `HTTP ${res.status}`);
  return json.data;
}

function toast(msg, type = 'success') {
  const id = `t${Date.now()}`;
  const icons = {success:'bi-check-circle-fill text-success', danger:'bi-x-circle-fill text-danger', warning:'bi-exclamation-triangle-fill text-warning'};
  document.getElementById('toastContainer').insertAdjacentHTML('beforeend',`
    <div id="${id}" class="toast align-items-center border-0 shadow-sm" role="alert">
      <div class="d-flex">
        <div class="toast-body fw-semibold"><i class="bi ${icons[type]||icons.success} me-2"></i>${msg}</div>
        <button type="button" class="btn-close me-2 m-auto" data-bs-dismiss="toast"></button>
      </div>
    </div>`);
  const el = document.getElementById(id);
  new bootstrap.Toast(el, {delay:3500}).show();
  el.addEventListener('hidden.bs.toast', () => el.remove());
}

function esc(s) { return String(s??'').replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;'); }

function initials(name) {
  return name.trim().split(' ').slice(0,2).map(w=>w[0]?.toUpperCase()||'').join('');
}

function roleClass(code) {
  const map = {patron:'role-patron',manager:'role-manager',chef:'role-chef',barman:'role-barman',waiter:'role-waiter'};
  return map[code] || 'role-default';
}

function formatDate(dt) {
  if (!dt) return '—';
  const d = new Date(dt);
  return d.toLocaleDateString('tr-TR',{day:'2-digit',month:'short',year:'numeric'});
}

/* ── LIST ── */
let staffList = [];

async function loadStaff() {
  try {
    staffList = await apiFetch('GET', 'staff/list');
    renderStaff();
  } catch(e) {
    document.getElementById('staffList').innerHTML =
      `<div class="alert alert-danger">${esc(e.message)}</div>`;
  }
}

function renderStaff() {
  if (!staffList.length) {
    document.getElementById('staffList').innerHTML =
      `<div class="text-center py-5 text-muted"><i class="bi bi-people fs-1 d-block mb-2"></i>Henüz personel yok.</div>`;
    return;
  }

  const active   = staffList.filter(s => s.is_active);
  const inactive = staffList.filter(s => !s.is_active);

  let html = renderGroup('Aktif Personel', active);
  if (inactive.length) html += renderGroup('Pasif Hesaplar', inactive, true);
  document.getElementById('staffList').innerHTML = html;
}

function renderGroup(title, list, muted = false) {
  if (!list.length) return '';
  let html = `<h6 class="text-muted fw-semibold text-uppercase small mt-3 mb-2 px-1" style="letter-spacing:.07em">${esc(title)}</h6>
              <div class="list-group shadow-sm mb-2">`;
  list.forEach(s => {
    const isSelf = s.id == ME;
    html += `
      <div class="list-group-item d-flex align-items-center gap-3 py-3 ${muted?'opacity-50':''}">
        <div class="staff-avatar ${roleClass(s.role_code)}">${esc(initials(s.name))}</div>
        <div class="flex-grow-1 min-width-0">
          <div class="fw-semibold">${esc(s.name)} ${isSelf?'<span class="badge bg-secondary ms-1" style="font-size:.6rem">Sen</span>':''}</div>
          <div class="text-muted small">@${esc(s.username)} &middot; ${esc(s.role_name)}</div>
          <div class="text-muted" style="font-size:.7rem">Son giriş: ${formatDate(s.last_login_at)}</div>
        </div>
        <div class="d-flex gap-1 flex-shrink-0">
          <button class="btn btn-sm btn-outline-success" onclick="openFinance(${s.id})" title="Maaş / Avans">
            <i class="bi bi-wallet2"></i>
          </button>
          <button class="btn btn-sm btn-outline-secondary" onclick="openEdit(${s.id})" title="Düzenle">
            <i class="bi bi-pencil-square"></i>
          </button>
        </div>
      </div>`;
  });
  return html + '</div>';
}

/* ── MODAL ── */
const modal = new bootstrap.Modal(document.getElementById('modalStaff'));
let isEditMode = false;

function openNew() {
  isEditMode = false;
  document.getElementById('modalStaffTitle').textContent = 'Yeni Personel';
  document.getElementById('stf_id').value       = '';
  document.getElementById('stf_name').value     = '';
  document.getElementById('stf_username').value = '';
  document.getElementById('stf_role').value     = '';
  document.getElementById('stf_password').value = '';
  document.getElementById('stf_pin').value      = '';
  document.getElementById('stf_active_wrap').classList.add('d-none');
  document.getElementById('stf_pass_hint').textContent  = 'Şifre ve PIN zorunlu.';
  document.getElementById('stf_pass_label').textContent = '(min 6 karakter)';
  modal.show();
  setTimeout(() => document.getElementById('stf_name').focus(), 300);
}

function openEdit(id) {
  const s = staffList.find(x => x.id == id);
  if (!s) return;
  isEditMode = true;
  document.getElementById('modalStaffTitle').textContent = 'Personeli Düzenle';
  document.getElementById('stf_id').value       = s.id;
  document.getElementById('stf_name').value     = s.name;
  document.getElementById('stf_username').value = s.username;
  document.getElementById('stf_role').value     = s.role_code;
  document.getElementById('stf_password').value = '';
  document.getElementById('stf_pin').value      = '';
  document.getElementById('stf_active').checked = !!s.is_active;
  document.getElementById('stf_active_wrap').classList.remove('d-none');
  document.getElementById('stf_pass_hint').textContent  = 'Şifre / PIN değiştirmek için doldurun, değiştirmeyecekseniz boş bırakın.';
  document.getElementById('stf_pass_label').textContent = '(boş = değişmez)';
  modal.show();
}

document.getElementById('btnNewStaff').addEventListener('click', openNew);

document.getElementById('btnStaffSave').addEventListener('click', async () => {
  const id       = document.getElementById('stf_id').value;
  const name     = document.getElementById('stf_name').value.trim();
  const username = document.getElementById('stf_username').value.trim();
  const role     = document.getElementById('stf_role').value;
  const password = document.getElementById('stf_password').value;
  const pin      = document.getElementById('stf_pin').value;
  const isActive = document.getElementById('stf_active').checked;

  if (!name)     { toast('Ad Soyad zorunlu','warning'); return; }
  if (!username) { toast('Kullanıcı adı zorunlu','warning'); return; }
  if (!role)     { toast('Rol seçin','warning'); return; }
  if (!isEditMode && !password) { toast('Yeni personel için şifre zorunlu','warning'); return; }
  if (password && password.length < 6) { toast('Şifre en az 6 karakter','warning'); return; }
  if (pin && !/^\d{4}$/.test(pin)) { toast('PIN 4 rakam olmalı','warning'); return; }

  const payload = { name, username, role_code: role, is_active: isActive ? 1 : 0 };
  if (password) payload.password = password;
  if (pin)      payload.pin      = pin;

  const btn = document.getElementById('btnStaffSave');
  btn.disabled = true;
  btn.innerHTML = '<span class="spinner-border spinner-border-sm"></span>';

  try {
    if (isEditMode) {
      await apiFetch('PUT', `staff/update/${id}`, payload);
      toast('Personel güncellendi');
    } else {
      await apiFetch('POST', 'staff/create', payload);
      toast('Personel eklendi');
    }
    modal.hide();
    await loadStaff();
  } catch(e) {
    toast(e.message, 'danger');
  } finally {
    btn.disabled = false;
    btn.innerHTML = '<i class="bi bi-check-lg me-1"></i>Kaydet';
  }
});

/* Şifre göster/gizle */
document.getElementById('btnTogglePass').addEventListener('click', () => {
  const inp = document.getElementById('stf_password');
  const ico = document.querySelector('#btnTogglePass i');
  if (inp.type === 'password') { inp.type = 'text';     ico.className = 'bi bi-eye-slash'; }
  else                         { inp.type = 'password'; ico.className = 'bi bi-eye'; }
});

/* ── FİNANS MODAL ── */
const finModal = new bootstrap.Modal(document.getElementById('modalFinance'));
let finUserId  = null;
let finMonth   = new Date().toISOString().slice(0,7); // 'YYYY-MM'
let finData    = null;

function fmtTL(n) { return '₺' + parseFloat(n||0).toLocaleString('tr-TR',{minimumFractionDigits:0,maximumFractionDigits:0}); }

function monthLabel(ym) {
  const [y,m] = ym.split('-');
  const names = ['','Ocak','Şubat','Mart','Nisan','Mayıs','Haziran','Temmuz','Ağustos','Eylül','Ekim','Kasım','Aralık'];
  return `${names[+m]} ${y}`;
}

function shiftMonth(ym, delta) {
  const [y,m] = ym.split('-').map(Number);
  const d = new Date(y, m - 1 + delta, 1);
  return d.toISOString().slice(0,7);
}

async function openFinance(uid) {
  const s = staffList.find(x => x.id == uid);
  if (!s) return;
  finUserId = uid;
  document.getElementById('finTitle').textContent    = s.name;
  document.getElementById('finSubtitle').textContent = s.role_name;
  finModal.show();
  await loadFinance();
}

async function loadFinance() {
  document.getElementById('finMonthLabel').textContent = monthLabel(finMonth);
  document.getElementById('finAdvanceList').innerHTML = '<div class="text-muted small py-2">Yükleniyor…</div>';

  try {
    finData = await apiFetch('GET', `staff/finance?user_id=${finUserId}&month=${finMonth}`);
    renderFinance();
  } catch(e) {
    toast(e.message, 'danger');
  }
}

function renderFinance() {
  const d = finData;
  const salary    = d.salary ? parseFloat(d.salary.amount) : 0;
  const advances  = parseFloat(d.advance_total || 0);
  const net       = salary - advances;

  // Maaş
  document.getElementById('finSalaryAmount').textContent = salary > 0 ? fmtTL(salary) : 'Tanımlanmamış';
  document.getElementById('finSalaryInput').value        = salary > 0 ? salary : '';

  // Özet
  document.getElementById('sumGross').textContent    = fmtTL(salary);
  document.getElementById('sumAdvances').textContent = fmtTL(advances);
  document.getElementById('sumNet').textContent      = fmtTL(net);

  // Avans listesi
  const advEl = document.getElementById('finAdvanceList');
  if (!d.advances.length) {
    advEl.innerHTML = '<div class="text-muted small py-1">Bu ay avans yok.</div>';
  } else {
    advEl.innerHTML = d.advances.map(a => `
      <div class="d-flex justify-content-between align-items-center py-1 border-bottom">
        <div>
          <span class="fw-semibold">${fmtTL(a.amount)}</span>
          <span class="text-muted small ms-2">${a.given_date}</span>
          ${a.note ? `<span class="text-muted small ms-1">· ${esc(a.note)}</span>` : ''}
        </div>
        <button class="btn btn-sm btn-link text-danger p-0 ms-2" onclick="deleteAdvance(${a.id})">
          <i class="bi bi-trash"></i>
        </button>
      </div>`).join('');
  }

  // Ödeme durumu
  const payEl = document.getElementById('paymentStatus');
  if (d.payment) {
    payEl.innerHTML = `
      <div class="alert alert-success py-2 mb-0">
        <i class="bi bi-check-circle-fill me-2"></i>
        <strong>${monthLabel(finMonth)}</strong> ödemesi yapıldı — ${fmtTL(d.payment.net_payment)}
        <small class="d-block text-muted mt-1">${d.payment.paid_date}</small>
      </div>`;
  } else {
    payEl.innerHTML = `
      <button class="btn btn-success w-100 fw-bold" id="btnPayNow" ${salary<=0?'disabled':''}>
        <i class="bi bi-cash-coin me-2"></i>${fmtTL(net)} Ödendi Kaydet
      </button>`;
    document.getElementById('btnPayNow')?.addEventListener('click', recordPayment);
  }

  // Geçmiş
  const histEl = document.getElementById('finHistory');
  if (d.history.length > 1) {
    histEl.innerHTML = `<div class="text-muted small text-uppercase fw-semibold mb-1" style="letter-spacing:.06em">Ödeme Geçmişi</div>` +
      d.history.map(h => `
        <div class="d-flex justify-content-between py-1 border-bottom small">
          <span>${monthLabel(h.period_month)}</span>
          <span class="fw-semibold">${fmtTL(h.net_payment)}</span>
          <span class="text-muted">${h.paid_date}</span>
        </div>`).join('');
  } else { histEl.innerHTML = ''; }
}

async function deleteAdvance(id) {
  try {
    await apiFetch('DELETE', `staff/advance/${id}`);
    await loadFinance();
  } catch(e) { toast(e.message,'danger'); }
}

async function recordPayment() {
  const salary   = finData.salary ? parseFloat(finData.salary.amount) : 0;
  const advances = parseFloat(finData.advance_total || 0);
  try {
    await apiFetch('POST', 'staff/payment', {
      user_id: finUserId, month: finMonth,
      gross_salary: salary, total_advances: advances,
      paid_date: new Date().toISOString().slice(0,10),
    });
    toast('Ödeme kaydedildi');
    await loadFinance();
  } catch(e) { toast(e.message,'danger'); }
}

// Ay navigasyon
document.getElementById('finPrevMonth').addEventListener('click', async () => {
  finMonth = shiftMonth(finMonth, -1); await loadFinance();
});
document.getElementById('finNextMonth').addEventListener('click', async () => {
  finMonth = shiftMonth(finMonth, +1); await loadFinance();
});

// Maaş düzenleme
document.getElementById('btnEditSalary').addEventListener('click', () => {
  document.getElementById('salaryEditBox').classList.toggle('d-none');
});
document.getElementById('btnSalaryCancel').addEventListener('click', () => {
  document.getElementById('salaryEditBox').classList.add('d-none');
});
document.getElementById('btnSalarySave').addEventListener('click', async () => {
  const amount = parseFloat(document.getElementById('finSalaryInput').value);
  if (!amount || amount <= 0) { toast('Geçerli tutar girin','warning'); return; }
  try {
    await apiFetch('POST', 'staff/salary', { user_id: finUserId, amount, month: finMonth });
    document.getElementById('salaryEditBox').classList.add('d-none');
    toast('Maaş güncellendi');
    await loadFinance();
  } catch(e) { toast(e.message,'danger'); }
});

// Avans ekleme
document.getElementById('advDate').value = new Date().toISOString().slice(0,10);
document.getElementById('btnAddAdvance').addEventListener('click', () => {
  document.getElementById('advanceAddBox').classList.toggle('d-none');
});
document.getElementById('btnAdvanceSave').addEventListener('click', async () => {
  const amount = parseFloat(document.getElementById('advAmount').value);
  const date   = document.getElementById('advDate').value;
  const note   = document.getElementById('advNote').value.trim();
  if (!amount || amount <= 0) { toast('Tutar girin','warning'); return; }
  if (!date) { toast('Tarih girin','warning'); return; }
  try {
    await apiFetch('POST', 'staff/advance', { user_id: finUserId, amount, given_date: date, note });
    document.getElementById('advanceAddBox').classList.add('d-none');
    document.getElementById('advAmount').value = '';
    document.getElementById('advNote').value   = '';
    toast('Avans eklendi');
    await loadFinance();
  } catch(e) { toast(e.message,'danger'); }
});

/* ── BAŞLAT ── */
loadStaff();
</script>
</body>
</html>
