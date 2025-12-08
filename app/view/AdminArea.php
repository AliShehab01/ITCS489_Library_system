<?php
session_start();
require_once __DIR__ . '/../../config.php';
require_once __DIR__ . '/../controller/checkifadmin.php';
require_once __DIR__ . '/../models/dbconnect.php';

$db = new Database();
$conn = $db->conn;

// Get stats
$totalBooks = 0;
$totalUsers = 0;
$activeBorrows = 0;
$overdueBorrows = 0;

try {
    $totalBooks = (int)$conn->query("SELECT COUNT(*) FROM books")->fetchColumn();
    $totalUsers = (int)$conn->query("SELECT COUNT(*) FROM users")->fetchColumn();
    $activeBorrows = (int)$conn->query("SELECT COUNT(*) FROM borrows WHERE isReturned = 'false'")->fetchColumn();
    $overdueBorrows = (int)$conn->query("SELECT COUNT(*) FROM borrows WHERE isReturned = 'false' AND dueDate < CURDATE()")->fetchColumn();
} catch (Exception $e) {
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Control Panel</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        html,
        body {
            margin: 0;
            padding: 0;
        }

        body {
            padding-top: 56px;
            background: #f8f9fa;
        }

        .stat-card {
            text-align: center;
        }

        .stat-card h2 {
            font-size: 2.5rem;
            margin-bottom: 0;
        }

        .admin-card {
            transition: transform 0.2s;
        }

        .admin-card:hover {
            transform: translateY(-5px);
        }
    </style>
</head>

<body>
    <?php include __DIR__ . '/navbar.php'; ?>

    <div class="container py-4">
        <h1 class="mb-4">⚙️ Admin Control Panel</h1>

        <!-- Stats -->
        <div class="row g-3 mb-4">
            <div class="col-6 col-md-3">
                <div class="card stat-card">
                    <div class="card-body">
                        <h2 class="text-primary"><?= $totalBooks ?></h2>
                        <small class="text-muted">Total Books</small>
                    </div>
                </div>
            </div>
            <div class="col-6 col-md-3">
                <div class="card stat-card">
                    <div class="card-body">
                        <h2 class="text-success"><?= $totalUsers ?></h2>
                        <small class="text-muted">Total Users</small>
                    </div>
                </div>
            </div>
            <div class="col-6 col-md-3">
                <div class="card stat-card">
                    <div class="card-body">
                        <h2 class="text-info"><?= $activeBorrows ?></h2>
                        <small class="text-muted">Active Borrows</small>
                    </div>
                </div>
            </div>
            <div class="col-6 col-md-3">
                <div class="card stat-card <?= $overdueBorrows > 0 ? 'border-danger' : '' ?>">
                    <div class="card-body">
                        <h2 class="<?= $overdueBorrows > 0 ? 'text-danger' : 'text-secondary' ?>"><?= $overdueBorrows ?></h2>
                        <small class="text-muted">Overdue</small>
                    </div>
                </div>
            </div>
        </div>

        <!-- Admin Actions -->
        <div class="row g-4">
            <div class="col-md-6 col-lg-4">
                <div class="card admin-card h-100 shadow-sm">
                    <div class="card-body">
                        <h5 class="card-title">📚 Book Management</h5>
                        <p class="card-text">Add new books, update details, change quantities, or remove books from catalog.</p>
                        <a href="<?= BASE_URL ?>view/bookPage.php" class="btn btn-primary">Manage Books</a>
                    </div>
                </div>
            </div>
            <div class="col-md-6 col-lg-4">
                <div class="card admin-card h-100 shadow-sm">
                    <div class="card-body">
                        <h5 class="card-title">👥 User Management</h5>
                        <p class="card-text">View all users, change roles, edit profiles, or deactivate accounts.</p>
                        <a href="<?= BASE_URL ?>controller/ManagingUsers.php" class="btn btn-primary">Manage Users</a>
                    </div>
                </div>
            </div>
            <div class="col-md-6 col-lg-4">
                <div class="card admin-card h-100 shadow-sm">
                    <div class="card-body">
                        <h5 class="card-title">🔄 Borrow / Return</h5>
                        <p class="card-text">Process book returns, renew loans, and manage overdue items.</p>
                        <a href="<?= BASE_URL ?>view/bookReturnAndRenew.php" class="btn btn-primary">Process Returns</a>
                    </div>
                </div>
            </div>
            <div class="col-md-6 col-lg-4">
                <div class="card admin-card h-100 shadow-sm">
                    <div class="card-body">
                        <h5 class="card-title">🔖 Reservations</h5>
                        <p class="card-text">View and manage all book reservations in the system.</p>
                        <a href="<?= BASE_URL ?>view/adminReservations.php" class="btn btn-outline-primary">View Reservations</a>
                    </div>
                </div>
            </div>
            <div class="col-md-6 col-lg-4">
                <div class="card admin-card h-100 shadow-sm">
                    <div class="card-body">
                        <h5 class="card-title">🔔 Notifications</h5>
                        <p class="card-text">Send announcements to all users or specific groups.</p>
                        <a href="<?= BASE_URL ?>view/notifications.php" class="btn btn-outline-primary">Send Notifications</a>
                    </div>
                </div>
            </div>
            <div class="col-md-6 col-lg-4">
                <div class="card admin-card h-100 shadow-sm">
                    <div class="card-body">
                        <h5 class="card-title">📊 Reports</h5>
                        <p class="card-text">View borrowing statistics, popular books, and user activity.</p>
                        <a href="<?= BASE_URL ?>view/reports.php" class="btn btn-outline-primary">View Reports</a>
                    </div>
                </div>
            </div>
            <div class="col-md-6 col-lg-4">
                <div class="card admin-card h-100 shadow-sm border-warning">
                    <div class="card-body">
                        <h5 class="card-title">📋 Borrowing Policies</h5>
                        <p class="card-text">Configure loan durations, borrowing limits, fine rates, and reservation rules.</p>
                        <a href="<?= BASE_URL ?>view/adminPolicies.php" class="btn btn-warning">Configure Policies</a>
                    </div>
                </div>
            </div>
            <div class="col-md-6 col-lg-4">
                <div class="card admin-card h-100 shadow-sm border-success">
                    <div class="card-body">
                        <h5 class="card-title">💾 Backup & Restore</h5>
                        <p class="card-text">Create backups, download, and restore system data.</p>
                        <a href="<?= BASE_URL ?>view/adminBackup.php" class="btn btn-success">Manage Backups</a>
                    </div>
                </div>
            </div>
            <div class="col-md-6 col-lg-4">
                <div class="card admin-card h-100 shadow-sm border-info">
                    <div class="card-body">
                        <h5 class="card-title">📊 Audit Logs</h5>
                        <p class="card-text">Monitor system usage, track user activities, and review security events.</p>
                        <a href="<?= BASE_URL ?>view/adminAuditLogs.php" class="btn btn-info">View Audit Logs</a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>