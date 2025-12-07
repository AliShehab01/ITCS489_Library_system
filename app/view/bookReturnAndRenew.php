<?php
session_start();
require_once __DIR__ . '/../../config.php';
require_once __DIR__ . '/../models/dbconnect.php';

// Admin check
require_once __DIR__ . '/../controller/checkifadmin.php';

include __DIR__ . '/navbar.php';

$db = new Database();
$conn = $db->conn;

$message = '';

// Handle return
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['return_id'])) {
  $borrowId = (int)$_POST['return_id'];

  // Get borrow details
  $stmt = $conn->prepare("SELECT * FROM borrows WHERE borrow_id = :id AND isReturned = 'false'");
  $stmt->execute([':id' => $borrowId]);
  $borrow = $stmt->fetch(PDO::FETCH_ASSOC);

  if ($borrow) {
    // Mark as returned
    $conn->prepare("UPDATE borrows SET isReturned = 'true' WHERE borrow_id = :id")->execute([':id' => $borrowId]);

    // Update book quantity
    $conn->prepare("UPDATE books SET quantity = quantity + :qty WHERE id = :bid")->execute([
      ':qty' => (int)$borrow['quantity'],
      ':bid' => (int)$borrow['bookId']
    ]);

    // Update user borrow count
    $conn->prepare("UPDATE users SET currentNumOfBorrows = GREATEST(0, currentNumOfBorrows - :qty) WHERE id = :uid")->execute([
      ':qty' => (int)$borrow['quantity'],
      ':uid' => (int)$borrow['user_id']
    ]);

    $message = '<div class="alert alert-success">Book returned successfully.</div>';
  }
}

// Handle renew
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['renew_id'])) {
  $borrowId = (int)$_POST['renew_id'];
  $newDueDate = date('Y-m-d', strtotime('+14 days'));

  $conn->prepare("UPDATE borrows SET dueDate = :due WHERE borrow_id = :id AND isReturned = 'false'")->execute([
    ':due' => $newDueDate,
    ':id' => $borrowId
  ]);

  $message = '<div class="alert alert-success">Loan renewed. New due date: ' . $newDueDate . '</div>';
}

// Fetch active borrows
$borrows = $conn->query("
    SELECT bo.borrow_id, bo.quantity, bo.dueDate, u.username, b.title, b.author
    FROM borrows bo
    JOIN users u ON u.id = bo.user_id
    JOIN books b ON b.id = bo.bookId
    WHERE bo.isReturned = 'false'
    ORDER BY bo.dueDate ASC
")->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Return & Renew</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="<?= BASE_URL ?>public/css/style.css">
</head>

<body>
  <div class="container mt-5">
    <h1 class="mb-4">Return & Renew Books</h1>

    <?= $message ?>

    <?php if (empty($borrows)): ?>
      <div class="alert alert-info">No active borrows found.</div>
    <?php else: ?>
      <div class="table-responsive">
        <table class="table table-striped">
          <thead>
            <tr>
              <th>User</th>
              <th>Book</th>
              <th>Author</th>
              <th>Qty</th>
              <th>Due Date</th>
              <th>Status</th>
              <th>Actions</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($borrows as $b):
              $isOverdue = strtotime($b['dueDate']) < time();
            ?>
              <tr class="<?= $isOverdue ? 'table-danger' : '' ?>">
                <td><?= htmlspecialchars($b['username']) ?></td>
                <td><?= htmlspecialchars($b['title']) ?></td>
                <td><?= htmlspecialchars($b['author']) ?></td>
                <td><?= (int)$b['quantity'] ?></td>
                <td><?= htmlspecialchars($b['dueDate']) ?></td>
                <td>
                  <?php if ($isOverdue): ?>
                    <span class="badge bg-danger">Overdue</span>
                  <?php else: ?>
                    <span class="badge bg-success">Active</span>
                  <?php endif; ?>
                </td>
                <td>
                  <form method="post" style="display:inline">
                    <input type="hidden" name="return_id" value="<?= (int)$b['borrow_id'] ?>">
                    <button type="submit" class="btn btn-sm btn-primary">Return</button>
                  </form>
                  <form method="post" style="display:inline">
                    <input type="hidden" name="renew_id" value="<?= (int)$b['borrow_id'] ?>">
                    <button type="submit" class="btn btn-sm btn-outline-secondary">Renew</button>
                  </form>
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