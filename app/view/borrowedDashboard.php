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

$currentBorrows = [];
$pastBorrows = [];

if ($userId) {
    $stmt = $conn->prepare("SELECT bo.*, b.title, b.author, b.isbn FROM borrows bo JOIN books b ON b.id=bo.bookId WHERE bo.user_id=:uid AND bo.isReturned='false' ORDER BY bo.dueDate ASC");
    $stmt->execute([':uid' => $userId]);
    $currentBorrows = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $stmt2 = $conn->prepare("SELECT bo.*, b.title, b.author, b.isbn FROM borrows bo JOIN books b ON b.id=bo.bookId WHERE bo.user_id=:uid AND bo.isReturned='true' ORDER BY bo.dueDate DESC LIMIT 20");
    $stmt2->execute([':uid' => $userId]);
    $pastBorrows = $stmt2->fetchAll(PDO::FETCH_ASSOC);
}

include __DIR__ . '/navbar.php';
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Borrowed Books</title>
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
        <h1 class="mb-4">📖 My Borrowed Books</h1>

        <h2 class="h5 mb-3">Currently Borrowed (<?= count($currentBorrows) ?>)</h2>
        <?php if (empty($currentBorrows)): ?>
            <div class="alert alert-info">No books currently borrowed. <a href="<?= BASE_URL ?>view/CatalogSearch_Browsing-EN.php">Browse catalog</a></div>
        <?php else: ?>
            <div class="table-responsive mb-5">
                <table class="table table-hover">
                    <thead class="table-light">
                        <tr>
                            <th>Title</th>
                            <th>Author</th>
                            <th>Qty</th>
                            <th>Due Date</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($currentBorrows as $b): $overdue = strtotime($b['dueDate']) < time(); ?>
                            <tr class="<?= $overdue ? 'table-danger' : '' ?>">
                                <td><strong><?= htmlspecialchars($b['title']) ?></strong></td>
                                <td><?= htmlspecialchars($b['author']) ?></td>
                                <td><?= (int)$b['quantity'] ?></td>
                                <td><?= date('M d, Y', strtotime($b['dueDate'])) ?></td>
                                <td><?= $overdue ? '<span class="badge bg-danger">Overdue</span>' : '<span class="badge bg-success">Active</span>' ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>

        <h2 class="h5 mb-3">Borrowing History</h2>
        <?php if (empty($pastBorrows)): ?>
            <div class="alert alert-secondary">No history yet.</div>
        <?php else: ?>
            <div class="table-responsive">
                <table class="table table-sm">
                    <thead>
                        <tr>
                            <th>Title</th>
                            <th>Author</th>
                            <th>Due Date</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($pastBorrows as $b): ?>
                            <tr>
                                <td><?= htmlspecialchars($b['title']) ?></td>
                                <td><?= htmlspecialchars($b['author']) ?></td>
                                <td><?= date('M d, Y', strtotime($b['dueDate'])) ?></td>
                                <td><span class="badge bg-secondary">Returned</span></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>