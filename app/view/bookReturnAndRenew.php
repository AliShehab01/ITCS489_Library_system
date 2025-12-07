<<?php
  // bookReturnAndRenew.php
  require_once __DIR__ . '/../../config.php';
  require_once __DIR__ . '/../models/dbconnect.php';
  require_once __DIR__ . '/../controller/reservations_lib.php';


  // Handle a return
  if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['return'])) {
    $borrowId = filter_input(INPUT_POST, 'borrow_id', FILTER_VALIDATE_INT);
    $bookId   = filter_input(INPUT_POST, 'book_id', FILTER_VALIDATE_INT);
    $qty      = filter_input(INPUT_POST, 'qty', FILTER_VALIDATE_INT);

    if ($borrowId && $bookId && $qty) {
      // Mark as returned
      $stmt = $conn->prepare("UPDATE borrows SET isReturned = 'true' WHERE borrow_id = ?");
      $stmt->bind_param("i", $borrowId);
      $stmt->execute();

      // Notify the next user waiting for this book
      notify_next_in_queue($conn, (int)$bookId);


      // Restore stock
      $stmt = $conn->prepare("UPDATE books SET quantity = quantity + ? WHERE id = ?");
      $stmt->bind_param("ii", $qty, $bookId);
      $stmt->execute();

      $success = "Borrow #{$borrowId} returned and stock updated.";
    } else {
      $error = "Missing return fields.";
    }
  }

  // List active borrows
  $sql = "
  SELECT b.borrow_id, b.bookId, b.quantity, b.dueDate, u.username, bk.title
  FROM borrows b
  JOIN users u ON u.id = b.user_id
  JOIN books bk ON bk.id = b.bookId
  WHERE b.isReturned = 'false'
  ORDER BY b.dueDate ASC
";
  $active = $conn->query($sql)->fetch_all(MYSQLI_ASSOC);
<!DOCTYPE html>
  <html lang="en">

  <head>
    <meta charset="utf-8">
    <title>Return / Renew</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  </head>

  <body class="bg-light">
    <div class="container py-4">
      <h2 class="mb-3">Return / Renew</h2>

      <?php if (!empty($error)): ?>
        <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
      <?php elseif (!empty($success)): ?>
        <div class="alert alert-success"><?= htmlspecialchars($success) ?></div>
      <?php endif; ?>

      <?php if (!$active): ?>
        <div class="alert alert-info">No active borrows.</div>
      <?php else: ?>
        <div class="table-responsive">
          <table class="table table-sm align-middle">
            <thead>
              <tr>
                <th>#</th>
                <th>Book</th>
                <th>User</th>
                <th>Qty</th>
                <th>Due</th>
                <th>Action</th>
              </tr>
            </thead>
            <tbody>
              <?php foreach ($active as $row): ?>
                <tr>
                  <td><?= (int)$row['borrow_id'] ?></td>
                  <td><?= htmlspecialchars($row['title']) ?></td>
                  <td><?= htmlspecialchars($row['username']) ?></td>
                  <td><?= (int)$row['quantity'] ?></td>
                  <td><?= htmlspecialchars($row['dueDate']) ?></td>
                  <td>
                    <form method="POST" class="d-inline">
                      <input type="hidden" name="borrow_id" value="<?= (int)$row['borrow_id'] ?>">
                      <input type="hidden" name="book_id" value="<?= (int)$row['bookId'] ?>">
                      <input type="hidden" name="qty" value="<?= (int)$row['quantity'] ?>">
                      <button class="btn btn-sm btn-success" name="return" value="1">Return</button>
                    </form>
                  </td>
                </tr>
              <?php endforeach; ?>
            </tbody>
          </table>
        </div>
      <?php endif; ?>

      <a href="AdminArea.php" class="btn btn-outline-secondary mt-3">Back</a>
    </div>
  </body>

  </html>