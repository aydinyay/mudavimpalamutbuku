import 'bootstrap';

// ── Navbar scroll effect ─────────────────────────────────────────────────────
window.addEventListener('scroll', () => {
    const navbar = document.querySelector('.navbar-mudavim');
    if (navbar) {
        navbar.style.background = window.scrollY > 50
            ? 'rgba(30,32,35,0.98)'
            : 'rgba(30,32,35,0.92)';
    }
});

// ── Language switcher ────────────────────────────────────────────────────────
document.querySelectorAll('[data-locale]').forEach(btn => {
    btn.addEventListener('click', () => {
        const locale = btn.dataset.locale;
        const url    = new URL(window.location.href);
        const parts  = url.pathname.split('/').filter(Boolean);
        const supported = ['en', 'de'];

        if (supported.includes(parts[0])) parts.shift();
        if (locale !== 'tr') parts.unshift(locale);

        url.pathname = '/' + parts.join('/');
        window.location.href = url.toString();
    });
});

// ── Reservation availability check ──────────────────────────────────────────
const checkBtn = document.getElementById('checkAvailability');
if (checkBtn) {
    checkBtn.addEventListener('click', () => {
        const date  = document.querySelector('[name=reservation_date]')?.value;
        const time  = document.querySelector('[name=arrival_time]')?.value;
        const count = document.querySelector('[name=guest_count]')?.value;

        if (!date || !time || !count) {
            alert('Lütfen tarih, saat ve kişi sayısını seçin.');
            return;
        }

        checkBtn.disabled    = true;
        checkBtn.textContent = '...';

        fetch('/api/rezervasyon/uygunluk', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content,
            },
            body: JSON.stringify({ reservation_date: date, arrival_time: time, guest_count: count }),
        })
            .then(r => r.json())
            .then(data => {
                const resultDiv     = document.getElementById('availabilityResult');
                const availableMsg  = document.getElementById('availableMsg');
                const unavailableMsg= document.getElementById('unavailableMsg');
                const guestCard     = document.getElementById('guestInfoCard');

                resultDiv.classList.remove('d-none');

                if (data.available) {
                    availableMsg.style.display  = '';
                    unavailableMsg.style.display = 'none';
                    if (guestCard) guestCard.style.display = '';

                    // Pre-select table if only one option
                    if (data.table_id) {
                        const tableInput = document.querySelector('[name=table_id]');
                        if (tableInput) tableInput.value = data.table_id;
                    }
                } else {
                    availableMsg.style.display  = 'none';
                    unavailableMsg.style.display = '';
                    if (guestCard) guestCard.style.display = 'none';
                }
            })
            .catch(() => alert('Sunucuya bağlanılamadı, lütfen tekrar deneyin.'))
            .finally(() => {
                checkBtn.disabled    = false;
                checkBtn.textContent = checkBtn.dataset.label || 'Uygunluk Kontrol Et';
            });
    });
}
