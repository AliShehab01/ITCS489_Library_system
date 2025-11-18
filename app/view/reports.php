<?php
// Reporting & Tracking page for Admins

session_start();

// Only allow admins
require_once __DIR__ . '/../controller/checkifadmin.php';

// DB connection
require_once __DIR__ . '/../models/dbconnect.php';
$db  = new Database();
$pdo = $db->getPdo(); // PDO instance



// ---------- 1) SUMMARY COUNTS ----------

// Total borrows (all time)
$stmt = $pdo->query("SELECT COUNT(*) FROM borrows");
$totalBorrowed = (int)$stmt->fetchColumn();

// Total returned (isReturned = 'true')
$stmt = $pdo->query("SELECT COUNT(*) FROM borrows WHERE isReturned = 'true'");
$totalReturned = (int)$stmt->fetchColumn();

// Overdue now: not returned AND dueDate < today
$stmt = $pdo->query("
    SELECT COUNT(*) 
    FROM borrows 
    WHERE isReturned = 'false'
      AND dueDate < CURDATE()
");
$totalOverdue = (int)$stmt->fetchColumn();

// Active reservations
$stmt = $pdo->query("
    SELECT COUNT(*) 
    FROM reservations 
    WHERE status = 'active'
");
$totalReserved = (int)$stmt->fetchColumn();


// ---------- 2) MOST BORROWED BOOKS ----------

$sqlPopularBooks = "
    SELECT b.id, b.title, COUNT(*) AS borrow_count
    FROM borrows br
    JOIN books b ON br.bookId = b.id
    GROUP BY b.id, b.title
    ORDER BY borrow_count DESC
    LIMIT 10
";
$popularBooks = $pdo->query($sqlPopularBooks)->fetchAll(PDO::FETCH_ASSOC);


// ---------- 3) TOP ACTIVE USERS (USER STATISTICS) ----------

$sqlTopUsers = "
    SELECT u.id, u.firstName, u.lastName, COUNT(*) AS borrow_count
    FROM borrows br
    JOIN users u ON br.user_id = u.id
    GROUP BY u.id, u.firstName, u.lastName
    ORDER BY borrow_count DESC
    LIMIT 10
";
$topUsers = $pdo->query($sqlTopUsers)->fetchAll(PDO::FETCH_ASSOC);


// ---------- 4) SIMPLE FINES ESTIMATE ----------

$sqlOutstandingFines = "
    SELECT SUM(price) 
    FROM borrows
    WHERE isReturned = 'false'
      AND dueDate < CURDATE()
";
$outstandingFines = (float)$pdo->query($sqlOutstandingFines)->fetchColumn();
$outstandingFines = $outstandingFines ?: 0.0;


// ---------- 5) USER BORROWING HISTORY ----------

$selectedUserId = isset($_GET['user_id']) ? (int)$_GET['user_id'] : 0;

// Get list of users for dropdown
$usersStmt = $pdo->query("SELECT id, firstName, lastName FROM users ORDER BY firstName");
$usersList = $usersStmt->fetchAll(PDO::FETCH_ASSOC);

$userHistory = [];
if ($selectedUserId > 0) {
    $sqlHistory = "
        SELECT br.borrow_id, br.bookId, br.dueDate, br.isReturned, br.price,
               b.title
        FROM borrows br
        JOIN books b ON br.bookId = b.id
        WHERE br.user_id = :uid
        ORDER BY br.borrow_id DESC
    ";
    $stmtHist = $pdo->prepare($sqlHistory);
    $stmtHist->execute([':uid' => $selectedUserId]);
    $userHistory = $stmtHist->fetchAll(PDO::FETCH_ASSOC);
}

?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Reports & Tracking</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <!-- Bootstrap CSS (same version as other pages is fine) -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>

<body class="bg-light">

    <?php include 'navbar.php'; ?>

    <div class="container mt-5 mb-5">

        <h1 class="mb-3">Reports &amp; Tracking</h1>
        <p class="text-muted">
            Overview of borrowed, returned, overdue and reserved books, user statistics, fines and history.
        </p>

        <!-- 1) SUMMARY CARDS -->
        <div class="row my-4 g-3">
            <div class="col-6 col-md-3">
                <div class="card text-center shadow-sm">
                    <div class="card-header">Borrowed (all time)</div>
                    <div class="card-body">
                        <h3 class="mb-0"><?php echo $totalBorrowed; ?></h3>
                    </div>
                </div>
            </div>

            <div class="col-6 col-md-3">
                <div class="card text-center shadow-sm">
                    <div class="card-header">Returned</div>
                    <div class="card-body">
                        <h3 class="mb-0"><?php echo $totalReturned; ?></h3>
                    </div>
                </div>
            </div>

            <div class="col-6 col-md-3">
                <div class="card text-center shadow-sm">
                    <div class="card-header">Overdue now</div>
                    <div class="card-body text-danger">
                        <h3 class="mb-0"><?php echo $totalOverdue; ?></h3>
                    </div>
                </div>
            </div>

            <div class="col-6 col-md-3">
                <div class="card text-center shadow-sm">
                    <div class="card-header">Active Reservations</div>
                    <div class="card-body">
                        <h3 class="mb-0"><?php echo $totalReserved; ?></h3>
                    </div>
                </div>
            </div>
        </div>

        <!-- 2) MOST BORROWED BOOKS -->
        <div class="mt-4">
            <h3>Most Borrowed Books</h3>
            <?php if (count($popularBooks) === 0): ?>
                <p class="text-muted">No borrowing data yet.</p>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-striped table-sm align-middle">
                        <thead>
                            <tr>
                                <th>Book ID</th>
                                <th>Title</th>
                                <th>Times Borrowed</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($popularBooks as $row): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($row['id']); ?></td>
                                    <td><?php echo htmlspecialchars($row['title']); ?></td>
                                    <td><?php echo (int)$row['borrow_count']; ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>

        <!-- 3) TOP ACTIVE BORROWERS -->
        <div class="mt-5">
            <h3>Top Active Borrowers</h3>
            <?php if (count($topUsers) === 0): ?>
                <p class="text-muted">No borrowing data yet.</p>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-striped table-sm align-middle">
                        <thead>
                            <tr>
                                <th>User ID</th>
                                <th>Name</th>
                                <th>Total Borrows</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($topUsers as $u): ?>
                                <tr>
                                    <td><?php echo (int)$u['id']; ?></td>
                                    <td><?php echo htmlspecialchars($u['firstName'] . ' ' . $u['lastName']); ?></td>
                                    <td><?php echo (int)$u['borrow_count']; ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>

        <!-- 4) FINES SUMMARY (ESTIMATED) -->
        <div class="mt-5">
            <h3>Fines (Estimated)</h3>
            <p class="text-muted">
                This estimate uses the <code>price</code> field of overdue and not-yet-returned borrows.
                If your group later adds dedicated fine columns, this section can be updated.
            </p>
            <div class="card border-danger shadow-sm">
                <div class="card-body">
                    <strong>Outstanding estimated fines:</strong>
                    <span class="text-danger fw-bold">
                        <?php echo number_format($outstandingFines, 2); ?>
                    </span>
                </div>
            </div>
        </div>

        <!-- 5) USER BORROWING HISTORY -->
        <div class="mt-5">
            <h3>User Borrowing History</h3>
            <form method="get" class="row g-2 mb-3">
                <div class="col-md-6">
                    <select name="user_id" class="form-select" required>
                        <option value="">Select a user…</option>
                        <?php foreach ($usersList as $u): ?>
                            <option value="<?php echo $u['id']; ?>"
                                <?php echo ($selectedUserId === (int)$u['id']) ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($u['firstName'] . ' ' . $u['lastName']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-2">
                    <button class="btn btn-primary">View History</button>
                </div>
            </form>

            <?php if ($selectedUserId > 0): ?>
                <?php if (count($userHistory) === 0): ?>
                    <p class="text-muted">No borrowing records found for this user.</p>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="table table-striped table-sm align-middle">
                            <thead>
                                <tr>
                                    <th>Borrow ID</th>
                                    <th>Book</th>
                                    <th>Due Date</th>
                                    <th>Returned?</th>
                                    <th>Price</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($userHistory as $row): ?>
                                    <tr>
                                        <td><?php echo (int)$row['borrow_id']; ?></td>
                                        <td><?php echo htmlspecialchars($row['title']); ?></td>
                                        <td><?php echo htmlspecialchars($row['dueDate']); ?></td>
                                        <td><?php echo htmlspecialchars($row['isReturned']); ?></td>
                                        <td><?php echo number_format((float)$row['price'], 2); ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
            <?php endif; ?>
        </div>

    </div> <!-- /container -->

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

</body>

</html>