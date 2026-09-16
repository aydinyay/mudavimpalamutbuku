/**
 * Mudavim v4 — Frontend Engine
 * Vanilla JS, no build tools, Bootstrap 5 CDN uyumlu.
 *
 * Mimari: tek global `Mud` nesnesi, modüler fonksiyon grupları.
 * Be Truly kararları:
 * - PIN: sudo oturumu (sessionStorage, 30 dk expire)
 * - Autocomplete: seçimde otomatik focus → miktar alanı
 * - Offline: localStorage kuyruğu, bağlantıda flush
 * - Öneri: rol birincil, saat ikincil
 */

/* ═══════════════════════════════════════════════
   SABIT TANIMLAR
═══════════════════════════════════════════════ */

const ACTION_MAP = {
  waste_log:      { icon: 'bi-trash3-fill',       label: 'Zayi Bildir',       color: 'danger',    type: 'WASTE',            scenario: 'outlet',   suggested: true  },
  prep_waste:     { icon: 'bi-scissors',           label: 'Hazırlık Firesi',   color: 'warning',   type: 'PREP_WASTE',       scenario: 'outlet',   suggested: true  },
  stock_entry:    { icon: 'bi-box-arrow-in-down',  label: 'Ürün Alımı',        color: 'success',   type: 'OFFICIAL_PURCHASE',scenario: 'entry',    suggested: false },
  patron_purchase:{ icon: 'bi-bag-heart',          label: 'Patron Alımı',      color: 'secondary', type: 'PATRON_PURCHASE',  scenario: 'entry',    suggested: false },
  cash_expense:   { icon: 'bi-cash-coin',          label: 'Kasadan Masraf',    color: 'warning',   type: 'CASH_EXPENSE',     scenario: 'cash',     suggested: false },
  complimentary:  { icon: 'bi-gift-fill',          label: 'İkram Gir',         color: 'info',      type: 'COMPLIMENTARY',    scenario: 'outlet',   suggested: false },
  patron_exit:    { icon: 'bi-house-door-fill',    label: 'Patron Eve Götürdü',color: 'secondary', type: 'PATRON_DRAWING',   scenario: 'outlet',   suggested: false },
  patron_cash:    { icon: 'bi-cash-stack',         label: 'Patron Nakit Çekimi',color:'dark',      type: '',                 scenario: 'cashwith', suggested: false },
  stock_outlet:   { icon: 'bi-bar-chart-steps',    label: 'Stok Düşüm',        color: 'primary',   type: 'SALE',             scenario: 'outlet',   suggested: false },
  physical_count: { icon: 'bi-clipboard2-check-fill', label: 'Sayım Yap',     color: 'dark',      type: '',                 scenario: 'count',    suggested: false },
};

// Rol + saat bazlı fallback öneri sırası
const ROLE_DEFAULTS = {
  chef:    ['waste_log', 'prep_waste', 'stock_entry', 'physical_count'],
  barman:  ['stock_outlet', 'waste_log', 'cash_expense', 'complimentary'],
  manager: ['stock_entry', 'waste_log', 'complimentary', 'patron_exit'],
  patron:  ['patron_exit', 'patron_cash', 'stock_entry', 'complimentary'],
  waiter:  ['cash_expense', 'complimentary', 'waste_log', 'stock_outlet'],
  default: ['stock_entry', 'waste_log', 'complimentary', 'cash_expense'],
};

// Saat bazlı ek ağırlık (rol öncelikli, saat ikincil)
function getTimeBoost(actionKey) {
  const h = new Date().getHours();
  if (h >= 8  && h < 12 && ['waste_log','prep_waste','stock_entry'].includes(actionKey)) return 1;
  if (h >= 18 && h < 24 && ['cash_expense','complimentary','patron_exit'].includes(actionKey)) return 1;
  return 0;
}

/* ═══════════════════════════════════════════════
   API İSTEMCİSİ
═══════════════════════════════════════════════ */

const Api = (() => {
  const base = window.MUD_API || '/mudavim/api';

  async function request(method, path, body = null) {
    const opts = {
      method,
      headers: { 'Content-Type': 'application/json' },
    };
    if (body) opts.body = JSON.stringify(body);
    const res  = await fetch(`${base}/${path}`, opts);
    const json = await res.json().catch(() => ({ status: 'error', data: { message: 'Sunucu yanıtı okunamadı' } }));
    if (!res.ok || json.status === 'error') {
      const msg = json?.data?.message || `HTTP ${res.status}`;
      throw new Error(msg);
    }
    return json.data;
  }

  return {
    get:  (path)       => request('GET',  path),
    post: (path, body) => request('POST', path, body),
  };
})();

/* ═══════════════════════════════════════════════
   HOLD-TO-REPEAT UTILITY
   Butona basılı tutulduğunda fn() tekrar eder.
═══════════════════════════════════════════════ */

function holdRepeat(el, fn, delay = 380, interval = 80) {
  if (!el) return;
  let timer, repeater;
  const stop = () => { clearTimeout(timer); clearInterval(repeater); };
  const start = (e) => {
    e.preventDefault(); // context menu + scroll engelle
    fn();
    timer = setTimeout(() => { repeater = setInterval(fn, interval); }, delay);
  };
  el.addEventListener('mousedown',  start);
  el.addEventListener('touchstart', start, { passive: false });
  document.addEventListener('mouseup',     stop);
  document.addEventListener('touchend',    stop);
  document.addEventListener('touchcancel', stop);
  el.addEventListener('contextmenu', (e) => e.preventDefault());
}

/* ═══════════════════════════════════════════════
   TOAST BİLDİRİMLERİ
═══════════════════════════════════════════════ */

const Toast = (() => {
  const container = () => document.getElementById('toastContainer');

  function show(message, type = 'success', duration = 4000) {
    const icons = { success: 'bi-check-circle-fill text-success', danger: 'bi-x-circle-fill text-danger', warning: 'bi-exclamation-triangle-fill text-warning', info: 'bi-info-circle-fill text-info' };
    const id   = `toast_${Date.now()}`;
    const html = `
      <div id="${id}" class="toast align-items-center border-0 shadow-sm" role="alert" aria-live="assertive">
        <div class="d-flex">
          <div class="toast-body fw-semibold">
            <i class="bi ${icons[type] || icons.info} me-2"></i>${message}
          </div>
          <button type="button" class="btn-close me-2 m-auto" data-bs-dismiss="toast"></button>
        </div>
      </div>`;
    container().insertAdjacentHTML('beforeend', html);
    const el = document.getElementById(id);
    const t  = new bootstrap.Toast(el, { delay: duration });
    t.show();
    el.addEventListener('hidden.bs.toast', () => el.remove());
  }

  return { show, success: (m) => show(m, 'success'), error: (m) => show(m, 'danger'), warn: (m) => show(m, 'warning') };
})();

/* ═══════════════════════════════════════════════
   PIN YÖNETİCİSİ — sudo oturumu (30 dk)
═══════════════════════════════════════════════ */

