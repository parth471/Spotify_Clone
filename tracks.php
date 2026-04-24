<?php
session_start();
$isArtist = isset($_SESSION['role']) && $_SESSION['role'] === 'artist';
if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    header("Location: login.html"); exit();
}
$user_email = htmlspecialchars($_SESSION['user_email']);

$slug = preg_replace('/[^a-z0-9]/', '', strtolower($_GET['genre'] ?? ''));
if (empty($slug)) { header("Location: genre.php"); exit(); }

$conn = new mysqli("localhost","root","","spotify_db");
if ($conn->connect_error) die("DB error: " . $conn->connect_error);

// Genre info
$gs = $conn->prepare("SELECT * FROM genres WHERE slug=? LIMIT 1");
$gs->bind_param("s",$slug); $gs->execute();
$genre = $gs->get_result()->fetch_assoc(); $gs->close();
if (!$genre) { header("Location: genre.php"); exit(); }

// Most played (top 6 cards)
$mv = $conn->prepare("SELECT * FROM songs WHERE genre_slug=? ORDER BY views DESC LIMIT 6");
$mv->bind_param("s",$slug); $mv->execute();
$most_viewed = $mv->get_result()->fetch_all(MYSQLI_ASSOC); $mv->close();

// Trending cards
$tr = $conn->prepare("SELECT * FROM songs WHERE genre_slug=? AND trending=1 ORDER BY views DESC LIMIT 6");
$tr->bind_param("s",$slug); $tr->execute();
$trending = $tr->get_result()->fetch_all(MYSQLI_ASSOC); $tr->close();

// ALL tracks for the table + live search
$al = $conn->prepare("SELECT * FROM songs WHERE genre_slug=? ORDER BY views DESC");
$al->bind_param("s",$slug); $al->execute();
$all_tracks = $al->get_result()->fetch_all(MYSQLI_ASSOC); $al->close();

$conn->close();

