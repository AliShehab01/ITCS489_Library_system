<?php
session_start();
require_once __DIR__ . '/../../config.php';
require_once __DIR__ . '/../models/dbconnect.php';
require_once __DIR__ . '/../models/CreateDefaultDBTables.php';

if (!isset($_SESSION['username'])) {
    header("Location: " . BASE_URL . "view/login.php");
    exit;
}

$userRole = strtolower($_SESSION['role'] ?? 'student');
$isAdmin = in_array($userRole, ['admin', 'staff']);
$firstName = $_SESSION['first_name'] ?? $_SESSION['username'];

// Fetch announcements from database
$db = new Database();
$conn = $db->conn;
$announcements = [];

try {
    $userId = $_SESSION['user_id'] ?? null;

    if ($userId) {
        $announcementsStmt = $conn->prepare("
            SELECT n.id, n.title, n.message, n.created_at, n.is_read
            FROM notifications n
            WHERE n.type = 'announcement' AND n.user_id = :user_id
            ORDER BY n.created_at DESC
            LIMIT 3
        ");
        $announcementsStmt->execute([':user_id' => (int)$userId]);
        $announcements = $announcementsStmt->fetchAll(PDO::FETCH_ASSOC);

        // Mark as read
        if ($announcements) {
            $unreadIds = array_map(fn($a) => (int)$a['id'], array_filter($announcements, fn($a) => empty($a['is_read'])));
            if (!empty($unreadIds)) {
                $placeholders = implode(',', array_fill(0, count($unreadIds), '?'));
                $upd = $conn->prepare("UPDATE notifications SET is_read = 1, updated_at = NOW() WHERE id IN ($placeholders)");
                $upd->execute($unreadIds);
            }
        }
    }
} catch (Exception $e) {
    // Silently handle errors
}

// Get borrowing stats for user
$borrowStats = ['current' => 0, 'overdue' => 0];
if (isset($_SESSION['user_id'])) {
    try {
        $statsStmt = $conn->prepare("SELECT COUNT(*) as total, SUM(CASE WHEN dueDate < CURDATE() THEN 1 ELSE 0 END) as overdue FROM borrows WHERE user_id = :uid AND isReturned = 'false'");
        $statsStmt->execute([':uid' => (int)$_SESSION['user_id']]);
        $stats = $statsStmt->fetch(PDO::FETCH_ASSOC);
        $borrowStats['current'] = (int)($stats['total'] ?? 0);
        $borrowStats['overdue'] = (int)($stats['overdue'] ?? 0);
    } catch (Exception $e) {
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <link rel="stylesheet" href="<?= BASE_URL ?>public/css/style.css" />
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet" />
    <title>Library System - Home</title>
    <style>
        html,
        body {
            margin: 0;
            padding: 0;
        }

        body {
            padding-top: 56px;
        }

        .welcome-section {
            margin-top: 0;
            padding-top: 1.5rem;
        }

        /* Remove any default margins from first element */
        body>section:first-of-type,
        body>.welcome-section {
            margin-top: 0 !important;
        }
    </style>
</head>

<body>
    <?php include __DIR__ . "/navbar.php"; ?>

    <!-- Welcome Section -->
    <section class="welcome-section py-4 bg-light">
        <div class="container">
            <div class="row align-items-center">
                <div class="col-md-8">
                    <h1 class="display-5">Welcome, <?= htmlspecialchars($firstName) ?>!</h1>
                    <p class="lead text-muted">
                        Role: <span class="badge bg-primary"><?= htmlspecialchars(ucfirst($userRole)) ?></span>
                        <?php if ($borrowStats['overdue'] > 0): ?>
                            <span class="badge bg-danger ms-2"><?= $borrowStats['overdue'] ?> Overdue</span>
                        <?php endif; ?>
                    </p>
                </div>
                <div class="col-md-4 text-end">
                    <div class="d-flex gap-2 justify-content-end">
                        <a href="<?= BASE_URL ?>view/CatalogSearch_Browsing-EN.php" class="btn btn-primary">Browse Catalog</a>
                        <?php if ($isAdmin): ?>
                            <a href="<?= BASE_URL ?>view/AdminArea.php" class="btn btn-outline-danger">Admin Panel</a>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Quick Stats for Users -->
    <?php if (!$isAdmin): ?>
        <section class="stats-section py-3">
            <div class="container">
                <div class="row g-3">
                    <div class="col-6 col-md-3">
                        <div class="card text-center">
                            <div class="card-body py-3">
                                <h3 class="mb-0"><?= $borrowStats['current'] ?></h3>
                                <small class="text-muted">Books Borrowed</small>
                            </div>
                        </div>
                    </div>
                    <div class="col-6 col-md-3">
                        <div class="card text-center <?= $borrowStats['overdue'] > 0 ? 'border-danger' : '' ?>">
                            <div class="card-body py-3">
                                <h3 class="mb-0 <?= $borrowStats['overdue'] > 0 ? 'text-danger' : '' ?>"><?= $borrowStats['overdue'] ?></h3>
                                <small class="text-muted">Overdue</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>
    <?php endif; ?>

    <!-- Announcements -->
    <section class="announcements py-4">
        <div class="container">
            <h2 class="h4 mb-3">📢 Announcements</h2>
            <div class="list-group shadow-sm">
                <?php if (empty($announcements)): ?>
                    <div class="list-group-item text-muted">
                        No announcements at this time.
                    </div>
                <?php else: ?>
                    <?php foreach ($announcements as $announcement): ?>
                        <div class="list-group-item d-flex justify-content-between align-items-start">
                            <div>
                                <div class="fw-semibold"><?= htmlspecialchars($announcement['title']) ?></div>
                                <small class="text-muted"><?= htmlspecialchars($announcement['message']) ?></small>
                                <br>
                                <small class="text-muted" style="font-size: 0.75rem;">
                                    <?= date('M d, Y g:i A', strtotime($announcement['created_at'])) ?>
                                </small>
                            </div>
                            <?php if (!$announcement['is_read']): ?>
                                <span class="badge bg-primary rounded-pill">New</span>
                            <?php endif; ?>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>
    </section>

    <!-- Explore the Site -->
    <section class="overview py-4">
        <div class="container">
            <h2 class="h4 mb-3">Explore the Site</h2>
            <div class="row g-3">
                <!-- Catalog -->
                <div class="col-12 col-md-6 col-lg-4">
                    <div class="card shadow-sm h-100">
                        <div class="card-body">
                            <h5 class="card-title">📚 Catalog Search & Browsing</h5>
                            <p class="card-text">
                                Search by title, author, ISBN, or category. Filter by availability and sort results.
                            </p>
                            <a class="btn btn-primary btn-sm" href="<?= BASE_URL ?>view/CatalogSearch_Browsing-EN.php">Go to Catalog</a>
                        </div>
                    </div>
                </div>
                <!-- My Account -->
                <div class="col-12 col-md-6 col-lg-4">
                    <div class="card shadow-sm h-100">
                        <div class="card-body">
                            <h5 class="card-title">👤 My Account</h5>
                            <p class="card-text">
                                View and update your profile, contact info, and check borrowing limits.
                            </p>
                            <a class="btn btn-outline-primary btn-sm" href="<?= BASE_URL ?>view/editUserProfile.php">Manage Account</a>
                        </div>
                    </div>
                </div>
                <!-- Borrowed & Returns -->
                <div class="col-12 col-md-6 col-lg-4">
                    <div class="card shadow-sm h-100">
                        <div class="card-body">
                            <h5 class="card-title">📖 Borrowed & Returns</h5>
                            <p class="card-text">
                                Track due dates, view current loans, return books early, and check your borrowing history.
                            </p>
                            <a class="btn btn-outline-primary btn-sm" href="<?= BASE_URL ?>view/borrowedDashboard.php">View Borrowed</a>
                        </div>
                    </div>
                </div>
                <!-- Reservations -->
                <div class="col-12 col-md-6 col-lg-4">
                    <div class="card shadow-sm h-100">
                        <div class="card-body">
                            <h5 class="card-title">🔖 Reservations</h5>
                            <p class="card-text">
                                Reserve checked-out books and get notified when they become available.
                            </p>
                            <a class="btn btn-outline-primary btn-sm" href="<?= BASE_URL ?>view/reservations.php">Manage Reservations</a>
                        </div>
                    </div>
                </div>
                <!-- Notifications -->
                <div class="col-12 col-md-6 col-lg-4">
                    <div class="card shadow-sm h-100">
                        <div class="card-body">
                            <h5 class="card-title">🔔 Notifications</h5>
                            <p class="card-text">
                                Due-date reminders, reservation alerts, and library updates.
                            </p>
                            <a class="btn btn-outline-primary btn-sm" href="<?= BASE_URL ?>view/userNotifications.php">Open Notifications</a>
                        </div>
                    </div>
                </div>
                <!-- Reading History -->
                <div class="col-12 col-md-6 col-lg-4">
                    <div class="card shadow-sm h-100">
                        <div class="card-body">
                            <h5 class="card-title">📜 Reading History</h5>
                            <p class="card-text">
                                Browse your past borrowing records and reading statistics.
                            </p>
                            <a class="btn btn-outline-primary btn-sm" href="<?= BASE_URL ?>view/borrowedDashboard.php">View History</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <?php if ($isAdmin): ?>
        <!-- Admin Quick Access -->
        <section class="admin-quick py-4 bg-dark text-white">
            <div class="container">
                <h2 class="h4 mb-3">⚙️ Admin Quick Access</h2>
                <div class="row g-3">
                    <div class="col-6 col-md-3">
                        <a href="<?= BASE_URL ?>controller/ManagingUsers.php" class="btn btn-outline-light w-100">User Management</a>
                    </div>
                    <div class="col-6 col-md-3">
                        <a href="<?= BASE_URL ?>view/bookPage.php" class="btn btn-outline-light w-100">Book Management</a>
                    </div>
                    <div class="col-6 col-md-3">
                        <a href="<?= BASE_URL ?>view/notifications.php" class="btn btn-outline-light w-100">Notifications</a>
                    </div>
                    <div class="col-6 col-md-3">
                        <a href="<?= BASE_URL ?>view/reports.php" class="btn btn-outline-light w-100">Reports</a>
                    </div>
                </div>
            </div>
        </section>
    <?php endif; ?>

    <footer class="bg-dark text-white pt-4 pb-3 mt-4">
        <div class="container text-center">
            © 2025 University Library. All rights reserved.
        </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>