const PIN = (() => {
  const KEY      = 'mud_pin_until';
  const DURATION = 30 * 60 * 1000; // 30 dakika

  let _resolveCallback = null;
  let _pinBuffer       = '';

  const modal    = () => document.getElementById('modalPIN');
  const display  = () => document.getElementById('pinDisplay');
  const errorEl  = () => document.getElementById('pinError');

  function isActive() {
    const exp = parseInt(sessionStorage.getItem(KEY) || '0');
    return Date.now() < exp;
  }

  function _updateDisplay() {
    display().querySelectorAll('.pin-dot').forEach((dot, i) => {
      dot.classList.toggle('filled', i < _pinBuffer.length);
    });
  }

  function _reset() {
    _pinBuffer = '';
    _updateDisplay();
    errorEl().classList.add('d-none');
  }

  async function _submit() {
    try {
      const res = await Api.post('auth/verify-pin', { pin: _pinBuffer });
      if (res.verified) {
        sessionStorage.setItem(KEY, String(Date.now() + DURATION));
        bootstrap.Modal.getInstance(modal()).hide();
        _reset();
        if (_resolveCallback) { _resolveCallback(true); _resolveCallback = null; }
      } else {
        errorEl().classList.remove('d-none');
        _reset();
      }
    } catch (e) {
      Toast.error('PIN doğrulanamadı: ' + e.message);
    }
  }

  // Tuş takımı olayları (modal içi delegation)
  document.addEventListener('click', (e) => {
    const key = e.target.closest('.pin-key');
    if (!key) return;

    const digit  = key.dataset.digit;
    const action = key.dataset.action;

    if (digit !== undefined && _pinBuffer.length < 4) {
      _pinBuffer += digit;
      _updateDisplay();
      if (_pinBuffer.length === 4) _submit();
    } else if (action === 'backspace') {
      _pinBuffer = _pinBuffer.slice(0, -1);
      _updateDisplay();
    } else if (action === 'clear') {
      _reset();
    }
  });

  /**
   * PIN doğrulaması ister, Promise döner.
   * Zaten aktif oturum varsa anında resolve eder.
   */
  function require() {
    if (isActive()) return Promise.resolve(true);
    return new Promise((resolve) => {
      _resolveCallback = resolve;
      _reset();
      new bootstrap.Modal(modal()).show();
    });
  }

  // Modal kapanınca resolve'u temizle
  document.addEventListener('hidden.bs.modal', (e) => {
    if (e.target.id === 'modalPIN' && _resolveCallback) {
      _resolveCallback(false);
      _resolveCallback = null;
    }
  });

  return { require, isActive };
})();

/* ═══════════════════════════════════════════════
   OTOMATİK TAMAMLAMA (Autocomplete)
═══════════════════════════════════════════════ */

const Autocomplete = (() => {
  let _debounceTimer = null;

  function init(inputEl, dropEl, onSelect) {
    inputEl.addEventListener('input', () => {
      clearTimeout(_debounceTimer);
      const q = inputEl.value.trim();
      if (q.length < 1) { hide(dropEl); return; }
      _debounceTimer = setTimeout(() => search(q, dropEl, inputEl, onSelect), 280);
    });

    // Dışarı tıklayınca kapat
    document.addEventListener('click', (e) => {
      if (!inputEl.contains(e.target) && !dropEl.contains(e.target)) hide(dropEl);
    });
  }

  async function search(q, dropEl, inputEl, onSelect) {
    try {
      const results = await Api.get(`products/search?q=${encodeURIComponent(q)}`);
      render(results, dropEl, inputEl, onSelect);
    } catch {
      hide(dropEl);
    }
  }

  function render(items, dropEl, inputEl, onSelect) {
    if (!items.length) { hide(dropEl); return; }
    dropEl.innerHTML = items.map(p => {
      const qty   = parseFloat(p.current_stock);
      const isLow = qty < 2;
      return `
        <div class="ac-item" data-id="${p.id}" data-name="${escHtml(p.name)}"
             data-unit="${escHtml(p.stock_unit)}" data-stock="${qty}" data-cat="${escHtml(p.category_name)}">
          <div>
            <div class="ac-name">${highlight(p.name, inputEl.value)}</div>
            <div class="text-muted" style="font-size:.72rem">${escHtml(p.category_name)}</div>
          </div>
          <div class="ac-stock ${isLow ? 'low' : ''}">
            <i class="bi ${isLow ? 'bi-exclamation-triangle-fill' : 'bi-box-seam'} me-1"></i>
            ${qty.toLocaleString('tr-TR', {maximumFractionDigits:2})} ${escHtml(p.stock_unit)}
          </div>
        </div>`;
    }).join('');
    dropEl.classList.remove('d-none');

    dropEl.querySelectorAll('.ac-item').forEach(item => {
      item.addEventListener('click', () => {
        const data = {
          id:    parseInt(item.dataset.id),
          name:  item.dataset.name,
          unit:  item.dataset.unit,
          stock: parseFloat(item.dataset.stock),
        };
        inputEl.value = data.name;
        hide(dropEl);
        onSelect(data);
      });
    });
  }

  function hide(dropEl) { dropEl.classList.add('d-none'); dropEl.innerHTML = ''; }

  function highlight(text, q) {
    if (!q) return escHtml(text);
    const re  = new RegExp(`(${q.replace(/[.*+?^${}()|[\]\\]/g, '\\$&')})`, 'gi');
    return escHtml(text).replace(re, '<strong>$1</strong>');
  }

  return { init };
})();

/* ═══════════════════════════════════════════════
   OFFLİNE KUYRUK
═══════════════════════════════════════════════ */

const OfflineQueue = (() => {
  const KEY = 'mud_offline_queue';

  function push(endpoint, body) {
    const q = load();
    q.push({ endpoint, body, ts: Date.now() });
    localStorage.setItem(KEY, JSON.stringify(q));
    updateBanner(q.length);
  }

  function load() {
    try { return JSON.parse(localStorage.getItem(KEY) || '[]'); } catch { return []; }
  }

  function updateBanner(count) {
    const banner  = document.getElementById('offlineBanner');
    const counter = document.getElementById('offlineQueueCount');
    if (count > 0) {
      banner.classList.remove('d-none');
      counter.textContent = `(${count} bekleyen kayıt)`;
    } else {
      banner.classList.add('d-none');
    }
  }

  async function flush() {
    const q = load();
    if (!q.length || !navigator.onLine) return;
    const remaining = [];
    for (const item of q) {
      try {
        await Api.post(item.endpoint, item.body);
      } catch {
        remaining.push(item); // Hata varsa tekrar dene
      }
    }
    localStorage.setItem(KEY, JSON.stringify(remaining));
    updateBanner(remaining.length);
    if (remaining.length < q.length) {
      Toast.success(`${q.length - remaining.length} kayıt senkronize edildi.`);
      Dashboard.refresh();
    }
  }

  // Sayfa açılışında ve online event'te flush
  window.addEventListener('online',  flush);
  window.addEventListener('offline', () => document.getElementById('offlineBanner').classList.remove('d-none'));

  return { push, flush, queueSize: () => load().length };
})();

/* ═══════════════════════════════════════════════
   AKSİYON MODALI — evrensel form
═══════════════════════════════════════════════ */

