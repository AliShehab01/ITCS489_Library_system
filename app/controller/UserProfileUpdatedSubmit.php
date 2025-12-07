<?php

session_start();
require_once __DIR__ . '/../../config.php';
require_once __DIR__ . '/../models/dbconnect.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: ' . BASE_URL . 'view/login.php');
    exit;
}

$db = new Database();
$pdo = $db->getPdo();

$user_name = $_POST['username'] ?? '';
$new_role = $_POST['new_role'] ?? null;
$new_email = $_POST['new_email'] ?? null;
$new_first_name = $_POST['new_first_name'] ?? null;
$new_last_name = $_POST['new_last_name'] ?? null;
$new_phone_number = $_POST['new_phone_number'] ?? null;

if (empty($user_name)) {
    header('Location: ' . BASE_URL . 'view/HomePage-EN.php');
    exit;
}

$isAdmin = isset($_SESSION['role']) && $_SESSION['role'] === 'admin';

// Build update query
if ($isAdmin && $new_role) {
    $stmt = $pdo->prepare("UPDATE users SET role = :role, email = :email, firstName = :firstName, lastName = :lastName, phoneNumber = :phone WHERE username = :username");
    $stmt->execute([
        ':role' => $new_role,
        ':email' => $new_email,
        ':firstName' => $new_first_name,
        ':lastName' => $new_last_name,
        ':phone' => $new_phone_number,
        ':username' => $user_name
    ]);
} else {
    $stmt = $pdo->prepare("UPDATE users SET email = :email, firstName = :firstName, lastName = :lastName, phoneNumber = :phone WHERE username = :username");
    $stmt->execute([
        ':email' => $new_email,
        ':firstName' => $new_first_name,
        ':lastName' => $new_last_name,
        ':phone' => $new_phone_number,
        ':username' => $user_name
    ]);
}

// Redirect back
if ($isAdmin) {
    header('Location: ' . BASE_URL . 'controller/ManagingUsers.php');
} else {
    header('Location: ' . BASE_URL . 'view/HomePage-EN.php');
}
exit;
