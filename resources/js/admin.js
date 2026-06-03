import 'bootstrap';
import Sortable from 'sortablejs';
import interact from '@interactjs/interactjs';

// ── Sidebar mobile toggle ────────────────────────────────────────────────────
const sidebarToggle = document.getElementById('sidebarToggle');
const sidebar       = document.querySelector('.admin-sidebar');

if (sidebarToggle && sidebar) {
    sidebarToggle.addEventListener('click', () => sidebar.classList.toggle('open'));
    document.addEventListener('click', e => {
        if (!sidebar.contains(e.target) && !sidebarToggle.contains(e.target)) {
            sidebar.classList.remove('open');
        }
    });
}

// ── Auto-dismiss alerts ──────────────────────────────────────────────────────
document.querySelectorAll('.alert-auto-dismiss').forEach(el => {
    setTimeout(() => {
        el.classList.remove('show');
        el.addEventListener('transitionend', () => el.remove());
    }, 4000);
});

// ── Confirm delete buttons ───────────────────────────────────────────────────
document.querySelectorAll('[data-confirm-delete]').forEach(btn => {
    btn.addEventListener('click', e => {
        if (!confirm(btn.dataset.confirmDelete || 'Silmek istediğinizden emin misiniz?')) {
            e.preventDefault();
        }
    });
});

// ── Table plan drag-drop ─────────────────────────────────────────────────────
document.querySelectorAll('.table-plan-draggable').forEach(el => {
    const areaEl = el.closest('[data-area-id]');
    if (!areaEl) return;

    let x = parseFloat(el.dataset.x) || 0;
    let y = parseFloat(el.dataset.y) || 0;
    el.style.transform = `translate(${x}px, ${y}px)`;

    interact(el).draggable({
        inertia: false,
        modifiers: [
            interact.modifiers.restrictRect({ restriction: 'parent', endOnly: true }),
        ],
        listeners: {
            move(event) {
                x += event.dx;
                y += event.dy;
                event.target.style.transform = `translate(${x}px, ${y}px)`;
            },
            end(event) {
                const tableId = event.target.dataset.tableId;
                const rect    = areaEl.getBoundingClientRect();
                const posX    = Math.round(x);
                const posY    = Math.round(y);

                fetch(`/yonetim/masalar/${tableId}/pozisyon`, {
                    method: 'PATCH',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content,
                    },
                    body: JSON.stringify({ pos_x: posX, pos_y: posY }),
                }).catch(() => {});
            },
        },
    });
});

// ── Category sort (Sortable.js) ──────────────────────────────────────────────
const catList = document.getElementById('sortable-categories');
if (catList) {
    Sortable.create(catList, {
        animation: 150,
        handle: '.drag-handle',
        onEnd() {
            const order = [...catList.querySelectorAll('[data-id]')].map(el => el.dataset.id);
            fetch('/yonetim/menu/kategoriler/sirala', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content,
                },
                body: JSON.stringify({ order }),
            }).catch(() => {});
        },
    });
}

// ── Menu item toggle available ───────────────────────────────────────────────
window.toggleAvailable = function (itemId, btn) {
    fetch(`/yonetim/menu/urunler/${itemId}/toggle-available`, {
        method: 'PATCH',
        headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content },
    })
        .then(r => r.json())
        .then(data => {
            if (data.is_available) {
                btn.textContent = 'Mevcut';
                btn.className   = 'btn btn-sm btn-success';
            } else {
                btn.textContent = 'Tükendi';
                btn.className   = 'btn btn-sm btn-secondary';
            }
            btn.style.borderRadius = '12px';
            btn.style.fontSize     = '0.75rem';
        })
        .catch(() => {});
};

// ── Reservation auto-refresh (60s) ──────────────────────────────────────────
if (document.getElementById('reservation-board')) {
    setInterval(() => window.location.reload(), 60000);
}