const ActionModal = (() => {
  const modal        = () => document.getElementById('modalAction');
  const title        = () => document.getElementById('modalActionTitle');
  const scenarioInp  = () => document.getElementById('frmAction_scenario');
  const typeInp      = () => document.getElementById('frmAction_type');
  const productIdInp = () => document.getElementById('frmAction_productId');
  const searchInp    = () => document.getElementById('frmAction_productSearch');
  const dropEl       = () => document.getElementById('frmAction_acDrop');
  const stockInfo    = () => document.getElementById('frmAction_stockInfo');
  const unitSpan     = () => document.getElementById('frmAction_unit');
  const qtyInp       = () => document.getElementById('frmAction_qty');
  const purchFields  = () => document.getElementById('frmAction_purchaseFields');
  const submitBtn    = () => document.getElementById('btnActionSubmit');
  const supplierSel  = () => document.getElementById('frmAction_supplier');

  let _bsModal        = null;
  let _selectedUnit   = '';
  let _acBound        = false;
  let _suppliersLoaded = false;

  async function loadSuppliers() {
    if (_suppliersLoaded) return;
    try {
      const res  = await fetch('api/supplier/list', { credentials: 'same-origin' });
      const json = await res.json();
      const sel  = supplierSel();
      if (!sel) return;
      (json.data || []).filter(s => s.is_active).forEach(s => {
        const opt = document.createElement('option');
        opt.value       = s.id;
        opt.textContent = s.name + (parseFloat(s.balance||0) > 0 ? ` (Borç: ₺${parseFloat(s.balance).toLocaleString('tr-TR',{maximumFractionDigits:0})})` : '');
        sel.appendChild(opt);
      });
      _suppliersLoaded = true;
    } catch {}
  }

  function open(actionKey) {
    const def = ACTION_MAP[actionKey];
    if (!def) return;

    // Sıfırla
    document.getElementById('frmAction').reset();
    productIdInp().value    = '';
    searchInp().value       = '';
    unitSpan().textContent  = '—';
    dropEl().classList.add('d-none');
    stockInfo().classList.add('d-none');
    _selectedUnit = '';

    if (def.scenario === 'entry') {
      purchFields().classList.remove('d-none');
      loadSuppliers();
    } else {
      purchFields().classList.add('d-none');
    }

    title().innerHTML = `<i class="bi ${def.icon} me-2"></i>${def.label}`;
    typeInp().value     = def.type;
    scenarioInp().value = def.scenario;

    submitBtn().className = `btn btn-${def.color} btn-action-lg`;
    submitBtn().querySelector('.btn-text').innerHTML =
      `<i class="bi bi-check2-circle me-1"></i>Kaydet`;

    _bsModal = _bsModal || new bootstrap.Modal(modal());
    _bsModal.show();

    // Autocomplete bir kez bağla (modal yeniden açılınca duplicate listener oluşmasın)
    if (!_acBound) {
      _acBound = true;
      Autocomplete.init(searchInp(), dropEl(), (product) => {
        productIdInp().value   = product.id;
        unitSpan().textContent = product.unit;
        _selectedUnit          = product.unit;
        qtyInp().focus();
        qtyInp().select();
        showStockInfo(product);
      });
    }

    // Modal açılınca search'e focus
    modal().addEventListener('shown.bs.modal', () => searchInp().focus(), { once: true });
  }

  function showStockInfo(product) {
    const el = stockInfo();
    const q  = parseFloat(product.stock);
    el.classList.remove('d-none');
    el.innerHTML = q <= 0
      ? `<i class="bi bi-exclamation-octagon-fill text-danger me-1"></i><strong>Stok sıfır!</strong> ${escHtml(product.name)}`
      : `<i class="bi bi-box-seam me-1"></i>Mevcut stok: <strong>${q.toLocaleString('tr-TR',{maximumFractionDigits:2})} ${escHtml(product.unit)}</strong>`;
  }

  // Miktar ±1 butonları — basılı tutunca tekrar eder
  document.querySelectorAll('.qty-inc').forEach(btn => holdRepeat(btn, () => {
    const qi = qtyInp(); if (!qi) return;
    const step = parseFloat(qi.step) || 0.1;
    qi.value = Math.round((parseFloat(qi.value || '0') + step) * 1000) / 1000;
  }));
  document.querySelectorAll('.qty-dec').forEach(btn => holdRepeat(btn, () => {
    const qi = qtyInp(); if (!qi) return;
    const step = parseFloat(qi.step) || 0.1;
    qi.value = Math.max(0, Math.round((parseFloat(qi.value || '0') - step) * 1000) / 1000);
  }));

  // Submit
  document.getElementById('btnActionSubmit').addEventListener('click', async () => {
    const scenario = scenarioInp().value;
    const type     = typeInp().value;
    const pid      = parseInt(productIdInp().value);
    const qty      = parseFloat(qtyInp().value);
    const note     = document.getElementById('frmAction_note').value.trim();

    if (!pid)       { Toast.warn('Lütfen bir ürün seçin.'); return; }
    if (!qty || qty <= 0) { Toast.warn('Geçerli bir miktar girin.'); return; }

    // Kasadan masraf: PIN iste
    if (scenario === 'cash') {
      const ok = await PIN.require();
      if (!ok) return;
    }

    setSubmitLoading(true);

    const payload = {
      product_id:      pid,
      location_id:     parseInt(document.getElementById('frmAction_locationId').value),
      quantity:        qty,
      reference_note:  note,
    };

    let endpoint, bodyKey;
    if (scenario === 'outlet' || scenario === 'cash') {
      endpoint = 'stock/outlets';
      payload.outlet_type_code = type;
      payload.quantity = qty;
    } else {
      endpoint = 'stock/entries';
      payload.entry_type_code  = type;
      payload.quantity_gross   = qty;
      payload.unit_price       = parseFloat(document.getElementById('frmAction_price').value || '0');
      payload.invoice_number   = document.getElementById('frmAction_invoice').value.trim() || null;
      const supVal             = supplierSel()?.value;
      if (supVal) payload.supplier_id = parseInt(supVal);
    }

    // unit_id yok elimizde — productSearch'teki data'dan alabiliriz; şimdilik 1 (stok birimi)
    // Gerçek projede autocomplete'de unit_id de taşınmalı
    payload.unit_id = 1;

    try {
      if (navigator.onLine) {
        await Api.post(endpoint, payload);
        Toast.success('Kayıt başarıyla oluşturuldu.');
      } else {
        OfflineQueue.push(endpoint, payload);
        Toast.warn('Çevrimdışısınız. Kayıt sıraya alındı.');
      }
      _bsModal.hide();
      Dashboard.refresh();
    } catch (err) {
      Toast.error(err.message || 'Kayıt oluşturulamadı.');
    } finally {
      setSubmitLoading(false);
    }
  });

  function setSubmitLoading(loading) {
    const btn = submitBtn();
    btn.disabled = loading;
    btn.querySelector('.btn-text').classList.toggle('d-none', loading);
    btn.querySelector('.btn-loading').classList.toggle('d-none', !loading);
  }

  return { open };
})();

/* ═══════════════════════════════════════════════
   PATRON NAKİT ÇEKİMİ
═══════════════════════════════════════════════ */

document.getElementById('btnCashWithdrawSubmit').addEventListener('click', async () => {
  const amount = parseFloat(document.getElementById('cwAmount').value);
  const note   = document.getElementById('cwNote').value.trim();

  if (!amount || amount <= 0) { Toast.warn('Geçerli bir tutar girin.'); return; }

  // PIN zorunlu
  const ok = await PIN.require();
  if (!ok) return;

  const btn = document.getElementById('btnCashWithdrawSubmit');
  btn.disabled = true;
  btn.querySelector('.btn-text').classList.add('d-none');
  btn.querySelector('.btn-loading').classList.remove('d-none');

  try {
    // Patron cash withdraw için outlet değil, cash_transactions üzerinden çalışır
    // Şimdilik PATRON_DRAWING çıkışı olarak yaz (tutar = qty, unit_cost = 1)
    await Api.post('stock/outlets', {
      outlet_type_code: 'PATRON_CASH_WITHDRAW',
      product_id: null, // Backend'de bu tip için ürün zorunluluğu kaldırılmalı (sonraki faz)
      quantity:   amount,
      unit_id:    1,
      location_id: window.MUD_USER.location_id,
      reference_note: note || 'Patron nakit çekimi',
    });
    Toast.success(`₺${amount.toLocaleString('tr-TR')} patron nakit çekimi kaydedildi.`);
    bootstrap.Modal.getInstance(document.getElementById('modalCashWithdraw')).hide();
    Dashboard.refresh();
  } catch (err) {
    Toast.error(err.message);
  } finally {
    btn.disabled = false;
    btn.querySelector('.btn-text').classList.remove('d-none');
    btn.querySelector('.btn-loading').classList.add('d-none');
  }
});

/* ═══════════════════════════════════════════════
   HIZLI İŞLEM BUTONLARI
═══════════════════════════════════════════════ */

