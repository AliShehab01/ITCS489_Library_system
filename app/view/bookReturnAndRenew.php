<?php
session_start();
require_once __DIR__ . '/../../config.php';
require_once __DIR__ . '/../controller/checkifadmin.php';
require_once __DIR__ . '/../models/dbconnect.php';
require_once __DIR__ . '/../controller/policy_helper.php';
require_once __DIR__ . '/../controller/audit_logger.php';

$db = new Database();
$conn = $db->conn;

$message = '';
$messageType = 'info';

// Handle return
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  if (isset($_POST['return_id'])) {
    $borrowId = (int)$_POST['return_id'];
    try {
      $stmt = $conn->prepare("SELECT bo.*, b.title FROM borrows bo JOIN books b ON b.id = bo.bookId WHERE bo.borrow_id = :id AND bo.isReturned = 'false'");
      $stmt->execute([':id' => $borrowId]);
      $borrow = $stmt->fetch(PDO::FETCH_ASSOC);

      if ($borrow) {
        // Mark as returned
        $conn->prepare("UPDATE borrows SET isReturned = 'true' WHERE borrow_id = :id")->execute([':id' => $borrowId]);

        // Update book quantity
        $conn->prepare("UPDATE books SET quantity = quantity + :qty, status = 'available' WHERE id = :bid")->execute([
          ':qty' => (int)$borrow['quantity'],
          ':bid' => (int)$borrow['bookId']
        ]);

        // Update user borrow count
        $conn->prepare("UPDATE users SET currentNumOfBorrows = GREATEST(0, currentNumOfBorrows - :qty) WHERE id = :uid")->execute([
          ':qty' => (int)$borrow['quantity'],
          ':uid' => (int)$borrow['user_id']
        ]);

        logAuditEvent(
          $conn,
          'RETURN_BOOK',
          'borrow',
          $borrowId,
          "Returned '{$borrow['title']}' (qty: {$borrow['quantity']})"
        );

        $message = "Book returned successfully!";
        $messageType = 'success';
      }
    } catch (PDOException $e) {
      $message = 'Error processing return.';
      $messageType = 'danger';
    }
  } elseif (isset($_POST['renew_id'])) {
    $borrowId = (int)$_POST['renew_id'];

    // Get renewal days from policy
    $renewalDays = (int)getSystemConfig($conn, 'loan_days_student', 14);
    $newDueDate = date('Y-m-d', strtotime("+{$renewalDays} days"));

    try {
      $conn->prepare("UPDATE borrows SET dueDate = :due WHERE borrow_id = :id AND isReturned = 'false'")->execute([
        ':due' => $newDueDate,
        ':id' => $borrowId
      ]);

      logAuditEvent($conn, 'RENEW_LOAN', 'borrow', $borrowId, "Renewed to: {$newDueDate}");

      $message = "Loan renewed. New due date: $newDueDate";
      $messageType = 'success';
    } catch (PDOException $e) {
      $message = 'Error renewing loan.';
      $messageType = 'danger';
    }
  }
}

// Fetch active borrows
$borrows = $conn->query("
    SELECT bo.borrow_id, bo.quantity, bo.dueDate, bo.bookId, bo.user_id,
           u.username, u.firstName, u.lastName,
           b.title, b.author, b.isbn
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
  <title>Return & Renew Books</title>
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
      <h1>🔄 Return & Renew Books</h1>
      <a href="<?= BASE_URL ?>view/AdminArea.php" class="btn btn-outline-secondary">← Back to Admin</a>
    </div>

    <?php if ($message): ?>
      <div class="alert alert-<?= $messageType ?> alert-dismissible fade show">
        <?= htmlspecialchars($message) ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
      </div>
    <?php endif; ?>

    <div class="card">
      <div class="card-header">
        <h5 class="mb-0">Active Borrows (<?= count($borrows) ?>)</h5>
      </div>
      <div class="card-body p-0">
        <?php if (empty($borrows)): ?>
          <div class="p-4 text-center text-muted">No active borrows.</div>
        <?php else: ?>
          <div class="table-responsive">
            <table class="table table-hover mb-0">
              <thead class="table-light">
                <tr>
                  <th>User</th>
                  <th>Book</th>
                  <th>Qty</th>
                  <th>Due Date</th>
                  <th>Status</th>
                  <th>Actions</th>
                </tr>
              </thead>
              <tbody>
                <?php foreach ($borrows as $b):
                  $isOverdue = strtotime($b['dueDate']) < time();
                  $daysLeft = (strtotime($b['dueDate']) - time()) / 86400;
                ?>
                  <tr class="<?= $isOverdue ? 'table-danger' : '' ?>">
                    <td>
                      <strong><?= htmlspecialchars($b['username']) ?></strong>
                      <br><small class="text-muted"><?= htmlspecialchars($b['firstName'] . ' ' . $b['lastName']) ?></small>
                    </td>
                    <td>
                      <strong><?= htmlspecialchars($b['title']) ?></strong>
                      <br><small class="text-muted"><?= htmlspecialchars($b['author']) ?></small>
                    </td>
                    <td><?= (int)$b['quantity'] ?></td>
                    <td><?= date('M d, Y', strtotime($b['dueDate'])) ?></td>
                    <td>
                      <?php if ($isOverdue): ?>
                        <span class="badge bg-danger">Overdue <?= abs(floor($daysLeft)) ?> days</span>
                      <?php elseif ($daysLeft <= 3): ?>
                        <span class="badge bg-warning text-dark">Due soon</span>
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
    </div>
  </div>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>