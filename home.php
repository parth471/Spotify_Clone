<?php
session_start();

// If not logged in, kick back to login page
if (!isset($_SESSION["logged_in"]) || $_SESSION["logged_in"] !== true) {
    header("Location: login.html");
    exit();
}

$user_email = htmlspecialchars($_SESSION["user_email"]);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Music Home</title>
    <link rel="stylesheet" href="home.css">
    <style>
        /* Success banner */
        .success-banner {
            position: fixed;
            top: 18px;
            left: 50%;
            transform: translateX(-50%);
            background: #1db954;
            color: #000;
            font-weight: 700;
            font-size: 0.9rem;
            padding: 10px 24px;
            border-radius: 50px;
            box-shadow: 0 4px 20px #1db95466;
            z-index: 9999;
            animation: slideDown 0.4s ease, fadeOut 0.5s ease 3s forwards;
            white-space: nowrap;
        }
        @keyframes slideDown {
            from { top: -50px; opacity: 0; }
            to   { top: 18px;  opacity: 1; }
        }
        @keyframes fadeOut {
            to { opacity: 0; pointer-events: none; }
        }
        .logout-btn {
            background: transparent;
            border: 1px solid #555;
            color: #fff;
            padding: 6px 16px;
            border-radius: 20px;
            cursor: pointer;
            font-size: 0.8rem;
            margin-left: 10px;
            transition: background 0.2s;
        }
        .logout-btn:hover { background: #333; }
    </style>
</head>
<body>

<!-- ✅ Login Success Banner -->
<div class="success-banner">✅ Login successful! Welcome, <?= $user_email ?></div>

<!-- Sidebar -->
<aside class="sidebar">
    <div class="logo">Spotify</div>

    <ul class="menu">
        <li class="active">Home</li>
        <li onclick="window.location='genre.php'" style="cursor:pointer;">Browse Genres</li>
        <li>Your Library</li>
    </ul>

    <div class="playlist-section">
        <p class="section-title">Playlists</p>
        <ul>
            <li>Liked Songs</li>
            <li>Chill Vibes</li>
            <li>Top Hits</li>
        </ul>
    </div>
</aside>

<!-- Main Content -->
<main class="main">

    <!-- Topbar -->
    <div class="topbar">
        <input type="text" placeholder="What do you want to play?">
        <span style="color:#aaa; font-size:0.85rem;"><?= $user_email ?></span>
        <!-- Logout button -->
        <a href="logout.php">
            <button class="logout-btn">Logout</button>
        </a>
    </div>

    <!-- Hero -->
    <section class="hero">
        <div class="hero-content">
            <h1>Feel the Music.</h1>
            <p>Discover millions of songs curated just for you.</p>
            <a href="genre.php" style="text-decoration:none;">
                <button class="primary-btn">🎵 Browse Genres</button>
            </a>
        </div>
    </section>

    <!-- Trending Section -->
    <section class="section">
        <h2>Trending Now</h2>

        <div class="card-grid">

            <div class="music-card">
                <img src="https://picsum.photos/300?1">
                <div class="card-info">
                    <h4>Midnight City</h4>
                    <p>Electronic • 2026</p>
                </div>
                <button class="play-btn">▶</button>
            </div>

            <div class="music-card">
                <img src="https://picsum.photos/300?2">
                <div class="card-info">
                    <h4>Neon Dreams</h4>
                    <p>Pop • 2026</p>
                </div>
                <button class="play-btn">▶</button>
            </div>

            <div class="music-card">
                <img src="https://picsum.photos/300?3">
                <div class="card-info">
                    <h4>Chill Mode</h4>
                    <p>Lo-Fi • 2026</p>
                </div>
                <button class="play-btn">▶</button>
            </div>

            <div class="music-card">
                <img src="https://picsum.photos/300?4">
                <div class="card-info">
                    <h4>Energy Boost</h4>
                    <p>Workout • 2026</p>
                </div>
                <button class="play-btn">▶</button>
            </div>

        </div>
    </section>

</main>

<script src="home.js"></script>
</body>
</html>