const QuickActions = (() => {
  let _suggestionKeys = [];

  function renderButtons(container, keys, isSuggested) {
    if (!keys.length) { container.innerHTML = '<div class="col-12 text-muted small text-center py-2">İşlem bulunamadı.</div>'; return; }

    // Önerilen: 2 sütun büyük; tüm liste: 3 sütun küçük
    const colCls = isSuggested ? 'col-6' : 'col-4';

    container.innerHTML = keys.map(key => {
      const def = ACTION_MAP[key];
      if (!def) return '';
      const btnCls = isSuggested ? 'quick-btn suggested' : 'quick-btn secondary';
      return `
        <div class="${colCls}">
          <button class="${btnCls} btn-${def.color} bg-${def.color} bg-opacity-10 border-${def.color}"
                  data-action-key="${key}" style="color:var(--bs-${def.color});">
            <i class="bi ${def.icon} quick-icon"></i>
            <span>${def.label}</span>
          </button>
        </div>`;
    }).join('');

    // Tıklama
    container.querySelectorAll('[data-action-key]').forEach(btn => {
      btn.addEventListener('click', () => {
        const key = btn.dataset.actionKey;
        const def = ACTION_MAP[key];
        if (!def) return;

        // Davranış izle
        BehaviorTracker.track(key);

        if (def.scenario === 'cashwith') {
          PIN.require().then(ok => ok && new bootstrap.Modal(document.getElementById('modalCashWithdraw')).show());
        } else if (def.scenario === 'count') {
          PhysicalCount.open();
        } else {
          ActionModal.open(key);
        }
      });
    });
  }

  async function load() {
    const container   = document.getElementById('quickActions');
    const allContainer = document.getElementById('allActions');

    try {
      const suggestions = await Api.get('ui/suggestions');

      // Backend'den gelen action_type'ları ACTION_MAP ile eşleştir
      _suggestionKeys = suggestions
        .map(s => s.action_type)
        .filter(k => ACTION_MAP[k])
        .slice(0, 4);

      // Fallback: boşsa rol bazlı default
      if (!_suggestionKeys.length) {
        const role = window.MUD_USER.role;
        const base = ROLE_DEFAULTS[role] || ROLE_DEFAULTS.default;
        // Zaman boost: sırayı yeniden sırala
        _suggestionKeys = [...base].sort((a, b) => getTimeBoost(b) - getTimeBoost(a)).slice(0, 4);
      }

      renderButtons(container, _suggestionKeys, true);

      // Tüm işlemler = quickActions'ta olmayan kalanlar (tekrar önlemek için)
      const allKeys = Object.keys(ACTION_MAP).filter(k => !_suggestionKeys.includes(k));
      renderButtons(allContainer, allKeys, false);

    } catch {
      // Offline fallback
      const role = window.MUD_USER.role;
      _suggestionKeys = (ROLE_DEFAULTS[role] || ROLE_DEFAULTS.default).slice(0, 4);
      renderButtons(container, _suggestionKeys, true);
    }
  }

  return { load };
})();

/* ═══════════════════════════════════════════════
   STOK UYARILARI
═══════════════════════════════════════════════ */

const Alerts = (() => {
  const typeConfig = {
    negative_stock: { cls: 'critical', icon: 'bi-exclamation-octagon-fill text-danger', label: 'Negatif Stok' },
    critical_stock: { cls: 'critical', icon: 'bi-x-circle-fill text-danger',             label: 'Kritik'       },
    low_stock:      { cls: 'low',      icon: 'bi-exclamation-triangle-fill text-warning', label: 'Düşük Stok'  },
    expiry_soon:    { cls: 'expiry',   icon: 'bi-calendar-x-fill text-warning',           label: 'Son. Yakın'  },
    high_velocity:  { cls: 'low',      icon: 'bi-lightning-fill text-info',               label: 'Hızlı Tüket.'},
  };

  async function load() {
    try {
      const alerts  = await Api.get('stock/alerts');
      const section = document.getElementById('alertsSection');
      const list    = document.getElementById('alertsList');
      const badge   = document.getElementById('alertBadge');

      if (!alerts.length) { section.classList.add('d-none'); badge.classList.add('d-none'); return; }

      badge.textContent = alerts.length;
      badge.classList.remove('d-none');
      section.classList.remove('d-none');

      list.innerHTML = alerts.map(a => {
        const cfg = typeConfig[a.alert_type] || typeConfig.low_stock;
        const days = a.days_remaining ? ` · ~${a.days_remaining} gün` : '';
        return `
          <div class="alert-stock-item ${cfg.cls}">
            <i class="bi ${cfg.icon} alert-icon"></i>
            <div>
              <strong>${escHtml(a.product_name)}</strong>
              <div class="text-muted" style="font-size:.75rem">
                <span class="badge bg-secondary me-1">${cfg.label}</span>
                Mevcut: ${parseFloat(a.current_qty).toLocaleString('tr-TR',{maximumFractionDigits:2})}${days}
              </div>
            </div>
            <button class="alert-dismiss" data-alert-id="${a.id}" title="Okundu olarak işaretle">
              <i class="bi bi-check2-circle"></i>
            </button>
          </div>`;
      }).join('');

      list.querySelectorAll('.alert-dismiss').forEach(btn => {
        btn.addEventListener('click', async () => {
          try {
            await Api.post('stock/alerts', { id: parseInt(btn.dataset.alertId) });
            btn.closest('.alert-stock-item').remove();
            const remaining = list.querySelectorAll('.alert-stock-item').length;
            badge.textContent = remaining;
            if (!remaining) { section.classList.add('d-none'); badge.classList.add('d-none'); }
          } catch { Toast.error('Uyarı güncellenemedi.'); }
        });
      });

    } catch { /* sessiz hata — offline olabilir */ }
  }

  // Navbar uyarı butonuna tıklayınca uyarı bölümüne scroll
  document.getElementById('btnAlerts').addEventListener('click', () => {
    document.getElementById('alertsSection').scrollIntoView({ behavior: 'smooth' });
  });

  return { load };
})();

/* ═══════════════════════════════════════════════
   DASHBOARD ÖZET ve SON HAREKETLERİ
═══════════════════════════════════════════════ */

const Dashboard = (() => {
  const KIND_CFG = {
    entry:  { cls: 'bg-success text-white',  label: 'GİRİŞ'  },
    outlet: { cls: 'bg-primary text-white',  label: 'ÇIKIŞ'  },
  };

  async function loadSummary() {
    try {
      const s = await Api.get('dashboard/summary');
      document.getElementById('sumEntries').textContent = s.entry_count;
      document.getElementById('sumOutlets').textContent = s.outlet_count;
      document.getElementById('sumWaste').textContent   = s.waste_value
        ? s.waste_value.toLocaleString('tr-TR', { maximumFractionDigits: 0 })
        : '0';
      document.getElementById('sumCash').textContent    = s.cash_balance !== null
        ? s.cash_balance.toLocaleString('tr-TR', { maximumFractionDigits: 0 })
        : '—';
    } catch { /* sessiz */ }
  }

  async function loadRecent() {
    const container = document.getElementById('recentList');
    const dateEl    = document.getElementById('recentDate');
    try {
      const items = await Api.get('dashboard/recent');
      dateEl.textContent = new Date().toLocaleDateString('tr-TR', { weekday:'long', day:'numeric', month:'long' });

      if (!items.length) {
        container.innerHTML = '<div class="section-card"><div class="text-center py-4 text-muted small"><i class="bi bi-inbox fs-3 d-block mb-1"></i>Bugün henüz kayıt yok.</div></div>';
        return;
      }

      container.innerHTML = `<div class="section-card">${items.map(item => {
        const cfg  = KIND_CFG[item.kind] || KIND_CFG.entry;
        const time = new Date(item.created_at).toLocaleTimeString('tr-TR', { hour: '2-digit', minute: '2-digit' });
        const qty  = parseFloat(item.qty).toLocaleString('tr-TR', { maximumFractionDigits: 2 });
        return `
          <div class="recent-item">
            <span class="recent-kind-badge ${cfg.cls}">${cfg.label}</span>
            <div>
              <div class="fw-semibold" style="font-size:.85rem">${escHtml(item.product_name)}</div>
              <div class="text-muted" style="font-size:.72rem">${escHtml(item.type_name)} · ${qty} ${escHtml(item.unit)} · ${escHtml(item.user_name)}</div>
            </div>
            <span class="recent-time">${time}</span>
          </div>`;
      }).join('')}</div>`;

    } catch {
      container.innerHTML = '<div class="section-card text-center py-3 text-muted small"><i class="bi bi-wifi-off me-1"></i>Veriler yüklenemedi.</div>';
    }
  }

  function refresh() {
    loadSummary();
    loadRecent();
    Alerts.load();
  }

  return { init: refresh, refresh };
})();

/* ═══════════════════════════════════════════════
   DAVRANIŞ TAKİBİ (Predictive UI öğrenimi)
═══════════════════════════════════════════════ */

const BehaviorTracker = (() => {
  function track(actionKey) {
    // Yerel sayaç — backend'e asenkron gönder (sessiz hata)
    const h   = new Date().getHours();
    const dow = new Date().getDay();
    Api.post('ui/behavior', { action_type: actionKey, hour: h, dow }).catch(() => {});
  }
  return { track };
})();

