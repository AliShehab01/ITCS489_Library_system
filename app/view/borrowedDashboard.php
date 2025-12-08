<?php
session_start();
require_once __DIR__ . '/../../config.php';
require_once __DIR__ . '/../models/dbconnect.php';

// تأكد أن المستخدم مسجل دخول
if (!isset($_SESSION['user_id'])) {
    $loginUrl = (defined('BASE_URL') ? BASE_URL : '/') . 'app/view/login.php';
    header('Location: ' . $loginUrl);
    exit;
}

$userId   = (int)$_SESSION['user_id'];
$username = $_SESSION['username'] ?? '';

$db   = new Database();
$conn = $db->conn;

// --------------------
// جلب البيانات من قاعدة البيانات
// --------------------
date_default_timezone_set('Asia/Bahrain');
$today = date('Y-m-d');

// Current borrows (لم تُرجع بعد)
$sqlCurrent = "
    SELECT
        b.borrow_id,
        b.bookId,
        b.dueDate,
        b.isReturned,
        bk.title,
        bk.author
    FROM borrows b
    JOIN books bk ON bk.id = b.bookId
    WHERE b.user_id = :uid
      AND b.isReturned = 'false'
    ORDER BY b.dueDate ASC
";
$stmt = $conn->prepare($sqlCurrent);
$stmt->execute([':uid' => $userId]);
$currentBorrows = $stmt->fetchAll(PDO::FETCH_ASSOC);

// History (كتب أُرجعت)
$sqlHistory = "
    SELECT
        b.borrow_id,
        b.bookId,
        b.dueDate,
        b.isReturned,
        bk.title,
        bk.author
    FROM borrows b
    JOIN books bk ON bk.id = b.bookId
    WHERE b.user_id = :uid
      AND b.isReturned = 'true'
    ORDER BY b.dueDate DESC
    LIMIT 20
";
$stmtH = $conn->prepare($sqlHistory);
$stmtH->execute([':uid' => $userId]);
$historyBorrows = $stmtH->fetchAll(PDO::FETCH_ASSOC);

// تقسيم current إلى overdue و active
$activeNow  = [];
$overdueNow = [];

foreach ($currentBorrows as $row) {
    $due = $row['dueDate'] ?? null;
    if ($due && $due < $today) {
        $overdueNow[] = $row;
    } else {
        $activeNow[] = $row;
    }
}

$activeCount   = count($activeNow);
$overdueCount  = count($overdueNow);
$historyCount  = count($historyBorrows);
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Borrowed Items – Library</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="<?= BASE_URL ?>public/css/style.css">
</head>

<body>

<?php include __DIR__ . '/navbar.php'; ?>

