<?php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Path to config
require_once __DIR__ . '/../../config.php';

// Compute unread notification count for logged-in user
$unreadCount = 0;
if (isset($_SESSION['user_id'])) {
    try {
        require_once __DIR__ . '/../models/dbconnect.php';
        $db = new Database();
        $pdo = $db->conn;
        if ($pdo) {
            $stmt = $pdo->prepare("SELECT COUNT(*) FROM notifications WHERE user_id = :uid AND is_read = 0");
            $stmt->execute([':uid' => (int)$_SESSION['user_id']]);
            $unreadCount = (int)$stmt->fetchColumn();
        }
    } catch (Exception $e) {
        $unreadCount = 0;
    }
}

$isLoggedIn = isset($_SESSION['username']);
$userRole = strtolower($_SESSION['role'] ?? '');
$isAdmin = in_array($userRole, ['admin', 'staff']);
$firstName = $_SESSION['first_name'] ?? $_SESSION['username'] ?? '';

// Define public home URL
$publicHomeUrl = '/public/index.php';
?>

<nav class="navbar navbar-expand-lg fixed-top shadow-sm navbar-dark" style="background-color: #111827; border-bottom: 3px solid #3b82f6;">
    <div class="container-fluid">
        <!-- Logo -->
        <a class="navbar-brand d-flex align-items-center" href="<?= $isLoggedIn ? BASE_URL . 'view/HomePage-EN.php' : $publicHomeUrl ?>">
            <span class="fw-bold text-white">📚 Library</span>
        </a>

        <!-- Hamburger for mobile -->
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#mainNavbar"
            aria-controls="mainNavbar" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>

        <!-- Navbar links -->
        <div class="collapse navbar-collapse" id="mainNavbar">
            <ul class="navbar-nav me-auto mb-2 mb-lg-0">
                <li class="nav-item">
                    <a class="nav-link" href="<?= $isLoggedIn ? BASE_URL . 'view/HomePage-EN.php' : $publicHomeUrl ?>">Home</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="<?= BASE_URL ?>view/CatalogSearch_Browsing-EN.php">Catalog</a>
                </li>

                <?php if ($isLoggedIn): ?>
                    <!-- Dropdown Menu for logged-in users -->
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                            My Library
                        </a>
                        <ul class="dropdown-menu dropdown-menu-dark">
                            <li><a class="dropdown-item" href="<?= BASE_URL ?>view/editUserProfile.php">My Account</a></li>
                            <li><a class="dropdown-item" href="<?= BASE_URL ?>view/borrowedDashboard.php">Borrowed Books</a></li>
                            <li><a class="dropdown-item" href="<?= BASE_URL ?>view/reservations.php">Reservations</a></li>
                            <li>
                                <hr class="dropdown-divider">
                            </li>
                            <li><a class="dropdown-item" href="<?= BASE_URL ?>view/userNotifications.php">Notifications <?php if ($unreadCount > 0): ?><span class="badge bg-danger"><?= $unreadCount ?></span><?php endif; ?></a></li>
                        </ul>
                    </li>
                <?php endif; ?>

                <?php if ($isAdmin): ?>
                    <li class="nav-item">
                        <a class="nav-link text-warning" href="<?= BASE_URL ?>view/AdminArea.php">⚙️ Admin Area</a>
                    </li>
                <?php endif; ?>
            </ul>

            <!-- Right side -->
            <div class="d-flex align-items-center gap-2">
                <?php if ($isLoggedIn): ?>
                    <!-- Notification bell -->
                    <a href="<?= BASE_URL ?>view/userNotifications.php" class="btn btn-outline-light btn-sm position-relative">
                        🔔
                        <?php if ($unreadCount > 0): ?>
                            <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger" style="font-size: 0.6rem;">
                                <?= (int)$unreadCount ?>
                            </span>
                        <?php endif; ?>
                    </a>

                    <!-- User info -->
                    <span class="badge bg-success"><?= htmlspecialchars($firstName) ?></span>
                    <a href="<?= BASE_URL ?>controller/logout.php" class="btn btn-outline-light btn-sm">Logout</a>
                <?php else: ?>
                    <a href="<?= BASE_URL ?>view/signup.php" class="btn btn-outline-light btn-sm">Sign Up</a>
                    <a href="<?= BASE_URL ?>view/login.php" class="btn btn-primary btn-sm">Login</a>
                <?php endif; ?>
            </div>
        </div>
    </div>
</nav>

<style>
    .navbar .nav-link {
        color: rgba(255, 255, 255, 0.85) !important;
    }

    .navbar .nav-link:hover {
        color: #3b82f6 !important;
    }

    .dropdown-menu-dark {
        background-color: #1f2937;
    }

    .dropdown-menu-dark .dropdown-item {
        color: #f3f4f6;
    }

    .dropdown-menu-dark .dropdown-item:hover {
        background-color: #374151;
        color: #3b82f6;
    }
</style>