/* ═══════════════════════════════════════════════
   TÜM İŞLEMLER TOGGLE
═══════════════════════════════════════════════ */

document.getElementById('btnShowAll').addEventListener('click', (e) => {
  const btn       = e.currentTarget;
  const expanded  = btn.dataset.expanded === 'true';
  const allDiv    = document.getElementById('allActions');
  allDiv.classList.toggle('d-none', expanded);
  btn.dataset.expanded = String(!expanded);
  btn.querySelector('span').textContent = expanded ? 'Tüm İşlemler' : 'Gizle';
});

/* ═══════════════════════════════════════════════
   DEMİRBAŞ ENVANTERİ
   Çatal / bıçak / tabak / minder / runner vb.
   Yemek stoğundan farkı: WAC yok, velocity yok,
   sadece say + kırık/kayıp bildir.
═══════════════════════════════════════════════ */

const FixedAssets = (() => {

  // İkon eşlemesi — SKU prefix veya ürün adı anahtar kelimesine göre
  const ICONS = {
    'CATAL': 'bi-fork',      'BICAK': 'bi-slash',       'KASIK': 'bi-spoon',
    'TABAK': 'bi-circle',    'KASE':  'bi-cup-hot',      'EKMEK': 'bi-circle-half',
    'BARDAG': 'bi-cup',      'KADEH': 'bi-cup-straw',    'MINDER': 'bi-person-arms-up',
    'RUNNER': 'bi-distribute-horizontal', 'ORTU': 'bi-table',
    'PECETE': 'bi-file-earmark', 'TEPSI': 'bi-tray',    'TUZLUK': 'bi-droplet',
  };

  function getIcon(sku) {
    if (!sku) return 'bi-box-seam';
    const key = Object.keys(ICONS).find(k => sku.includes(k));
    return key ? ICONS[key] : 'bi-box-seam';
  }

  let _assets      = [];   // son yüklenen liste
  let _lossModal   = null;
  let _addModal    = null;
  let _selectedLossType = '';

  // ── Tüm demirbaşları yükle ve grid'i oluştur ──
  async function load() {
    const grid = document.getElementById('assetGrid');
    const locationId = window.MUD_USER.location_id;
    try {
      _assets = await Api.get(`assets/list?location_id=${locationId}`);
      renderGrid(_assets);
      // Select'i de doldur (yeni giriş modalı için)
      populateAddSelect(_assets);
    } catch {
      grid.innerHTML = '<div class="col-12 text-muted small text-center py-2"><i class="bi bi-wifi-off me-1"></i>Yüklenemedi</div>';
    }
  }

  function renderGrid(assets) {
    const grid = document.getElementById('assetGrid');
    if (!assets.length) {
      grid.innerHTML = '<div class="col-12 text-muted small text-center py-2">Demirbaş ürünü tanımlanmamış.</div>';
      return;
    }
    grid.innerHTML = assets.map(a => {
      const total       = parseInt(a.total_qty)       || 0;
      const broken      = parseInt(a.broken_this_month) || 0;
      const lost        = parseInt(a.lost_this_month)   || 0;
      const serviceable = Math.max(0, total - broken - lost);
      const hasLoss     = broken + lost > 0;
      const icon        = getIcon(a.sku || '');

      return `
        <div class="col-6 col-sm-4 col-md-3">
          <div class="asset-card${hasLoss ? ' has-loss' : ''}">
            <div class="asset-icon"><i class="bi ${icon}"></i></div>
            <div class="asset-name">${escHtml(a.name)}</div>
            <div class="asset-count">${total.toLocaleString('tr-TR')}</div>
            <div class="asset-sub">
              ${broken > 0 ? `<span class="text-warning"><i class="bi bi-stars me-1"></i>${broken} kirlik</span>` : ''}
              ${lost   > 0 ? `<span class="text-danger ms-1"><i class="bi bi-question-circle me-1"></i>${lost} kayip</span>` : ''}
              ${!broken && !lost ? '<span class="text-success"><i class="bi bi-check-circle me-1"></i>tamam</span>' : ''}
            </div>
            <button class="btn btn-sm btn-outline-danger asset-loss-btn mt-2 w-100"
                    data-id="${a.id}" data-name="${escHtml(a.name)}" data-qty="${total}"
                    ${total <= 0 ? 'disabled' : ''}>
              <i class="bi bi-dash-circle me-1"></i>Kirik/Kayip
            </button>
          </div>
        </div>`;
    }).join('');

    // Kırık/Kayıp butonu tıklama
    grid.querySelectorAll('.asset-loss-btn').forEach(btn => {
      btn.addEventListener('click', () => {
        openLossModal(
          parseInt(btn.dataset.id),
          btn.dataset.name,
          parseInt(btn.dataset.qty)
        );
      });
    });
  }

  // ── Kayıp modalını aç ───────────────────────
  function openLossModal(productId, name, currentQty) {
    document.getElementById('assetLoss_productId').value = productId;
    document.getElementById('assetLoss_productName').textContent =
      `${name}  —  Mevcut: ${currentQty} adet`;
    document.getElementById('assetLoss_qty').value   = 1;
    document.getElementById('assetLoss_note').value  = '';
    document.getElementById('assetLoss_lossType').value = '';
    document.getElementById('btnAssetLossSubmit').disabled = true;

    // Tür butonlarını sıfırla
    document.querySelectorAll('.asset-loss-type-btn').forEach(b => b.classList.remove('active','btn-warning','btn-danger'));
    _selectedLossType = '';

    _lossModal = _lossModal || new bootstrap.Modal(document.getElementById('modalAssetLoss'));
    _lossModal.show();
  }

  // ── Geçmiş paneli ───────────────────────────
  async function toggleHistory() {
    const panel = document.getElementById('assetHistoryPanel');
    if (!panel.classList.contains('d-none')) {
      panel.classList.add('d-none');
      return;
    }
    try {
      const rows = await Api.get('assets/history?months=1');
      const list = document.getElementById('assetHistoryList');
      if (!rows.length) {
        list.innerHTML = '<div class="text-center py-3 text-muted small">Bu ay kayıp yok.</div>';
      } else {
        list.innerHTML = rows.map(r => {
          const isBroken = r.loss_code === 'BREAKAGE';
          return `
            <div class="recent-item">
              <i class="bi ${isBroken ? 'bi-stars text-warning' : 'bi-question-circle text-danger'} flex-shrink-0"></i>
              <div class="flex-fill">
                <div class="fw-semibold" style="font-size:.84rem">${escHtml(r.product_name)}</div>
                <div class="text-muted" style="font-size:.72rem">${escHtml(r.user_name)} &middot; ${r.outlet_date}</div>
              </div>
              <div class="text-end flex-shrink-0">
                <div class="fw-bold ${isBroken ? 'text-warning' : 'text-danger'}" style="font-size:.84rem">
                  ${parseFloat(r.quantity).toLocaleString('tr-TR')} adet
                </div>
                <div class="text-muted" style="font-size:.72rem">${escHtml(r.loss_type)}</div>
              </div>
            </div>`;
        }).join('');
      }
      panel.classList.remove('d-none');
    } catch (e) {
      Toast.error('Geçmiş yüklenemedi: ' + e.message);
    }
  }

  // ── Yeni giriş select ───────────────────────
  function populateAddSelect(assets) {
    const sel = document.getElementById('assetAdd_productId');
    if (!sel) return;
    sel.innerHTML = '<option value="">Secin...</option>' +
      assets.map(a => `<option value="${a.id}">${escHtml(a.name)} (${parseInt(a.total_qty)} adet)</option>`).join('');
  }

  // ── Event bağlama ────────────────────────────
  function bindEvents() {

    // Kayıp tipi seçimi
    document.addEventListener('click', (e) => {
      const btn = e.target.closest('.asset-loss-type-btn');
      if (!btn) return;
      document.querySelectorAll('.asset-loss-type-btn').forEach(b => {
        b.classList.remove('btn-warning','btn-danger','text-white');
        b.classList.add('btn-outline-warning', 'btn-outline-danger'.replace('btn-outline-', b.dataset.loss === 'BREAKAGE' ? 'btn-outline-warning' : 'btn-outline-danger'));
      });
      const isBreakage = btn.dataset.loss === 'BREAKAGE';
      btn.classList.remove(isBreakage ? 'btn-outline-warning' : 'btn-outline-danger');
      btn.classList.add(isBreakage ? 'btn-warning' : 'btn-danger', 'text-white');
      _selectedLossType = btn.dataset.loss;
      document.getElementById('assetLoss_lossType').value = _selectedLossType;
      document.getElementById('btnAssetLossSubmit').disabled = false;
    });

    // Adet ±1 — basılı tutunca tekrar eder
    holdRepeat(document.getElementById('assetLossQtyDec'), () => {
      const inp = document.getElementById('assetLoss_qty');
      inp.value = Math.max(1, parseInt(inp.value || '1') - 1);
    });
    holdRepeat(document.getElementById('assetLossQtyInc'), () => {
      const inp = document.getElementById('assetLoss_qty');
      inp.value = parseInt(inp.value || '1') + 1;
    });

    // Kayıp kaydet
    document.getElementById('btnAssetLossSubmit')?.addEventListener('click', async () => {
      const pid      = parseInt(document.getElementById('assetLoss_productId').value);
      const qty      = parseInt(document.getElementById('assetLoss_qty').value);
      const lossType = document.getElementById('assetLoss_lossType').value;
      const note     = document.getElementById('assetLoss_note').value.trim();
      if (!pid || !qty || !lossType) { Toast.warn('Tüm alanları doldurun.'); return; }

      const btn = document.getElementById('btnAssetLossSubmit');
      btn.disabled = true;
      btn.querySelector('.btn-text').classList.add('d-none');
      btn.querySelector('.btn-loading').classList.remove('d-none');

      try {
        await Api.post('assets/loss', {
          product_id:  pid,
          quantity:    qty,
          loss_type:   lossType,
          location_id: window.MUD_USER.location_id,
          note,
        });
        const label = lossType === 'BREAKAGE' ? 'kırıldı' : 'kayıp';
        Toast.success(`${qty} adet ${label} olarak kaydedildi.`);
        _lossModal.hide();
        load(); // Grid'i yenile
      } catch (err) {
        Toast.error(err.message);
        btn.disabled = false;
        btn.querySelector('.btn-text').classList.remove('d-none');
        btn.querySelector('.btn-loading').classList.add('d-none');
      }
    });

    // Geçmiş toggle
    document.getElementById('btnAssetHistory')?.addEventListener('click', toggleHistory);
    document.getElementById('btnAssetHistoryClose')?.addEventListener('click', () => {
      document.getElementById('assetHistoryPanel').classList.add('d-none');
    });

    // Yeni giriş aç
    document.getElementById('btnAssetAdd')?.addEventListener('click', () => {
      _addModal = _addModal || new bootstrap.Modal(document.getElementById('modalAssetAdd'));
      document.getElementById('assetAdd_qty').value   = 1;
      document.getElementById('assetAdd_price').value = '';
      document.getElementById('assetAdd_note').value  = '';
      _addModal.show();
    });

    // Yeni giriş kaydet
    document.getElementById('btnAssetAddSubmit')?.addEventListener('click', async () => {
      const pid   = parseInt(document.getElementById('assetAdd_productId').value);
      const qty   = parseInt(document.getElementById('assetAdd_qty').value);
      const price = parseFloat(document.getElementById('assetAdd_price').value || '0');
      const note  = document.getElementById('assetAdd_note').value.trim();
      if (!pid || !qty) { Toast.warn('Ürün ve adet zorunlu.'); return; }

      const btn = document.getElementById('btnAssetAddSubmit');
      btn.disabled = true;
      btn.querySelector('.btn-text').classList.add('d-none');
      btn.querySelector('.btn-loading').classList.remove('d-none');

      try {
        await Api.post('assets/add', {
          product_id:  pid,
          quantity:    qty,
          unit_price:  price,
          location_id: window.MUD_USER.location_id,
          note,
        });
        Toast.success(`${qty} adet demirbaş girişi kaydedildi.`);
        _addModal.hide();
        load();
      } catch (err) {
        Toast.error(err.message);
        btn.disabled = false;
        btn.querySelector('.btn-text').classList.remove('d-none');
        btn.querySelector('.btn-loading').classList.add('d-none');
      }
    });
  }

  return { load, bindEvents };
})();

