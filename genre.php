<?php
session_start();
if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    header("Location: login.html"); exit();
}
$user_email = htmlspecialchars($_SESSION['user_email']);

$conn = new mysqli("localhost","root","","spotify_db");
if ($conn->connect_error) die("DB error: " . $conn->connect_error);
$genres = $conn->query("SELECT * FROM genres ORDER BY id");
$conn->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Browse Genres — Spotify</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">
<link rel="stylesheet" href="genre.css">
<style>
/* Search bar upgrade */
.search-bar { position:relative; }
.search-clear {
    position:absolute; right:14px; top:50%; transform:translateY(-50%);
    background:none; border:none; color:#666; font-size:18px;
    cursor:pointer; display:none; line-height:1;
}
.search-bar input:not(:placeholder-shown) ~ .search-clear { display:block; }
.search-hint {
    font-size:13px; color:#555; margin-top:-20px; margin-bottom:28px;
    padding:0 4px;
}
</style>
</head>
<body>

<!-- Sidebar -->
<aside class="sidebar">
    <div class="sidebar-logo">
        <svg viewBox="0 0 24 24" fill="currentColor" width="28" height="28">
            <path d="M12 0C5.4 0 0 5.4 0 12s5.4 12 12 12 12-5.4 12-12S18.66 0 12 0zm5.521 17.34c-.24.359-.66.48-1.021.24-2.82-1.74-6.36-2.101-10.561-1.141-.418.122-.779-.179-.899-.539-.12-.421.18-.78.54-.9 4.56-1.021 8.52-.6 11.64 1.32.42.18.479.659.301 1.02zm1.44-3.3c-.301.42-.841.6-1.262.3-3.239-1.98-8.159-2.58-11.939-1.38-.479.12-1.02-.12-1.14-.6-.12-.48.12-1.021.6-1.141C9.6 9.9 15 10.561 18.72 12.84c.361.181.54.78.241 1.2zm.12-3.36C15.24 8.4 8.82 8.16 5.16 9.301c-.6.179-1.2-.181-1.38-.721-.18-.601.18-1.2.72-1.381 4.26-1.26 11.28-1.02 15.721 1.621.539.3.719 1.02.419 1.56-.299.421-1.02.599-1.559.3z"/>
        </svg>
        <span>Spotify</span>
    </div>
    <nav class="sidebar-nav">
        <a href="home.php" class="nav-item">
            <svg width="20" height="20" viewBox="0 0 24 24" fill="currentColor"><path d="M10 20v-6h4v6h5v-8h3L12 3 2 12h3v8z"/></svg> Home
        </a>
        <a href="genre.php" class="nav-item active">
            <svg width="20" height="20" viewBox="0 0 24 24" fill="currentColor"><path d="M15.5 14h-.79l-.28-.27A6.471 6.471 0 0 0 16 9.5 6.5 6.5 0 1 0 9.5 16c1.61 0 3.09-.59 4.23-1.57l.27.28v.79l5 4.99L20.49 19l-4.99-5zm-6 0C7.01 14 5 11.99 5 9.5S7.01 5 9.5 5 14 7.01 14 9.5 11.99 14 9.5 14z"/></svg> Browse
        </a>
        <a href="home.php" class="nav-item">
            <svg width="20" height="20" viewBox="0 0 24 24" fill="currentColor"><path d="M20 2H4c-1.1 0-2 .9-2 2v18l4-4h14c1.1 0 2-.9 2-2V4c0-1.1-.9-2-2-2z"/></svg> Your Library
        </a>
    </nav>
    <div class="sidebar-section-title">Playlists</div>
    <div class="sidebar-playlists">
        <div class="playlist-item">Liked Songs</div>
        <div class="playlist-item">Chill Vibes</div>
        <div class="playlist-item">Top Hits</div>
    </div>
    <div class="sidebar-user">
        <div class="user-avatar"><?= strtoupper(substr($user_email,0,1)) ?></div>
        <div class="user-info">
            <span class="user-name"><?= $user_email ?></span>
            <a href="logout.php" class="logout-link">Log out</a>
        </div>
    </div>
</aside>

<!-- Main -->
<main class="main-content">
    <header class="topbar">
        <div class="topbar-left">
            <button class="nav-arrow" onclick="history.back()">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="currentColor"><path d="M20 11H7.83l5.59-5.59L12 4l-8 8 8 8 1.41-1.41L7.83 13H20v-2z"/></svg>
            </button>
        </div>
        <div class="search-bar">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="currentColor"><path d="M15.5 14h-.79l-.28-.27A6.471 6.471 0 0 0 16 9.5 6.5 6.5 0 1 0 9.5 16c1.61 0 3.09-.59 4.23-1.57l.27.28v.79l5 4.99L20.49 19l-4.99-5zm-6 0C7.01 14 5 11.99 5 9.5S7.01 5 9.5 5 14 7.01 14 9.5 11.99 14 9.5 14z"/></svg>
            <input type="text" placeholder="Search genres…" id="genreSearch" autocomplete="off">
        </div>
    </header>

    <div class="page-body">
        <div class="page-hero">
            <h1>Browse All</h1>
            <p>Pick a genre and dive into the music you love</p>
        </div>
        <p class="search-hint" id="searchHint" style="display:none">
            Showing results for "<span id="hintQ"></span>"
        </p>

        <div class="genre-grid" id="genreGrid">
            <?php while ($g = $genres->fetch_assoc()): ?>
            <a href="tracks.php?genre=<?= $g['slug'] ?>"
               class="genre-card"
               style="--c1:<?= $g['color1'] ?>;--c2:<?= $g['color2'] ?>"
               data-name="<?= strtolower($g['name']) ?>"
               data-desc="<?= strtolower($g['description']) ?>">
                <div class="genre-bg"></div>
                <div class="genre-emoji"><?= $g['emoji'] ?></div>
                <div class="genre-info">
                    <h3><?= $g['name'] ?></h3>
                    <p><?= $g['description'] ?></p>
                </div>
                <div class="genre-arrow">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="currentColor"><path d="M8.59 16.59L13.17 12 8.59 7.41 10 6l6 6-6 6z"/></svg>
                </div>
            </a>
            <?php endwhile; ?>
        </div>

        <div class="no-results-msg" id="noResults">
            🔍 No genres found for that search.<br>
            <small>Try: Pop, Rock, Jazz, Hip-Hop, Electronic…</small>
        </div>

    </div>
</main>

<script src="genre.js"></script>
<script>
// Sync hint text with search
document.getElementById('genreSearch').addEventListener('input', function () {
    const hint = document.getElementById('searchHint');
    const hintQ = document.getElementById('hintQ');
    if (this.value.trim()) {
        hint.style.display = 'block';
        hintQ.textContent = this.value.trim();
    } else {
        hint.style.display = 'none';
    }
});
</script>
</body>
</html>
