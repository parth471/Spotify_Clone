<?php
session_start();

$host   = "localhost";
$dbname = "*********";
$dbuser = "*******";
$dbpass = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $firstname = trim($_POST["firstname"] ?? "");
    $lastname  = trim($_POST["lastname"]  ?? "");
    $role      = trim($_POST["role"]  ?? "");
    $email     = trim($_POST["email"]     ?? "");
    $password  = trim($_POST["password"]  ?? "");
    $confirm   = trim($_POST["confirm"]   ?? "");
    
    // Basic server-side validation
    if (empty($firstname) || empty($lastname) || empty($email) || empty($password)) {
        header("Location: signup.html?error=empty");
        exit();
    }

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        header("Location: signup.html?error=invalid_email");
        exit();
    }

    if ($password !== $confirm) {
        header("Location: signup.html?error=mismatch");
        exit();
    }

    if (strlen($password) < 6) {
        header("Location: signup.html?error=short_pw");
        exit();
    }

    // Connect to DB
    $conn = new mysqli($host, $dbuser, $dbpass, $dbname);
    if ($conn->connect_error) {
        die("DB error: " . $conn->connect_error);
    }

    // Check if email already exists
    $check = $conn->prepare("SELECT id FROM users WHERE email = ?");
    $check->bind_param("s", $email);
    $check->execute();
    $check->store_result();

    if ($check->num_rows > 0) {
        $check->close();
        $conn->close();
        header("Location: signup.html?error=exists");
        exit();
    }
    $check->close();

    // Insert new user with hashed password
    $hashed = password_hash($password, PASSWORD_DEFAULT);
    $fullname = $firstname . " " . $lastname;

    $stmt = $conn->prepare("INSERT INTO users (email, password, name, role) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("ssss", $email, $hashed, $fullname, $role);

    if ($stmt->execute()) {
        $stmt->close();
        $conn->close();
        // Redirect to login with success message
        header("Location: login.html?signup=success");
        exit();
    } else {
        $stmt->close();
        $conn->close();
        header("Location: signup.html?error=db");
        exit();
    }
}

// Direct access without POST → send to signup page
header("Location: signup.html");
exit();
?>
