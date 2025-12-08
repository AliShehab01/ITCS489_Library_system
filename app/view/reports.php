<?php
session_start();

// فقط الأدمن
require_once __DIR__ . '/../controller/checkifadmin.php';

// الاتصال بقاعدة البيانات (PDO)
require_once __DIR__ . '/../models/dbconnect.php';
$db  = new Database();
$pdo = $db->getPdo();

// --------- 1) Summary cards ---------
$totalBooks = (int)$pdo->query("SELECT COUNT(*) FROM books")->fetchColumn();
$totalUsers = (int)$pdo->query("SELECT COUNT(*) FROM users")->fetchColumn();

$activeBorrows = (int)$pdo->query("
    SELECT COUNT(*) 
    FROM borrows 
    WHERE isReturned = 'false'
")->fetchColumn();

$overdueBorrows = (int)$pdo->query("
    SELECT COUNT(*) 
    FROM borrows 
    WHERE isReturned = 'false'
      AND dueDate < CURDATE()
")->fetchColumn();

$activeReservations = (int)$pdo->query("
    SELECT COUNT(*) 
    FROM reservations 
    WHERE status IN ('active','notified')
")->fetchColumn();

// --------- 2) Top borrowed books (أفضل الكتب استعارة) ---------
$sqlTopBooks = "
    SELECT 
        b.id,
        b.title,
        b.author,
        COUNT(*) AS borrow_count
    FROM borrows br
    JOIN books b ON b.id = br.bookId
    GROUP BY br.bookId
    ORDER BY borrow_count DESC
    LIMIT 5
";
$topBooks = $pdo->query($sqlTopBooks)->fetchAll(PDO::FETCH_ASSOC);

// --------- 3) Borrows per category (إحصاء على مستوى التصنيف) ---------
$sqlPerCategory = "
    SELECT 
        b.category,
        COUNT(br.borrow_id) AS borrow_count
    FROM books b
    LEFT JOIN borrows br ON br.bookId = b.id
    GROUP BY b.category
    ORDER BY b.category
";
$perCategory = $pdo->query($sqlPerCategory)->fetchAll(PDO::FETCH_ASSOC);

// --------- 4) User borrowing history (حسب المستخدم) ---------
$selectedUserId = isset($_GET['user']) ? (int)$_GET['user'] : 0;

// قائمة المستخدمين للـ dropdown
$usersStmt = $pdo->query("SELECT id, firstName, lastName FROM users ORDER BY firstName");
$usersList = $usersStmt->fetchAll(PDO::FETCH_ASSOC);

$userHistory = [];
if ($selectedUserId > 0) {
    $sqlHistory = "
        SELECT 
            br.borrow_id,
            br.bookId,
            br.dueDate,
            br.isReturned,
            br.price,
            b.title
        FROM borrows br
        JOIN books b ON br.bookId = b.id
        WHERE br.user_id = :uid
        ORDER BY br.borrow_id DESC
        LIMIT 50
    ";
    $stmtHistory = $pdo->prepare($sqlHistory);
    $stmtHistory->execute([':uid' => $selectedUserId]);
    $userHistory = $stmtHistory->fetchAll(PDO::FETCH_ASSOC);
}

// Helper بسيط لعرض اسم المستخدم في التاريخ
function formatUserName($row) {
    $first = $row['firstName'] ?? '';
    $last  = $row['lastName'] ?? '';
    $full  = trim("$first $last");
    return $full !== '' ? $full : 'User #' . ($row['id'] ?? '');
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reports &amp; Analytics – Admin</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="<?= BASE_URL ?>public/css/style.css">
</head>

<body>

<?php include __DIR__ . '/navbar.php'; ?>

<main class="site-content">

    <!-- Header -->
    <section class="py-4 bg-white border-bottom">
        <div class="container">
            <h1 class="h4 mb-1">Reports &amp; Analytics</h1>
            <p class="text-muted mb-0">
                Overview of library usage, borrowing activity, and user history.
            </p>
        </div>
    </section>

    <section class="py-4">
        <div class="container my-2">
            <div class="admin-wrapper mx-auto">

                <!-- Summary cards -->
                <div class="row g-3 mb-4">
                    <div class="col-12 col-md-4 col-lg-2">
                        <div class="card shadow-custom h-100">
                            <div class="card-body py-3">
                                <div class="text-muted small mb-1">Total books</div>
                                <div class="h5 mb-0"><?= $totalBooks; ?></div>
                            </div>
                        </div>
                    </div>

                    <div class="col-12 col-md-4 col-lg-2">
                        <div class="card shadow-custom h-100">
                            <div class="card-body py-3">
                                <div class="text-muted small mb-1">Total users</div>
                                <div class="h5 mb-0"><?= $totalUsers; ?></div>
                            </div>
                        </div>
                    </div>

                    <div class="col-12 col-md-4 col-lg-2">
                        <div class="card shadow-custom h-100">
                            <div class="card-body py-3">
                                <div class="text-muted small mb-1">Active borrows</div>
                                <div class="h5 mb-0"><?= $activeBorrows; ?></div>
                            </div>
                        </div>
                    </div>

                    <div class="col-12 col-md-4 col-lg-3">
                        <div class="card shadow-custom h-100">
                            <div class="card-body py-3">
                                <div class="text-muted small mb-1">Overdue borrows</div>
                                <div class="h5 mb-0 text-danger"><?= $overdueBorrows; ?></div>
                            </div>
                        </div>
                    </div>

                    <div class="col-12 col-md-4 col-lg-3">
                        <div class="card shadow-custom h-100">
                            <div class="card-body py-3">
                                <div class="text-muted small mb-1">Active reservations</div>
                                <div class="h5 mb-0"><?= $activeReservations; ?></div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Top books + per category -->
                <div class="row g-4 mb-4">
                    <!-- Top borrowed books -->
                    <div class="col-12 col-lg-6">
                        <div class="card shadow-custom h-100">
                            <div class="card-header">
                                <h2 class="h6 mb-0">Top borrowed books</h2>
                            </div>
                            <div class="card-body p-0">
                                <?php if (empty($topBooks)): ?>
                                    <div class="p-3 text-muted small">
                                        No borrowing data available yet.
                                    </div>
                                <?php else: ?>
                                    <div class="table-responsive">
                                        <table class="table align-middle mb-0">
                                            <thead>
                                            <tr>
                                                <th>Title</th>
                                                <th>Author</th>
                                                <th style="width: 120px;">Borrows</th>
                                            </tr>
                                            </thead>
                                            <tbody>
                                            <?php foreach ($topBooks as $row): ?>
                                                <tr>
                                                    <td><?= htmlspecialchars($row['title']); ?></td>
                                                    <td class="small text-muted">
                                                        <?= htmlspecialchars($row['author']); ?>
                                                    </td>
                                                    <td>
                                                        <span class="badge bg-primary-subtle text-primary">
                                                            <?= (int)$row['borrow_count']; ?>
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

                    <!-- Borrows per category -->
                    <div class="col-12 col-lg-6">
                        <div class="card shadow-custom h-100">
                            <div class="card-header">
                                <h2 class="h6 mb-0">Borrowing by category</h2>
                            </div>
                            <div class="card-body p-0">
                                <?php if (empty($perCategory)): ?>
                                    <div class="p-3 text-muted small">
                                        No category data available.
                                    </div>
                                <?php else: ?>
                                    <div class="table-responsive">
                                        <table class="table align-middle mb-0">
                                            <thead>
                                            <tr>
                                                <th>Category</th>
                                                <th style="width: 140px;">Borrows</th>
                                            </tr>
                                            </thead>
                                            <tbody>
                                            <?php foreach ($perCategory as $row): ?>
                                                <tr>
                                                    <td><?= htmlspecialchars($row['category'] ?? '—'); ?></td>
                                                    <td>
                                                        <span class="badge bg-secondary-subtle text-secondary">
                                                            <?= (int)$row['borrow_count']; ?>
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

                <!-- User borrowing history -->
                <div class="card shadow-custom">
                    <div class="card-header d-flex flex-wrap justify-content-between align-items-center gap-2">
                        <div>
                            <h2 class="h6 mb-0">User borrowing history</h2>
                            <small class="text-muted">
                                Select a user to view recent borrows.
                            </small>
                        </div>

                        <form method="get" class="d-flex align-items-center gap-2">
                            <label for="user" class="small text-muted mb-0">User:</label>
                            <select name="user" id="user" class="form-select form-select-sm" onchange="this.form.submit()">
                                <option value="0">All / none selected</option>
                                <?php foreach ($usersList as $u): ?>
                                    <?php $uid = (int)$u['id']; ?>
                                    <option value="<?= $uid; ?>" <?= $uid === $selectedUserId ? 'selected' : ''; ?>>
                                        <?= htmlspecialchars(formatUserName($u)); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </form>
                    </div>

                    <div class="card-body p-0">
                        <?php if ($selectedUserId <= 0): ?>
                            <div class="p-3 text-muted small">
                                Select a user from the dropdown above to view their recent borrowing history.
                            </div>
                        <?php elseif (empty($userHistory)): ?>
                            <div class="p-3 text-muted small">
                                No borrowing history found for this user.
                            </div>
                        <?php else: ?>
                            <div class="table-responsive">
                                <table class="table align-middle mb-0">
                                    <thead>
                                    <tr>
                                        <th>Borrow #</th>
                                        <th>Book</th>
                                        <th>Due date</th>
                                        <th>Status</th>
                                        <th>Price</th>
                                    </tr>
                                    </thead>
                                    <tbody>
                                    <?php foreach ($userHistory as $h): ?>
                                        <?php
                                        $isReturned = ($h['isReturned'] ?? '') === 'true';
                                        $statusLabel = $isReturned ? 'Returned' : 'Active';
                                        $statusClass = $isReturned ? 'bg-success' : 'bg-warning text-dark';
                                        ?>
                                        <tr>
                                            <td>#<?= htmlspecialchars($h['borrow_id']); ?></td>
                                            <td><?= htmlspecialchars($h['title']); ?></td>
                                            <td class="small">
                                                <?= htmlspecialchars($h['dueDate'] ?? '—'); ?>
                                            </td>
                                            <td>
                                                <span class="badge <?= $statusClass; ?>">
                                                    <?= $statusLabel; ?>
                                                </span>
                                            </td>
                                            <td>
                                                <?php if (isset($h['price'])): ?>
                                                    <span class="small">
                                                        <?= number_format((float)$h['price'], 2); ?>
                                                    </span>
                                                <?php else: ?>
                                                    <span class="small text-muted">—</span>
                                                <?php endif; ?>
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

<footer class="py-3 mt-4 bg-dark text-white">
    <div class="container text-center small">
        © 2025 Library System. All rights reserved.
    </div>
</footer>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>