/* ═══════════════════════════════════════════════
   FİZİKSEL SAYIM MODÜLÜ
   State machine: idle → loading → counting → variance → summary → saved

   Be Truly kararları:
   - Kör sayım: teorik miktar counting aşamasında gizlenir
   - Varsa fark → neden picker → reason ile draft'a işle
   - Fark yoksa → 1 sn "eşleşiyor" göster → otomatik ileri
   - Draft: localStorage + sunucu; yarım kalan devam edilebilir
═══════════════════════════════════════════════ */

const PhysicalCount = (() => {

  const DRAFT_KEY = 'mud_count_draft'; // localStorage anahtar
  const FREQ_LABEL = { daily: 'Günlük', weekly: 'Haftalık', monthly: 'Aylık' };

  const state = {
    phase:       'idle',    // idle|loading|counting|variance|summary|saving
    countId:     null,
    products:    [],        // sunucudan gelen ürün listesi
    currentIdx:  0,
    results:     [],        // {productId, name, unit, theoretical, counted, variance, varValue, reason}
    currentItem: null,      // saveCountItem API'sından dönen veri
    bsModal:     null,
  };

  // ── DOM refs ───────────────────────────────────
  const el = {
    modal:          () => document.getElementById('modalCount'),
    title:          () => document.getElementById('cntModalTitle'),
    body:           () => document.getElementById('cntBody'),
    progressWrap:   () => document.getElementById('cntProgressWrap'),
    progressBar:    () => document.getElementById('cntProgressBar'),
    progressLabel:  () => document.getElementById('cntProgressLabel'),
    progressPct:    () => document.getElementById('cntProgressPct'),
    // Sayfalar
    pageStart:      () => document.getElementById('cntPageStart'),
    pageCard:       () => document.getElementById('cntPageCard'),
    pageVariance:   () => document.getElementById('cntPageVariance'),
    pageSummary:    () => document.getElementById('cntPageSummary'),
    // Kart
    productName:    () => document.getElementById('cntProductName'),
    productFreq:    () => document.getElementById('cntProductFreq'),
    productHint:    () => document.getElementById('cntProductHint'),
    qtyInput:       () => document.getElementById('cntQtyInput'),
    unitLabel:      () => document.getElementById('cntUnitLabel'),
    // Fark
    varProduct:     () => document.getElementById('cntVarianceProduct'),
    varTheoretical: () => document.getElementById('cntVarianceTheoretical'),
    varCounted:     () => document.getElementById('cntVarianceCounted'),
    varCountedBox:  () => document.getElementById('cntVarianceCountedBox'),
    varDiff:        () => document.getElementById('cntVarianceDiff'),
    varDiffIcon:    () => document.getElementById('cntVarianceDiffIcon'),
    varDiffText:    () => document.getElementById('cntVarianceDiffText'),
    varOk:          () => document.getElementById('cntVarianceOk'),
    reasonPicker:   () => document.getElementById('cntReasonPicker'),
    // Özet
    summaryDate:    () => document.getElementById('cntSummaryDate'),
    summaryList:    () => document.getElementById('cntSummaryList'),
    totalShortage:  () => document.getElementById('cntTotalShortage'),
    totalSurplus:   () => document.getElementById('cntTotalSurplus'),
    approvalWarn:   () => document.getElementById('cntApprovalWarn'),
    // Footer butonlar
    footer:         () => document.getElementById('cntFooter'),
    btnBack:        () => document.getElementById('cntBtnBack'),
    btnSkip:        () => document.getElementById('cntBtnSkip'),
    btnNext:        () => document.getElementById('cntBtnNext'),
    btnConfirm:     () => document.getElementById('cntBtnConfirm'),
    btnResume:      () => document.getElementById('btnResumeCount'),
    resumeWrap:     () => document.getElementById('cntResumeWrap'),
    resumeProgress: () => document.getElementById('cntResumeProgress'),
  };

  // ── Sayfa geçişleri ─────────────────────────
  function showPage(page) {
    ['pageStart','pageCard','pageVariance','pageSummary'].forEach(p => {
      el[p]().classList.toggle('d-none', p !== page);
    });
  }

  function setFooterButtons(config) {
    // config: { back, skip, next, confirm }
    el.btnBack().classList.toggle('d-none',    !config.back);
    el.btnSkip().classList.toggle('d-none',    !config.skip);
    el.btnNext().classList.toggle('d-none',    !config.next);
    el.btnConfirm().classList.toggle('d-none', !config.confirm);
  }

  function updateProgress() {
    const total   = state.products.length;
    const counted = state.results.length;
    const pct     = total > 0 ? Math.round((counted / total) * 100) : 0;
    el.progressBar().style.width   = pct + '%';
    el.progressLabel().textContent = `${counted} / ${total} ürün`;
    el.progressPct().textContent   = pct + '%';
    el.progressWrap().classList.remove('d-none');
  }

  // ── Başlatma ────────────────────────────────
  function open() {
    state.bsModal = state.bsModal || new bootstrap.Modal(el.modal());
    state.phase   = 'idle';
    state.results = [];
    state.currentIdx = 0;

    showPage('pageStart');
    setFooterButtons({});
    el.progressWrap().classList.add('d-none');

    // Draft kontrolü
    checkForDraft();

    state.bsModal.show();
  }

  async function checkForDraft() {
    try {
      const locationId = window.MUD_USER.location_id;
      const draft      = await Api.get(`stock/count-draft?location_id=${locationId}`);

      if (draft && draft.count_id && draft.progress.counted > 0) {
        el.resumeWrap().classList.remove('d-none');
        el.resumeProgress().textContent = `${draft.progress.counted}/${draft.progress.total}`;
        el.btnResume().dataset.countId  = draft.count_id;
        // Sayılmış ürünleri results'a yükle
        state.countId   = draft.count_id;
        state.products  = draft.products || [];
      }
    } catch { /* Offline veya hata — yeni sayıma başla */ }
  }

  // ── Yeni sayım başlat ───────────────────────
  async function startNewCount() {
    state.phase = 'loading';
    el.pageStart().innerHTML = `<div class="text-center py-5">
      <div class="spinner-border text-dark mb-3"></div>
      <p class="text-muted">Ürün listesi hazırlanıyor…</p>
    </div>`;

    try {
      const locationId = window.MUD_USER.location_id;
      const draft      = await Api.get(`stock/count-draft?location_id=${locationId}`);
      state.countId    = draft.count_id;
      state.products   = draft.products || [];
      state.results    = [];
      state.currentIdx = 0;

      if (!state.products.length) {
        showPage('pageStart');
        el.pageStart().innerHTML = `<div class="text-center py-4 text-muted">
          <i class="bi bi-inbox display-4 d-block mb-2"></i>
          Bugün sayılacak ürün bulunmuyor.
        </div>`;
        return;
      }

      goToCountCard(0);
    } catch (e) {
      Toast.error('Sayım başlatılamadı: ' + e.message);
      showPage('pageStart');
    }
  }

  // ── Ürün kartına git ────────────────────────
  function goToCountCard(index) {
    if (index >= state.products.length) { goToSummary(); return; }

    state.phase      = 'counting';
    state.currentIdx = index;
    const product    = state.products[index];

    showPage('pageCard');
    el.productName().textContent = product.name;
    el.productFreq().textContent = FREQ_LABEL[product.count_frequency] || '';
    el.unitLabel().textContent   = product.unit;

    // unit sınıfı tüm .cnt-unit elementlerine
    document.querySelectorAll('.cnt-unit').forEach(u => u.textContent = product.unit);

    el.qtyInput().value = '';
    el.qtyInput().focus();

    updateProgress();
    setFooterButtons({ skip: true, next: true });

    // Enter → İleri
    el.qtyInput().onkeydown = (e) => { if (e.key === 'Enter') handleNext(); };
  }

  // ── "İleri" tıklandı ────────────────────────
  async function handleNext() {
    const rawQty = el.qtyInput().value.trim();

    if (rawQty === '') {
      // Boş = değişiklik yok, geç
      handleSkip();
      return;
    }

    const qty = parseFloat(rawQty);
    if (isNaN(qty) || qty < 0) { Toast.warn('Geçerli bir miktar girin.'); return; }

    state.phase = 'variance';
    setFooterButtons({});

    try {
      // Draft'a kaydet — API theoretical_qty dahil variances döner
      const result = await Api.post('stock/count-draft', {
        count_id:    state.countId,
        product_id:  state.products[state.currentIdx].id,
        counted_qty: qty,
        variance_reason: 'unknown', // henüz seçilmedi
      });

      state.currentItem = result;
      showVariancePage(result, qty);

    } catch (e) {
      // Offline: localStorage'a kaydet
      const product = state.products[state.currentIdx];
      const localItem = {
        productId: product.id, name: product.name, unit: product.unit,
        counted: qty, theoretical: null, variance: null, varValue: null, reason: 'unknown',
      };
      saveDraftLocally(localItem);
      state.results.push(localItem);
      goToCountCard(state.currentIdx + 1);
      Toast.warn(`${product.name} çevrimdışı kaydedildi.`);
    }
  }

  // ── "Geç" tıklandı ──────────────────────────
  function handleSkip() {
    const product = state.products[state.currentIdx];
    // Teorik ile aynı say (değişiklik yok) — API'ya gönderme
    state.results.push({
      productId: product.id, name: product.name, unit: product.unit,
      counted: null, theoretical: null, variance: 0, varValue: 0, reason: null,
      skipped: true,
    });
    goToCountCard(state.currentIdx + 1);
  }

  // ── Fark sayfası ────────────────────────────
  function showVariancePage(result, countedQty) {
    showPage('pageVariance');

    const variance = result.variance ?? 0;
    const unit     = result.unit;

    el.varProduct().textContent     = result.product_name;
    el.varTheoretical().textContent = fmtNum(result.theoretical_qty) + ' ' + unit;
    el.varCounted().textContent     = fmtNum(countedQty) + ' ' + unit;
    document.querySelectorAll('.cnt-unit').forEach(u => u.textContent = unit);

    if (Math.abs(variance) < 0.001) {
      // Eşleşiyor
      el.varDiff().classList.add('d-none');
      el.varOk().classList.remove('d-none');
      el.reasonPicker().classList.add('d-none');
      setFooterButtons({});

      // 1.2 sn sonra otomatik ilerle
      setTimeout(() => {
        state.results.push({
          productId: result.product_id, name: result.product_name,
          unit, counted: countedQty, theoretical: result.theoretical_qty,
          variance: 0, varValue: 0, reason: null,
        });
        goToCountCard(state.currentIdx + 1);
      }, 1200);

    } else {
      // Fark var
      el.varOk().classList.add('d-none');
      el.varDiff().classList.remove('d-none');

      const shortage = variance < 0;
      el.varDiff().className      = `cnt-diff-badge mb-4 ${shortage ? 'shortage' : 'surplus'}`;
      el.varDiffIcon().className  = `bi ${shortage ? 'bi-arrow-down-circle-fill' : 'bi-arrow-up-circle-fill'}`;
      el.varDiffText().textContent =
        (shortage ? '↓ Açık: ' : '↑ Fazla: ') +
        fmtNum(Math.abs(variance)) + ' ' + unit +
        ' (₺' + fmtNum(result.variance_value ?? 0) + ')';

      el.varCountedBox().classList.toggle('border-danger', shortage);
      el.varCountedBox().classList.toggle('border-success', !shortage);

      el.reasonPicker().classList.remove('d-none');
      setFooterButtons({});
    }
  }

  // ── Neden seçildi ───────────────────────────
  async function handleReasonSelect(reason) {
    const result = state.currentItem;
    if (!result) return;

    // Reason'ı API'ya güncelle
    try {
      await Api.post('stock/count-draft', {
        count_id:        state.countId,
        product_id:      result.product_id,
        counted_qty:     result.counted_qty ?? parseFloat(el.qtyInput().value),
        variance_reason: reason,
      });
    } catch { /* offline, ignore */ }

    state.results.push({
      productId:   result.product_id,
      name:        result.product_name,
      unit:        result.unit,
      counted:     result.counted_qty,
      theoretical: result.theoretical_qty,
      variance:    result.variance,
      varValue:    result.variance_value,
      reason,
    });

    state.currentItem = null;
    goToCountCard(state.currentIdx + 1);
  }

  // ── Özet sayfası ────────────────────────────
  function goToSummary() {
    state.phase = 'summary';
    showPage('pageSummary');
    el.progressWrap().classList.add('d-none');
    setFooterButtons({ confirm: true });

    const today = new Date().toLocaleDateString('tr-TR', { day:'numeric', month:'long', year:'numeric' });
    el.summaryDate().textContent = today;

    let totalShortage = 0, totalSurplus = 0;
    const rows = state.results
      .filter(r => !r.skipped && r.variance !== 0 && r.variance !== null)
      .map(r => {
        if (r.variance < 0) totalShortage += (r.varValue ?? 0);
        else                totalSurplus  += (r.varValue ?? 0);

        const cls = r.variance < 0 ? 'text-danger' : 'text-success';
        const icon = r.variance < 0 ? 'bi-arrow-down' : 'bi-arrow-up';
        const reasonLabel = {
          entry_error: 'Giriş Hatası', natural_shrinkage: 'Fire/Kuruma',
          theft_suspected: 'Hırsızlık Şüphesi', unknown: 'Bilinmiyor', normal_waste: 'Fire',
        }[r.reason] || '—';

        return `
          <div class="recent-item">
            <i class="bi ${icon} ${cls} me-1 flex-shrink-0"></i>
            <div class="flex-fill">
              <div class="fw-semibold" style="font-size:.84rem">${escHtml(r.name)}</div>
              <div class="text-muted" style="font-size:.72rem">${reasonLabel}</div>
            </div>
            <div class="text-end flex-shrink-0">
              <div class="${cls} fw-bold" style="font-size:.84rem">${fmtNum(r.variance)} ${escHtml(r.unit)}</div>
              <div class="text-muted" style="font-size:.72rem">₺${fmtNum(r.varValue ?? 0)}</div>
            </div>
          </div>`;
      });

    el.summaryList().innerHTML = rows.length
      ? rows.join('')
      : '<div class="text-center py-3 text-muted small"><i class="bi bi-check-circle-fill text-success me-1"></i>Tüm ürünler eşleşiyor!</div>';

    el.totalShortage().textContent = '₺' + fmtNum(totalShortage);
    el.totalSurplus().textContent  = '₺' + fmtNum(totalSurplus);

    const totalVariance = totalShortage + totalSurplus;
    el.approvalWarn().classList.toggle('d-none', totalVariance <= 500);
  }

  // ── Onayla ve kaydet ────────────────────────
  async function confirmAndSave() {
    if (!state.countId) { Toast.error('Sayım ID bulunamadı.'); return; }
    state.phase = 'saving';

    const btn = el.btnConfirm();
    btn.disabled = true;
    btn.querySelector('.btn-text').classList.add('d-none');
    btn.querySelector('.btn-loading').classList.remove('d-none');

    try {
      const result = await Api.post('stock/count-confirm', { count_id: state.countId });
      state.bsModal.hide();
      localStorage.removeItem(DRAFT_KEY);

      if (result.needs_approval) {
        Toast.warn(`Sayım kaydedildi. Toplam fark ₺${fmtNum(result.total_variance_value)} — Müdür onayı bekleniyor.`);
      } else {
        Toast.success(`Sayım tamamlandı! ${result.adjustments.filter(a => a.type !== 'none').length} ürün düzeltildi.`);
      }
      Dashboard.refresh();
    } catch (e) {
      Toast.error('Kayıt hatası: ' + e.message);
      btn.disabled = false;
      btn.querySelector('.btn-text').classList.remove('d-none');
      btn.querySelector('.btn-loading').classList.add('d-none');
    }
  }

  // ── Offline draft yerel kayıt ───────────────
  function saveDraftLocally(item) {
    const draft = JSON.parse(localStorage.getItem(DRAFT_KEY) || '[]');
    draft.push(item);
    localStorage.setItem(DRAFT_KEY, JSON.stringify(draft));
  }

  // ── Format yardımcı ─────────────────────────
  function fmtNum(n) {
    const num = parseFloat(n ?? 0);
    return num.toLocaleString('tr-TR', { maximumFractionDigits: 2 });
  }

  // ── Event listener'lar ──────────────────────
  function bindEvents() {
    document.getElementById('btnStartNewCount').addEventListener('click', startNewCount);

    document.getElementById('btnResumeCount').addEventListener('click', async () => {
      // Draft ürünleri tekrar yükle, sayılmayanlardan devam et
      const locationId = window.MUD_USER.location_id;
      const draft = await Api.get(`stock/count-draft?location_id=${locationId}`);
      state.countId  = draft.count_id;
      state.products = draft.products || [];
      // Sayılmış olanları bul
      const countedIds = new Set(
        (draft.products || [])
          .filter(p => (p.counted_qty ?? -1) >= 0)
          .map(p => p.id)
      );
      // İlk sayılmamış ürüne git
      const firstUncounted = state.products.findIndex(p => !countedIds.has(p.id));
      state.currentIdx = firstUncounted >= 0 ? firstUncounted : 0;
      goToCountCard(state.currentIdx);
    });

    el.btnSkip().addEventListener('click', handleSkip);
    el.btnNext().addEventListener('click', handleNext);
    el.btnConfirm().addEventListener('click', confirmAndSave);

    // ±0.1 stepper — basılı tutunca tekrar eder
    holdRepeat(document.querySelector('.cnt-qty-dec'), () => {
      const v = parseFloat(el.qtyInput().value || '0');
      el.qtyInput().value = Math.max(0, Math.round((v - 0.1) * 100) / 100);
    });
    holdRepeat(document.querySelector('.cnt-qty-inc'), () => {
      const v = parseFloat(el.qtyInput().value || '0');
      el.qtyInput().value = Math.round((v + 0.1) * 100) / 100;
    });

    // Neden seçimi
    document.getElementById('cntPageVariance').addEventListener('click', (e) => {
      const btn = e.target.closest('.cnt-reason-btn');
      if (!btn) return;
      // Seçili görünüm
      document.querySelectorAll('.cnt-reason-btn').forEach(b => b.classList.remove('btn-dark','text-white'));
      btn.classList.add('btn-dark', 'text-white');
      setTimeout(() => handleReasonSelect(btn.dataset.reason), 350);
    });
  }

  return { open, bindEvents };
})();

