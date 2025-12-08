<?php
session_start();
require_once __DIR__ . '/../../config.php';
require_once __DIR__ . '/../models/dbconnect.php';

// تأكد أن المستخدم مسجّل دخول
if (!isset($_SESSION['user_id'])) {
    $loginUrl = BASE_URL . 'app/view/login.php';
    header('Location: ' . $loginUrl);
    exit;
}

$userId   = (int)($_SESSION['user_id'] ?? 0);
$username = $_SESSION['username'] ?? '';

// bookid (accept GET/POST fallback to handle different entry points)
$bookId = null;
foreach (['bookid', 'bookId'] as $paramName) {
    if ($bookId === null && isset($_GET[$paramName])) {
        $bookId = (int)$_GET[$paramName];
    } elseif ($bookId === null && isset($_POST[$paramName])) {
        $bookId = (int)$_POST[$paramName];
    }
}
$bookId = ($bookId !== null && $bookId > 0) ? $bookId : 0;

// قيم جاية من الكرت (لو أرسلتها من هناك)
$qtyFromPost     = isset($_POST['QuantityWanted']) ? (int)$_POST['QuantityWanted'] : null;
$dueDateFromPost = $_POST['dueDate'] ?? null;

// لو ما في bookId أصلاً، نعرض رسالة بدل ما نرجّعك بالهيدر
$bookTitle  = '';
$bookAuthor = '';
if ($bookId > 0) {
    try {
        $db  = new Database();
        $pdo = $db->getPdo();

        // Use the real primary key column (id), not bookId
        $stmt = $pdo->prepare("SELECT id, title, author FROM books WHERE id = :id");
        $stmt->execute([':id' => $bookId]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($row) {
            $bookId     = (int)($row['id'] ?? $bookId);
            $bookTitle  = $row['title']  ?? '';
            $bookAuthor = $row['author'] ?? '';
        } else {
            $bookId = 0;
        }
    } catch (Throwable $e) {
        $bookId = 0;
    }
}

// قيم افتراضية للفورم
$qtyDefault     = $qtyFromPost     !== null && $qtyFromPost > 0 ? $qtyFromPost : 1;
$dueDateDefault = $dueDateFromPost ?? '';
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Borrow book – Library</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="<?= BASE_URL ?>public/css/style.css">
</head>

<body>

<?php include __DIR__ . '/navbar.php'; ?>

<main class="site-content">

    <!-- هيدر الصفحة -->
    <section class="py-4 bg-white border-bottom">
        <div class="container">
            <h1 class="h4 mb-1">Confirm borrow</h1>
            <p class="text-muted mb-0">
                Review the details below, then confirm your borrow request.
            </p>
        </div>
    </section>

    <!-- كرت التأكيد -->
    <section class="py-4">
        <div class="container my-2">
            <div class="admin-wrapper mx-auto">
                <div class="card shadow-custom">
                    <div class="card-body">

                        <?php if ($bookId <= 0): ?>
                            <div class="alert alert-danger small">
                                Invalid book. Please go back to the catalog and try again.
                            </div>
                            <a href="CatalogSearch_Browsing-EN.php" class="btn btn-outline-secondary btn-sm">
                                Back to catalog
                            </a>
                        <?php else: ?>

                            <h2 class="h5 mb-2">
                                <?= htmlspecialchars($bookTitle ?: 'Selected book'); ?>
                            </h2>

                            <?php if (!empty($bookAuthor)): ?>
                                <p class="text-muted small mb-2">
                                    Author: <?= htmlspecialchars($bookAuthor); ?>
                                </p>
                            <?php endif; ?>

                            <p class="text-muted small mb-3">
                                Borrower:
                                <strong><?= htmlspecialchars($username ?: ('User #' . $userId)); ?></strong>
                            </p>

                            <form method="post" action="../controller/BorrowBook.php" class="row g-3">
                                <!-- قيم مخفية للمستخدم والكتاب -->
                                <input type="hidden" name="userId" value="<?= $userId; ?>">
                                <input type="hidden" name="bookId" value="<?= $bookId; ?>">

                                <div class="col-md-4">
                                    <label class="form-label small">Quantity</label>
                                    <input type="number"
                                           name="QuantityWanted"
                                           class="form-control"
                                           min="1"
                                           max="5"
                                           value="<?= htmlspecialchars($qtyDefault); ?>"
                                           required>
                                </div>

                                <div class="col-md-4">
                                    <label class="form-label small">Due date</label>
                                    <input type="date"
                                           name="dueDate"
                                           class="form-control"
                                           value="<?= htmlspecialchars($dueDateDefault); ?>"
                                           required>
                                </div>

                                <div class="col-12">
                                    <button type="submit" class="btn btn-primary">
                                        Confirm borrow
                                    </button>
                                    <a href="CatalogSearch_Browsing-EN.php" class="btn btn-outline-secondary ms-2">
                                        Cancel / Back to catalog
                                    </a>
                                </div>
                            </form>

                        <?php endif; ?>

                    </div>
                </div>
            </div>
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
