import 'bootstrap';

// Sidebar mobile toggle
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

// Auto-dismiss alerts after 4s
document.querySelectorAll('.alert-auto-dismiss').forEach(el => {
    setTimeout(() => {
        el.classList.remove('show');
        el.addEventListener('transitionend', () => el.remove());
    }, 4000);
});

// Confirm delete buttons
document.querySelectorAll('[data-confirm-delete]').forEach(btn => {
    btn.addEventListener('click', e => {
        if (!confirm(btn.dataset.confirmDelete || 'Silmek istediğinizden emin misiniz?')) {
            e.preventDefault();
        }
    });
});
