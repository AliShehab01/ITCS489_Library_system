<?php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!(isset($_SESSION['role']) && ($_SESSION['role'] == 'admin' || $_SESSION['role'] == 'staff'))) {
    header("Location: ../../public/index.php");
}
