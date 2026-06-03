import 'bootstrap';

// Navbar scroll effect
window.addEventListener('scroll', () => {
    const navbar = document.querySelector('.navbar-mudavim');
    if (navbar) {
        navbar.style.background = window.scrollY > 50
            ? 'rgba(30,32,35,0.98)'
            : 'rgba(30,32,35,0.92)';
    }
});

// Language switcher
document.querySelectorAll('[data-locale]').forEach(btn => {
    btn.addEventListener('click', () => {
        const locale = btn.dataset.locale;
        const url    = new URL(window.location.href);
        const parts  = url.pathname.split('/').filter(Boolean);
        const supported = ['en', 'de'];

        // Remove existing locale prefix
        if (supported.includes(parts[0])) parts.shift();
        if (locale !== 'tr') parts.unshift(locale);

        url.pathname = '/' + parts.join('/');
        window.location.href = url.toString();
    });
});
