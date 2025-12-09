<?php
session_start();

// Include config.php (in htdocs/)
require_once __DIR__ . '/../config.php';

$isLoggedIn = isset($_SESSION['username']);
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="<?= BASE_URL ?>public/css/style.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet">
    <title>Library System</title>
    <style>
        .hero-section {
            background: linear-gradient(135deg, #1e3a8a, #3b82f6);
        }

        .cta-section {
            background: linear-gradient(135deg, #059669, #10b981);
        }
    </style>
</head>

<body>
    <div style="height: 55px;"></div>
    <?php include __DIR__ . '/../app/view/navbar.php'; ?>

    <!-- Hero Section -->
    <section class="hero-section py-5 text-white">
        <div class="container text-center">
            <h1 class="display-4 fw-bold">Welcome to the University Library</h1>
            <p class="lead mb-4">Discover thousands of books, manage your borrowings, and explore knowledge.</p>
            <?php if (!$isLoggedIn): ?>
                <div class="d-flex gap-3 justify-content-center">
                    <a href="<?= BASE_URL ?>view/login.php" class="btn btn-light btn-lg">Login</a>
                    <a href="<?= BASE_URL ?>view/signup.php" class="btn btn-outline-light btn-lg">Sign Up</a>
                </div>
            <?php else: ?>
                <a href="<?= BASE_URL ?>view/HomePage-EN.php" class="btn btn-light btn-lg">Go to Dashboard</a>
            <?php endif; ?>
        </div>
    </section>

    <!-- Features Section -->
    <section class="py-5">
        <div class="container">
            <h2 class="text-center mb-5">What We Offer</h2>
            <div class="row row-cols-1 row-cols-sm-2 row-cols-lg-4 g-4">
                <div class="col">
                    <div class="card h-100 text-center shadow-sm">
                        <div class="card-body">
                            <div class="display-4 mb-3">📚</div>
                            <h5 class="card-title">Browse Catalog</h5>
                            <p class="card-text">Search thousands of books by title, author, ISBN, or category.</p>
                            <a href="<?= BASE_URL ?>view/CatalogSearch_Browsing-EN.php"
                                class="btn btn-outline-primary btn-sm">Browse Now</a>
                        </div>
                    </div>
                </div>
                <div class="col">
                    <div class="card h-100 text-center shadow-sm">
                        <div class="card-body">
                            <div class="display-4 mb-3">📖</div>
                            <h5 class="card-title">Easy Borrowing</h5>
                            <p class="card-text">Borrow books with just a few clicks and track your due dates.</p>
                        </div>
                    </div>
                </div>
                <div class="col">
                    <div class="card h-100 text-center shadow-sm">
                        <div class="card-body">
                            <div class="display-4 mb-3">🔔</div>
                            <h5 class="card-title">Notifications</h5>
                            <p class="card-text">Get reminders for due dates and alerts when reserved books are
                                available.</p>
                        </div>
                    </div>
                </div>
                <div class="col">
                    <div class="card h-100 text-center shadow-sm">
                        <div class="card-body">
                            <div class="display-4 mb-3">🔖</div>
                            <h5 class="card-title">Reservations</h5>
                            <p class="card-text">Reserve books that are currently checked out and get in queue.</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- CTA Section -->
    <section class="cta-section py-5 text-white text-center">
        <div class="container">
            <h2 class="display-5 fw-bold mb-3">Join Our Library Today!</h2>
            <p class="lead mb-4">Explore the best books and start your reading journey now!</p>
            <?php if (!$isLoggedIn): ?>
                <a href="<?= BASE_URL ?>view/signup.php" class="btn btn-lg btn-warning fw-bold text-dark">Sign Up Now →</a>
            <?php else: ?>
                <a href="<?= BASE_URL ?>view/CatalogSearch_Browsing-EN.php"
                    class="btn btn-lg btn-warning fw-bold text-dark">Browse
                    Catalog →</a>
            <?php endif; ?>
        </div>
    </section>

    <footer class="bg-dark text-white pt-4 pb-3">
        <div class="container text-center">© 2025 University Library. All Rights Reserved.</div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>