<?php
session_start();

// ── Database config ──────────────────────────────────────────
$host   = "localhost";
$dbname = "*****";
$dbuser = "******";
$dbpass = "";          
// ─────────────────────────────────────────────────────────────

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $email    = trim($_POST["email"]    ?? "");
    $password = trim($_POST["password"] ?? "");

    // Connect
    $conn = new mysqli($host, $dbuser, $dbpass, $dbname);
    if ($conn->connect_error) {
        die("DB Connection failed: " . $conn->connect_error);
    }

    // Use prepared statement to prevent SQL injection
    $stmt = $conn->prepare("SELECT id, email, password, role FROM users WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 1) {
        $row = $result->fetch_assoc();

        // Verify hashed password
        if (password_verify($password, $row["password"])) {
            $_SESSION["logged_in"]  = true;
            $_SESSION["user_email"] = $row["email"];
            $_SESSION["user_id"]    = $row["id"];
            $_SESSION["role"] = $row['role'];

            header("Location: home.php");
            exit();
        }
    }

    // Wrong email or password
    $stmt->close();
    $conn->close();
    header("Location: login.html?error=1");
    exit();
}
?>
