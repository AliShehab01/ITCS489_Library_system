<?php
session_start();
require_once __DIR__ . '/../../config.php';
require_once __DIR__ . '/../controller/checkifadmin.php';
require_once __DIR__ . '/../models/dbconnect.php';

include __DIR__ . '/navbar.php';
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Settings</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="<?= PUBLIC_URL ?>css/style.css">
</head>

<body>
    <div class="container mt-5">
        <h1 class="mb-4">System Administration</h1>

        <div class="row g-4">
            <div class="col-md-6 col-lg-4">
                <div class="card h-100">
                    <div class="card-body">
                        <h5 class="card-title">User Management</h5>
                        <p class="card-text">Manage user accounts, roles, and permissions.</p>
                        <a href="<?= BASE_URL ?>controller/ManagingUsers.php" class="btn btn-primary">Manage Users</a>
                    </div>
                </div>
            </div>
            <div class="col-md-6 col-lg-4">
                <div class="card h-100">
                    <div class="card-body">
                        <h5 class="card-title">Book Management</h5>
                        <p class="card-text">Add, update, or remove books from the catalog.</p>
                        <a href="<?= BASE_URL ?>view/bookPage.php" class="btn btn-primary">Manage Books</a>
                    </div>
                </div>
            </div>
            <div class="col-md-6 col-lg-4">
                <div class="card h-100">
                    <div class="card-body">
                        <h5 class="card-title">Notifications</h5>
                        <p class="card-text">Send announcements and manage notification settings.</p>
                        <a href="<?= BASE_URL ?>view/notifications.php" class="btn btn-primary">Manage Notifications</a>
                    </div>
                </div>
            </div>
            <div class="col-md-6 col-lg-4">
                <div class="card h-100">
                    <div class="card-body">
                        <h5 class="card-title">Reports</h5>
                        <p class="card-text">View library statistics and generate reports.</p>
                        <a href="<?= BASE_URL ?>view/reports.php" class="btn btn-primary">View Reports</a>
                    </div>
                </div>
            </div>
            <div class="col-md-6 col-lg-4">
                <div class="card h-100">
                    <div class="card-body">
                        <h5 class="card-title">Return & Renew</h5>
                        <p class="card-text">Process book returns and renewals.</p>
                        <a href="<?= BASE_URL ?>view/bookReturnAndRenew.php" class="btn btn-primary">Process Returns</a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>
