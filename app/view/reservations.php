<?php
require_once __DIR__ . '/../../config.php';
require_once __DIR__ . '/../models/dbconnect.php';

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
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <title>Reservations</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="<?= BASE_URL ?>public/css/style.css">
    <?php if (!defined('STYLE_LOADED')) { define('STYLE_LOADED', true); } ?>
</head>

<body>
    <?php include __DIR__ . '/navbar.php'; ?>

    <main class="page-shell">
        <div class="section-title">
            <span class="pill">⏳</span>
            <span>Reservation management</span>
        </div>

        <?php if (!empty($msg)): ?>
        <div class="alert alert-info"><?= htmlspecialchars($msg) ?></div>
        <?php endif; ?>

        <div class="card shadow-custom mb-4">
            <div class="card-body">
                <form method="POST" class="row g-3 align-items-end">
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
                                <?= htmlspecialchars($b['title']) ?> (<?= htmlspecialchars($b['status']) ?>, qty:
                                <?= (int)$b['quantity'] ?>)
                            </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-2 d-grid align-items-end">
                        <button class="btn btn-primary" name="create_res" value="1">Add Reservation</button>
                    </div>
                </form>
            </div>
        </div>

        <div class="card shadow-custom">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h4 class="mb-0">Reservation queues</h4>
                    <a href="<?= BASE_URL ?>app/view/AdminArea.php" class="btn btn-outline-primary btn-sm">Back to Admin</a>
                </div>
                <?php if (!$queues): ?>
                <div class="alert alert-secondary mb-0">No active reservations.</div>
                <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-sm align-middle">
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
                                <td><span class="badge bg-primary"><?= htmlspecialchars($q['status']) ?></span></td>
                                <td><?= htmlspecialchars($q['reserved_at']) ?></td>
                                <td class="text-end">
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
            </div>
        </div>
    </main>

    <footer class="app-footer text-center">
        <small>© 2025 Library System.</small>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>