function fmt_views($n) {
    if ($n >= 1000000) return number_format($n/1000000,1).'M';
    if ($n >= 1000)    return number_format($n/1000,1).'K';
    return $n;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title><?= htmlspecialchars($genre['name']) ?> — Spotify</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">
<link rel="stylesheet" href="genre.css">
<link rel="stylesheet" href="tracks.css">
<style>
/* ── Song search bar ── */
.song-search-wrap {
    display: flex;
    align-items: center;
    gap: 12px;
    margin-bottom: 24px;
}
.song-search-bar {
    display: flex;
    align-items: center;
    gap: 10px;
    background: rgba(255,255,255,0.07);
    border: 1.5px solid rgba(255,255,255,0.1);
    border-radius: 50px;
    padding: 11px 20px;
    flex: 1;
    max-width: 460px;
    transition: border-color .2s, background .2s;
}
.song-search-bar:focus-within {
    border-color: #1DB954;
    background: rgba(255,255,255,0.1);
}
.song-search-bar svg { color: #888; flex-shrink: 0; }
.song-search-bar input {
    background: none; border: none; outline: none;
    color: #fff; font-size: 14px; font-family: 'Inter', sans-serif; width: 100%;
}
.song-search-bar input::placeholder { color: #555; }
.search-count {
    font-size: 13px; color: #666; white-space: nowrap;
}
/* hidden rows */
.track-row.hidden { display: none; }
.no-song-results {
    display: none; text-align: center;
    padding: 40px; color: #555; font-size: 15px;
}
.no-song-results.show { display: block; }
/* sort pills */
.sort-pills { display: flex; gap: 8px; flex-wrap: wrap; margin-bottom: 16px; }
.sort-pill {
    padding: 5px 14px; border-radius: 50px;
    border: 1px solid rgba(255,255,255,0.12);
    background: transparent; color: #aaa;
    font-size: 13px; font-weight: 600; cursor: pointer;
    transition: .2s; font-family: 'Inter', sans-serif;
}
.sort-pill:hover { background: rgba(255,255,255,0.08); color: #fff; }
.sort-pill.active { background: #1DB954; color: #000; border-color: #1DB954; }
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
        <button class="nav-arrow" onclick="window.location='genre.php'">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="currentColor"><path d="M20 11H7.83l5.59-5.59L12 4l-8 8 8 8 1.41-1.41L7.83 13H20v-2z"/></svg>
        </button>
        <div class="topbar-breadcrumb">
            <a href="genre.php">Browse</a>
            <svg width="14" height="14" viewBox="0 0 24 24" fill="currentColor"><path d="M8.59 16.59L13.17 12 8.59 7.41 10 6l6 6-6 6z"/></svg>
            <span><?= htmlspecialchars($genre['name']) ?></span>
        </div>
    </header>

    <div class="page-body">

        <!-- Genre Hero -->
        <div class="genre-hero" style="--c1:<?= $genre['color1'] ?>;--c2:<?= $genre['color2'] ?>">
            <div class="genre-hero-bg"></div>
            <div class="genre-hero-emoji"><?= $genre['emoji'] ?></div>
            <div class="genre-hero-content">
                <p class="genre-hero-label">Genre</p>
                <h1 class="genre-hero-title"><?= htmlspecialchars($genre['name']) ?></h1>
                <p class="genre-hero-desc"><?= htmlspecialchars($genre['description']) ?></p>
                <div class="genre-hero-meta">
                    <span><?= count($all_tracks) ?> songs</span>
                    <span>•</span>
                    <span><?= fmt_views(array_sum(array_column($all_tracks,'views'))) ?> total plays</span>
                </div>
            </div>
            <button class="hero-play-btn" id="heroPlayBtn">
                <svg width="24" height="24" viewBox="0 0 24 24" fill="currentColor"><path d="M8 5v14l11-7z"/></svg>
            </button>
        </div>

        <!-- Trending -->
        <?php if (!empty($trending)): ?>
        <section class="tracks-section">
            <div class="section-header">
                <div><h2>🔥 Trending Now</h2><p>Hot in <?= htmlspecialchars($genre['name']) ?> right now</p></div>
            </div>
            <div class="cards-row">
                <?php foreach ($trending as $i => $s): ?>
                <div class="track-card" data-id="<?= $s['id'] ?>">
                    <div class="track-card-img">
                        <img src="https://picsum.photos/seed/<?= $s['cover_seed'] ?>/300/300" alt="<?= htmlspecialchars($s['title']) ?>" loading="lazy">
                        <div class="track-card-overlay">
                            <button class="card-play-btn"><svg width="20" height="20" viewBox="0 0 24 24" fill="currentColor"><path d="M8 5v14l11-7z"/></svg></button>
                        </div>
                        <div class="trending-badge">TRENDING</div>
                    </div>
                    <div class="track-card-info">
                        <h4><?= htmlspecialchars($s['title']) ?></h4>
                        <p><?= htmlspecialchars($s['artist']) ?></p>
                        <span class="views-badge">▶ <?= fmt_views($s['views']) ?></span>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </section>
        <?php endif; ?>

        <!-- Most Played -->
        <section class="tracks-section">
            <div class="section-header">
                <div><h2>👑 Most Played</h2><p>All-time top tracks in <?= htmlspecialchars($genre['name']) ?></p></div>
            </div>
            <div class="cards-row">
                <?php foreach ($most_viewed as $i => $s): ?>
                <div class="track-card" data-id="<?= $s['id'] ?>">
                    <div class="track-card-img">
                        <img src="https://picsum.photos/seed/<?= $s['cover_seed'] ?>/300/300" alt="" loading="lazy">
                        <div class="track-card-overlay">
                            <button class="card-play-btn"><svg width="20" height="20" viewBox="0 0 24 24" fill="currentColor"><path d="M8 5v14l11-7z"/></svg></button>
                        </div>
                        <div class="rank-badge">#<?= $i+1 ?></div>
                    </div>
                    <div class="track-card-info">
                        <h4><?= htmlspecialchars($s['title']) ?></h4>
                        <p><?= htmlspecialchars($s['artist']) ?></p>
                        <span class="views-badge">▶ <?= fmt_views($s['views']) ?></span>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </section>

        <!-- All Tracks with SEARCH -->
        <div class="section-header">
    <div>
        <h2>All Tracks</h2>
        <p>Complete <?= htmlspecialchars($genre['name']) ?> catalogue</p>
    </div>
    <?php if ($isArtist): ?>
        <<button onclick="toggleUpload()" 
        style="padding:8px 16px; background:#1DB954; border:none; border-radius:20px; cursor:pointer;">
    + Upload Song
</button>
    <?php endif; ?>
</div>

<?php if ($isArtist): ?>
<div id="uploadBox" style="display:none; margin:20px 0; padding:20px; background:#181818; border-radius:10px;">

    <form action="upload_song.php" method="POST">

        <input type="hidden" name="genre_slug" value="<?= $slug ?>">

        <input type="text" name="title" placeholder="Song Title" required
               style="padding:10px; margin-right:10px;">

        <input type="text" name="artist" placeholder="Artist Name" required
               style="padding:10px; margin-right:10px;">

        <button type="submit"
                style="padding:10px 20px; background:#1DB954; border:none; cursor:pointer;">
            Add
        </button>

    </form>

</div>
<?php endif; ?>

            <!-- ★ SEARCH BAR FOR SONGS ★ -->
            <div class="song-search-wrap">
                <div class="song-search-bar">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="currentColor"><path d="M15.5 14h-.79l-.28-.27A6.471 6.471 0 0 0 16 9.5 6.5 6.5 0 1 0 9.5 16c1.61 0 3.09-.59 4.23-1.57l.27.28v.79l5 4.99L20.49 19l-4.99-5zm-6 0C7.01 14 5 11.99 5 9.5S7.01 5 9.5 5 14 7.01 14 9.5 11.99 14 9.5 14z"/></svg>
                    <input type="text" id="songSearch" placeholder="Search songs or artists in <?= htmlspecialchars($genre['name']) ?>…" autocomplete="off">
                </div>
                <span class="search-count" id="searchCount"><?= count($all_tracks) ?> songs</span>
            </div>

            <!-- Sort pills -->
            <div class="sort-pills">
                <button class="sort-pill active" data-sort="views">Most Played</button>
                <button class="sort-pill" data-sort="title">A – Z</button>
                <button class="sort-pill" data-sort="artist">By Artist</button>
                <button class="sort-pill" data-sort="year">Newest</button>
            </div>

            <div class="table-wrap">
                <table class="track-table" id="trackTable">
                    <thead>
                        <tr>
                            <th class="col-num">#</th>
                            <th class="col-title">Title</th>
                            <th class="col-artist">Artist</th>
                            <th class="col-year">Year</th>
                            <th class="col-views">Plays</th>
                            <th class="col-dur"><svg width="14" height="14" viewBox="0 0 24 24" fill="currentColor"><path d="M12 2C6.5 2 2 6.5 2 12s4.5 10 10 10 10-4.5 10-10S17.5 2 12 2zm0 18c-4.41 0-8-3.59-8-8s3.59-8 8-8 8 3.59 8 8-3.59 8-8 8zm.5-13H11v6l5.25 3.15.75-1.23-4.5-2.67V7z"/></svg></th>
                        </tr>
                    </thead>
                    <tbody id="trackBody">
                    <?php foreach ($all_tracks as $i => $s): ?>
                    <tr class="track-row"
                        data-id="<?= $s['id'] ?>"
                        data-title="<?= strtolower(htmlspecialchars($s['title'])) ?>"
                        data-artist="<?= strtolower(htmlspecialchars($s['artist'])) ?>"
                        data-views="<?= $s['views'] ?>"
                        data-year="<?= $s['year'] ?>">
                        <td class="col-num">
                            <span class="row-num"><?= $i+1 ?></span>
                            <button class="row-play-btn"><svg width="14" height="14" viewBox="0 0 24 24" fill="currentColor"><path d="M8 5v14l11-7z"/></svg></button>
                        </td>
                        <td class="col-title">
                            <div class="table-track">
                                <img src="https://picsum.photos/seed/<?= $s['cover_seed'] ?>/40/40" alt="">
                                <div>
                                    <strong><?= htmlspecialchars($s['title']) ?></strong>
                                        <?php if ($isArtist && isset($s['artist_id']) && $s['artist_id'] == $_SESSION['user_id']): ?>
                                            <span style="color:#1DB954; font-size:12px; margin-left:6px;">(You)</span>
                                        <?php endif; ?>
                                    <?php if ($s['trending']): ?><span class="inline-badge">HOT</span><?php endif; ?>
                                </div>
                            </div>
                        </td>
                        <td class="col-artist"><?= htmlspecialchars($s['artist']) ?></td>
                        <td class="col-year"><?= $s['year'] ?></td>
                        <td class="col-views"><?= fmt_views($s['views']) ?></td>
                        <td class="col-dur"><?= $s['duration'] ?></td>
                    </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
                <div class="no-song-results" id="noSongResults">
                    🔍 No songs match your search.<br>
                    <small>Try searching by song title or artist name</small>
                </div>
            </div>
        </section>

    </div>
</main>

<!-- Mini Player -->
<div class="player-bar" id="playerBar">
    <div class="player-track">
        <img id="playerImg" src="" alt="">
        <div>
            <div id="playerTitle" class="player-title">—</div>
            <div id="playerArtist" class="player-artist">—</div>
        </div>
        <button class="player-heart">♥</button>
    </div>
    <div class="player-controls">
        <button class="ctrl-btn">⏮</button>
        <button class="ctrl-btn play-pause" id="playPauseBtn">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="currentColor"><path d="M8 5v14l11-7z"/></svg>
        </button>
        <button class="ctrl-btn">⏭</button>
    </div>
    <div class="player-right">
        <div class="volume-row">
            <svg width="14" height="14" viewBox="0 0 24 24" fill="currentColor"><path d="M3 9v6h4l5 5V4L7 9H3zm13.5 3c0-1.77-1.02-3.29-2.5-4.03v8.05c1.48-.73 2.5-2.25 2.5-4.02z"/></svg>
            <input type="range" class="volume-slider" value="80" min="0" max="100">
        </div>
    </div>
</div>

<script src="tracks.js"></script>
</body>
</html>

<script>
function toggleUpload() {
    let box = document.getElementById("uploadBox");
    box.style.display = (box.style.display === "none") ? "block" : "none";
}
</script>