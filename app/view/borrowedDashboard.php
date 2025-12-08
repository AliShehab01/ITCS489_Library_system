<?php
session_start();
require_once __DIR__ . '/../../config.php';
require_once __DIR__ . '/../models/dbconnect.php';
require_once __DIR__ . '/../controller/audit_logger.php';

if (!isset($_SESSION['username'])) {
    header("Location: " . BASE_URL . "view/login.php");
    exit;
}

$db = new Database();
$conn = $db->conn;
$userId = $_SESSION['user_id'] ?? null;

$message = '';
$messageType = 'info';

// Handle self-return
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['return_borrow_id']) && $userId) {
    $borrowId = (int)$_POST['return_borrow_id'];

    try {
        // Verify this borrow belongs to the current user and is not yet returned
        $stmt = $conn->prepare("SELECT bo.*, b.title, b.id as book_id FROM borrows bo JOIN books b ON b.id = bo.bookId WHERE bo.borrow_id = :bid AND bo.user_id = :uid AND bo.isReturned = 'false'");
        $stmt->execute([':bid' => $borrowId, ':uid' => $userId]);
        $borrow = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($borrow) {
            $conn->beginTransaction();

            // Mark as returned
            $conn->prepare("UPDATE borrows SET isReturned = 'true' WHERE borrow_id = :id")->execute([':id' => $borrowId]);

            // Update book quantity and status
            $conn->prepare("UPDATE books SET quantity = quantity + :qty, status = 'available' WHERE id = :bid")->execute([
                ':qty' => (int)$borrow['quantity'],
                ':bid' => (int)$borrow['book_id']
            ]);

            // Update user borrow count
            $conn->prepare("UPDATE users SET currentNumOfBorrows = GREATEST(0, currentNumOfBorrows - :qty) WHERE id = :uid")->execute([
                ':qty' => (int)$borrow['quantity'],
                ':uid' => $userId
            ]);

            $conn->commit();

            // Log the return
            if (function_exists('logAuditEvent')) {
                logAuditEvent($conn, 'SELF_RETURN_BOOK', 'borrow', $borrowId, "User returned '{$borrow['title']}' (qty: {$borrow['quantity']})");
            }

            $message = "Successfully returned: " . htmlspecialchars($borrow['title']);
            $messageType = 'success';
        } else {
            $message = "Unable to process return. Book not found or already returned.";
            $messageType = 'danger';
        }
    } catch (PDOException $e) {
        if ($conn->inTransaction()) {
            $conn->rollBack();
        }
        $message = "Error processing return. Please try again.";
        $messageType = 'danger';
    }
}

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
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Borrowed Books</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        html,
        body {
            margin: 0;
            padding: 0;
        }

        body {
            padding-top: 56px;
            background: #f8f9fa;
        }
    </style>
</head>

<body>
    <?php include __DIR__ . '/navbar.php'; ?>

    <div class="container py-4">
        <h1 class="mb-4">📖 My Borrowed Books</h1>

        <?php if ($message): ?>
            <div class="alert alert-<?= $messageType ?> alert-dismissible fade show">
                <?= $message ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <div class="card mb-4">
            <div class="card-header bg-primary text-white">
                <h5 class="mb-0">Currently Borrowed (<?= count($currentBorrows) ?>)</h5>
            </div>
            <div class="card-body p-0">
                <?php if (empty($currentBorrows)): ?>
                    <div class="p-4 text-center">
                        <p class="text-muted mb-2">No books currently borrowed.</p>
                        <a href="<?= BASE_URL ?>view/CatalogSearch_Browsing-EN.php" class="btn btn-primary">Browse Catalog</a>
                    </div>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>Title</th>
                                    <th>Author</th>
                                    <th>Qty</th>
                                    <th>Due Date</th>
                                    <th>Status</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($currentBorrows as $b):
                                    $dueTimestamp = strtotime($b['dueDate']);
                                    $now = time();
                                    $daysLeft = floor(($dueTimestamp - $now) / 86400);
                                    $isOverdue = $dueTimestamp < $now;
                                ?>
                                    <tr class="<?= $isOverdue ? 'table-danger' : ($daysLeft <= 3 ? 'table-warning' : '') ?>">
                                        <td><strong><?= htmlspecialchars($b['title']) ?></strong></td>
                                        <td><?= htmlspecialchars($b['author']) ?></td>
                                        <td><?= (int)$b['quantity'] ?></td>
                                        <td><?= date('M d, Y', $dueTimestamp) ?></td>
                                        <td>
                                            <?php if ($isOverdue): ?>
                                                <span class="badge bg-danger">Overdue (<?= abs($daysLeft) ?> days)</span>
                                            <?php elseif ($daysLeft <= 3): ?>
                                                <span class="badge bg-warning text-dark">Due soon (<?= $daysLeft ?> days)</span>
                                            <?php else: ?>
                                                <span class="badge bg-success">Active (<?= $daysLeft ?> days left)</span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <form method="POST" style="display:inline" onsubmit="return confirm('Are you sure you want to return this book?')">
                                                <input type="hidden" name="return_borrow_id" value="<?= (int)$b['borrow_id'] ?>">
                                                <button type="submit" class="btn btn-sm btn-outline-primary">
                                                    📤 Return Book
                                                </button>
                                            </form>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">📜 Borrowing History</h5>
            </div>
            <div class="card-body p-0">
                <?php if (empty($pastBorrows)): ?>
                    <div class="p-4 text-center text-muted">No borrowing history yet.</div>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="table table-sm mb-0">
                            <thead class="table-light">
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
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>