<?php
session_start();
require_once __DIR__ . '/../../config.php';
require_once __DIR__ . '/../models/dbconnect.php';
require_once __DIR__ . '/../controller/policy_helper.php';
require_once __DIR__ . '/../controller/audit_logger.php';

// Redirect if not logged in
if (!isset($_SESSION['username'])) {
    header("Location: " . BASE_URL . "view/login.php");
    exit;
}

include __DIR__ . '/navbar.php';

$db = new Database();
$conn = $db->conn;

$message = '';
$bookId = $_GET['bookid'] ?? null;
$book = null;

$userId = $_SESSION['user_id'] ?? null;
$userRole = $_SESSION['role'] ?? 'Student';

// Get limits from database policies
$borrowLimit = getBorrowLimitByRole($conn, $userRole);
$maxLoanDays = getLoanDaysByRole($conn, $userRole);

// Get current borrow count
$currentBorrows = 0;
if ($userId) {
    $countStmt = $conn->prepare("SELECT COUNT(*) FROM borrows WHERE user_id = :uid AND isReturned = 'false'");
    $countStmt->execute([':uid' => $userId]);
    $currentBorrows = (int)$countStmt->fetchColumn();
}

if ($bookId) {
    $stmt = $conn->prepare("SELECT * FROM books WHERE id = :id");
    $stmt->execute([':id' => (int)$bookId]);
    $book = $stmt->fetch(PDO::FETCH_ASSOC);
}

// Handle borrow submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $book) {
    $quantity = max(1, min(5, (int)($_POST['QuantityWanted'] ?? 1)));
    $dueDate = $_POST['dueDate'] ?? date('Y-m-d', strtotime("+{$maxLoanDays} days"));

    // Validate due date is not too far in future
    $maxDueDate = date('Y-m-d', strtotime("+{$maxLoanDays} days"));
    if ($dueDate > $maxDueDate) {
        $dueDate = $maxDueDate;
    }

    if (!$userId) {
        $message = '<div class="alert alert-danger">User session error. Please login again.</div>';
    } elseif ($currentBorrows + $quantity > $borrowLimit) {
        $remaining = $borrowLimit - $currentBorrows;
        $message = '<div class="alert alert-danger">Borrowing limit exceeded. Your limit is ' . $borrowLimit . ' books. You currently have ' . $currentBorrows . ' borrowed. You can borrow ' . max(0, $remaining) . ' more.</div>';
    } elseif ($book['quantity'] < $quantity) {
        $message = '<div class="alert alert-danger">Not enough copies available. Only ' . (int)$book['quantity'] . ' available.</div>';
    } else {
        try {
            $conn->beginTransaction();

            // Insert borrow record
            $insertStmt = $conn->prepare("INSERT INTO borrows (bookId, quantity, price, dueDate, isReturned, user_id) VALUES (:bookId, :qty, 0, :due, 'false', :uid)");
            $insertStmt->execute([
                ':bookId' => (int)$book['id'],
                ':qty' => $quantity,
                ':due' => $dueDate,
                ':uid' => $userId
            ]);

            // Update book quantity
            $updateStmt = $conn->prepare("UPDATE books SET quantity = quantity - :qty WHERE id = :id");
            $updateStmt->execute([':qty' => $quantity, ':id' => (int)$book['id']]);

            // Update book status if quantity becomes 0
            $checkQty = $conn->prepare("SELECT quantity FROM books WHERE id = :id");
            $checkQty->execute([':id' => (int)$book['id']]);
            $newQty = (int)$checkQty->fetchColumn();
            if ($newQty <= 0) {
                $conn->prepare("UPDATE books SET status = 'unavailable' WHERE id = :id")->execute([':id' => (int)$book['id']]);
            }

            // Update user borrow count
            $userStmt = $conn->prepare("UPDATE users SET currentNumOfBorrows = currentNumOfBorrows + :qty WHERE id = :uid");
            $userStmt->execute([':qty' => $quantity, ':uid' => $userId]);

            $conn->commit();

            $message = '<div class="alert alert-success">
                <strong>Success!</strong> Book borrowed successfully.<br>
                <strong>Due date:</strong> ' . htmlspecialchars($dueDate) . '<br>
                <a href="' . BASE_URL . 'view/borrowedDashboard.php" class="alert-link">View your borrowed books</a>
            </div>';

            // Refresh book data
            $stmt->execute([':id' => (int)$bookId]);
            $book = $stmt->fetch(PDO::FETCH_ASSOC);
            $currentBorrows += $quantity;

            // Log the borrow event
            logAuditEvent(
                $conn,
                'BORROW_BOOK',
                'book',
                (int)$book['id'],
                "User borrowed '{$book['title']}' (qty: {$quantity}, due: {$dueDate})"
            );
        } catch (PDOException $e) {
            $conn->rollBack();
            $message = '<div class="alert alert-danger">Failed to borrow book. Please try again.</div>';
        }
    }
}

