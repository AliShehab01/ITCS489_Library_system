<?php
session_start();
require '../controller/checkifadmin.php';
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Area</title>

    <!-- Bootstrap + الثيم العام -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="<?= BASE_URL ?>public/css/style.css">

    <!-- Lucide Icons (اختياري للأيقونات في الكروت) -->
    <script src="https://unpkg.com/lucide@latest"></script>
</head>

<body>

<?php include 'navbar.php'; ?>

<main class="site-content">

    <!-- هيدر الصفحة -->
    <section class="py-4 bg-white border-bottom">
        <div class="container d-flex flex-wrap justify-content-between align-items-center gap-2">
            <div>
                <h1 class="h4 mb-1">Admin Control Panel</h1>
                <p class="text-muted mb-0">
                    Manage users, catalog, borrowing, reservations, notifications, and system settings.
                </p>
            </div>
        </div>
    </section>

    <!-- المحتوى الرئيسي -->
    <section class="py-4">
        <div class="container my-3">
            <div class="admin-wrapper mx-auto">

                <div class="row row-cols-1 row-cols-sm-2 row-cols-lg-4 g-3">

                    <!-- User Management -->
                    <div class="col">
                        <div class="card text-center shadow-custom h-100">
                            <div class="card-body d-flex flex-column">
                                <h5 class="card-title mb-2">
                                    <i data-lucide="users" class="me-1"></i>
                                    User Management
                                </h5>
                                <p class="card-text flex-grow-1 text-muted small">
                                    Add, edit, or remove users, assign roles, and manage borrowing limits.
                                </p>
                                <a href="<?= BASE_URL ?>app/controller/ManagingUsers.php" class="btn btn-primary mt-auto">
                                    Manage Users
                                </a>
                            </div>
                        </div>
                    </div>

                    <!-- Books Management -->
                    <div class="col">
                        <div class="card text-center shadow-custom h-100">
                            <div class="card-body d-flex flex-column">
                                <h5 class="card-title mb-2">
                                    <i data-lucide="book-open" class="me-1"></i>
                                    Books Management
                                </h5>
                                <p class="card-text flex-grow-1 text-muted small">
                                    Add or edit books, update copies, and manage availability status.
                                </p>
                                <a href="<?= BASE_URL ?>app/view/bookPage.php" class="btn btn-primary mt-auto">
                                    Manage Books
                                </a>
                            </div>
                        </div>
                    </div>

                    <!-- Catalog Search -->
                    <div class="col">
                        <div class="card text-center shadow-custom h-100">
                            <div class="card-body d-flex flex-column">
                                <h5 class="card-title mb-2">
                                    <i data-lucide="search" class="me-1"></i>
                                    Catalog Search
                                </h5>
                                <p class="card-text flex-grow-1 text-muted small">
                                    Search and browse books by title, author, category, or filters.
                                </p>
                                <a href="<?= BASE_URL ?>app/view/CatalogSearch_Browsing-EN.php" class="btn btn-primary mt-auto">
                                    Search Catalog
                                </a>
                            </div>
                        </div>
                    </div>

                    <!-- Borrow / Return -->
                    <div class="col">
                        <div class="card text-center shadow-custom h-100">
                            <div class="card-body d-flex flex-column">
                                <h5 class="card-title mb-2">
                                    <i data-lucide="scan-barcode" class="me-1"></i>
                                    Borrow &amp; Return
                                </h5>
                                <p class="card-text flex-grow-1 text-muted small">
                                    Issue and return books, handle due dates, renewals, and fines.
                                </p>
                                <a href="<?= BASE_URL ?>app/view/BorrowBook.php" class="btn btn-primary mt-auto">
                                    Manage Borrowing
                                </a>
                                <a href="<?= BASE_URL ?>app/view/bookReturnAndRenew.php" class="btn btn-outline-secondary btn-sm mt-2">
                                    Return / Renew
                                </a>
                            </div>
                        </div>
                    </div>

                    <!-- Reservations -->
                    <div class="col">
                        <div class="card text-center shadow-custom h-100">
                            <div class="card-body d-flex flex-column">
                                <h5 class="card-title mb-2">
                                    <i data-lucide="bookmark" class="me-1"></i>
                                    Reservations
                                </h5>
                                <p class="card-text flex-grow-1 text-muted small">
                                    Manage reservation queues, notify users, and track availability.
                                </p>
                                <a href="<?= BASE_URL ?>app/view/reservations.php" class="btn btn-primary mt-auto">
                                    Manage Reservations
                                </a>
                            </div>
                        </div>
                    </div>

                    <!-- Notifications -->
                    <div class="col">
                        <div class="card text-center shadow-custom h-100">
                            <div class="card-body d-flex flex-column">
                                <h5 class="card-title mb-2">
                                    <i data-lucide="bell" class="me-1"></i>
                                    Notifications &amp; Alerts
                                </h5>
                                <p class="card-text flex-grow-1 text-muted small">
                                    Send borrowing alerts, reservation availability, and library announcements.
                                </p>
                                <a href="<?= BASE_URL ?>app/view/notifications.php" class="btn btn-primary mt-auto">
                                    Open Notification Center
                                </a>
                            </div>
                        </div>
                    </div>

                    <!-- Reports -->
                    <div class="col">
                        <div class="card text-center shadow-custom h-100">
                            <div class="card-body d-flex flex-column">
                                <h5 class="card-title mb-2">
                                    <i data-lucide="bar-chart-2" class="me-1"></i>
                                    Reports &amp; Analytics
                                </h5>
                                <p class="card-text flex-grow-1 text-muted small">
                                    View borrowing trends, popular titles, and usage statistics.
                                </p>
                                <a href="<?= BASE_URL ?>app/view/reports.php" class="btn btn-primary mt-auto">
                                    View Reports
                                </a>
                            </div>
                        </div>
                    </div>

                    <!-- System Administration -->
                    <div class="col">
                        <div class="card text-center shadow-custom h-100">
                            <div class="card-body d-flex flex-column">
                                <h5 class="card-title mb-2">
                                    <i data-lucide="settings" class="me-1"></i>
                                    System Administration
                                </h5>
                                <p class="card-text flex-grow-1 text-muted small">
                                    Configure policies, manage staff access, and update system settings.
                                </p>
                                <a href="<?= BASE_URL ?>app/view/adminSettings.php" class="btn btn-primary mt-auto">
                                    Admin Settings
                                </a>
                            </div>
                        </div>
                    </div>

                </div><!-- /row -->
            </div><!-- /admin-wrapper -->
        </div>
    </section>
</main>

<footer class="py-3 mt-4">
    <div class="container text-center small text-muted">
        © 2025 Library System. All rights reserved.
    </div>
</footer>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
    // تفعيل أيقونات Lucide لو حاب تستعملها
    if (window.lucide) {
        lucide.createIcons();
    }
</script>
</body>

</html>
