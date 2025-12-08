<?php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Load BASE_URL for accurate redirects
require_once __DIR__ . '/../../config.php';

if (!(isset($_SESSION['role']) && $_SESSION['role'] === 'admin')) {
    $target = (defined('BASE_URL') ? BASE_URL : '/');
    $target .= 'public/index.php';
    header('Location: ' . $target);
    exit;
}