<main class="site-content">

    <!-- Header -->
    <section class="py-4 bg-white border-bottom">
        <div class="container">
            <h1 class="h4 mb-1">My borrowed items</h1>
            <p class="text-muted mb-0">
                Track due dates, overdue items, and your borrowing history.
            </p>
        </div>
    </section>

    <section class="py-4">
        <div class="container my-2">
            <div class="admin-wrapper mx-auto">

                <!-- Summary cards -->
                <div class="row g-3 mb-4">
                    <div class="col-12 col-md-4">
                        <div class="card shadow-custom h-100">
                            <div class="card-body">
                                <div class="text-muted small mb-1">Currently borrowed</div>
                                <div class="h4 mb-0"><?= (int)$activeCount; ?></div>
                            </div>
                        </div>
                    </div>

                    <div class="col-12 col-md-4">
                        <div class="card shadow-custom h-100">
                            <div class="card-body">
                                <div class="text-muted small mb-1">Overdue</div>
                                <div class="h4 mb-0 text-danger"><?= (int)$overdueCount; ?></div>
                            </div>
                        </div>
                    </div>

                    <div class="col-12 col-md-4">
                        <div class="card shadow-custom h-100">
                            <div class="card-body">
                                <div class="text-muted small mb-1">History records</div>
                                <div class="h4 mb-0"><?= (int)$historyCount; ?></div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Active + Overdue -->
                <div class="row g-4 mb-4">
                    <!-- Active -->
                    <div class="col-12 col-lg-6">
                        <div class="card shadow-custom h-100">
                            <div class="card-header">
                                <h2 class="h6 mb-0">Currently borrowed</h2>
                            </div>
                            <div class="card-body p-0">
                                <?php if (empty($activeNow)): ?>
                                    <div class="p-3 text-muted small">
                                        You have no currently borrowed items.
                                    </div>
                                <?php else: ?>
                                    <div class="table-responsive">
                                        <table class="table align-middle mb-0">
                                            <thead>
                                            <tr>
                                                <th>Title</th>
                                                <th>Due date</th>
                                            </tr>
                                            </thead>
                                            <tbody>
                                            <?php foreach ($activeNow as $b): ?>
                                                <tr>
                                                    <td>
                                                        <div class="fw-semibold">
                                                            <?= htmlspecialchars($b['title']); ?>
                                                        </div>
                                                        <?php if (!empty($b['author'])): ?>
                                                            <div class="small text-muted">
                                                                <?= htmlspecialchars($b['author']); ?>
                                                            </div>
                                                        <?php endif; ?>
                                                    </td>
                                                    <td>
                                                        <span class="small">
                                                            <?= htmlspecialchars($b['dueDate'] ?? '—'); ?>
                                                        </span>
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

                    <!-- Overdue -->
                    <div class="col-12 col-lg-6">
                        <div class="card shadow-custom h-100">
                            <div class="card-header">
                                <h2 class="h6 mb-0">Overdue items</h2>
                            </div>
                            <div class="card-body p-0">
                                <?php if (empty($overdueNow)): ?>
                                    <div class="p-3 text-muted small">
                                        You have no overdue items. Great job keeping up!
                                    </div>
                                <?php else: ?>
                                    <div class="table-responsive">
                                        <table class="table align-middle mb-0">
                                            <thead>
                                            <tr>
                                                <th>Title</th>
                                                <th>Due date</th>
                                            </tr>
                                            </thead>
                                            <tbody>
                                            <?php foreach ($overdueNow as $b): ?>
                                                <tr>
                                                    <td>
                                                        <div class="fw-semibold">
                                                            <?= htmlspecialchars($b['title']); ?>
                                                        </div>
                                                        <?php if (!empty($b['author'])): ?>
                                                            <div class="small text-muted">
                                                                <?= htmlspecialchars($b['author']); ?>
                                                            </div>
                                                        <?php endif; ?>
                                                    </td>
                                                    <td>
                                                        <span class="small text-danger fw-semibold">
                                                            <?= htmlspecialchars($b['dueDate'] ?? '—'); ?>
                                                        </span>
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
                </div>

                <!-- History -->
                <div class="card shadow-custom">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <div>
                            <h2 class="h6 mb-0">Borrowing history</h2>
                            <small class="text-muted">Most recent 20 records</small>
                        </div>
                    </div>
                    <div class="card-body p-0">
                        <?php if (empty($historyBorrows)): ?>
                            <div class="p-3 text-muted small">
                                No past borrowing history yet.
                            </div>
                        <?php else: ?>
                            <div class="table-responsive">
                                <table class="table align-middle mb-0">
                                    <thead>
                                    <tr>
                                        <th>Title</th>
                                        <th>Due date</th>
                                    </tr>
                                    </thead>
                                    <tbody>
                                    <?php foreach ($historyBorrows as $b): ?>
                                        <tr>
                                            <td>
                                                <div class="fw-semibold">
                                                    <?= htmlspecialchars($b['title']); ?>
                                                </div>
                                                <?php if (!empty($b['author'])): ?>
                                                    <div class="small text-muted">
                                                        <?= htmlspecialchars($b['author']); ?>
                                                    </div>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <span class="small">
                                                    <?= htmlspecialchars($b['dueDate'] ?? '—'); ?>
                                                </span>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>

            </div><!-- /.admin-wrapper -->
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