$defaultDueDate = date('Y-m-d', strtotime("+{$maxLoanDays} days"));
$minDate = date('Y-m-d');
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Borrow Book</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="<?= PUBLIC_URL ?>css/style.css">
    <style>
        html,
        body {
            margin: 0;
            padding: 0;
        }

        body {
            padding-top: 56px;
        }
    </style>
</head>

<body>
    <div class="container mt-4" style="max-width: 700px;">
        <h1 class="mb-4">Borrow Book</h1>

        <!-- User borrowing status -->
        <div class="alert alert-info mb-4">
            <div class="d-flex justify-content-between">
                <span><strong>Your Role:</strong> <?= htmlspecialchars($userRole) ?></span>
                <span><strong>Borrowing Limit:</strong> <?= $borrowLimit ?> books</span>
            </div>
            <div class="d-flex justify-content-between">
                <span><strong>Currently Borrowed:</strong> <?= $currentBorrows ?></span>
                <span><strong>Max Loan Period:</strong> <?= $maxLoanDays ?> days</span>
            </div>
        </div>

        <?= $message ?>

        <?php if (!$book): ?>
            <div class="alert alert-warning">
                Book not found. <a href="<?= BASE_URL ?>view/CatalogSearch_Browsing-EN.php">Browse catalog</a>
            </div>
        <?php else: ?>
            <div class="card mb-4">
                <div class="card-body">
                    <h5 class="card-title"><?= htmlspecialchars($book['title']) ?></h5>
                    <p class="card-text">
                        <strong>Author:</strong> <?= htmlspecialchars($book['author']) ?><br>
                        <strong>ISBN:</strong> <?= htmlspecialchars($book['isbn']) ?><br>
                        <strong>Category:</strong> <?= htmlspecialchars($book['category'] ?? 'N/A') ?><br>
                        <strong>Available Copies:</strong> <?= (int)$book['quantity'] ?>
                    </p>
                </div>
            </div>

            <?php if ($book['quantity'] > 0 && $currentBorrows < $borrowLimit): ?>
                <form method="post" class="p-4 border rounded bg-light">
                    <div class="mb-3">
                        <label for="QuantityWanted" class="form-label">Quantity</label>
                        <input type="number" name="QuantityWanted" id="QuantityWanted" class="form-control"
                            min="1" max="<?= min(5, (int)$book['quantity'], $borrowLimit - $currentBorrows) ?>" value="1" required>
                        <small class="text-muted">Max: <?= min(5, (int)$book['quantity'], $borrowLimit - $currentBorrows) ?></small>
                    </div>
                    <div class="mb-3">
                        <label for="dueDate" class="form-label">Due Date</label>
                        <input type="date" name="dueDate" id="dueDate" class="form-control"
                            min="<?= $minDate ?>" max="<?= $defaultDueDate ?>" value="<?= $defaultDueDate ?>" required>
                        <small class="text-muted">Your role allows up to <?= $maxLoanDays ?> days loan period.</small>
                    </div>
                    <button type="submit" class="btn btn-primary w-100">Confirm Borrow</button>
                </form>
            <?php elseif ($currentBorrows >= $borrowLimit): ?>
                <div class="alert alert-warning">
                    You have reached your borrowing limit (<?= $borrowLimit ?> books).
                    Please return some books before borrowing more.
                    <br><a href="<?= BASE_URL ?>view/borrowedDashboard.php">View your borrowed books</a>
                </div>
            <?php else: ?>
                <div class="alert alert-warning">
                    This book is currently unavailable.
                    <br><a href="<?= BASE_URL ?>view/reservations.php?book_id=<?= (int)$book['id'] ?>">Place a reservation</a>
                </div>
            <?php endif; ?>

            <div class="mt-3">
                <a href="<?= BASE_URL ?>view/CatalogSearch_Browsing-EN.php" class="btn btn-outline-secondary">Back to Catalog</a>
            </div>
        <?php endif; ?>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>
