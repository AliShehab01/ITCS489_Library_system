<?php
session_start();
require_once __DIR__ . '/../../config.php';
require_once __DIR__ . '/../models/dbconnect.php';
require_once __DIR__ . '/../models/CreateDefaultDBTables.php';

if (!isset($_SESSION['username'])) {
    header('Location: ' . BASE_URL . 'app/view/login.php');
    exit;
}

// Fetch announcements from database
$db = new Database();
$conn = $db->conn;
$announcements = [];
$debugInfo = '';

try {
    $userId = $_SESSION['user_id'] ?? null;
    
    if (!$userId) {
        $debugInfo = "Error: user_id not found in session";
    } else {
        // fetch latest 3 announcements for this user, include is_read so we can show the badge once
        $announcementsStmt = $conn->prepare("
            SELECT n.id, n.title, n.message, n.created_at, n.is_read
            FROM notifications n
            WHERE n.type = 'announcement' AND n.user_id = :user_id
            ORDER BY n.created_at DESC
            LIMIT 3
        ");

        $announcementsStmt->execute([':user_id' => (int)$userId]);
        $announcements = $announcementsStmt->fetchAll(PDO::FETCH_ASSOC);

        if (!$announcements) {
            $debugInfo = "No announcements found for user ID: " . $userId;
        } else {
            // collect unread announcement ids so we can mark them read after showing
            $unreadIds = array_map(function($a){ return (int)$a['id']; }, array_filter($announcements, function($a){ return empty($a['is_read']) || $a['is_read'] == 0; }));
            if (!empty($unreadIds)) {
                // build placeholders for prepared statement
                $placeholders = implode(',', array_fill(0, count($unreadIds), '?'));
                $upd = $conn->prepare("UPDATE notifications SET is_read = 1, updated_at = NOW() WHERE id IN ($placeholders)");
                $upd->execute($unreadIds);
            }
        }
    }
} catch (Exception $e) {
    $debugInfo = "Database error: " . $e->getMessage();
}

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <link rel="stylesheet" href="<?= BASE_URL ?>public/css/style.css" />
    <?php if (!defined('STYLE_LOADED')) { define('STYLE_LOADED', true); } ?>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet"
        integrity="sha384-sRIl4kxILFvY47J16cr9ZwB07vP4J8+LH7qKQnuqkuIAvNWLzeN8tE5YBujZqJLB" crossorigin="anonymous" />
    <title>Library System</title>
</head>

<body>
    <?php include __DIR__ . '/navbar.php'; ?>

    <main class="page-shell">

        <section class="page-hero mb-4">
            <div class="row align-items-center g-4">
                <div class="col-lg-8">
                    <p class="text-uppercase text-muted fw-semibold mb-2">Library system</p>
                    <h1 class="display-5 mb-3">Welcome back<?= isset($_SESSION['first_name']) ? ', ' . htmlspecialchars($_SESSION['first_name']) : '' ?>.</h1>
                    <p class="lead mb-0">Track your borrows, discover new titles, and keep up with announcements—all from one consistent dashboard.</p>
                </div>
                <div class="col-lg-4 text-lg-end d-flex d-lg-block gap-2">
                    <a class="btn btn-primary me-2" href="<?= BASE_URL ?>app/view/CatalogSearch_Browsing-EN.php">Browse catalog</a>
                    <a class="btn btn-outline-primary" href="<?= BASE_URL ?>app/view/borrowedDashboard.php">View borrowed</a>
                </div>
            </div>
        </section>

        <!-- Announcements -->
        <section class="announcements">
            <div class="section-title">
                <span class="pill">!</span>
                <span>Announcements</span>
            </div>
            <?php if ($debugInfo): ?>
                <div class="alert alert-warning mb-3">Debug: <?= htmlspecialchars($debugInfo) ?></div>
            <?php endif; ?>
            <div class="card shadow-custom mb-4">
                <div class="card-body p-0">
                    <?php if (empty($announcements)): ?>
                        <div class="list-group-item text-muted">
                            <p class="mb-0">No announcements at this time.</p>
                        </div>
                    <?php else: ?>
                        <?php foreach ($announcements as $announcement): ?>
                            <div class="list-group-item d-flex justify-content-between align-items-start">
                                <div>
                                    <div class="fw-semibold"><?= htmlspecialchars($announcement['title']) ?></div>
                                    <small class="text-muted d-block"><?= htmlspecialchars($announcement['message']) ?></small>
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

        <!-- Site overview -->
        <section class="overview">
            <div class="section-title">
                <span class="pill">↺</span>
                <span>Explore the site</span>
            </div>
            <div class="row g-3">
                <div class="col-12 col-md-6 col-lg-4">
                    <div class="card shadow-custom h-100">
                        <div class="card-body">
                            <h5 class="card-title">Catalog Search &amp; Browsing</h5>
                            <p class="card-text">
                                Search by title, author, ISBN, or category. Filter by availability and sort by date added.
                            </p>
                            <a class="btn btn-primary btn-sm" href="<?= BASE_URL ?>app/view/CatalogSearch_Browsing-EN.php">Go to catalog</a>
                        </div>
                    </div>
                </div>
                <div class="col-12 col-md-6 col-lg-4">
                    <div class="card shadow-custom h-100">
                        <div class="card-body">
                            <h5 class="card-title">My Account</h5>
                            <p class="card-text">
                                View and update your profile and contact info, and check borrowing limits.
                            </p>
                            <a class="btn btn-outline-primary btn-sm" href="<?= BASE_URL ?>app/view/editUserProfile.php?username=<?= urlencode($_SESSION['username']) ?>">Manage account</a>
                        </div>
                    </div>
                </div>
                <div class="col-12 col-md-6 col-lg-4">
                    <div class="card shadow-custom h-100">
                        <div class="card-body">
                            <h5 class="card-title">Borrowed &amp; Returns</h5>
                            <p class="card-text">
                                Track due dates, renew eligible items, and review fines or overdues if any.
                            </p>
                            <a class="btn btn-outline-primary btn-sm" href="<?= BASE_URL ?>app/view/borrowedDashboard.php">View borrowed</a>
                        </div>
                    </div>
                </div>
                <div class="col-12 col-md-6 col-lg-4">
                    <div class="card shadow-custom h-100">
                        <div class="card-body">
                            <h5 class="card-title">Reservations</h5>
                            <p class="card-text">
                                Reserve checked-out books and get notified when they become available.
                            </p>
                            <a class="btn btn-outline-primary btn-sm" href="<?= BASE_URL ?>app/view/reservations.php">Manage reservations</a>
                        </div>
                    </div>
                </div>
                <div class="col-12 col-md-6 col-lg-4">
                    <div class="card shadow-custom h-100">
                        <div class="card-body">
                            <h5 class="card-title">Notifications</h5>
                            <p class="card-text">
                                Due-date reminders, reservation alerts, and key library updates in one place.
                            </p>
                            <a class="btn btn-outline-primary btn-sm" href="<?= BASE_URL ?>app/view/userNotifications.php">Open notifications</a>
                        </div>
                    </div>
                </div>
                <div class="col-12 col-md-6 col-lg-4">
                    <div class="card shadow-custom h-100">
                        <div class="card-body">
                            <h5 class="card-title">Reports &amp; history</h5>
                            <p class="card-text">
                                Review borrowing patterns and generate quick summaries for your activity.
                            </p>
                            <a class="btn btn-outline-primary btn-sm" href="<?= BASE_URL ?>app/view/reports.php">View reports</a>
                        </div>
                    </div>
                </div>
            </div>
        </section>
    </main>

    <footer class="app-footer text-center">
        <small>© 2025 Library System. All rights reserved.</small>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"
        integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous">
    </script>
</body>

</html>
