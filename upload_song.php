<?php
session_start();

// 🔒 Check login + role properly
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'artist') {
    die("Access Denied");
}

$artist_id = $_SESSION['user_id'];

$conn = new mysqli("localhost","root","","spotify_db");
if ($conn->connect_error) {
    die("DB Connection Failed");
}

// Get data safely
$title = $_POST['title'] ?? '';
$artist = $_POST['artist'] ?? '';
$genre_slug = $_POST['genre_slug'] ?? '';

// Basic validation
if (empty($title) || empty($artist) || empty($genre_slug)) {
    die("Missing data");
}

// Mock values
$file_path = "audio/sample.mp3"; // better than mock.mp3
$views = rand(100,10000);
$trending = rand(0,1);
$year = date("Y");
$duration = "3:00";
$cover_seed = rand(1,1000);

// Use prepared statement (SAFE)
$stmt = $conn->prepare("INSERT INTO songs 
(title, artist, file_path, genre_slug, views, trending, year, duration, cover_seed, artist_id)
VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");

$stmt->bind_param(
    "ssssiiissi",
    $title,
    $artist,
    $file_path,
    $genre_slug,
    $views,
    $trending,
    $year,
    $duration,
    $cover_seed,
    $artist_id
);

if ($stmt->execute()) {
    header("Location: tracks.php?genre=$genre_slug");
    exit();
} else {
    echo "Error inserting song";
}

$stmt->close();
$conn->close();
?>