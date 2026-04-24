// ── Player ────────────────────────────────────────────────────
const playerBar    = document.getElementById('playerBar');
const playerImg    = document.getElementById('playerImg');
const playerTitle  = document.getElementById('playerTitle');
const playerArtist = document.getElementById('playerArtist');
const playPauseBtn = document.getElementById('playPauseBtn');
const heroPlayBtn  = document.getElementById('heroPlayBtn');

let currentRow = null;
let isPlaying  = false;

const iconPlay  = `<svg width="18" height="18" viewBox="0 0 24 24" fill="currentColor"><path d="M8 5v14l11-7z"/></svg>`;
const iconPause = `<svg width="18" height="18" viewBox="0 0 24 24" fill="currentColor"><path d="M6 19h4V5H6v14zm8-14v14h4V5h-4z"/></svg>`;

function loadTrack(imgSrc, title, artist, rowEl) {
    playerImg.src = imgSrc;
    playerTitle.textContent  = title;
    playerArtist.textContent = artist;
    playerBar.classList.add('visible');
    if (currentRow) currentRow.classList.remove('playing');
    currentRow = rowEl;
    if (currentRow) currentRow.classList.add('playing');
    isPlaying = true;
    playPauseBtn.innerHTML = iconPause;
}

function togglePlay() {
    isPlaying = !isPlaying;
    playPauseBtn.innerHTML = isPlaying ? iconPause : iconPlay;
}
playPauseBtn.addEventListener('click', togglePlay);

// Space bar
document.addEventListener('keydown', e => {
    if (e.code === 'Space' && e.target.tagName !== 'INPUT') {
        e.preventDefault();
        if (playerBar.classList.contains('visible')) togglePlay();
    }
});

// ── Table row clicks ──────────────────────────────────────────
function attachTableRows() {
    document.querySelectorAll('.track-row').forEach(row => {
        row.addEventListener('click', function () {
            const img    = this.querySelector('.table-track img');
            const title  = this.querySelector('.table-track strong').textContent;
            const artist = this.querySelector('.col-artist').textContent;
            loadTrack(img.src, title, artist, this);
        });
    });
}
attachTableRows();

// ── Card clicks ───────────────────────────────────────────────
document.querySelectorAll('.track-card').forEach(card => {
    card.addEventListener('click', function () {
        const img    = this.querySelector('.track-card-img img');
        const title  = this.querySelector('.track-card-info h4').textContent;
        const artist = this.querySelector('.track-card-info p').textContent;
        loadTrack(img.src, title, artist, null);
    });
});

// ── Hero play ─────────────────────────────────────────────────
heroPlayBtn.addEventListener('click', () => {
    const firstVisible = document.querySelector('.track-row:not(.hidden)');
    if (firstVisible) firstVisible.click();
});

// ══════════════════════════════════════════════════════════════
// ★ SONG SEARCH (live filter on table rows) ★
// ══════════════════════════════════════════════════════════════
const songSearch   = document.getElementById('songSearch');
const searchCount  = document.getElementById('searchCount');
const noSongResults= document.getElementById('noSongResults');
const allRows      = Array.from(document.querySelectorAll('.track-row'));
const totalCount   = allRows.length;

function filterSongs() {
    const q = songSearch.value.toLowerCase().trim();
    let visible = 0;
    allRows.forEach(row => {
        const title  = row.dataset.title  || '';
        const artist = row.dataset.artist || '';
        const match  = q === '' || title.includes(q) || artist.includes(q);
        row.classList.toggle('hidden', !match);
        if (match) visible++;
    });
    searchCount.textContent = q === '' ? `${totalCount} songs` : `${visible} of ${totalCount} songs`;
    noSongResults.classList.toggle('show', q !== '' && visible === 0);
    renumberRows();
}

function renumberRows() {
    let n = 1;
    allRows.forEach(row => {
        if (!row.classList.contains('hidden')) {
            const num = row.querySelector('.row-num');
            if (num) num.textContent = n++;
        }
    });
}

songSearch.addEventListener('input', filterSongs);
songSearch.addEventListener('keydown', e => {
    if (e.key === 'Escape') { songSearch.value = ''; filterSongs(); songSearch.blur(); }
});

// ══════════════════════════════════════════════════════════════
// ★ SORT PILLS ★
// ══════════════════════════════════════════════════════════════
const tbody = document.getElementById('trackBody');

document.querySelectorAll('.sort-pill').forEach(pill => {
    pill.addEventListener('click', function () {
        document.querySelectorAll('.sort-pill').forEach(p => p.classList.remove('active'));
        this.classList.add('active');

        const sortKey = this.dataset.sort;
        const rows = Array.from(tbody.querySelectorAll('.track-row'));

        rows.sort((a, b) => {
            if (sortKey === 'title')  return a.dataset.title.localeCompare(b.dataset.title);
            if (sortKey === 'artist') return a.dataset.artist.localeCompare(b.dataset.artist);
            if (sortKey === 'year')   return parseInt(b.dataset.year)  - parseInt(a.dataset.year);
            if (sortKey === 'views')  return parseInt(b.dataset.views) - parseInt(a.dataset.views);
        });

        rows.forEach(r => tbody.appendChild(r));
        renumberRows();
        // re-apply current search filter after sort
        filterSongs();
    });
});

// ── Stagger card animation ────────────────────────────────────
const observer = new IntersectionObserver(entries => {
    entries.forEach(entry => {
        if (entry.isIntersecting) {
            entry.target.style.animation =
                `cardIn .4s ${entry.target.dataset.delay || '0s'} cubic-bezier(.22,1,.36,1) both`;
            observer.unobserve(entry.target);
        }
    });
}, { threshold: .1 });

document.querySelectorAll('.track-card').forEach((card, i) => {
    card.dataset.delay = (i * 0.05) + 's';
    card.style.opacity = '0';
    observer.observe(card);
});

const cardStyle = document.createElement('style');
cardStyle.textContent = `@keyframes cardIn{from{opacity:0;transform:translateY(16px)}to{opacity:1;transform:none}}`;
document.head.appendChild(cardStyle);

// auto-focus song search
window.addEventListener('DOMContentLoaded', () => {
    // don't steal focus from top of page – just select on scroll
});
