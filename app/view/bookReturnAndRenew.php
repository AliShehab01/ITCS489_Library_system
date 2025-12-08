<?php
session_start();
require_once __DIR__ . '/../../config.php';
require_once __DIR__ . '/../models/dbconnect.php';

// بإمكانك هنا استخدام checkifadmin.php لو حاب تقفلها على الأدمن فقط
// require_once __DIR__ . '/../controller/checkifadmin.php';

$db  = new Database();
$pdo = $db->getPdo();

$error   = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $borrowId = isset($_POST['borrow_id']) ? (int)$_POST['borrow_id'] : 0;
    $action   = $_POST['action'] ?? '';
    $extendDays = isset($_POST['extend_days']) ? (int)$_POST['extend_days'] : 7;

    if (!$borrowId || !$action) {
        $error = 'Invalid request.';
    } else {
        try {
            // جلب الاستعارة + الكتاب
            $stmt = $pdo->prepare("
                SELECT br.borrow_id, br.bookId, br.quantity, br.dueDate, br.isReturned,
                       b.title, b.quantity AS book_quantity
                FROM borrows br
                JOIN books b ON b.id = br.bookId
                WHERE br.borrow_id = :bid
            ");
            $stmt->execute([':bid' => $borrowId]);
            $borrow = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$borrow) {
                $error = 'Borrow record not found.';
            } else {
                if ($action === 'return') {
                    if ($borrow['isReturned'] === 'true') {
                        $error = 'This borrow is already returned.';
                    } else {
                        $pdo->beginTransaction();

                        // تعليم كسُجّل مُرجع
                        $upd = $pdo->prepare("
                            UPDATE borrows
                            SET isReturned = 'true',
                                returnDate = CURDATE()
                            WHERE borrow_id = :bid
                        ");
                        $upd->execute([':bid' => $borrowId]);

                        // إعادة الكمية إلى الكتب
                        $updBook = $pdo->prepare("
                            UPDATE books
                            SET quantity = quantity + :qty
                            WHERE id = :bookId
                        ");
                        $updBook->execute([
                            ':qty'    => (int)$borrow['quantity'],
                            ':bookId' => (int)$borrow['bookId'],
                        ]);

                        $pdo->commit();
                        $success = "Book '{$borrow['title']}' has been returned successfully.";
                    }
                } elseif ($action === 'renew') {
                    if ($borrow['isReturned'] === 'true') {
                        $error = 'Cannot renew a returned borrow.';
                    } else {
                        $upd = $pdo->prepare("
                            UPDATE borrows
                            SET dueDate = DATE_ADD(dueDate, INTERVAL :days DAY)
                            WHERE borrow_id = :bid
                        ");
                        $upd->execute([
                            ':days' => $extendDays,
                            ':bid'  => $borrowId,
                        ]);
                        $success = "Borrow for '{$borrow['title']}' has been renewed by {$extendDays} days.";
                    }
                }
            }
        } catch (Exception $e) {
            if ($pdo->inTransaction()) {
                $pdo->rollBack();
            }
            $error = 'Error processing request.';
        }
    }
}

// جلب كل الاستعارات النشطة (لم تُرجع بعد)
$sqlActive = "
    SELECT
        br.borrow_id,
        br.bookId,
        br.user_id,
        br.quantity,
        br.dueDate,
        u.username,
        b.title
    FROM borrows br
    JOIN users u ON u.id = br.user_id
    JOIN books b ON b.id = br.bookId
    WHERE br.isReturned = 'false'
    ORDER BY br.dueDate ASC, br.borrow_id ASC
";
$activeBorrows = $pdo->query($sqlActive)->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Return &amp; Renew – Admin</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="<?= BASE_URL ?>public/css/style.css">
</head>

<body>

<?php include __DIR__ . '/navbar.php'; ?>

<main class="site-content">

    <!-- Header -->
    <section class="py-4 bg-white border-bottom">
        <div class="container">
            <h1 class="h4 mb-1">Return &amp; renew borrows</h1>
            <p class="text-muted mb-0">
                Manage active borrows: mark books as returned or extend due dates.
            </p>
        </div>
    </section>

    <section class="py-4">
        <div class="container my-2">
            <div class="admin-wrapper mx-auto">

                <div class="card shadow-custom mb-4">
                    <div class="card-body">
                        <?php if ($error): ?>
                            <div class="alert alert-danger small mb-2">
                                <?= htmlspecialchars($error); ?>
                            </div>
                        <?php endif; ?>

                        <?php if ($success): ?>
                            <div class="alert alert-success small mb-2">
                                <?= htmlspecialchars($success); ?>
                            </div>
                        <?php endif; ?>

                        <p class="small text-muted mb-0">
                            Below is a list of all active borrows (not yet returned).
                        </p>
                    </div>
                </div>

                <div class="card shadow-custom">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h2 class="h6 mb-0">Active borrows</h2>
                        <span class="badge bg-secondary">
                            Total: <?= $activeBorrows ? count($activeBorrows) : 0; ?>
                        </span>
                    </div>
                    <div class="card-body p-0">
                        <?php if (empty($activeBorrows)): ?>
                            <div class="p-3 text-muted small">
                                There are no active borrows at the moment.
                            </div>
                        <?php else: ?>
                            <div class="table-responsive">
                                <table class="table align-middle mb-0">
                                    <thead>
                                    <tr>
                                        <th>#</th>
                                        <th>Book</th>
                                        <th>User</th>
                                        <th>Qty</th>
                                        <th>Due date</th>
                                        <th>Actions</th>
                                    </tr>
                                    </thead>
                                    <tbody>
                                    <?php foreach ($activeBorrows as $row): ?>
                                        <tr>
                                            <td><?= (int)$row['borrow_id']; ?></td>
                                            <td>
                                                <div class="fw-semibold">
                                                    <?= htmlspecialchars($row['title']); ?>
                                                </div>
                                                <div class="small text-muted">
                                                    Book ID: <?= htmlspecialchars($row['bookId']); ?>
                                                </div>
                                            </td>
                                            <td>
                                                <div class="small">
                                                    <?= htmlspecialchars($row['username']); ?>
                                                </div>
                                                <div class="small text-muted">
                                                    User ID: <?= htmlspecialchars($row['user_id']); ?>
                                                </div>
                                            </td>
                                            <td><?= (int)$row['quantity']; ?></td>
                                            <td class="small">
                                                <?= htmlspecialchars($row['dueDate'] ?? '—'); ?>
                                            </td>
                                            <td>
                                                <div class="d-flex flex-column flex-sm-row gap-1">
                                                    <!-- Return -->
                                                    <form method="post" class="d-inline">
                                                        <input type="hidden" name="borrow_id" value="<?= (int)$row['borrow_id']; ?>">
                                                        <input type="hidden" name="action" value="return">
                                                        <button type="submit" class="btn btn-sm btn-success">
                                                            Return
                                                        </button>
                                                    </form>

                                                    <!-- Renew -->
                                                    <form method="post" class="d-inline">
                                                        <input type="hidden" name="borrow_id" value="<?= (int)$row['borrow_id']; ?>">
                                                        <input type="hidden" name="action" value="renew">
                                                        <input type="number"
                                                               name="extend_days"
                                                               value="7"
                                                               min="1"
                                                               max="60"
                                                               class="form-control form-control-sm d-inline-block"
                                                               style="width: 70px;">
                                                        <button type="submit" class="btn btn-sm btn-outline-primary">
                                                            Renew
                                                        </button>
                                                    </form>
                                                </div>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>

                <div class="mt-3">
                    <a href="AdminArea.php" class="btn btn-outline-secondary btn-sm">
                        Back to admin area
                    </a>
                </div>

            </div><!-- /.admin-wrapper -->
        </div>
    </section>

</main>

<footer class="py-3 mt-4 bg-dark text-white">
    <div class="container text-center small">
        © 2025 Library System. All rights reserved.
    </div>
</footer>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
