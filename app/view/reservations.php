<?php
require '../models/dbconnect.php';

$db = new Database();
$conn = $db->conn;
$msg = "";

// Create reservation
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['create_res'])) {
  $userId = filter_input(INPUT_POST, 'user_id', FILTER_VALIDATE_INT);
  $bookId = filter_input(INPUT_POST, 'book_id', FILTER_VALIDATE_INT);

  if ($userId && $bookId) {
    $chk = $conn->prepare("SELECT 1 FROM reservations WHERE user_id = :user_id AND book_id = :book_id AND status IN ('active','notified')");
    $chk->execute([':user_id' => $userId, ':book_id' => $bookId]);

    if ($chk->fetch()) {
      $msg = "User already has an active reservation for this book.";
    } else {
      $ins = $conn->prepare("INSERT INTO reservations (user_id, book_id) VALUES (:user_id, :book_id)");
      $ins->execute([':user_id' => $userId, ':book_id' => $bookId]);
      $msg = "Reservation added successfully.";
    }
  } else {
    $msg = "Please select both user and book.";
  }
}

// Cancel reservation
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['cancel_res'])) {
  $resId = filter_input(INPUT_POST, 'reservation_id', FILTER_VALIDATE_INT);
  if ($resId) {
    $upd = $conn->prepare("UPDATE reservations SET status = 'cancelled' WHERE reservation_id = :res_id");
    $upd->execute([':res_id' => $resId]);
    $msg = "Reservation cancelled.";
  }
}

// Lists
$unavailableBooks = $conn->query("
  SELECT id, title, status, quantity FROM books
  WHERE quantity = 0 OR status IN ('issued','unavailable','reserved')
  ORDER BY title
")->fetchAll(PDO::FETCH_ASSOC);



$users = $conn->query("SELECT id, username FROM users ORDER BY username")->fetchAll(PDO::FETCH_ASSOC);

$queues = $conn->query("
  SELECT r.reservation_id, r.status, r.reserved_at, u.username, b.title
  FROM reservations r
  JOIN users u ON u.id = r.user_id
  JOIN books b ON b.id = r.book_id
  WHERE r.status IN ('active','notified')
  ORDER BY b.title, r.reserved_at
")->fetchAll(PDO::FETCH_ASSOC);
?>
<!doctype html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <title>Reservations</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>

<body class="bg-light">
    <div class="container py-4">
        <h2 class="mb-3">Reservation Management</h2>
        <?php if (!empty($msg)): ?>
        <div class="alert alert-info"><?= htmlspecialchars($msg) ?></div>
        <?php endif; ?>

        <form method="POST" class="row g-3 mb-4">
            <div class="col-md-5">
                <label class="form-label">User</label>
                <select name="user_id" class="form-select" required>
                    <option value="">Select user…</option>
                    <?php foreach ($users as $u): ?>
                    <option value="<?= (int)$u['id'] ?>"><?= htmlspecialchars($u['username']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-5">
                <label class="form-label">Book (currently on loan/unavailable)</label>
                <select name="book_id" class="form-select" required>
                    <option value="">Select book…</option>
                    <?php foreach ($unavailableBooks as $b): ?>
                    <option value="<?= (int)$b['id'] ?>">
                        <?= htmlspecialchars($b['title']) ?> (<?= htmlspecialchars($b['availability']) ?>, qty:
                        <?= (int)$b['quantity'] ?>)
                    </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-2 d-grid align-items-end">
                <button class="btn btn-primary" name="create_res" value="1">Add Reservation</button>
            </div>
        </form>

        <h4>Reservation Queues</h4>
        <?php if (!$queues): ?>
        <div class="alert alert-secondary">No active reservations.</div>
        <?php else: ?>
        <div class="table-responsive">
            <table class="table table-sm bg-white align-middle">
                <thead>
                    <tr>
                        <th>Book</th>
                        <th>User</th>
                        <th>Status</th>
                        <th>Reserved At</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($queues as $q): ?>
                    <tr>
                        <td><?= htmlspecialchars($q['title']) ?></td>
                        <td><?= htmlspecialchars($q['username']) ?></td>
                        <td><?= htmlspecialchars($q['status']) ?></td>
                        <td><?= htmlspecialchars($q['reserved_at']) ?></td>
                        <td>
                            <form method="POST" class="d-inline">
                                <input type="hidden" name="reservation_id" value="<?= (int)$q['reservation_id'] ?>">
                                <button class="btn btn-sm btn-outline-danger" name="cancel_res"
                                    value="1">Cancel</button>
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