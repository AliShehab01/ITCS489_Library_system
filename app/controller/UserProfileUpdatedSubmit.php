<?php

session_start();
require_once __DIR__ . '/../../config.php';
require_once __DIR__ . '/../models/dbconnect.php';

// Get form data
$username = $_POST['username'] ?? null;
$new_firstName = $_POST['new_firstName'] ?? null;
$new_lastName = $_POST['new_lastName'] ?? null;
$new_email = $_POST['new_email'] ?? null;
$new_phoneNumber = $_POST['new_phoneNumber'] ?? null;
$new_role = $_POST['new_role'] ?? null;

if (!$username || !$new_firstName || !$new_lastName || !$new_email) {
    die('Missing required fields.');
}

$db = new Database();
$conn = $db->conn;

// Update user profile
$stmt = $conn->prepare("
    UPDATE users
    SET firstName = :firstName,
        lastName = :lastName,
        email = :email,
        phoneNumber = :phoneNumber
        " . ($new_role ? ", role = :role" : "") . "
    WHERE username = :username
");

$params = [
    ':firstName' => $new_firstName,
    ':lastName' => $new_lastName,
    ':email' => $new_email,
    ':phoneNumber' => $new_phoneNumber,
    ':username' => $username
];

if ($new_role) {
    $params[':role'] = $new_role;
}

if ($stmt->execute($params)) {
    $_SESSION['success'] = 'Profile updated successfully.';
    header('Location: ' . BASE_URL . 'app/view/AdminArea.php');
    exit;
} else {
    $_SESSION['error'] = 'Failed to update profile.';
    header('Location: ' . BASE_URL . 'app/view/editUserProfile.php?username=' . urlencode($username));
    exit;
}
?>