<?php
session_start();
require_once __DIR__ . '/../../config.php';
$_SESSION = [];
session_destroy();
header("Location: " . PUBLIC_URL . "index.php");
exit;
