<?php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once __DIR__ . '/../../config.php';

// Check if user is logged in
if (!isset($_SESSION['username'])) {
    header("Location: " . BASE_URL . "view/login.php");
    exit;
}

// Check if user has admin or staff role (case-insensitive)
$userRole = strtolower($_SESSION['role'] ?? '');

if (!in_array($userRole, ['admin', 'staff'])) {
    // Redirect non-admin users to home page
    header("Location: " . BASE_URL . "view/HomePage-EN.php");
    exit;
}
