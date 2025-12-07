<?php
session_start();
require_once __DIR__ . '/../../config.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: ' . (defined('BASE_URL') ? BASE_URL : '/') . 'app/view/login.php');
    exit;
}

// Defensive: ignore any user_id passed from the request - always use session value
if (isset($_REQUEST['user_id'])) {
    unset($_REQUEST['user_id']);
}

require_once __DIR__ . '/../models/dbconnect.php';

$db = new Database();
$conn = $db->conn;
$userId = (int)$_SESSION['user_id'];
$flash = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['mark_read'])) {
        $id = (int)$_POST['notification_id'];
        $stmt = $conn->prepare("UPDATE notifications SET is_read = 1 WHERE id = :id AND user_id = :uid");
        $stmt->execute([':id' => $id, ':uid' => $userId]);
    } elseif (isset($_POST['mark_unread'])) {
        $id = (int)$_POST['notification_id'];
        $stmt = $conn->prepare("UPDATE notifications SET is_read = 0 WHERE id = :id AND user_id = :uid");
        $stmt->execute([':id' => $id, ':uid' => $userId]);
    } elseif (isset($_POST['mark_all_read'])) {
        $stmt = $conn->prepare("UPDATE notifications SET is_read = 1 WHERE user_id = :uid");
        $stmt->execute([':uid' => $userId]);
    }
}

$filter = $_GET['filter'] ?? 'all';
$notifications = fetchUserNotifications($conn, $userId, $filter);
$counts = fetchUserCounts($conn, $userId);

