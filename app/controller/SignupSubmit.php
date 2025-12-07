<?php
session_start();
require_once __DIR__ . '/../../config.php';
require_once __DIR__ . '/../models/dbconnect.php';
require_once __DIR__ . '/../models/CreateDefaultDBTables.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: " . BASE_URL . "view/signup.php");
    exit;
}

$username = trim($_POST['username'] ?? '');
$password = $_POST['password'] ?? '';
$firstName = trim($_POST['firstName'] ?? $_POST['first_name'] ?? '');
$lastName = trim($_POST['lastName'] ?? $_POST['last_name'] ?? '');
$email = trim($_POST['email'] ?? '');
$phoneNumber = trim($_POST['phoneNumber'] ?? $_POST['phone_number'] ?? '');

if (empty($username) || empty($password) || empty($firstName) || empty($lastName)) {
    $_SESSION['error'] = "Please fill in all required fields.";
    header("Location: " . BASE_URL . "view/signup.php");
    exit;
}

try {
    $db = new Database();
    $conn = $db->conn;

    // Check if username already exists
    $checkStmt = $conn->prepare("SELECT id FROM users WHERE username = :username");
    $checkStmt->execute([':username' => $username]);

    if ($checkStmt->fetch()) {
        $_SESSION['error'] = "Username already exists. Please choose another.";
        header("Location: " . BASE_URL . "view/signup.php");
        exit;
    }

    // Insert new user (default role: Student)
    $stmt = $conn->prepare("INSERT INTO users (username, password, firstName, lastName, email, phoneNumber, currentNumOfBorrows, role) VALUES (:username, :password, :firstName, :lastName, :email, :phoneNumber, 0, 'Student')");

    $stmt->execute([
        ':username' => $username,
        ':password' => $password,
        ':firstName' => $firstName,
        ':lastName' => $lastName,
        ':email' => $email,
        ':phoneNumber' => $phoneNumber
    ]);

    $newUserId = $conn->lastInsertId();

    // Auto-login after signup
    $_SESSION['user_id'] = (int)$newUserId;
    $_SESSION['username'] = $username;
    $_SESSION['first_name'] = $firstName;
    $_SESSION['last_name'] = $lastName;
    $_SESSION['role'] = 'Student';
    $_SESSION['email'] = $email;
    $_SESSION['BorrowLimit'] = 3;

    // Redirect to home page
    header("Location: " . BASE_URL . "view/HomePage-EN.php");
    exit;
} catch (PDOException $e) {
    $_SESSION['error'] = "Registration failed. Please try again.";
    header("Location: " . BASE_URL . "view/signup.php");
    exit;
}
