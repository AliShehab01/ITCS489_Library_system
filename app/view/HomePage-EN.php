<?php
session_start();
require_once __DIR__ . '/../../config.php';

include "../view/navbar.php";
if (!isset($_SESSION['username'])) {
    header("Location: login.php");
}

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <link rel="stylesheet" href="../../public/css/style.css" />
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet"
        integrity="sha384-sRIl4kxILFvY47J16cr9ZwB07vP4J8+LH7qKQnuqkuIAvNWLzeN8tE5YBujZqJLB" crossorigin="anonymous" />
    <title>Library System</title>
</head>

<body>

    <!-- Announcements -->
    <section class="announcements">
        <div class="container">
            <h1 class="sectionTitle">Announcements</h1>
            <div class="list-group shadow-custom">
                <!-- Examples; replace later with dynamic content -->
                <div class="list-group-item d-flex justify-content-between align-items-start">
                    <div>
                        <div class="fw-semibold">Midterm Hours Update</div>
                        <small class="text-muted">Library open 8:00 to 22:00 this week.</small>
                    </div>
                    <span class="badge bg-primary rounded-pill">New</span>
                </div>
                <div class="list-group-item">
                    <div class="fw-semibold">System Maintenance</div>
                    <small class="text-muted">Catalog search may be slow on Friday from 9 to 10 PM.</small>
                </div>
            </div>
        </div>
    </section>

    <!-- Site overview -->
    <section class="overview">
        <div class="container">
            <h2 class="sectionTitle">Explore the Site</h2>
            <div class="row g-3">
                <!-- Catalog -->
                <div class="col-12 col-md-6 col-lg-4">
                    <div class="card shadow-custom h-100">
                        <div class="card-body">
                            <h5 class="card-title">Catalog Search &amp; Browsing</h5>
                            <p class="card-text">
                                Search by title, author, ISBN, or category. Filter by availability and sort by
                                publication year or date added.
                            </p>
                            <a class="btn btn-primary btn-sm" href="CatalogSearch_Browsing-EN.php">Go to Catalog</a>
                        </div>
                    </div>
                </div>
                <!-- My Account -->
                <div class="col-12 col-md-6 col-lg-4">
                    <div class="card shadow-custom h-100">
                        <div class="card-body">
                            <h5 class="card-title">My Account</h5>
                            <p class="card-text">
                                View and update your profile and contact info, and check borrowing limits based on
                                membership type.
                            </p>
                            <a class="btn btn-outline-primary btn-sm" href="account.html">Manage Account</a>
                        </div>
                    </div>
                </div>
                <!-- Borrowed & Returns -->
                <div class="col-12 col-md-6 col-lg-4">
                    <div class="card shadow-custom h-100">
                        <div class="card-body">
                            <h5 class="card-title">Borrowed &amp; Returns</h5>
                            <p class="card-text">
                                Track due dates, renew eligible items, and review fines or overdues if any.
                            </p>
                            <a class="btn btn-outline-primary btn-sm" href="borrowed.html">View Borrowed</a>
                        </div>
                    </div>
                </div>
                <!-- Reservations -->
                <div class="col-12 col-md-6 col-lg-4">
                    <div class="card shadow-custom h-100">
                        <div class="card-body">
                            <h5 class="card-title">Reservations</h5>
                            <p class="card-text">
                                Reserve checked-out books and get notified when they become available.
                            </p>
                            <a class="btn btn-outline-primary btn-sm" href="reservations.html">Manage Reservations</a>
                        </div>
                    </div>
                </div>
                <!-- Notifications -->
                <div class="col-12 col-md-6 col-lg-4">
                    <div class="card shadow-custom h-100">
                        <div class="card-body">
                            <h5 class="card-title">Notifications</h5>
                            <p class="card-text">
                                Due-date reminders, reservation alerts, and key library updates in one place.
                            </p>
                            <a class="btn btn-outline-primary btn-sm" href="notifications.html">Open Notifications</a>
                        </div>
                    </div>
                </div>
                <!-- Reading History -->
                <div class="col-12 col-md-6 col-lg-4">
                    <div class="card shadow-custom h-100">
                        <div class="card-body">
                            <h5 class="card-title">Reading History</h5>
                            <p class="card-text">
                                Browse your past borrowing records and most-read categories.
                            </p>
                            <a class="btn btn-outline-primary btn-sm" href="history.html">View History</a>
                        </div>
                    </div>
                </div>

    </section>

    <footer class="bg-dark text-white pt-5 pb-4">
        <div class="container text-center text-md-start">
            <div class="row text-center text-md-start">
                © 2025 XXXXXX. All rights reserved.
            </div>
        </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"
        integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous">
    </script>
</body>

</html>