/* ═══════════════════════════════════════════════
   YARDIMCI FONKSİYONLAR
═══════════════════════════════════════════════ */

function escHtml(str) {
  if (!str) return '';
  return String(str)
    .replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;')
    .replace(/"/g,'&quot;').replace(/'/g,'&#039;');
}

/* ═══════════════════════════════════════════════
   BOOTSTRAP MODAL ROUTER
   (dashboard.php içindeki modal butonları için)
═══════════════════════════════════════════════ */

document.addEventListener('click', (e) => {
  const btn = e.target.closest('[data-action-key]');
  if (btn && !btn.closest('#quickActions, #allActions')) {
    // Fallback — zaten QuickActions.renderButtons içinde ele alınıyor
  }
});

/* ═══════════════════════════════════════════════
   BAŞLATMA
═══════════════════════════════════════════════ */

document.addEventListener('DOMContentLoaded', () => {
  PhysicalCount.bindEvents();
  FixedAssets.bindEvents();
  FixedAssets.load();
  QuickActions.load();
  Dashboard.init();
  OfflineQueue.flush();

  // Her 2 dakikada uyarıları yenile
  setInterval(() => Alerts.load(), 2 * 60 * 1000);
  // Her 30 sn özet güncelle
  setInterval(() => Dashboard.refresh(), 30 * 1000);
});
