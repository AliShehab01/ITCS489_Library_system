<?php
// BorrowBook.php
require '../models/dbconnect.php';          // mysqli $conn to database `library_system`
require_once '../controller/reservations_lib.php'; // <-- queue helpers (fairness + fulfill)

/* -------------------------
   Handle form submit
-------------------------- */
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $bookId  = filter_input(INPUT_POST, 'bookid', FILTER_VALIDATE_INT);
  $qty     = filter_input(INPUT_POST, 'QuantityWanted', FILTER_VALIDATE_INT);
  $dueDate = $_POST['dueDate'] ?? null;
  $userId  = filter_input(INPUT_POST, 'user_id', FILTER_VALIDATE_INT);
  $price   = isset($_POST['price']) ? (float)$_POST['price'] : 0;

  if (!$bookId || !$qty || !$dueDate || !$userId) {
    $error = "Please fill all required fields.";
  } else {

    //  FAIR QUEUE ENFORCEMENT:
    // If there is a reservation queue for this book, only the FIRST user in queue may borrow.
    if (!borrower_is_first_in_queue($conn, $bookId, $userId)) {
      $error = "This book currently has a reservation queue. Only the first user in the queue may borrow now.";
    } else {
      // Check stock
      $stmt = $conn->prepare("SELECT quantity, title FROM books WHERE id = ?");
      $stmt->bind_param("i", $bookId);
      $stmt->execute();
      $res = $stmt->get_result()->fetch_assoc();
      $available = $res['quantity'] ?? 0;
      $bookTitle = $res['title'] ?? 'Unknown';

      if ($available < $qty) {
        // No enough copies — suggest using Reservations page
        $error = "Not enough stock for '{$bookTitle}'. Available: {$available}. "
          . "If all copies are on loan, please add a reservation from the Reservations page.";
      } else {
        // Insert borrow
        $stmt = $conn->prepare("
                    INSERT INTO borrows (bookId, quantity, price, dueDate, isReturned, user_id)
                    VALUES (?, ?, ?, ?, 'false', ?)
                ");
        $stmt->bind_param("iissi", $bookId, $qty, $price, $dueDate, $userId);
        $stmt->execute();

        // Decrease stock
        $stmt = $conn->prepare("UPDATE books SET quantity = quantity - ? WHERE id = ?");
        $stmt->bind_param("ii", $qty, $bookId);
        $stmt->execute();

        // If the borrower had a reservation, mark it fulfilled so the queue advances fairly
        fulfill_user_reservation($conn, $bookId, $userId);

        $success = "Borrow created successfully for '{$bookTitle}'.";
      }
    }
  }
}

/* -------------------------
   Load books and users for the selects
-------------------------- */
$books = $conn->query("SELECT id, title, quantity FROM books ORDER BY title ASC")->fetch_all(MYSQLI_ASSOC);
$users = $conn->query("SELECT id, username FROM users ORDER BY username ASC")->fetch_all(MYSQLI_ASSOC);
?>
<!doctype html>
<html lang="en">

<head>
  <meta charset="utf-8">
  <title>Borrow Book</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>

<body class="bg-light">
  <div class="container py-4">
    <h2 class="mb-3">Borrow a Book</h2>

    <?php if (!empty($error)): ?>
      <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
    <?php elseif (!empty($success)): ?>
      <div class="alert alert-success"><?= htmlspecialchars($success) ?></div>
    <?php endif; ?>

    <form method="POST" class="row g-3">
      <div class="col-md-6">
        <label class="form-label">Book</label>
        <select name="bookid" class="form-select" required>
          <option value="">Select a book…</option>
          <?php foreach ($books as $b): ?>
            <option value="<?= (int)$b['id'] ?>">
              <?= htmlspecialchars($b['title']) ?> (in stock: <?= (int)$b['quantity'] ?>)
            </option>
          <?php endforeach; ?>
        </select>
      </div>

      <div class="col-md-3">
        <label class="form-label">Quantity</label>
        <input type="number" name="QuantityWanted" min="1" value="1" class="form-control" required>
      </div>

      <div class="col-md-3">
        <label class="form-label">Due date</label>
        <input type="date" name="dueDate" class="form-control" required>
      </div>

      <div class="col-md-4">
        <label class="form-label">User</label>
        <select name="user_id" class="form-select" required>
          <option value="">Select user…</option>
          <?php foreach ($users as $u): ?>
            <option value="<?= (int)$u['id'] ?>"><?= htmlspecialchars($u['username']) ?></option>
          <?php endforeach; ?>
        </select>
      </div>

      <div class="col-md-3">
        <label class="form-label">Price (optional)</label>
        <input type="number" name="price" step="0.01" value="0" class="form-control">
      </div>

      <div class="col-12">
        <button class="btn btn-primary">Create Borrow</button>
        <a href="AdminArea.php" class="btn btn-outline-secondary">Back</a>
      </div>
    </form>

    <div class="mt-3">
      <small class="text-muted">
        Tip: If a book has no available copies, use the <a href="reservations.php">Reservations</a> page to add
        users to the queue.
        When a copy is returned, the next user in line is automatically notified.
      </small>
    </div>
  </div>
</body>

</html>