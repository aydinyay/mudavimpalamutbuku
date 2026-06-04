import 'bootstrap';

// ── Search ───────────────────────────────────────────────────────────────────
const searchInput = document.getElementById('menuSearch');
const noResults   = document.getElementById('noResults');

if (searchInput) {
    searchInput.addEventListener('input', () => {
        const q = searchInput.value.trim().toLowerCase();
        let anyVisible = false;

        document.querySelectorAll('.category-section').forEach(section => {
            let sectionHasMatch = false;
            section.querySelectorAll('.menu-item').forEach(item => {
                const name = item.dataset.name || '';
                const match = !q || name.includes(q);
                item.classList.toggle('search-hidden', !match);
                if (match) sectionHasMatch = true;
            });
            section.classList.toggle('search-hidden', !sectionHasMatch);
            if (sectionHasMatch) anyVisible = true;
        });

        if (noResults) noResults.style.display = anyVisible || !q ? 'none' : 'block';
    });
}

// ── Allergen filter ──────────────────────────────────────────────────────────
const excludedAllergens = new Set(
    JSON.parse(localStorage.getItem('excludedAllergens') || '[]')
);

function applyAllergenFilter() {
    document.querySelectorAll('.menu-item[data-allergens]').forEach(card => {
        const allergens = JSON.parse(card.dataset.allergens || '[]');
        const blocked   = allergens.some(a => excludedAllergens.has(a));
        card.style.display = blocked ? 'none' : '';
    });

    // Sync chip styles
    document.querySelectorAll('.allergen-chip').forEach(chip => {
        chip.classList.toggle('active-exclude', excludedAllergens.has(chip.dataset.allergen));
    });

    localStorage.setItem('excludedAllergens', JSON.stringify([...excludedAllergens]));
}

document.querySelectorAll('.allergen-chip').forEach(chip => {
    chip.addEventListener('click', () => {
        const a = chip.dataset.allergen;
        excludedAllergens.has(a) ? excludedAllergens.delete(a) : excludedAllergens.add(a);
        applyAllergenFilter();
    });
});

applyAllergenFilter();

// ── Category nav highlight on scroll ────────────────────────────────────────
const sections = document.querySelectorAll('.category-section[id]');
const navPills  = document.querySelectorAll('.category-nav .nav-pill');

const observer = new IntersectionObserver(entries => {
    entries.forEach(entry => {
        if (entry.isIntersecting) {
            navPills.forEach(p => p.classList.remove('active'));
            const active = document.querySelector(`.nav-pill[href="#${entry.target.id}"]`);
            if (active) {
                active.classList.add('active');
                active.scrollIntoView({ behavior: 'smooth', block: 'nearest', inline: 'center' });
            }
        }
    });
}, { rootMargin: '-30% 0px -60% 0px' });

sections.forEach(s => observer.observe(s));

// ── Language switcher ────────────────────────────────────────────────────────
document.querySelectorAll('.locale-btn[data-locale]').forEach(btn => {
    btn.addEventListener('click', () => {
        const locale    = btn.dataset.locale;
        const tableCode = document.body.dataset.tableCode || '';
        const supported = ['en', 'de'];
        const base      = tableCode ? `/menu/masa/${tableCode}` : '/menu';
        const url       = locale === 'tr' ? base : `/${locale}${base}`;
        window.location.href = url;
    });
});

// ── Scan counter (fire and forget) ──────────────────────────────────────────
const scanMeta = document.querySelector('meta[name="qr-uuid"]');
if (scanMeta) {
    fetch(`/api/qr-scan/${scanMeta.content}`, { method: 'POST' }).catch(() => {});
}
