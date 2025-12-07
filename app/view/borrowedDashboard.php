<?php
session_start();

require_once __DIR__ . '/../../config.php';
require_once __DIR__ . '/../models/dbconnect.php';
require_once __DIR__ . '/../models/CreateDefaultDBTables.php';

$userid = $_SESSION['user_id'] ?? null;

if (!$userid) {
    echo "User not logged in.";
    exit;
}

// Prepare the PDO statement
$db = new Database();
$conn = $db->conn;
$stmt = $conn->prepare('SELECT * FROM borrows WHERE user_id = :userid');
$stmt->bindParam(':userid', $userid, PDO::PARAM_INT);
$stmt->execute();

$borrows = $stmt->fetchAll(PDO::FETCH_ASSOC);

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Borrowed Dashboard</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet" />
    <link rel="stylesheet" href="<?= BASE_URL ?>public/css/style.css">
    <?php if (!defined('STYLE_LOADED')) { define('STYLE_LOADED', true); } ?>
</head>

<body>

    <?php include __DIR__ . '/navbar.php'; ?>

    <main class="page-shell">
        <div class="section-title">
            <span class="pill">📚</span>
            <span>Your borrowed items</span>
        </div>

        <?php if (empty($borrows)): ?>
            <div class="alert alert-secondary">You have no borrowed items right now.</div>
        <?php else: ?>
            <div class="row g-3">
                <?php foreach ($borrows as $row): ?>
                    <?php
                        $currentDate = strtotime(date("Y-m-d"));
                        $dueDate = strtotime($row["dueDate"]);
                        $datediff = round(($currentDate - $dueDate) / (60 * 60 * 24));

                        $fines = 0;
                        if ($datediff > 0) {
                            $fines = $datediff * $row["quantity"];
                        }

                        $totalPrice = $row['price'] + $fines;
                    ?>
                    <div class="col-12 col-lg-6">
                        <div class="card shadow-custom h-100">
                            <div class="card-body">
                                <div class="d-flex justify-content-between align-items-start mb-2">
                                    <div>
                                        <h5 class="card-title mb-1">Borrow ID: <?= htmlspecialchars($row['borrow_id']) ?></h5>
                                        <p class="card-text mb-0 text-muted">Book ID: <?= htmlspecialchars($row['bookId']) ?></p>
                                    </div>
                                    <?php if ($row["isReturned"] === "false"): ?>
                                        <span class="badge bg-warning text-dark">Issued</span>
                                    <?php else: ?>
                                        <span class="badge bg-success">Returned</span>
                                    <?php endif; ?>
                                </div>

                                <div class="row g-2 text-muted">
                                    <div class="col-6"><small>Copies</small><div class="fw-semibold text-dark"><?= htmlspecialchars($row['quantity']) ?></div></div>
                                    <div class="col-6"><small>Price</small><div class="fw-semibold text-dark"><?= htmlspecialchars($row['price']) ?> BD</div></div>
                                    <div class="col-6"><small>Fines</small><div class="fw-semibold text-dark"><?= $fines ?> BD</div></div>
                                    <div class="col-6"><small>Total</small><div class="fw-semibold text-dark"><?= $totalPrice ?> BD</div></div>
                                </div>

                                <?php if ($row["isReturned"] === "false"): ?>
                                    <div class="mt-3">
                                        <small class="text-muted d-block mb-1">Due date: <?= htmlspecialchars($row['dueDate']) ?></small>
                                        <form action="bookReturnAndRenew.php" method="post" class="row g-2 align-items-end">
                                            <input type="hidden" name="borrow_id" value="<?= htmlspecialchars($row['borrow_id']) ?>">
                                            <input type="hidden" name="book_id" value="<?= htmlspecialchars($row['bookId']) ?>">
                                            <div class="col-sm-7">
                                                <label for="newDueDate-<?= htmlspecialchars($row['borrow_id']) ?>" class="form-label">New due date</label>
                                                <input type="date" class="form-control" id="newDueDate-<?= htmlspecialchars($row['borrow_id']) ?>" name="newDueDate">
                                            </div>
                                            <div class="col-sm-5 d-flex gap-2">
                                                <button type="submit" name="RenewReturnAction" value="Renew" class="btn btn-outline-primary w-100">Renew</button>
                                                <button type="submit" name="RenewReturnAction" value="Return book" class="btn btn-primary w-100">Return</button>
                                            </div>
                                        </form>
                                    </div>
                                <?php else: ?>
                                    <div class="mt-3 text-success fw-semibold">Borrow status: Returned</div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </main>

    <footer class="app-footer text-center">
        <small>© 2025 Library System.</small>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>
