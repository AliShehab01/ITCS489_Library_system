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

$db   = new Database();
$conn = $db->conn;

// فلتر بسيط بالـ GET: all | unread | due | overdue | reservation | announcement
$filter = $_GET['filter'] ?? 'all';
$allowedFilters = ['all', 'unread', 'due', 'overdue', 'reservation', 'announcement'];
if (!in_array($filter, $allowedFilters, true)) {
    $filter = 'all';
}

// بناء الشرط حسب الفلتر
$where = "user_id = :uid";
$params = [':uid' => $userId];

if ($filter === 'unread') {
    $where .= " AND is_read = 0";
} elseif ($filter !== 'all') {
    $where .= " AND type = :type";
    $params[':type'] = $filter;
}

// جلب آخر 50 إشعار
$sql = "
    SELECT id, title, message, type, is_read, created_at
    FROM notifications
    WHERE $where
    ORDER BY created_at DESC
    LIMIT 50
";
$stmt = $conn->prepare($sql);
$stmt->execute($params);
$notifications = $stmt->fetchAll(PDO::FETCH_ASSOC);

// إحصائيات سريعة للبطاقات العلوية
$statsSql = "
    SELECT
        COUNT(*) AS total,
        SUM(CASE WHEN is_read = 0 THEN 1 ELSE 0 END) AS unread,
        SUM(CASE WHEN type = 'due' THEN 1 ELSE 0 END) AS due,
        SUM(CASE WHEN type = 'overdue' THEN 1 ELSE 0 END) AS overdue,
        SUM(CASE WHEN type = 'reservation' THEN 1 ELSE 0 END) AS reservation,
        SUM(CASE WHEN type = 'announcement' THEN 1 ELSE 0 END) AS announcement
    FROM notifications
    WHERE user_id = :uid
";
$stmtStats = $conn->prepare($statsSql);
$stmtStats->execute([':uid' => $userId]);
$stats = $stmtStats->fetch(PDO::FETCH_ASSOC) ?: [];

