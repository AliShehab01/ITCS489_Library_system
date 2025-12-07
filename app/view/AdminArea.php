<?php
session_start();
require_once __DIR__ . '/../../config.php';
require_once __DIR__ . '/../controller/checkifadmin.php';
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Area</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet"
        integrity="sha384-0pUGZvbkm6XF6gxjEnlmuGrJXVbNuzT9qBBavbLwCsOGabYfZo0T0to5eqruptLy" crossorigin="anonymous">
    <link rel="stylesheet" href="<?= BASE_URL ?>public/css/style.css">
    <?php if (!defined('STYLE_LOADED')) { define('STYLE_LOADED', true); } ?>
    <script src="https://unpkg.com/lucide@latest"></script>
</head>

<body>

    <?php include __DIR__ . '/navbar.php'; ?>

    <main class="page-shell">
        <section class="page-hero mb-4">
            <div class="row align-items-center g-4">
                <div class="col-lg-8">
                    <p class="text-uppercase text-muted fw-semibold mb-2">Admin</p>
                    <h1 class="display-5 mb-2">Control Panel</h1>
                    <p class="lead mb-0">Manage users, catalog, reservations, and notifications with a consistent set of tools.</p>
                </div>
                <div class="col-lg-4 text-lg-end d-flex d-lg-block gap-2">
                    <a href="<?= BASE_URL ?>app/view/notifications.php" class="btn btn-outline-primary">Notifications</a>
                    <a href="<?= BASE_URL ?>app/view/reports.php" class="btn btn-primary">View reports</a>
                </div>
            </div>
        </section>

        <div class="row g-4 justify-content-center">
            <div class="col-12 col-sm-6 col-md-4 col-lg-3">
                <div class="card text-center shadow-custom h-100 border-0">
                    <div class="card-body d-flex flex-column">
                        <h5 class="card-title">
                            <i data-lucide="users"></i>
                            User Management
                        </h5>
                        <p class="card-text flex-grow-1">Add, edit, or remove users, assign roles, manage borrowing limits.</p>
                        <a href="<?= BASE_URL ?>app/controller/ManagingUsers.php" class="btn btn-primary mt-auto">Manage Users</a>
                    </div>
                </div>
            </div>

            <div class="col-12 col-sm-6 col-md-4 col-lg-3">
                <div class="card text-center shadow-custom h-100 border-0">
                    <div class="card-body d-flex flex-column">
                        <h5 class="card-title">
                            <i data-lucide="book-open"></i>
                            Book Management
                        </h5>
                        <p class="card-text flex-grow-1">Add, update, remove books, track availability, categorize books via database.</p>
                        <a href="<?= BASE_URL ?>app/view/bookPage.php" class="btn btn-primary mt-auto">Manage Books</a>
                    </div>
                </div>
            </div>

            <div class="col-12 col-sm-6 col-md-4 col-lg-3">
                <div class="card text-center shadow-custom h-100 border-0">
                    <div class="card-body d-flex flex-column">
                        <h5 class="card-title">
                            <i data-lucide="search"></i>
                            Catalog Search
                        </h5>
                        <p class="card-text flex-grow-1">Search and browse books by title, author, category, or filters.</p>
                        <a href="<?= BASE_URL ?>app/view/CatalogSearch_Browsing-EN.php" class="btn btn-primary mt-auto">Search Catalog</a>
                    </div>
                </div>
            </div>

            <div class="col-12 col-sm-6 col-md-4 col-lg-3">
                <div class="card text-center shadow-custom h-100 border-0">
                    <div class="card-body d-flex flex-column">
                        <h5 class="card-title">
                            <i data-lucide="scan-barcode"></i>
                            Borrow / Return
                        </h5>
                        <p class="card-text flex-grow-1">Issue and return books, handle due dates, renewals, and fines.</p>
                        <a href="<?= BASE_URL ?>app/controller/BorrowBook.php" class="btn btn-primary mt-auto">Manage Borrowing</a>
                        <a href="<?= BASE_URL ?>app/view/bookReturnAndRenew.php" class="btn btn-outline-primary mt-2">Return / Renew</a>
                    </div>
                </div>
            </div>

            <div class="col-12 col-sm-6 col-md-4 col-lg-3">
                <div class="card text-center shadow-custom h-100 border-0">
                    <div class="card-body d-flex flex-column">
                        <h5 class="card-title">
                            <i data-lucide="bookmark"></i>
                            Reservations
                        </h5>
                        <p class="card-text flex-grow-1">Allow users to reserve books currently on loan. Manage queues and notifications.</p>
                        <a href="<?= BASE_URL ?>app/view/reservations.php" class="btn btn-primary mt-auto">Manage Reservations</a>
                    </div>
                </div>
            </div>

            <div class="col-12 col-sm-6 col-md-4 col-lg-3">
                <div class="card text-center shadow-custom h-100 border-0">
                    <div class="card-body d-flex flex-column">
                        <h5 class="card-title">
                            <i data-lucide="bell"></i>
                            Notifications
                        </h5>
                        <p class="card-text flex-grow-1">View and manage notifications sent to users when books become available.</p>
                        <a href="<?= BASE_URL ?>app/view/notifications.php" class="btn btn-primary mt-auto">Manage Notifications</a>
                    </div>
                </div>
            </div>

            <div class="col-12 col-sm-6 col-md-4 col-lg-3">
                <div class="card text-center shadow-custom h-100 border-0">
                    <div class="card-body d-flex flex-column">
                        <h5 class="card-title">
                            <i data-lucide="line-chart"></i>
                            Reports &amp; Tracking
                        </h5>
                        <p class="card-text flex-grow-1">Generate reports on borrowed, overdue, and reserved books, fines, and statistics.</p>
                        <a href="<?= BASE_URL ?>app/view/reports.php" class="btn btn-primary mt-auto">View Reports</a>
                    </div>
                </div>
            </div>

            <div class="col-12 col-sm-6 col-md-4 col-lg-3">
                <div class="card text-center shadow-custom h-100 border-0">
                    <div class="card-body d-flex flex-column">
                        <h5 class="card-title">
                            <i data-lucide="settings"></i>
                            System Administration
                        </h5>
                        <p class="card-text flex-grow-1">Manage staff access, configure policies, backup data, and monitor system usage.</p>
                        <a href="<?= BASE_URL ?>app/view/adminSettings.php" class="btn btn-primary mt-auto">Admin Settings</a>
                    </div>
                </div>
            </div>

        </div> <!-- row -->
    </main>

    <footer class="app-footer text-center">
        <small>&copy; 2025 Library System. All rights reserved.</small>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"
        integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous">
    </script>
    <script>
    lucide.createIcons();
    </script>
</body>

</html>
