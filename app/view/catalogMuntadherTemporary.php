<?php

session_start();
require_once __DIR__ . '/../../config.php';
require_once __DIR__ . '/../models/dbconnect.php';

$db = new Database();
$pdo = $db->conn;
$books = $pdo->query("SELECT * FROM books")->fetchAll(PDO::FETCH_ASSOC);

?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Catalog (Temporary)</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet"
        integrity="sha384-0pUGZvbkm6XF6gxjEnlmuGrJXVbNuzT9qBBavbLwCsOGabYfZo0T0to5eqruptLy" crossorigin="anonymous">
    <link rel="stylesheet" href="<?= BASE_URL ?>public/css/style.css">
    <?php if (!defined('STYLE_LOADED')) { define('STYLE_LOADED', true); } ?>
</head>

<body>

    <?php include __DIR__ . '/navbar.php'; ?>

    <main class="page-shell">
        <section class="page-hero mb-4">
            <div class="d-flex flex-wrap justify-content-between align-items-start gap-3">
                <div>
                    <p class="text-uppercase text-muted fw-semibold mb-2">Archive</p>
                    <h1 class="display-6 mb-1">Catalog (temporary)</h1>
                    <p class="text-muted mb-0">Legacy listing view kept for reference. Use the main catalog for current browsing.</p>
                </div>
            </div>
        </section>

        <?php if (!$books): ?>
            <div class="alert alert-secondary">No books found.</div>
        <?php else: ?>
            <div class="row g-3">
                <?php foreach ($books as $row): ?>
                    <div class="col-md-4">
                        <div class="card shadow-custom h-100">
                            <div class="card-body">
                                <h5 class="card-title mb-1">
                                    <a href="book.php?bookid=<?= (int)$row['id'] ?>" class="text-decoration-none">
                                        <?= htmlspecialchars($row["title"]) ?>
                                    </a>
                                </h5>
                                <div class="text-muted small mb-2">
                                    Author: <?= htmlspecialchars($row["author"]) ?><br>
                                    ISBN: <?= htmlspecialchars($row["isbn"]) ?><br>
                                    Category: <?= htmlspecialchars($row["category"]) ?><br>
                                    Publisher: <?= htmlspecialchars($row["publisher"]) ?><br>
                                    Year: <?= htmlspecialchars($row["year"]) ?><br>
                                    Quantity: <?= htmlspecialchars($row["quantity"]) ?>
                                </div>
                                <?php if (!empty($row["image_path"])): ?>
                                    <img src="<?= htmlspecialchars($row["image_path"]) ?>" class="img-fluid rounded" alt="<?= htmlspecialchars($row["title"]) ?>">
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </main>

    <footer class="app-footer text-center">
        <small>&copy; 2025 Library System. All rights reserved.</small>
    </footer>

</body>

</html>
