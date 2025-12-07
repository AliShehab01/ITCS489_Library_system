<?php
// Reporting & Tracking page for Admins

session_start();
require_once __DIR__ . '/../../config.php';
require_once __DIR__ . '/../controller/checkifadmin.php';
require_once __DIR__ . '/../models/dbconnect.php';

$db = new Database();
$conn = $db->conn;

// Stats
$stats = [];
try {
    $stats['totalBooks'] = (int)$conn->query("SELECT COUNT(*) FROM books")->fetchColumn();
    $stats['totalCopies'] = (int)$conn->query("SELECT COALESCE(SUM(quantity), 0) FROM books")->fetchColumn();
    $stats['totalUsers'] = (int)$conn->query("SELECT COUNT(*) FROM users")->fetchColumn();
    $stats['totalBorrows'] = (int)$conn->query("SELECT COUNT(*) FROM borrows")->fetchColumn();
    $stats['activeBorrows'] = (int)$conn->query("SELECT COUNT(*) FROM borrows WHERE isReturned = 'false'")->fetchColumn();
    $stats['overdueBorrows'] = (int)$conn->query("SELECT COUNT(*) FROM borrows WHERE isReturned = 'false' AND dueDate < CURDATE()")->fetchColumn();
    $stats['returnedBorrows'] = (int)$conn->query("SELECT COUNT(*) FROM borrows WHERE isReturned = 'true'")->fetchColumn();

    // Top borrowed books
    $topBooks = $conn->query("
        SELECT b.title, b.author, COUNT(bo.borrow_id) AS borrow_count
        FROM borrows bo JOIN books b ON b.id = bo.bookId
        GROUP BY bo.bookId ORDER BY borrow_count DESC LIMIT 10
    ")->fetchAll(PDO::FETCH_ASSOC);

    // Users by role
    $usersByRole = $conn->query("SELECT role, COUNT(*) as cnt FROM users GROUP BY role")->fetchAll(PDO::FETCH_ASSOC);

    // Category distribution
    $categories = $conn->query("SELECT category, COUNT(*) as cnt FROM books WHERE category IS NOT NULL AND category != '' GROUP BY category ORDER BY cnt DESC")->fetchAll(PDO::FETCH_ASSOC);
} catch (Exception $e) {
}

include __DIR__ . '/navbar.php';
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reports & Statistics</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            padding-top: 80px;
            background: #f8f9fa;
        }

        .stat-card h2 {
            font-size: 2rem;
        }
    </style>
</head>

<body>
    <div class="container py-4">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h1>📊 Reports & Statistics</h1>
            <a href="<?= BASE_URL ?>view/AdminArea.php" class="btn btn-outline-secondary">← Back to Admin</a>
        </div>

        <!-- Overview Stats -->
        <div class="row g-3 mb-4">
            <?php
            $statCards = [
                ['Total Books', $stats['totalBooks'] ?? 0, 'primary', '📚'],
                ['Total Copies', $stats['totalCopies'] ?? 0, 'info', '📖'],
                ['Total Users', $stats['totalUsers'] ?? 0, 'success', '👥'],
                ['Active Borrows', $stats['activeBorrows'] ?? 0, 'warning', '📤'],
                ['Returned', $stats['returnedBorrows'] ?? 0, 'secondary', '✅'],
                ['Overdue', $stats['overdueBorrows'] ?? 0, 'danger', '⚠️'],
            ];
            foreach ($statCards as $card): ?>
                <div class="col-6 col-md-4 col-lg-2">
                    <div class="card stat-card text-center">
                        <div class="card-body py-3">
                            <div class="h4"><?= $card[3] ?></div>
                            <h2 class="text-<?= $card[2] ?> mb-0"><?= $card[1] ?></h2>
                            <small class="text-muted"><?= $card[0] ?></small>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>

        <div class="row g-4">
            <!-- Top Books -->
            <div class="col-md-6">
                <div class="card h-100">
                    <div class="card-header">📈 Most Borrowed Books</div>
                    <ul class="list-group list-group-flush">
                        <?php if (empty($topBooks)): ?>
                            <li class="list-group-item text-muted">No data</li>
                        <?php else: ?>
                            <?php foreach ($topBooks as $tb): ?>
                                <li class="list-group-item d-flex justify-content-between">
                                    <div>
                                        <strong><?= htmlspecialchars($tb['title']) ?></strong>
                                        <br><small class="text-muted"><?= htmlspecialchars($tb['author']) ?></small>
                                    </div>
                                    <span class="badge bg-primary align-self-center"><?= $tb['borrow_count'] ?></span>
                                </li>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </ul>
                </div>
            </div>

            <!-- Users by Role -->
            <div class="col-md-3">
                <div class="card h-100">
                    <div class="card-header">👥 Users by Role</div>
                    <ul class="list-group list-group-flush">
                        <?php foreach ($usersByRole as $ur): ?>
                            <li class="list-group-item d-flex justify-content-between">
                                <span><?= htmlspecialchars(ucfirst($ur['role'])) ?></span>
                                <span class="badge bg-secondary"><?= $ur['cnt'] ?></span>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            </div>

            <!-- Categories -->
            <div class="col-md-3">
                <div class="card h-100">
                    <div class="card-header">📂 Books by Category</div>
                    <ul class="list-group list-group-flush">
                        <?php if (empty($categories)): ?>
                            <li class="list-group-item text-muted">No data</li>
                        <?php else: ?>
                            <?php foreach ($categories as $cat): ?>
                                <li class="list-group-item d-flex justify-content-between">
                                    <span><?= htmlspecialchars($cat['category']) ?></span>
                                    <span class="badge bg-info"><?= $cat['cnt'] ?></span>
                                </li>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </ul>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>