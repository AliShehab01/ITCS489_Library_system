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

$currentBorrows = [];
$pastBorrows = [];

if ($userId) {
    // Current borrows (not returned)
    $stmt = $conn->prepare("
        SELECT bo.borrow_id, bo.quantity, bo.dueDate, bo.isReturned, b.title, b.author, b.isbn
        FROM borrows bo
        JOIN books b ON b.id = bo.bookId
        WHERE bo.user_id = :uid AND bo.isReturned = 'false'
        ORDER BY bo.dueDate ASC
    ");
    $stmt->execute([':uid' => $userId]);
    $currentBorrows = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Past borrows (returned)
    $stmt2 = $conn->prepare("
        SELECT bo.borrow_id, bo.quantity, bo.dueDate, bo.isReturned, b.title, b.author, b.isbn
        FROM borrows bo
        JOIN books b ON b.id = bo.bookId
        WHERE bo.user_id = :uid AND bo.isReturned = 'true'
        ORDER BY bo.dueDate DESC
        LIMIT 20
    ");
    $stmt2->execute([':uid' => $userId]);
    $pastBorrows = $stmt2->fetchAll(PDO::FETCH_ASSOC);
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Borrowed Books</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="<?= BASE_URL ?>public/css/style.css">
</head>

<body>
    <div class="container mt-5">
        <h1 class="mb-4">My Borrowed Books</h1>

        <h2 class="h4 mb-3">Currently Borrowed</h2>
        <?php if (empty($currentBorrows)): ?>
            <div class="alert alert-info">You have no books currently borrowed.</div>
        <?php else: ?>
            <div class="table-responsive mb-5">
                <table class="table table-striped">
                    <thead>
                        <tr>
                            <th>Title</th>
                            <th>Author</th>
                            <th>ISBN</th>
                            <th>Quantity</th>
                            <th>Due Date</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($currentBorrows as $b):
                            $isOverdue = strtotime($b['dueDate']) < time();
                        ?>
                            <tr class="<?= $isOverdue ? 'table-danger' : '' ?>">
                                <td><?= htmlspecialchars($b['title']) ?></td>
                                <td><?= htmlspecialchars($b['author']) ?></td>
                                <td><?= htmlspecialchars($b['isbn']) ?></td>
                                <td><?= (int)$b['quantity'] ?></td>
                                <td><?= htmlspecialchars($b['dueDate']) ?></td>
                                <td>
                                    <?php if ($isOverdue): ?>
                                        <span class="badge bg-danger">Overdue</span>
                                    <?php else: ?>
                                        <span class="badge bg-success">Active</span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>

        <h2 class="h4 mb-3">Borrowing History</h2>
        <?php if (empty($pastBorrows)): ?>
            <div class="alert alert-secondary">No borrowing history found.</div>
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
                                <td><?= htmlspecialchars($b['dueDate']) ?></td>
                                <td><span class="badge bg-secondary">Returned</span></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>