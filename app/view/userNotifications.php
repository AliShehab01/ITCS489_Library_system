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

// Mark as read
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['mark_read'])) {
    $conn->prepare("UPDATE notifications SET is_read=1 WHERE id=:id AND user_id=:uid")->execute([':id' => (int)$_POST['mark_read'], ':uid' => $userId]);
}
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['mark_all'])) {
    $conn->prepare("UPDATE notifications SET is_read=1 WHERE user_id=:uid")->execute([':uid' => $userId]);
}

$notifications = [];
if ($userId) {
    $stmt = $conn->prepare("SELECT * FROM notifications WHERE user_id=:uid ORDER BY is_read ASC, created_at DESC LIMIT 50");
    $stmt->execute([':uid' => $userId]);
    $notifications = $stmt->fetchAll(PDO::FETCH_ASSOC);
}
$unread = count(array_filter($notifications, fn($n) => !$n['is_read']));

include __DIR__ . '/navbar.php';
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Notifications</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            padding-top: 80px;
            background: #f8f9fa;
        }
    </style>
</head>

<body>
    <div class="container py-4">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h1>🔔 Notifications</h1>
            <?php if ($unread > 0): ?>
                <form method="post">
                    <input type="hidden" name="mark_all" value="1">
                    <button class="btn btn-outline-primary btn-sm">Mark all read</button>
                </form>
            <?php endif; ?>
        </div>
        <?php if (empty($notifications)): ?>
            <div class="alert alert-info">No notifications.</div>
        <?php else: ?>
            <div class="list-group">
                <?php foreach ($notifications as $n): ?>
                    <div class="list-group-item <?= $n['is_read'] ? '' : 'list-group-item-warning' ?>">
                        <div class="d-flex justify-content-between">
                            <div>
                                <strong><?= htmlspecialchars($n['title']) ?></strong>
                                <p class="mb-1"><?= htmlspecialchars($n['message']) ?></p>
                                <small class="text-muted"><?= date('M d, Y g:i A', strtotime($n['created_at'])) ?></small>
                            </div>
                            <div>
                                <span class="badge bg-<?= $n['type'] === 'announcement' ? 'primary' : ($n['type'] === 'due' ? 'warning' : 'secondary') ?>"><?= ucfirst($n['type']) ?></span>
                                <?php if (!$n['is_read']): ?>
                                    <form method="post" class="d-inline">
                                        <input type="hidden" name="mark_read" value="<?= $n['id'] ?>">
                                        <button class="btn btn-sm btn-outline-secondary">✓</button>
                                    </form>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>