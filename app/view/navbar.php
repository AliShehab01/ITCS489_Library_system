<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Path to config
require_once __DIR__ . '/../../config.php';
?>

<nav class="navbar navbar-expand-lg navbar-light bg-light fixed-top shadow-sm">
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
                    <a class="nav-link active" href="<?= BASE_URL ?>public/index.php">Home</a>
                </li>

                <!-- Dropdown Menu -->
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle" href="#" id="menuDropdown" role="button"
                        data-bs-toggle="dropdown" aria-expanded="false">Menu</a>
                    <ul class="dropdown-menu" aria-labelledby="menuDropdown">
                        <li><a class="dropdown-item"
                                href="<?= BASE_URL ?>app/view/CatalogSearch_Browsing-EN.php">Catalog</a></li>
                        <li><a class="dropdown-item" href="<?= BASE_URL ?>app/view/account.php">My Account</a></li>
                        <li><a class="dropdown-item" href="<?= BASE_URL ?>app/view/BorrowedDashboard.php">Borrowed</a>
                        </li>
                        <li><a class="dropdown-item" href="<?= BASE_URL ?>app/view/reservations.php">Reservations</a>
                        </li>
                    </ul>
                </li>
            </ul>

            <!-- Right side -->
            <div class="d-flex align-items-center gap-2">

                <?php if (isset($_SESSION['role']) && $_SESSION['role'] === 'admin'): ?>
                    <a href="<?= BASE_URL ?>app/view/AdminArea.php" class="btn btn-outline-danger btn-sm">Admin Area</a>
                <?php endif; ?>

                <button type="button" class="btn btn-primary btn-sm" id="notific">
                    Notifications <span class="badge bg-secondary">0</span>
                </button>

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
    /* Add padding to prevent navbar overlap */
    body {
        padding-top: 80px;
    }
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