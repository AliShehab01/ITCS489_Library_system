<?php
session_start();
require_once __DIR__ . '/../../config.php';
require_once __DIR__ . '/../models/dbconnect.php';

// Require login (any authenticated user can access)
if (!isset($_SESSION['user_id'])) {
    $loginUrl = (defined('BASE_URL') ? BASE_URL : '/') . 'app/view/login.php';
    header('Location: ' . $loginUrl);
    exit;
}

$db  = new Database();
$conn = $db->conn;
$msg = "";

// Create reservation
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['create_res'])) {
    $userId = filter_input(INPUT_POST, 'user_id', FILTER_VALIDATE_INT);
    $bookId = filter_input(INPUT_POST, 'book_id', FILTER_VALIDATE_INT);

    if ($userId && $bookId) {
        // تأكد ما عنده رزرفيشن نشط لنفس الكتاب
        $chk = $conn->prepare("
            SELECT 1
            FROM reservations
            WHERE user_id = :user_id
              AND book_id = :book_id
              AND status IN ('active','notified')
        ");
        $chk->execute([':user_id' => $userId, ':book_id' => $bookId]);

        if ($chk->fetch()) {
            $msg = "User already has an active reservation for this book.";
        } else {
            $ins = $conn->prepare("
                INSERT INTO reservations (user_id, book_id)
                VALUES (:user_id, :book_id)
            ");
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
        $upd = $conn->prepare("
            UPDATE reservations
            SET status = 'cancelled'
            WHERE reservation_id = :res_id
        ");
        $upd->execute([':res_id' => $resId]);
        $msg = "Reservation cancelled.";
    }
}

// Lists
$unavailableBooks = $conn->query("
    SELECT id, title, status, quantity
    FROM books
    WHERE quantity = 0
       OR status IN ('issued','unavailable','reserved')
    ORDER BY title
")->fetchAll(PDO::FETCH_ASSOC);

$users = $conn->query("
    SELECT id, username
    FROM users
    ORDER BY username
")->fetchAll(PDO::FETCH_ASSOC);

$queues = $conn->query("
    SELECT
        r.reservation_id,
        r.status,
        r.reserved_at,
        u.username,
        b.title
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
    <title>Reservation Management</title>

    <!-- Bootstrap + styles -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="<?= BASE_URL ?>public/css/style.css">
</head>

<body>

<?php include __DIR__ . '/navbar.php'; ?>

<main class="site-content">

    <!-- هيدر بسيط -->
    <section class="py-4 bg-white border-bottom">
        <div class="container d-flex flex-wrap justify-content-between align-items-center gap-2">
            <div>
                <h1 class="h4 mb-1">Reservation Management</h1>
                <p class="text-muted mb-0">
                    Add reservations for unavailable books and manage active queues.
                </p>
            </div>
            <a href="AdminArea.php" class="btn btn-outline-secondary btn-sm">
                ← Back to Admin Area
            </a>
        </div>
    </section>

<section class="py-4">
    <div class="container my-3">
        <div class="admin-wrapper mx-auto">

            <?php if (!empty($msg)): ?>
                <div class="alert alert-info">
                    <?= htmlspecialchars($msg); ?>
                </div>
            <?php endif; ?>

            <!-- كرت إضافة رزرفيشن -->
            <div class="card shadow-custom mb-4">
                <div class="card-header">
                    <h2 class="h6 mb-0">Create new reservation</h2>
                </div>
                <div class="card-body">
                    <br>
          <form method="POST"
      class="reservation-form">

    <div class="reservation-field flex-grow-1">
        
        <select name="user_id"
                class="form-select reservation-select"
                required>
            <option value="">Select user…</option>
            <?php foreach ($users as $u): ?>
                <option value="<?= (int)$u['id']; ?>">
                    <?= htmlspecialchars($u['username']); ?>
                </option>
            <?php endforeach; ?>
        </select>
    </div>
    <br>

    <div class="reservation-field flex-grow-1">
        
        <select name="book_id"
                class="form-select reservation-select"
                required>
            <option value="">Select book…</option>
            <?php foreach ($unavailableBooks as $b): ?>
                <option value="<?= (int)$b['id']; ?>">
                    <?= htmlspecialchars($b['title']); ?>
                    (status: <?= htmlspecialchars($b['status']); ?>,
                    qty: <?= (int)$b['quantity']; ?>)
                </option>
            <?php endforeach; ?>
        </select>
    </div>

    <div class="reservation-field reservation-action">
        <label class="form-label d-none d-md-block">&nbsp;</label>
        <button class="btn btn-primary reservation-btn"
                name="create_res"
                value="1">
            Add
        </button>
    </div>
</form>



                </div>
            </div>

            <!-- كرت الطوابير -->
            <div class="card shadow-custom">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <div>
                        <h2 class="h6 mb-0">Reservation queues</h2>
                        <small class="text-muted">
                            Active and notified reservations by book.
                        </small>
                    </div>
                    <span class="badge bg-light text-dark">
                        <?= count($queues); ?> active
                    </span>
                </div>
                <div class="card-body p-0">
                    <?php if (!$queues): ?>
                        <div class="p-4 text-center text-muted">
                            No active reservations.
                        </div>
                    <?php else: ?>
                        <div class="table-responsive">
                            <table class="table align-middle mb-0">
                                <thead>
                                <tr>
                                    <th>Book</th>
                                    <th>User</th>
                                    <th>Status</th>
                                    <th>Reserved at</th>
                                    <th class="text-end"></th>
                                </tr>
                                </thead>
                                <tbody>
                                <?php foreach ($queues as $q): ?>
                                    <tr>
                                        <td><?= htmlspecialchars($q['title']); ?></td>
                                        <td><?= htmlspecialchars($q['username']); ?></td>
                                        <td>
                                            <?php
                                            $status = $q['status'];
                                            $badgeClass = 'badge bg-secondary';
                                            if ($status === 'active') {
                                                $badgeClass = 'badge bg-primary';
                                            } elseif ($status === 'notified') {
                                                $badgeClass = 'badge bg-success';
                                            }
                                            ?>
                                            <span class="<?= $badgeClass; ?>">
                                                <?= htmlspecialchars(ucfirst($status)); ?>
                                            </span>
                                        </td>
                                        <td><?= htmlspecialchars($q['reserved_at']); ?></td>
                                        <td class="text-end">
                                            <form method="POST" class="d-inline">
                                                <input type="hidden" name="reservation_id"
                                                       value="<?= (int)$q['reservation_id']; ?>">
                                                <button class="btn btn-sm btn-outline-danger"
                                                        name="cancel_res" value="1">
                                                    Cancel
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

        </div>
    </section>
</main>

<footer class="py-3 mt-4">
    <div class="container text-center small text-muted">
        © 2025 Library System. All rights reserved.
    </div>
</footer>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
