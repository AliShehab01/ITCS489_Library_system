<?php
session_start();
require_once __DIR__ . '/../../config.php';
require '../controller/checkifadmin.php';
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Area</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet"
        xintegrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
    <!-- Lucide Icons CDN for professional icons -->
    <script src="https://unpkg.com/lucide@latest"></script>
    <!-- Custom Admin Style -->
    <style>
        :root {
            --admin-dark-bg: #1f2937;
            /* Dark Gray/Navy for professional feel */
            --admin-card-bg: #ffffff;
            /* White card */
            --admin-primary: #3b82f6;
            /* Blue primary color */
            --admin-primary-dark: #2563eb;
        }

        body {
            padding-top: 100px;
            background-color: var(--admin-dark-bg) !important;
            color: #f3f4f6;
            /* Light gray text for dark mode */
        }

        .navbar {
            background-color: #111827 !important;
            /* Even darker navbar */
            border-bottom: 3px solid var(--admin-primary);
        }

        .card {
            background-color: var(--admin-card-bg);
            border-radius: 12px;
            border: none;
            transition: transform 0.3s ease, box-shadow 0.3s ease;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
        }

        .card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 20px rgba(0, 0, 0, 0.2);
        }

        .card-title {
            color: var(--admin-primary-dark);
            font-weight: 700;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
        }

        /* Ensure card body text inside a card is readable against the white background */
        .card .card-body p,
        .card .card-body h5 {
            color: #1f2937;
        }

        .btn-primary {
            background-color: var(--admin-primary);
            border-color: var(--admin-primary);
            transition: background-color 0.2s;
        }

        .btn-primary:hover {
            background-color: var(--admin-primary-dark);
            border-color: var(--admin-primary-dark);
        }

        h1 {
            color: #ffffff;
            font-weight: 300;
            letter-spacing: 1px;
        }
    </style>
</head>

<body class="bg-light">

    <!-- Navbar -->
    <?php include __DIR__ . '/navbar.php'; ?>

    <!-- Main container -->
    <div class="container my-4">
        <h1 class="mb-5 text-center">Admin Control Panel</h1>

        <div class="row g-4 justify-content-center">
            <!-- User Management -->
            <div class="col-12 col-sm-6 col-md-4 col-lg-3">
                <div class="card text-center shadow-sm h-100">
                    <div class="card-body d-flex flex-column">
                        <h5 class="card-title">
                            <i data-lucide="users"></i>
                            User Management
                        </h5>
                        <p class="card-text flex-grow-1">Add, edit, or remove users, assign roles, manage borrowing
                            limits.</p>
                        <a href="<?= BASE_URL ?>controller/ManagingUsers.php" class="btn btn-primary mt-auto">Manage Users</a>
                    </div>
                </div>
            </div>

            <!-- Book Management -->
            <div class="col-12 col-sm-6 col-md-4 col-lg-3">
                <div class="card text-center shadow-sm h-100">
                    <div class="card-body d-flex flex-column">
                        <h5 class="card-title">
                            <i data-lucide="book-open"></i>
                            Book Management
                        </h5>
                        <p class="card-text flex-grow-1">Add, update, remove books, track availability, categorize books
                            via database.</p>
                        <a href="<?= BASE_URL ?>view/bookPage.php" class="btn btn-primary mt-auto">Manage Books</a>
                    </div>
                </div>
            </div>

            <!-- Catalog Search -->
            <div class="col-12 col-sm-6 col-md-4 col-lg-3">
                <div class="card text-center shadow-sm h-100">
                    <div class="card-body d-flex flex-column">
                        <h5 class="card-title">
                            <i data-lucide="search"></i>
                            Catalog Search
                        </h5>
                        <p class="card-text flex-grow-1">Search and browse books by title, author, category, or filters.
                        </p>
                        <a href="<?= BASE_URL ?>view/CatalogSearch_Browsing-EN.php" class="btn btn-primary mt-auto">Search Catalog</a>
                    </div>
                </div>
            </div>

            <!-- Borrowing & Returning -->
            <div class="col-12 col-sm-6 col-md-4 col-lg-3">
                <div class="card text-center shadow-sm h-100">
                    <div class="card-body d-flex flex-column">
                        <h5 class="card-title">
                            <i data-lucide="scan-barcode"></i>
                            Borrow / Return
                        </h5>
                        <p class="card-text flex-grow-1">Issue and return books, handle due dates, renewals, and fines.
                        </p>
                        <a href="<?= BASE_URL ?>view/BorrowBook.php" class="btn btn-primary mt-auto">Manage Borrowing</a>
                        <a href="<?= BASE_URL ?>view/bookReturnAndRenew.php" class="btn btn-outline-secondary mt-2">Return / Renew</a>
                    </div>
                </div>
            </div>

            <!-- Reservations -->
            <div class="col-12 col-sm-6 col-md-4 col-lg-3">
                <div class="card text-center shadow-sm h-100">
                    <div class="card-body d-flex flex-column">
                        <h5 class="card-title">
                            <i data-lucide="bookmark"></i>
                            Reservations
                        </h5>
                        <p class="card-text flex-grow-1">Allow users to reserve books currently on loan. Manage queues
                            and notifications.</p>
                        <a href="<?= BASE_URL ?>view/reservations.php" class="btn btn-primary mt-auto">Manage Reservations</a>
                    </div>
                </div>
            </div>

            <!-- Notifications -->
            <div class="col-12 col-sm-6 col-md-4 col-lg-3">
                <div class="card text-center shadow-sm h-100">
                    <div class="card-body d-flex flex-column">
                        <h5 class="card-title">
                            <i data-lucide="bell"></i>
                            Notifications
                        </h5>
                        <p class="card-text flex-grow-1">View and manage notifications sent to users when books become
                            available.</p>
                        <a href="<?= BASE_URL ?>view/notifications.php" class="btn btn-primary mt-auto">Manage Notifications</a>
                    </div>
                </div>
            </div>

            <!-- Reports -->
            <div class="col-12 col-sm-6 col-md-4 col-lg-3">
                <div class="card text-center shadow-sm h-100">
                    <div class="card-body d-flex flex-column">
                        <h5 class="card-title">
                            <i data-lucide="line-chart"></i>
                            Reports & Tracking
                        </h5>
                        <p class="card-text flex-grow-1">Generate reports on borrowed, overdue, and reserved books,
                            fines, and statistics.</p>
                        <a href="<?= BASE_URL ?>view/reports.php" class="btn btn-primary mt-auto">View Reports</a>
                    </div>
                </div>
            </div>

            <!-- System Administration -->
            <div class="col-12 col-sm-6 col-md-4 col-lg-3">
                <div class="card text-center shadow-sm h-100">
                    <div class="card-body d-flex flex-column">
                        <h5 class="card-title">
                            <i data-lucide="settings"></i>
                            System Administration
                        </h5>
                        <p class="card-text flex-grow-1">Manage staff access, configure policies, backup data, and
                            monitor system usage.</p>
                        <a href="<?= BASE_URL ?>view/adminSettings.php" class="btn btn-primary mt-auto">Admin Settings</a>
                    </div>
                </div>
            </div>

        </div> <!-- row -->
    </div> <!-- container -->

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"
        xintegrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous">
    </script>
    <!-- Initialize Lucide Icons -->
    <script>
        lucide.createIcons();
    </script>
</body>

</html>