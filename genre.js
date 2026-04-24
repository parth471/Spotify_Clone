// ── Genre card live search ────────────────────────────────────
const searchInput = document.getElementById('genreSearch');
const cards       = document.querySelectorAll('.genre-card');
const noResults   = document.getElementById('noResults');

searchInput.addEventListener('input', function () {
    const q = this.value.toLowerCase().trim();
    let visible = 0;
    cards.forEach(card => {
        const name = (card.dataset.name || '').toLowerCase();
        const desc = (card.dataset.desc || '').toLowerCase();
        const match = q === '' || name.includes(q) || desc.includes(q);
        card.classList.toggle('hidden', !match);
        if (match) visible++;
    });
    if (noResults) noResults.classList.toggle('show', q !== '' && visible === 0);
});

// Clear search on Escape
searchInput.addEventListener('keydown', function (e) {
    if (e.key === 'Escape') { this.value = ''; this.dispatchEvent(new Event('input')); }
});

// ── Ripple on genre card click ────────────────────────────────
cards.forEach(card => {
    card.addEventListener('click', function (e) {
        const ripple = document.createElement('span');
        ripple.style.cssText = `
            position:absolute;border-radius:50%;pointer-events:none;
            transform:scale(0);animation:ripple .5s ease-out forwards;
            background:rgba(255,255,255,0.25);
            width:200px;height:200px;
            left:${e.offsetX - 100}px;top:${e.offsetY - 100}px;
        `;
        this.appendChild(ripple);
        setTimeout(() => ripple.remove(), 500);
    });
});

const st = document.createElement('style');
st.textContent = '@keyframes ripple{to{transform:scale(3);opacity:0}}';
document.head.appendChild(st);

// ── Auto focus search on page load ───────────────────────────
window.addEventListener('DOMContentLoaded', () => searchInput.focus());