function fetchUserNotifications(PDO $conn, int $userId, string $filter): array
{
    $sql = "SELECT n.id, n.type, n.title, n.message, n.due_date, n.is_read, n.created_at, b.title AS book_title
            FROM notifications n
            LEFT JOIN books b ON b.id = n.book_id
            WHERE n.user_id = :uid";

    $params = [':uid' => $userId];
    if ($filter !== 'all') {
        $sql .= " AND n.type = :type";
        $params[':type'] = $filter;
    }

    $sql .= " ORDER BY n.is_read ASC, n.created_at DESC LIMIT 100";

    $stmt = $conn->prepare($sql);
    $stmt->execute($params);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function fetchUserCounts(PDO $conn, int $userId): array
{
    $stmt = $conn->prepare("
        SELECT
            SUM(CASE WHEN is_read = 0 THEN 1 ELSE 0 END) AS unread,
            SUM(CASE WHEN type = 'due' THEN 1 ELSE 0 END) AS due_count,
            SUM(CASE WHEN type = 'overdue' THEN 1 ELSE 0 END) AS overdue_count,
            SUM(CASE WHEN type = 'reservation' THEN 1 ELSE 0 END) AS reservation_count,
            SUM(CASE WHEN type = 'announcement' THEN 1 ELSE 0 END) AS announcement_count
        FROM notifications
        WHERE user_id = :uid
    ");
    $stmt->execute([':uid' => $userId]);
    $row = $stmt->fetch(PDO::FETCH_ASSOC) ?: [];
    return [
        'unread' => (int)($row['unread'] ?? 0),
        'due' => (int)($row['due_count'] ?? 0),
        'overdue' => (int)($row['overdue_count'] ?? 0),
        'reservation' => (int)($row['reservation_count'] ?? 0),
        'announcement' => (int)($row['announcement_count'] ?? 0)
    ];
}

function typeBadge(string $type): string
{
    switch ($type) {
        case 'due':
            return 'bg-info text-dark';
        case 'overdue':
            return 'bg-danger';
        case 'reservation':
            return 'bg-success';
        default:
            return 'bg-secondary';
    }
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <title>My Notifications</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>

<body class="bg-light">
    <?php include __DIR__ . '/navbar.php'; ?>

    <div class="container py-4">
        <div class="d-flex flex-wrap justify-content-between align-items-center mb-3">
            <div>
                <h1 class="h4 mb-1">Notifications & Alerts</h1>
                <p class="text-muted mb-0">Due-date reminders, reservation updates, and library announcements.</p>
            </div>
            <form method="POST">
                <button class="btn btn-outline-primary btn-sm" name="mark_all_read" value="1"
                    <?= empty($counts['unread']) ? 'disabled' : '' ?>>Mark all as read</button>
            </form>
        </div>

        <div class="row g-3 mb-4">
            <div class="col-6 col-md-3">
                <div class="card text-center shadow-sm">
                    <div class="card-body">
                        <div class="text-muted small">Unread</div>
                        <div class="fs-4"><?= (int)$counts['unread'] ?></div>
                    </div>
                </div>
            </div>
            <div class="col-6 col-md-3">
                <div class="card text-center shadow-sm">
                    <div class="card-body">
                        <div class="text-muted small">Due soon</div>
                        <div class="fs-4"><?= (int)$counts['due'] ?></div>
                    </div>
                </div>
            </div>
            <div class="col-6 col-md-3">
                <div class="card text-center shadow-sm">
                    <div class="card-body">
                        <div class="text-muted small">Overdue</div>
                        <div class="fs-4"><?= (int)$counts['overdue'] ?></div>
                    </div>
                </div>
            </div>
            <div class="col-6 col-md-3">
                <div class="card text-center shadow-sm">
                    <div class="card-body">
                        <div class="text-muted small">Reservations</div>
                        <div class="fs-4"><?= (int)$counts['reservation'] ?></div>
                    </div>
                </div>
            </div>
        </div>

        <div class="card shadow-sm">
            <div class="card-header bg-white">
                <div class="btn-group btn-group-sm" role="group">
                    <?php
                    $filters = ['all' => 'All', 'due' => 'Due soon', 'overdue' => 'Overdue', 'reservation' => 'Reservations', 'announcement' => 'Announcements'];
                    foreach ($filters as $value => $label):
                        $active = $filter === $value ? 'active' : '';
                        ?>
                        <a class="btn btn-outline-primary <?= $active ?>"
                            href="?filter=<?= urlencode($value) ?>"><?= htmlspecialchars($label) ?></a>
                    <?php endforeach; ?>
                </div>
            </div>
            <div class="card-body bg-light">
                <?php if (!$notifications): ?>
                    <div class="text-center text-muted py-5">No notifications yet. You're all caught up!</div>
                <?php else: ?>
                    <div class="list-group">
                        <?php foreach ($notifications as $note): ?>
                            <div
                                class="list-group-item list-group-item-action d-flex justify-content-between align-items-start <?= $note['is_read'] ? 'bg-white' : 'bg-warning-subtle' ?>">
                                <div class="me-3">
                                    <div class="d-flex align-items-center gap-2">
                                        <span class="badge <?= typeBadge($note['type']) ?> text-uppercase">
                                            <?= htmlspecialchars($note['type']) ?>
                                        </span>
                                        <strong><?= htmlspecialchars($note['title']) ?></strong>
                                    </div>
                                    <?php if (!empty($note['book_title'])): ?>
                                        <div class="small text-muted mt-1"><?= htmlspecialchars($note['book_title']) ?>
                                        </div>
                                    <?php endif; ?>
                                    <p class="mb-1 mt-2"><?= nl2br(htmlspecialchars($note['message'])) ?></p>
                                    <div class="small text-muted">
                                        <?php if (!empty($note['due_date'])): ?>
                                            Due <?= htmlspecialchars($note['due_date']) ?> ·
                                        <?php endif; ?>
                                        <?= htmlspecialchars($note['created_at']) ?>
                                    </div>
                                </div>
                                <form method="POST" class="text-end">
                                    <input type="hidden" name="notification_id" value="<?= (int)$note['id'] ?>">
                                    <?php if ($note['is_read']): ?>
                                        <button class="btn btn-sm btn-outline-secondary" name="mark_unread"
                                            value="1">Mark unread</button>
                                    <?php else: ?>
                                        <button class="btn btn-sm btn-outline-primary" name="mark_read"
                                            value="1">Mark read</button>
                                    <?php endif; ?>
                                </form>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</body>

</html>
