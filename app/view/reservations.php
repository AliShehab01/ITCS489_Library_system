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
$reservations = [];
$message = '';

// Handle new reservation from catalog
$bookIdToReserve = $_GET['book_id'] ?? null;
$bookToReserve = null;

if ($bookIdToReserve) {
  $stmt = $conn->prepare("SELECT * FROM books WHERE id = :id");
  $stmt->execute([':id' => (int)$bookIdToReserve]);
  $bookToReserve = $stmt->fetch(PDO::FETCH_ASSOC);
}

// Handle reservation submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  if (isset($_POST['cancel_id'])) {
    $cancelId = (int)$_POST['cancel_id'];
    $stmt = $conn->prepare("UPDATE reservations SET status = 'cancelled' WHERE reservation_id = :id AND user_id = :uid");
    $stmt->execute([':id' => $cancelId, ':uid' => $userId]);
    $message = '<div class="alert alert-success">Reservation cancelled.</div>';
  } elseif (isset($_POST['reserve_book_id'])) {
    $bookId = (int)$_POST['reserve_book_id'];

    // Check if already has active reservation for this book
    $checkStmt = $conn->prepare("SELECT reservation_id FROM reservations WHERE user_id = :uid AND book_id = :bid AND status IN ('active', 'notified')");
    $checkStmt->execute([':uid' => $userId, ':bid' => $bookId]);

    if ($checkStmt->fetch()) {
      $message = '<div class="alert alert-warning">You already have an active reservation for this book.</div>';
    } else {
      $insertStmt = $conn->prepare("INSERT INTO reservations (user_id, book_id, status) VALUES (:uid, :bid, 'active')");
      $insertStmt->execute([':uid' => $userId, ':bid' => $bookId]);
      $message = '<div class="alert alert-success">Reservation placed successfully! You will be notified when the book is available.</div>';
      $bookToReserve = null; // Clear the form
    }
  }
}

// Fetch user's reservations
if ($userId) {
  $stmt = $conn->prepare("
        SELECT r.reservation_id, r.status, r.reserved_at, b.id as book_id, b.title, b.author, b.isbn, b.quantity
        FROM reservations r
        JOIN books b ON b.id = r.book_id
        WHERE r.user_id = :uid
        ORDER BY r.reserved_at DESC
    ");
  $stmt->execute([':uid' => $userId]);
  $reservations = $stmt->fetchAll(PDO::FETCH_ASSOC);
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>My Reservations</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="<?= PUBLIC_URL ?>css/style.css">
  <style>
    body {
      padding-top: 56px;
      background: #f8f9fa;
    }
  </style>
</head>

<body>
  <?php include __DIR__ . '/navbar.php'; ?>

  <div class="container mt-5">
    <h1 class="mb-4">My Reservations</h1>

    <?= $message ?>

    <?php if ($bookToReserve): ?>
      <div class="card mb-4 border-primary">
        <div class="card-header bg-primary text-white">
          Reserve a Book
        </div>
        <div class="card-body">
          <h5 class="card-title"><?= htmlspecialchars($bookToReserve['title']) ?></h5>
          <p class="card-text">
            <strong>Author:</strong> <?= htmlspecialchars($bookToReserve['author']) ?><br>
            <strong>ISBN:</strong> <?= htmlspecialchars($bookToReserve['isbn']) ?><br>
            <strong>Current Availability:</strong> <?= (int)$bookToReserve['quantity'] ?> copies
          </p>
          <form method="post">
            <input type="hidden" name="reserve_book_id" value="<?= (int)$bookToReserve['id'] ?>">
            <button type="submit" class="btn btn-primary">Confirm Reservation</button>
            <a href="<?= BASE_URL ?>view/CatalogSearch_Browsing-EN.php" class="btn btn-outline-secondary">Cancel</a>
          </form>
        </div>
      </div>
    <?php endif; ?>

    <h2 class="h5 mb-3">Your Reservations</h2>

    <?php if (empty($reservations)): ?>
      <div class="alert alert-info">
        You have no reservations.
        <a href="<?= BASE_URL ?>view/CatalogSearch_Browsing-EN.php">Browse the catalog</a> to find books to reserve.
      </div>
    <?php else: ?>
      <div class="table-responsive">
        <table class="table table-striped">
          <thead>
            <tr>
              <th>Title</th>
              <th>Author</th>
              <th>Reserved On</th>
              <th>Status</th>
              <th>Available Now</th>
              <th>Action</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($reservations as $r): ?>
              <tr>
                <td><?= htmlspecialchars($r['title']) ?></td>
                <td><?= htmlspecialchars($r['author']) ?></td>
                <td><?= date('M d, Y', strtotime($r['reserved_at'])) ?></td>
                <td>
                  <?php
                  $badgeClass = match ($r['status']) {
                    'active' => 'bg-primary',
                    'notified' => 'bg-success',
                    'fulfilled' => 'bg-secondary',
                    'cancelled' => 'bg-danger',
                    default => 'bg-light text-dark'
                  };
                  ?>
                  <span class="badge <?= $badgeClass ?>"><?= ucfirst($r['status']) ?></span>
                </td>
                <td>
                  <?php if ((int)$r['quantity'] > 0): ?>
                    <span class="badge bg-success">Yes (<?= (int)$r['quantity'] ?>)</span>
                  <?php else: ?>
                    <span class="badge bg-warning text-dark">No</span>
                  <?php endif; ?>
                </td>
                <td>
                  <?php if ($r['status'] === 'active'): ?>
                    <form method="post" style="display:inline">
                      <input type="hidden" name="cancel_id" value="<?= (int)$r['reservation_id'] ?>">
                      <button type="submit" class="btn btn-sm btn-outline-danger">Cancel</button>
                    </form>
                  <?php elseif ($r['status'] === 'notified' && (int)$r['quantity'] > 0): ?>
                    <a href="<?= BASE_URL ?>view/BorrowBook.php?bookid=<?= (int)$r['book_id'] ?>" class="btn btn-sm btn-success">Borrow Now</a>
                  <?php else: ?>
                    —
                  <?php endif; ?>
                </td>
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
