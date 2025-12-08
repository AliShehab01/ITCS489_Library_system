<?php
session_start();
require_once __DIR__ . '/../../config.php';
require_once __DIR__ . '/../models/dbconnect.php';

if (!isset($_SESSION['username'])) {
    header("Location: " . BASE_URL . "view/login.php");
    exit;
}

$db = new Database();
$conn = $db->conn;
$userId = $_SESSION['user_id'] ?? null;

// Mark notification as read
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['mark_read'])) {
    $notifId = (int)$_POST['mark_read'];
    $conn->prepare("UPDATE notifications SET is_read = 1, updated_at = NOW() WHERE id = :id AND user_id = :uid")
        ->execute([':id' => $notifId, ':uid' => $userId]);
}

// Mark all as read
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['mark_all_read'])) {
    $conn->prepare("UPDATE notifications SET is_read = 1, updated_at = NOW() WHERE user_id = :uid AND is_read = 0")
        ->execute([':uid' => $userId]);
}

// Fetch notifications
$notifications = [];
if ($userId) {
    $stmt = $conn->prepare("
        SELECT n.*, b.title as book_title
        FROM notifications n
        LEFT JOIN books b ON b.id = n.book_id
        WHERE n.user_id = :uid
        ORDER BY n.created_at DESC
        LIMIT 50
    ");
    $stmt->execute([':uid' => $userId]);
    $notifications = $stmt->fetchAll(PDO::FETCH_ASSOC);
}

$unreadCount = count(array_filter($notifications, fn($n) => !$n['is_read']));

include __DIR__ . '/navbar.php';
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Notifications</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            padding-top: 56px;
            background: #f8f9fa;
        }
    </style>
</head>

<body>
    <div class="container py-4">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h1>🔔 My Notifications</h1>
            <?php if ($unreadCount > 0): ?>
                <form method="POST">
                    <button type="submit" name="mark_all_read" class="btn btn-outline-primary">
                        Mark all as read (<?= $unreadCount ?>)
                    </button>
                </form>
            <?php endif; ?>
        </div>

        <?php if (empty($notifications)): ?>
            <div class="alert alert-info">No notifications yet.</div>
        <?php else: ?>
            <div class="list-group">
                <?php foreach ($notifications as $n): ?>
                    <div class="list-group-item <?= !$n['is_read'] ? 'list-group-item-warning' : '' ?>">
                        <div class="d-flex justify-content-between align-items-start">
                            <div>
                                <h6 class="mb-1">
                                    <?php
                                    $icon = match ($n['type']) {
                                        'due' => '⏰',
                                        'overdue' => '⚠️',
                                        'reservation' => '📗',
                                        'announcement' => '📢',
                                        default => '🔔'
                                    };
                                    echo $icon . ' ' . htmlspecialchars($n['title']);
                                    ?>
                                </h6>
                                <p class="mb-1"><?= htmlspecialchars($n['message']) ?></p>
                                <?php if ($n['book_title']): ?>
                                    <small class="text-muted">Book: <?= htmlspecialchars($n['book_title']) ?></small>
                                <?php endif; ?>
                                <br>
                                <small class="text-muted"><?= date('M d, Y g:i A', strtotime($n['created_at'])) ?></small>
                            </div>
                            <?php if (!$n['is_read']): ?>
                                <form method="POST">
                                    <input type="hidden" name="mark_read" value="<?= $n['id'] ?>">
                                    <button type="submit" class="btn btn-sm btn-outline-secondary">Mark read</button>
                                </form>
                            <?php else: ?>
                                <span class="badge bg-secondary">Read</span>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>