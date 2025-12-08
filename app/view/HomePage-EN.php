<?php
session_start();
require_once __DIR__ . '/../../config.php';
require_once __DIR__ . '/../models/dbconnect.php';

// تأكد أن المستخدم مسجل دخول
if (!isset($_SESSION['user_id'])) {
    $loginUrl = (defined('BASE_URL') ? BASE_URL : '/') . 'app/view/login.php';
    header('Location: ' . $loginUrl);
    exit;
}

$userId   = (int)$_SESSION['user_id'];
$username = $_SESSION['username'] ?? '';

// جلب آخر الإعلانات للمستخدم (من جدول notifications نوع announcement)
$db   = new Database();
$conn = $db->conn;

$announcements = [];
try {
    $stmt = $conn->prepare("
        SELECT id, title, message, type, is_read, created_at
        FROM notifications
        WHERE user_id = :uid
          AND type = 'announcement'
        ORDER BY created_at DESC
        LIMIT 2
    ");
    $stmt->execute([':uid' => $userId]);
    $announcements = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (Exception $e) {
    // ممكن تسجل الخطأ في لوج، بس ما نعرضه للمستخدم
    $announcements = [];
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Library Home</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="<?= BASE_URL ?>public/css/style.css">
</head>
<body>

<?php include __DIR__ . '/navbar.php'; ?>

<main class="site-content">

    <!-- Announcements -->
    <section class="py-4 bg-white border-bottom">
        <div class="container">
            <h1 class="h4 mb-1">Announcements</h1>
            <p class="text-muted mb-3">
                Midterm hours, system maintenance, and important library updates.
            </p>

            <div class="list-group shadow-custom">
                <?php if (empty($announcements)): ?>
                    <div class="list-group-item text-muted">
                        No announcements at this time.
                    </div>
                <?php else: ?>
                    <?php foreach ($announcements as $a): ?>
                        <div class="list-group-item d-flex justify-content-between align-items-start">
                            <div>
                                <div class="fw-semibold">
                                    <?= htmlspecialchars($a['title'] ?? 'Announcement'); ?>
                                </div>

                                <?php if (!empty($a['message'])): ?>
                                    <small class="text-muted">
                                        <?= nl2br(htmlspecialchars($a['message'])); ?>
                                    </small><br>
                                <?php endif; ?>

                                <?php if (!empty($a['created_at'])): ?>
                                    <small class="text-muted" style="font-size: 0.75rem;">
                                        <?= date('M d, Y g:i A', strtotime($a['created_at'])); ?>
                                    </small>
                                <?php endif; ?>
                            </div>

                            <?php if (empty($a['is_read'])): ?>
                                <span class="badge bg-primary rounded-pill">New</span>
                            <?php endif; ?>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>
    </section>

    <!-- Explore the site -->
    <section class="py-4">
        <div class="container my-2">
            <h2 class="h5 mb-3">Explore the site</h2>

            <div class="row row-cols-1 row-cols-md-2 row-cols-lg-3 g-3">

                <!-- Catalog Search & Browsing -->
                <div class="col">
                    <div class="card shadow-custom h-100">
                        <div class="card-body d-flex flex-column">
                            <h5 class="card-title">Catalog Search &amp; Browsing</h5>
                            <p class="card-text text-muted small flex-grow-1">
                                Search by title, author, ISBN, or category. Filter by availability and sort by
                                publication year or date added.
                            </p>
                            <a class="btn btn-primary btn-sm mt-auto"
                               href="CatalogSearch_Browsing-EN.php">
                                Go to Catalog
                            </a>
                        </div>
                    </div>
                </div>

                <!-- My Account -->
                <div class="col">
                    <div class="card shadow-custom h-100">
                        <div class="card-body d-flex flex-column">
                            <h5 class="card-title">My Account</h5>
                            <p class="card-text text-muted small flex-grow-1">
                                View and update your profile and contact info, and check borrowing limits based on
                                membership type.
                            </p>
                            <a class="btn btn-outline-primary btn-sm mt-auto"
                               href="editUserProfile.php?username=<?= urlencode($username); ?>">
                                Manage Account
                            </a>
                        </div>
                    </div>
                </div>

                <!-- Borrowed & Returns -->
                <div class="col">
                    <div class="card shadow-custom h-100">
                        <div class="card-body d-flex flex-column">
                            <h5 class="card-title">Borrowed &amp; Returns</h5>
                            <p class="card-text text-muted small flex-grow-1">
                                Track due dates, renew eligible items, and review fines or overdues if any.
                            </p>
                            <a class="btn btn-outline-primary btn-sm mt-auto"
                               href="borrowedDashboard.php">
                                View Borrowed
                            </a>
                        </div>
                    </div>
                </div>

                <!-- Reservations -->
                <div class="col">
                    <div class="card shadow-custom h-100">
                        <div class="card-body d-flex flex-column">
                            <h5 class="card-title">Reservations</h5>
                            <p class="card-text text-muted small flex-grow-1">
                                Reserve checked-out books and get notified when they become available.
                            </p>
                            <a class="btn btn-outline-primary btn-sm mt-auto"
                               href="reservations.php">
                                Manage Reservations
                            </a>
                        </div>
                    </div>
                </div>

                <!-- Notifications -->
                <div class="col">
                    <div class="card shadow-custom h-100">
                        <div class="card-body d-flex flex-column">
                            <h5 class="card-title">Notifications</h5>
                            <p class="card-text text-muted small flex-grow-1">
                                Due-date reminders, reservation alerts, and key library updates in one place.
                            </p>
                            <a class="btn btn-outline-primary btn-sm mt-auto"
                               href="userNotifications.php">
                                Open Notifications
                            </a>
                        </div>
                    </div>
                </div>

                <!-- Reading History -->
                <div class="col">
                    <div class="card shadow-custom h-100">
                        <div class="card-body d-flex flex-column">
                            <h5 class="card-title">Reading History</h5>
                            <p class="card-text text-muted small flex-grow-1">
                                Browse your past borrowing records and most-read categories.
                            </p>
                            <!-- حالياً نستخدم نفس صفحة borrowedDashboard كـ history -->
                            <a class="btn btn-outline-primary btn-sm mt-auto"
                               href="borrowedDashboard.php">
                                View History
                            </a>
                        </div>
                    </div>
                </div>

            </div> <!-- /row -->
        </div>
    </section>
</main>

<footer class="py-3 mt-4 bg-dark text-white">
    <div class="container text-center small">
        © 2025 Library System. All rights reserved.
    </div>
</footer>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
