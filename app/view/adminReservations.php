<?php
session_start();
require_once __DIR__ . '/../../config.php';
require_once __DIR__ . '/../controller/checkifadmin.php';
require_once __DIR__ . '/../models/dbconnect.php';

$db = new Database();
$conn = $db->conn;

$message = '';

// Handle actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['notify_id'])) {
        $rid = (int)$_POST['notify_id'];
        $conn->prepare("UPDATE reservations SET status = 'notified' WHERE reservation_id = :id")->execute([':id' => $rid]);
        $message = '<div class="alert alert-success">User notified!</div>';
    } elseif (isset($_POST['cancel_id'])) {
        $rid = (int)$_POST['cancel_id'];
        $conn->prepare("UPDATE reservations SET status = 'cancelled' WHERE reservation_id = :id")->execute([':id' => $rid]);
        $message = '<div class="alert alert-success">Reservation cancelled.</div>';
    }
}

// Fetch all reservations
$reservations = $conn->query("
    SELECT r.*, u.username, u.firstName, u.lastName, b.title, b.author, b.quantity
    FROM reservations r
    JOIN users u ON u.id = r.user_id
    JOIN books b ON b.id = r.book_id
    ORDER BY r.status = 'active' DESC, r.reserved_at DESC
")->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Reservations</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            padding-top: 56px;
            background: #f8f9fa;
        }
    </style>
</head>

<body>
    <?php include __DIR__ . '/navbar.php'; ?>

    <div class="container py-4">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h1>🔖 Manage Reservations</h1>
            <a href="<?= BASE_URL ?>view/AdminArea.php" class="btn btn-outline-secondary">← Back to Admin</a>
        </div>

        <?= $message ?>

        <div class="card">
            <div class="card-body p-0">
                <?php if (empty($reservations)): ?>
                    <div class="p-4 text-center text-muted">No reservations.</div>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>User</th>
                                    <th>Book</th>
                                    <th>Reserved On</th>
                                    <th>Status</th>
                                    <th>Available</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($reservations as $r): ?>
                                    <tr>
                                        <td>
                                            <strong><?= htmlspecialchars($r['username']) ?></strong>
                                            <br><small><?= htmlspecialchars($r['firstName'] . ' ' . $r['lastName']) ?></small>
                                        </td>
                                        <td>
                                            <strong><?= htmlspecialchars($r['title']) ?></strong>
                                            <br><small><?= htmlspecialchars($r['author']) ?></small>
                                        </td>
                                        <td><?= date('M d, Y', strtotime($r['reserved_at'])) ?></td>
                                        <td>
                                            <?php
                                            $badge = match ($r['status']) {
                                                'active' => 'bg-primary',
                                                'notified' => 'bg-success',
                                                'fulfilled' => 'bg-secondary',
                                                'cancelled' => 'bg-danger',
                                                default => 'bg-light text-dark'
                                            };
                                            ?>
                                            <span class="badge <?= $badge ?>"><?= ucfirst($r['status']) ?></span>
                                        </td>
                                        <td>
                                            <?= (int)$r['quantity'] > 0 ? '<span class="badge bg-success">Yes</span>' : '<span class="badge bg-warning">No</span>' ?>
                                        </td>
                                        <td>
                                            <?php if ($r['status'] === 'active' && (int)$r['quantity'] > 0): ?>
                                                <form method="post" style="display:inline">
                                                    <input type="hidden" name="notify_id" value="<?= $r['reservation_id'] ?>">
                                                    <button class="btn btn-sm btn-success">Notify</button>
                                                </form>
                                            <?php endif; ?>
                                            <?php if ($r['status'] === 'active'): ?>
                                                <form method="post" style="display:inline">
                                                    <input type="hidden" name="cancel_id" value="<?= $r['reservation_id'] ?>">
                                                    <button class="btn btn-sm btn-outline-danger">Cancel</button>
                                                </form>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>