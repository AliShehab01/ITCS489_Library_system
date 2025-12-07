<?php
// bookReturnAndRenew.php
session_start();
require_once __DIR__ . '/../../config.php';
require_once __DIR__ . '/../models/dbconnect.php';
require_once __DIR__ . '/../controller/reservations_lib.php';

// Create mysqli connection for the existing reservation helper functions
$conn = new mysqli("localhost", "root", "", "library_system");
if ($conn->connect_error) {
    die("Database Connection Failed: " . $conn->connect_error);
}

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
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <title>Return / Renew</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet"
        integrity="sha384-0pUGZvbkm6XF6gxjEnlmuGrJXVbNuzT9qBBavbLwCsOGabYfZo0T0to5eqruptLy" crossorigin="anonymous">
    <link rel="stylesheet" href="<?= BASE_URL ?>public/css/style.css">
    <?php if (!defined('STYLE_LOADED')) { define('STYLE_LOADED', true); } ?>
</head>

<body>
    <?php include __DIR__ . '/navbar.php'; ?>
    <main class="page-shell">
      <section class="page-hero mb-4">
        <div class="d-flex flex-wrap justify-content-between align-items-start gap-3">
          <div>
            <p class="text-uppercase text-muted fw-semibold mb-2">Admin</p>
            <h1 class="display-6 mb-1">Return / Renew</h1>
            <p class="text-muted mb-0">Process returns, free up inventory, and notify the next reader in line.</p>
          </div>
          <div class="text-end">
            <a href="<?= BASE_URL ?>app/view/AdminArea.php" class="btn btn-outline-primary">Back to Admin</a>
          </div>
        </div>
      </section>

      <?php if (!empty($error)): ?>
        <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
      <?php elseif (!empty($success)): ?>
        <div class="alert alert-success"><?= htmlspecialchars($success) ?></div>
      <?php endif; ?>

      <?php if (!$active): ?>
        <div class="alert alert-info">No active borrows.</div>
      <?php else: ?>
        <div class="card shadow-custom">
          <div class="card-body">
            <div class="section-title mb-3">
              <span class="pill">&#128197;</span>
              <span>Active borrows</span>
            </div>
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
          </div>
        </div>
      <?php endif; ?>
    </main>

    <footer class="app-footer text-center">
      <small>&copy; 2025 Library System. All rights reserved.</small>
    </footer>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"
      integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script>
  </body>

  </html>
