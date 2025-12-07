<?php
session_start();
require_once __DIR__ . '/../../config.php';
require_once __DIR__ . '/../models/dbconnect.php';

include __DIR__ . '/navbar.php';

if (!isset($_SESSION['username'])) {
    header("Location: " . BASE_URL . "view/login.php");
    exit;
}

$db = new Database();
$conn = $db->conn;

$userId = $_SESSION['user_id'] ?? null;
$notifications = [];

// Handle mark as read
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['mark_read'])) {
    $notifId = (int)$_POST['mark_read'];
    $stmt = $conn->prepare("UPDATE notifications SET is_read = 1, updated_at = NOW() WHERE id = :id AND user_id = :uid");
    $stmt->execute([':id' => $notifId, ':uid' => $userId]);
}

// Handle mark all as read
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['mark_all_read'])) {
    $stmt = $conn->prepare("UPDATE notifications SET is_read = 1, updated_at = NOW() WHERE user_id = :uid AND is_read = 0");
    $stmt->execute([':uid' => $userId]);
}

if ($userId) {
    $stmt = $conn->prepare("
        SELECT n.id, n.type, n.title, n.message, n.due_date, n.is_read, n.created_at, b.title AS book_title
        FROM notifications n
        LEFT JOIN books b ON b.id = n.book_id
        WHERE n.user_id = :uid
        ORDER BY n.is_read ASC, n.created_at DESC
        LIMIT 50
    ");
    $stmt->execute([':uid' => $userId]);
    $notifications = $stmt->fetchAll(PDO::FETCH_ASSOC);
}

$unreadCount = count(array_filter($notifications, fn($n) => !$n['is_read']));
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Notifications</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="<?= BASE_URL ?>public/css/style.css">
</head>

<body>
    <div class="container mt-5">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h1>Notifications</h1>
            <?php if ($unreadCount > 0): ?>
                <form method="post" style="display:inline">
                    <input type="hidden" name="mark_all_read" value="1">
                    <button type="submit" class="btn btn-outline-primary btn-sm">Mark all as read</button>
                </form>
            <?php endif; ?>
        </div>

        <?php if (empty($notifications)): ?>
            <div class="alert alert-info">You have no notifications.</div>
        <?php else: ?>
            <div class="list-group">
                <?php foreach ($notifications as $n): ?>
                    <div class="list-group-item <?= $n['is_read'] ? '' : 'list-group-item-warning' ?>">
                        <div class="d-flex justify-content-between align-items-start">
                            <div>
                                <div class="fw-bold"><?= htmlspecialchars($n['title']) ?></div>
                                <p class="mb-1"><?= htmlspecialchars($n['message']) ?></p>
                                <?php if ($n['book_title']): ?>
                                    <small class="text-muted">Book: <?= htmlspecialchars($n['book_title']) ?></small><br>
                                <?php endif; ?>
                                <?php if ($n['due_date']): ?>
                                    <small class="text-muted">Due: <?= htmlspecialchars($n['due_date']) ?></small><br>
                                <?php endif; ?>
                                <small class="text-muted"><?= date('M d, Y g:i A', strtotime($n['created_at'])) ?></small>
                            </div>
                            <div class="d-flex flex-column align-items-end gap-2">
                                <span class="badge bg-<?= match ($n['type']) {
                                                            'due' => 'warning text-dark',
                                                            'overdue' => 'danger',
                                                            'reservation' => 'success',
                                                            'announcement' => 'primary',
                                                            default => 'secondary'
                                                        } ?>">
                                    <?= ucfirst($n['type']) ?>
                                </span>
                                <?php if (!$n['is_read']): ?>
                                    <form method="post">
                                        <input type="hidden" name="mark_read" value="<?= (int)$n['id'] ?>">
                                        <button type="submit" class="btn btn-sm btn-outline-secondary">Mark read</button>
                                    </form>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>