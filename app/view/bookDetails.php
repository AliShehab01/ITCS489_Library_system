<?php
session_start();
require_once __DIR__ . '/../../config.php';
require_once __DIR__ . '/../models/dbconnect.php';

include __DIR__ . '/navbar.php';

$db = new Database();
$conn = $db->conn;

$bookId = $_GET['id'] ?? null;
$book = null;

if ($bookId) {
    $stmt = $conn->prepare("SELECT * FROM books WHERE id = :id");
    $stmt->execute([':id' => (int)$bookId]);
    $book = $stmt->fetch(PDO::FETCH_ASSOC);
}

$imageUrl = PUBLIC_URL . 'uploads/books/book_6935dd844fc0d.jpg';
if ($book && isset($book['image_path'])) {
    $rawImage = $book['image_path'] ?? '';
    if (preg_match('#^https?://#', $rawImage)) {
        $imageUrl = $rawImage;
    } else {
        $normalizedImage = ltrim($rawImage, '/');
        if (str_starts_with($normalizedImage, 'public/')) {
            $normalizedImage = substr($normalizedImage, strlen('public/'));
        } elseif ($normalizedImage && !str_contains($normalizedImage, '/')) {
            $normalizedImage = 'uploads/books/' . $normalizedImage;
        }
        $imageUrl = PUBLIC_URL . ($normalizedImage ?: 'uploads/books/book_6935dd844fc0d.jpg');
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $book ? htmlspecialchars($book['title']) : 'Book Details' ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="<?= PUBLIC_URL ?>css/style.css">
</head>

<body>
    <div class="container mt-5">
        <?php if (!$book): ?>
            <div class="alert alert-warning">Book not found. <a href="<?= BASE_URL ?>view/CatalogSearch_Browsing-EN.php">Browse catalog</a></div>
        <?php else: ?>
            <div class="row">
                <div class="col-md-4">
                    <img src="<?= htmlspecialchars($imageUrl) ?>" class="img-fluid rounded" alt="<?= htmlspecialchars($book['title']) ?>">
                </div>
                <div class="col-md-8">
                    <h1><?= htmlspecialchars($book['title']) ?></h1>
                    <p class="lead">by <?= htmlspecialchars($book['author']) ?></p>

                    <table class="table">
                        <tr>
                            <th>ISBN</th>
                            <td><?= htmlspecialchars($book['isbn']) ?></td>
                        </tr>
                        <tr>
                            <th>Category</th>
                            <td><?= htmlspecialchars($book['category'] ?? 'N/A') ?></td>
                        </tr>
                        <tr>
                            <th>Publisher</th>
                            <td><?= htmlspecialchars($book['publisher'] ?? 'N/A') ?></td>
                        </tr>
                        <tr>
                            <th>Year</th>
                            <td><?= $book['year'] ?? 'N/A' ?></td>
                        </tr>
                        <tr>
                            <th>Available</th>
                            <td><?= (int)$book['quantity'] ?> copies</td>
                        </tr>
                        <tr>
                            <th>Status</th>
                            <td><span class="badge bg-<?= $book['status'] === 'available' ? 'success' : 'secondary' ?>"><?= ucfirst($book['status']) ?></span></td>
                        </tr>
                    </table>

                    <?php if (isset($_SESSION['username']) && $book['quantity'] > 0): ?>
                        <a href="<?= BASE_URL ?>view/BorrowBook.php?bookid=<?= (int)$book['id'] ?>" class="btn btn-primary">Borrow This Book</a>
                    <?php elseif (!isset($_SESSION['username'])): ?>
                        <a href="<?= BASE_URL ?>view/login.php" class="btn btn-outline-primary">Login to Borrow</a>
                    <?php else: ?>
                        <button class="btn btn-secondary" disabled>Currently Unavailable</button>
                    <?php endif; ?>

                    <a href="<?= BASE_URL ?>view/CatalogSearch_Browsing-EN.php" class="btn btn-outline-secondary ms-2">Back to Catalog</a>
                </div>
            </div>
        <?php endif; ?>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>
