<?php
session_start();
require_once __DIR__ . '/../../config.php';

// Destroy all session data
$_SESSION = [];
session_destroy();

// Redirect to login page
header("Location: " . BASE_URL . "view/login.php");
exit;
