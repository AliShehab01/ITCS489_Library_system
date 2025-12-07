<?php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Path to config
require_once __DIR__ . '/../../config.php';

// Compute unread notification count for logged-in user (non-blocking)
$unreadCount = 0;
if (isset($_SESSION['user_id'])) {
    // Use the PDO-based Database helper used elsewhere in the app
    // Wrap in try/catch to avoid breaking pages if DB is unavailable
    try {
        require_once __DIR__ . '/../models/dbconnect.php';
        $db = new Database();
        $pdo = $db->conn;
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM notifications WHERE user_id = :uid AND is_read = 0");
        $stmt->execute([':uid' => (int)$_SESSION['user_id']]);
        $unreadCount = (int)$stmt->fetchColumn();
    } catch (Exception $e) {
        // silently ignore DB errors in navbar
        $unreadCount = 0;
    }
}
?>

<head>
        <link rel="stylesheet" href="../../public/css/style.css" />
</head>

<nav class="navbar navbar-expand-lg fixed-top shadow-sm">
    <div class="container-fluid">
        <!-- Logo -->
        <a class="navbar-brand" href="<?= BASE_URL ?>app/view/HomePage-EN.php">
            <img src="<?= BASE_URL ?>imgs/tlp-logo.svg" alt="Logo" height="50">
        </a>

        <!-- Hamburger for mobile -->
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#mainNavbar"
            aria-controls="mainNavbar" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>

        <!-- Navbar links -->
        <div class="collapse navbar-collapse" id="mainNavbar">
            <ul class="navbar-nav mx-auto mb-2 mb-lg-0">
                <li class="nav-item">
                    <a class="nav-link active" href="HomePage-EN.php">Home</a>
                </li>

                <!-- Dropdown Menu -->
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle" href="#" id="menuDropdown" role="button"
                        data-bs-toggle="dropdown" aria-expanded="false">Menu</a>
                    <ul class="dropdown-menu" aria-labelledby="menuDropdown">
            <li><a class="dropdown-item" href="CatalogSearch_Browsing-EN.php">Catalog</a></li>
            <li><a class="dropdown-item" href="editUserProfile.php">My Account</a></li>
            <li><a class="dropdown-item" href="borrowedDashboard.php">Borrowed</a>
            </li>
            <li><a class="dropdown-item" href="reservations.php">Reservations</a>
            </li>
                    </ul>
                </li>
            </ul>

            <!-- Right side -->
            <div class="d-flex align-items-center gap-2">

                <?php if (isset($_SESSION['role']) && $_SESSION['role'] === 'admin'): ?>
                    <a href="<?= BASE_URL ?>app/view/AdminArea.php" class="btn btn-outline-danger btn-sm">Admin Area</a>
                <?php endif; ?>

                <a href="userNotifications.php" class="btn btn-primary btn-sm position-relative">
                    Notifications
                    <?php if (!empty($unreadCount)): ?>
                        <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger">
                            <?= (int)$unreadCount ?>
                            <span class="visually-hidden">unread notifications</span>
                        </span>
                    <?php endif; ?>
                </a>

                <!-- Google Translate -->
                <div id="google_translate_element" class="ms-2"></div>

                <!-- Welcome/Login -->
                <div class="ms-2 d-flex align-items-center gap-1">
                    <?php if (isset($_SESSION['first_name'])): ?>
                        <span class="badge bg-success">Welcome <?= htmlspecialchars($_SESSION['first_name']) ?></span>
                        <a href="<?= BASE_URL ?>app/controller/logout.php"
                            class="btn btn-outline-secondary btn-sm">Logout</a>
                    <?php else: ?>
                        <a href="<?= BASE_URL ?>app/view/signup.php" class="btn btn-outline-primary btn-sm">Sign Up</a>
                        <a href="<?= BASE_URL ?>app/view/login.php" class="btn btn-primary btn-sm">Login</a>
                    <?php endif; ?>
                </div>

            </div>
        </div>
    </div>
</nav>

<style>

    body {
        padding-top: 80px;
    }
    :root {
        --admin-primary: #3b82f6;
        --admin-primary-dark: #2563eb;
        --navbar-bg: #111827; 
    }

    .navbar {
        background-color: var(--navbar-bg) !important;
        border-bottom: 3px solid var(--admin-primary);
    }

    .navbar .nav-link,
    .navbar .navbar-brand {
        color: #f3f4f6 !important;
    }

    .navbar .nav-link:hover,
    .navbar .nav-link.active {
        color: var(--admin-primary) !important;
    }

    .btn-primary {
        background-color: var(--admin-primary);
        border-color: var(--admin-primary);
        transition: background-color 0.2s;
        color: #fff;
    }

    .btn-primary:hover {
        background-color: var(--admin-primary-dark);
        border-color: var(--admin-primary-dark);
    }

    .btn-outline-secondary {
        color: #f3f4f6;
        border-color: #f3f4f6;
    }

    .btn-outline-secondary:hover {
        background-color: #f3f4f6;
        color: #111827;
    }

    body {
        padding-top: 80px; 
    }
</style>

</style>

<!-- Bootstrap JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js" crossorigin="anonymous">
</script>

<!-- Google Translate Script -->
<script type="text/javascript">
    function googleTranslateElementInit() {
        new google.translate.TranslateElement({
            pageLanguage: 'en',
            includedLanguages: 'ar',
            layout: google.translate.TranslateElement.InlineLayout.SIMPLE
        }, 'google_translate_element');
    }
</script>
<script type="text/javascript" src="//translate.google.com/translate_a/element.js?cb=googleTranslateElementInit">
</script>
