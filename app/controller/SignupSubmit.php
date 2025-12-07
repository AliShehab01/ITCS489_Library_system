<?php
session_start();
require_once __DIR__ . '/../../config.php';
require_once __DIR__ . '/../models/dbconnect.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: " . BASE_URL . "view/signup.php");
    exit;
}

$username = trim($_POST['username'] ?? '');
$password = $_POST['password'] ?? '';
$firstName = trim($_POST['firstName'] ?? '');
$lastName = trim($_POST['lastName'] ?? '');
$email = trim($_POST['email'] ?? '');
$phone = trim($_POST['phoneNumber'] ?? '');

if (empty($username) || empty($password) || empty($firstName) || empty($lastName)) {
    $_SESSION['error'] = "Please fill all required fields.";
    header("Location: " . BASE_URL . "view/signup.php");
    exit;
}

try {
    $db = new Database();
    $conn = $db->conn;

    $check = $conn->prepare("SELECT id FROM users WHERE username = :u");
    $check->execute([':u' => $username]);
    if ($check->fetch()) {
        $_SESSION['error'] = "Username already exists.";
        header("Location: " . BASE_URL . "view/signup.php");
        exit;
    }

    $stmt = $conn->prepare("INSERT INTO users (username, password, firstName, lastName, email, phoneNumber, currentNumOfBorrows, role) VALUES (:u, :p, :fn, :ln, :e, :ph, 0, 'Student')");
    $stmt->execute([':u' => $username, ':p' => $password, ':fn' => $firstName, ':ln' => $lastName, ':e' => $email, ':ph' => $phone]);

    $_SESSION['user_id'] = (int)$conn->lastInsertId();
    $_SESSION['username'] = $username;
    $_SESSION['first_name'] = $firstName;
    $_SESSION['role'] = 'Student';
    $_SESSION['BorrowLimit'] = 3;

    header("Location: " . BASE_URL . "view/HomePage-EN.php");
    exit;
} catch (PDOException $e) {
    $_SESSION['error'] = "Registration failed.";
    header("Location: " . BASE_URL . "view/signup.php");
    exit;
}
