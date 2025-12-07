<?php
session_start();
require_once __DIR__ . '/../../config.php';
require_once __DIR__ . '/../models/dbconnect.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: " . BASE_URL . "view/login.php");
    exit;
}

$username = trim($_POST['username'] ?? '');
$password = $_POST['password'] ?? '';

if (empty($username) || empty($password)) {
    $_SESSION['error'] = "Please enter username and password.";
    header("Location: " . BASE_URL . "view/login.php");
    exit;
}

try {
    $db = new Database();
    $conn = $db->conn;

    $stmt = $conn->prepare("SELECT * FROM users WHERE username = :username");
    $stmt->execute([':username' => $username]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    // Simple password check (in production, use password_verify with hashed passwords)
    if ($user && $user['password'] === $password) {
        // Set all session variables
        $_SESSION['user_id'] = (int)$user['id'];
        $_SESSION['username'] = $user['username'];
        $_SESSION['first_name'] = $user['firstName'];
        $_SESSION['last_name'] = $user['lastName'];
        $_SESSION['role'] = $user['role']; // Keep original case from DB
        $_SESSION['email'] = $user['email'] ?? '';
        $_SESSION['BorrowLimit'] = match (strtolower($user['role'])) {
            'admin', 'staff' => 10,
            'vipstudent' => 7,
            default => 3
        };

        header("Location: " . BASE_URL . "view/HomePage-EN.php");
        exit;
    } else {
        $_SESSION['error'] = "Invalid username or password.";
        header("Location: " . BASE_URL . "view/login.php");
        exit;
    }
} catch (PDOException $e) {
    $_SESSION['error'] = "Login failed. Please try again.";
    header("Location: " . BASE_URL . "view/login.php");
    exit;
}
