<?php
$host   = "localhost";
$dbuser = "root";
$dbpass = "";

$conn = new mysqli($host, $dbuser, $dbpass);
if ($conn->connect_error) die("❌ " . $conn->connect_error);

// Create DB
$conn->query("CREATE DATABASE IF NOT EXISTS spotify_db");
$conn->select_db("spotify_db");

// ================= USERS TABLE (UPDATED) =================
$conn->query("
    CREATE TABLE IF NOT EXISTS users (
        id       INT AUTO_INCREMENT PRIMARY KEY,
        name     VARCHAR(150) DEFAULT '',
        email    VARCHAR(100) NOT NULL UNIQUE,
        password VARCHAR(255) NOT NULL,
        role     ENUM('user','artist') DEFAULT 'user',   -- ✅ NEW
        created  TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )
");

// ================= SAFE ALTER (FOR EXISTING DB) =================
$conn->query("ALTER TABLE users ADD COLUMN IF NOT EXISTS name VARCHAR(150) DEFAULT ''");
$conn->query("ALTER TABLE users ADD COLUMN IF NOT EXISTS role ENUM('user','artist') DEFAULT 'user'");

// ================= DEMO USERS =================

// Demo USER
$hashed1 = password_hash("spotify123", PASSWORD_DEFAULT);
$stmt1 = $conn->prepare("INSERT IGNORE INTO users (name, email, password, role) VALUES (?, ?, ?, ?)");
$name1  = "Demo User";
$email1 = "demo@spotify.com";
$role1  = "user";
$stmt1->bind_param("ssss", $name1, $email1, $hashed1, $role1);
$stmt1->execute();
$stmt1->close();

// Demo ARTIST
$hashed2 = password_hash("artist123", PASSWORD_DEFAULT);
$stmt2 = $conn->prepare("INSERT IGNORE INTO users (name, email, password, role) VALUES (?, ?, ?, ?)");
$name2  = "Demo Artist";
$email2 = "artist@spotify.com";
$role2  = "artist";
$stmt2->bind_param("ssss", $name2, $email2, $hashed2, $role2);
$stmt2->execute();
$stmt2->close();

$conn->close();
?>

<!DOCTYPE html>
<html>
<body style="font-family:sans-serif;background:#111;color:#1db954;padding:40px;text-align:center;">

<h2>✅ Database setup complete!</h2>

<p style="color:#fff;">Database: <b>spotify_db</b> | Table: <b>users</b></p>

<table style="margin:20px auto;color:#fff;border-collapse:collapse;">
<tr>
<td style="padding:8px 20px;border:1px solid #333;">User Email</td>
<td style="padding:8px 20px;border:1px solid #333;color:#1db954;">demo@spotify.com</td>
</tr>
<tr>
<td style="padding:8px 20px;border:1px solid #333;">Password</td>
<td style="padding:8px 20px;border:1px solid #333;color:#1db954;">spotify123</td>
</tr>

<tr>
<td style="padding:8px 20px;border:1px solid #333;">Artist Email</td>
<td style="padding:8px 20px;border:1px solid #333;color:#1db954;">artist@spotify.com</td>
</tr>
<tr>
<td style="padding:8px 20px;border:1px solid #333;">Password</td>
<td style="padding:8px 20px;border:1px solid #333;color:#1db954;">artist123</td>
</tr>
</table>

<a href="login.html" style="background:#1db954;color:#000;padding:12px 30px;border-radius:25px;text-decoration:none;font-weight:bold;">
→ Go to Login
</a>

<p style="color:#555;margin-top:20px;font-size:12px;">
⚠️ Delete setup_db.php after setup!
</p>

</body>
</html>