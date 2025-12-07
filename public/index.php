<?php
session_start();

// Include config.php (in htdocs/)
require_once __DIR__ . '/../config.php';
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="<?= BASE_URL ?>public/css/style.css">
    <?php if (!defined('STYLE_LOADED')) { define('STYLE_LOADED', true); } ?>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet"
        integrity="sha384-sRIl4kxILFvY47J16cr9ZwB07vP4J8+LH7qKQnuqkuIAvNWLzeN8tE5YBujZqJLB" crossorigin="anonymous">
    <title>Library System</title>
</head>

<body>

    <?php include __DIR__ . '/../app/view/navbar.php'; ?>

    <main class="page-shell">
        <section class="page-hero mb-4">
            <div class="row align-items-center g-4">
                <div class="col-lg-8">
                    <p class="text-uppercase text-muted fw-semibold mb-2">Welcome to the library</p>
                    <h1 class="display-5 mb-3">Borrow smarter. Discover faster.</h1>
                    <p class="lead mb-0">Browse thousands of titles, manage your account, and receive updates with a consistent, modern experience.</p>
                </div>
                <div class="col-lg-4 text-lg-end d-flex d-lg-block gap-2">
                    <a href="<?= BASE_URL ?>app/view/login.php" class="btn btn-primary me-2">Login</a>
                    <a href="<?= BASE_URL ?>app/view/signup.php" class="btn btn-outline-primary">Create account</a>
                </div>
            </div>
        </section>

        <section class="mb-4">
            <div class="section-title">
                <span class="pill">★</span>
                <span>Why readers choose us</span>
            </div>
            <div class="row row-cols-1 row-cols-sm-2 row-cols-lg-4 g-3">
                <div class="col">
                    <div class="card h-100">
                        <img src="<?= BASE_URL ?>imgs/kindle.png" class="card-img-top" alt="Digital ready">
                        <div class="card-body">
                            <h5 class="card-title">Ready for every device</h5>
                            <p class="card-text">Download in the right format without extra steps—start reading right away.</p>
                        </div>
                    </div>
                </div>
                <div class="col">
                    <div class="card h-100">
                        <img src="<?= BASE_URL ?>imgs/adding.png" class="card-img-top" alt="Upload books">
                        <div class="card-body">
                            <h5 class="card-title">Contribute easily</h5>
                            <p class="card-text">Add new books with a guided flow so the catalog stays fresh and accurate.</p>
                        </div>
                    </div>
                </div>
                <div class="col">
                    <div class="card h-100">
                        <img src="<?= BASE_URL ?>imgs/kindle.png" class="card-img-top" alt="Faster search">
                        <div class="card-body">
                            <h5 class="card-title">Powerful search</h5>
                            <p class="card-text">Find titles by author, category, ISBN, or availability with clean filters.</p>
                        </div>
                    </div>
                </div>
                <div class="col">
                    <div class="card h-100">
                        <img src="<?= BASE_URL ?>imgs/kindle.png" class="card-img-top" alt="Support">
                        <div class="card-body">
                            <h5 class="card-title">Stay informed</h5>
                            <p class="card-text">Get timely reminders about reservations, dues, and new arrivals.</p>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <section class="card shadow-custom border-0" style="background: linear-gradient(135deg, rgba(59,91,219,.12), rgba(245,158,11,.12));">
            <div class="card-body d-flex flex-column flex-lg-row align-items-center justify-content-between">
                <div>
                    <h2 class="mb-2">Join our library community</h2>
                    <p class="mb-0 text-muted">Browse the catalog, reserve titles, and manage your account from one place.</p>
                </div>
                <div class="d-flex gap-2 mt-3 mt-lg-0">
                    <a href="<?= BASE_URL ?>app/view/CatalogSearch_Browsing-EN.php" class="btn btn-primary">Start browsing</a>
                    <a href="<?= BASE_URL ?>app/view/signup.php" class="btn btn-outline-primary">Create account</a>
                </div>
            </div>
        </section>
    </main>

    <footer class="app-footer text-center">
        <small>© 2025 University Library. All rights reserved.</small>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"
        integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous">
    </script>
</body>

</html>
