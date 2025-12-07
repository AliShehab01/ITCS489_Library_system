<?php
session_start();
require_once __DIR__ . '/../../config.php';
require_once __DIR__ . '/../controller/checkifadmin.php';
require_once __DIR__ . '/../models/dbconnect.php';

$db = new Database();
$conn = $db->conn;

$message = '';
$messageType = 'info';

// Handle send announcement
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['send_announcement'])) {
    $title = trim($_POST['title'] ?? '');
    $body = trim($_POST['message'] ?? '');
    $targetRole = $_POST['target_role'] ?? 'all';

    if (empty($title) || empty($body)) {
        $message = 'Title and message are required.';
        $messageType = 'danger';
    } else {
        try {
            // Get target users
            if ($targetRole === 'all') {
                $users = $conn->query("SELECT id FROM users")->fetchAll(PDO::FETCH_COLUMN);
            } else {
                $stmt = $conn->prepare("SELECT id FROM users WHERE LOWER(role) = :role");
                $stmt->execute([':role' => strtolower($targetRole)]);
                $users = $stmt->fetchAll(PDO::FETCH_COLUMN);
            }

            // Insert notification for each user
            $insertStmt = $conn->prepare("INSERT INTO notifications (user_id, type, title, message, is_read) VALUES (:uid, 'announcement', :title, :msg, 0)");
            foreach ($users as $uid) {
                $insertStmt->execute([':uid' => $uid, ':title' => $title, ':msg' => $body]);
            }

            $message = "Announcement sent to " . count($users) . " users!";
            $messageType = 'success';
        } catch (PDOException $e) {
            $message = 'Error sending announcement.';
            $messageType = 'danger';
        }
    }
}

include __DIR__ . '/navbar.php';
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Send Notifications</title>
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
            <h1>🔔 Send Notifications</h1>
            <a href="<?= BASE_URL ?>view/AdminArea.php" class="btn btn-outline-secondary">← Back to Admin</a>
        </div>

        <?php if ($message): ?>
            <div class="alert alert-<?= $messageType ?> alert-dismissible fade show">
                <?= htmlspecialchars($message) ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <div class="card">
            <div class="card-header bg-primary text-white">
                <h5 class="mb-0">📢 Send Announcement</h5>
            </div>
            <div class="card-body">
                <form method="post">
                    <input type="hidden" name="send_announcement" value="1">
                    <div class="mb-3">
                        <label class="form-label">Title <span class="text-danger">*</span></label>
                        <input type="text" name="title" class="form-control" placeholder="e.g., Library Hours Update" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Message <span class="text-danger">*</span></label>
                        <textarea name="message" class="form-control" rows="4" placeholder="Enter your announcement message..." required></textarea>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Send To</label>
                        <select name="target_role" class="form-select">
                            <option value="all">All Users</option>
                            <option value="student">Students Only</option>
                            <option value="vipstudent">VIP Students Only</option>
                            <option value="staff">Staff Only</option>
                        </select>
                    </div>
                    <button type="submit" class="btn btn-primary">Send Announcement</button>
                </form>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>