function safeInt($arr, $key) {
    return isset($arr[$key]) ? (int)$arr[$key] : 0;
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Notifications – Library</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="<?= BASE_URL ?>public/css/style.css">
</head>

<body>

<?php include __DIR__ . '/navbar.php'; ?>

<main class="site-content">

    <!-- Header -->
    <section class="py-4 bg-white border-bottom">
        <div class="container">
            <h1 class="h4 mb-1">Notifications</h1>
            <p class="text-muted mb-0">
                Due-date reminders, overdue alerts, reservation updates, and library announcements.
            </p>
        </div>
    </section>

    <section class="py-4">
        <div class="container my-2">
            <div class="admin-wrapper mx-auto">

                <!-- Summary cards -->
                <div class="row g-3 mb-4">
                    <div class="col-6 col-md-3">
                        <div class="card shadow-custom h-100">
                            <div class="card-body py-2">
                                <div class="text-muted small mb-1">All</div>
                                <div class="h5 mb-0"><?= safeInt($stats, 'total'); ?></div>
                            </div>
                        </div>
                    </div>
                    <div class="col-6 col-md-3">
                        <div class="card shadow-custom h-100">
                            <div class="card-body py-2">
                                <div class="text-muted small mb-1">Unread</div>
                                <div class="h5 mb-0 text-primary"><?= safeInt($stats, 'unread'); ?></div>
                            </div>
                        </div>
                    </div>
                    <div class="col-6 col-md-3">
                        <div class="card shadow-custom h-100">
                            <div class="card-body py-2">
                                <div class="text-muted small mb-1">Due / Overdue</div>
                                <div class="h5 mb-0">
                                    <?= safeInt($stats, 'due') + safeInt($stats, 'overdue'); ?>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-6 col-md-3">
                        <div class="card shadow-custom h-100">
                            <div class="card-body py-2">
                                <div class="text-muted small mb-1">Reservations</div>
                                <div class="h5 mb-0">
                                    <?= safeInt($stats, 'reservation'); ?>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Filters -->
                <div class="card shadow-custom mb-4">
                    <div class="card-body py-2">
                        <div class="d-flex flex-wrap align-items-center gap-2">
                            <span class="text-muted small me-2">Filter:</span>

                            <?php
                            // Helper صغير للفلتر
                            $baseUrl = 'userNotifications.php';
                            $filtersList = [
                                'all'          => 'All',
                                'unread'       => 'Unread',
                                'due'          => 'Due',
                                'overdue'      => 'Overdue',
                                'reservation'  => 'Reservations',
                                'announcement' => 'Announcements',
                            ];
                            foreach ($filtersList as $key => $label):
                                $active = ($filter === $key) ? 'btn-primary btn-sm' : 'btn-outline-secondary btn-sm';
                                $link   = $baseUrl . '?filter=' . urlencode($key);
                            ?>
                                <a href="<?= htmlspecialchars($link); ?>" class="btn <?= $active; ?>">
                                    <?= htmlspecialchars($label); ?>
                                </a>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>

                <!-- Notifications list -->
                <div class="card shadow-custom">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <div>
                            <h2 class="h6 mb-0">Recent notifications</h2>
                            <small class="text-muted">
                                Showing up to 50 notifications
                                <?= $filter !== 'all' ? '(filtered)' : ''; ?>
                            </small>
                        </div>
                    </div>
                    <div class="card-body p-0">
                        <?php if (empty($notifications)): ?>
                            <div class="p-4 text-center text-muted small">
                                No notifications to display.
                            </div>
                        <?php else: ?>
                            <div class="list-group list-group-flush">
                                <?php foreach ($notifications as $n): ?>
                                    <?php
                                    $type = $n['type'] ?? 'info';
                                    $title = $n['title'] ?: ucfirst($type);

                                    switch ($type) {
                                        case 'due':
                                            $badgeClass = 'bg-info';
                                            $typeLabel  = 'Due reminder';
                                            break;
                                        case 'overdue':
                                            $badgeClass = 'bg-danger';
                                            $typeLabel  = 'Overdue';
                                            break;
                                        case 'reservation':
                                            $badgeClass = 'bg-success';
                                            $typeLabel  = 'Reservation';
                                            break;
                                        case 'announcement':
                                            $badgeClass = 'bg-secondary';
                                            $typeLabel  = 'Announcement';
                                            break;
                                        default:
                                            $badgeClass = 'bg-dark';
                                            $typeLabel  = ucfirst($type);
                                            break;
                                    }

                                    $createdAt = $n['created_at'] ?? '';
                                    $isUnread  = empty($n['is_read']);
                                    ?>
                                    <div class="list-group-item">
                                        <div class="d-flex justify-content-between align-items-start">
                                            <div class="me-3">
                                                <div class="d-flex align-items-center gap-2 mb-1">
                                                    <span class="badge <?= $badgeClass; ?>">
                                                        <?= htmlspecialchars($typeLabel); ?>
                                                    </span>
                                                    <?php if ($isUnread): ?>
                                                        <span class="badge bg-primary-subtle text-primary border border-primary-subtle">
                                                            Unread
                                                        </span>
                                                    <?php endif; ?>
                                                </div>
                                                <div class="fw-semibold mb-1">
                                                    <?= htmlspecialchars($title); ?>
                                                </div>
                                                <?php if (!empty($n['message'])): ?>
                                                    <div class="text-muted small">
                                                        <?= nl2br(htmlspecialchars($n['message'])); ?>
                                                    </div>
                                                <?php endif; ?>
                                            </div>

                                            <?php if (!empty($createdAt)): ?>
                                                <div class="text-end small text-muted">
                                                    <?= date('M d, Y', strtotime($createdAt)); ?><br>
                                                    <span class="text-muted">
                                                        <?= date('g:i A', strtotime($createdAt)); ?>
                                                    </span>
                                                </div>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>

            </div><!-- /.admin-wrapper -->
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
