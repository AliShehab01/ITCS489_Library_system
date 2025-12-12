<?php
session_start();
require_once __DIR__ . '/../../config.php';
require_once __DIR__ . '/../controller/checkifadmin.php';
require_once __DIR__ . '/../models/dbconnect.php';

$db = new Database();
$conn = $db->conn;

$message = '';
$messageType = 'info';

// Create uploads directory if not exists
$uploadDir = __DIR__ . '/../../public/uploads/books/';
if (!is_dir($uploadDir)) {
    mkdir($uploadDir, 0755, true);
}

// Handle image upload
function handleImageUpload($file)
{
    global $uploadDir;

    if (!isset($file) || $file['error'] !== UPLOAD_ERR_OK) {
        return null;
    }

    $allowedTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
    if (!in_array($file['type'], $allowedTypes)) {
        return null;
    }

    $maxSize = 5 * 1024 * 1024; // 5MB
    if ($file['size'] > $maxSize) {
        return null;
    }

    $ext = pathinfo($file['name'], PATHINFO_EXTENSION);
    $filename = 'book_' . uniqid() . '.' . $ext;
    $targetPath = $uploadDir . $filename;

    if (move_uploaded_file($file['tmp_name'], $targetPath)) {
        return 'public/uploads/books/' . $filename;
    }

    return null;
}

function buildBookImageUrl(?string $path): string
{
    $fallback = PUBLIC_URL . 'uploads/books/book_6935dd844fc0d.jpg';
    if (!$path) {
        return $fallback;
    }
    if (preg_match('#^https?://#', $path)) {
        return $path;
    }

    $normalized = ltrim($path, '/');
    if (str_starts_with($normalized, 'public/')) {
        $normalized = substr($normalized, strlen('public/'));
    } elseif (!str_contains($normalized, '/')) {
        $normalized = 'uploads/books/' . $normalized;
    }

    return PUBLIC_URL . $normalized;
}

// Handle Add Book
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    if ($_POST['action'] === 'add') {
        $title = trim($_POST['title'] ?? '');
        $author = trim($_POST['author'] ?? '');
        $isbn = trim($_POST['isbn'] ?? '');
        $category = $_POST['category'] ?? '';
        $publisher = trim($_POST['publisher'] ?? '');
        $year = (int)($_POST['year'] ?? 0);
        $quantity = (int)($_POST['quantity'] ?? 1);

        // Handle image upload
        $imagePath = handleImageUpload($_FILES['book_image'] ?? null);

        if (empty($title) || empty($author) || empty($isbn)) {
            $message = 'Title, Author, and ISBN are required.';
            $messageType = 'danger';
        } else {
            try {
                $stmt = $conn->prepare("INSERT INTO books (title, author, isbn, category, publisher, year, quantity, status, image_path) VALUES (:title, :author, :isbn, :category, :publisher, :year, :quantity, 'available', :image)");
                $stmt->execute([
                    ':title' => $title,
                    ':author' => $author,
                    ':isbn' => $isbn,
                    ':category' => $category,
                    ':publisher' => $publisher,
                    ':year' => $year ?: null,
                    ':quantity' => $quantity,
                    ':image' => $imagePath
                ]);
                $message = "Book '$title' added successfully!";
                $messageType = 'success';
            } catch (PDOException $e) {
                $message = 'Error adding book: ' . $e->getMessage();
                $messageType = 'danger';
            }
        }
    } elseif ($_POST['action'] === 'update') {
        $bookId = (int)($_POST['book_id'] ?? 0);
        $title = trim($_POST['title'] ?? '');
        $author = trim($_POST['author'] ?? '');
        $category = $_POST['category'] ?? '';
        $quantity = (int)($_POST['quantity'] ?? 0);

        // Handle image upload for update
        $imagePath = handleImageUpload($_FILES['book_image'] ?? null);

        if ($bookId && $title) {
            try {
                if ($imagePath) {
                    $stmt = $conn->prepare("UPDATE books SET title = :title, author = :author, category = :category, quantity = :quantity, image_path = :image, status = CASE WHEN :quantity > 0 THEN 'available' ELSE 'unavailable' END WHERE id = :id");
                    $stmt->execute([':title' => $title, ':author' => $author, ':category' => $category, ':quantity' => $quantity, ':image' => $imagePath, ':id' => $bookId]);
                } else {
                    $stmt = $conn->prepare("UPDATE books SET title = :title, author = :author, category = :category, quantity = :quantity, status = CASE WHEN :quantity > 0 THEN 'available' ELSE 'unavailable' END WHERE id = :id");
                    $stmt->execute([':title' => $title, ':author' => $author, ':category' => $category, ':quantity' => $quantity, ':id' => $bookId]);
                }
                $message = "Book updated successfully!";
                $messageType = 'success';
            } catch (PDOException $e) {
                $message = 'Error updating book.';
                $messageType = 'danger';
            }
        }
    } elseif ($_POST['action'] === 'delete') {
        $bookId = (int)($_POST['book_id'] ?? 0);
        if ($bookId) {
            try {
                $conn->prepare("DELETE FROM books WHERE id = :id")->execute([':id' => $bookId]);
                $message = "Book deleted successfully!";
                $messageType = 'success';
            } catch (PDOException $e) {
                $message = 'Error deleting book.';
                $messageType = 'danger';
            }
        }
    } elseif ($_POST['action'] === 'add_quantity') {
        $bookId = (int)($_POST['book_id'] ?? 0);
        $addQty = (int)($_POST['add_quantity'] ?? 0);
        if ($bookId && $addQty > 0) {
            try {
                $conn->prepare("UPDATE books SET quantity = quantity + :qty, status = 'available' WHERE id = :id")->execute([':qty' => $addQty, ':id' => $bookId]);
                $message = "Added $addQty copies successfully!";
                $messageType = 'success';
            } catch (PDOException $e) {
                $message = 'Error adding quantity.';
                $messageType = 'danger';
            }
        }
    } elseif ($_POST['action'] === 'update_image') {
        $bookId = (int)($_POST['book_id'] ?? 0);
        $imagePath = handleImageUpload($_FILES['book_image'] ?? null);

        if ($bookId && $imagePath) {
            try {
                $conn->prepare("UPDATE books SET image_path = :image WHERE id = :id")->execute([':image' => $imagePath, ':id' => $bookId]);
                $message = "Book cover updated!";
                $messageType = 'success';
            } catch (PDOException $e) {
                $message = 'Error updating image.';
                $messageType = 'danger';
            }
        }
    }
}

// Fetch all books
$books = $conn->query("SELECT * FROM books ORDER BY title ASC")->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Book Management</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            padding-top: 56px;
            background: #f8f9fa;
        }

        .book-cover {
            width: 60px;
            height: 80px;
            object-fit: cover;
            border-radius: 4px;
        }

        .book-cover-placeholder {
            width: 60px;
            height: 80px;
            background: #e9ecef;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 4px;
            color: #6c757d;
            font-size: 1.5rem;
        }

        .preview-img {
            max-width: 150px;
            max-height: 200px;
            margin-top: 10px;
            border-radius: 4px;
        }
    </style>
</head>

<body>
    <?php include __DIR__ . '/navbar.php'; ?>

    <div class="container py-4">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h1>📚 Book Management</h1>
            <a href="<?= BASE_URL ?>view/AdminArea.php" class="btn btn-outline-secondary">← Back to Admin</a>
        </div>

        <?php if ($message): ?>
            <div class="alert alert-<?= $messageType ?> alert-dismissible fade show">
                <?= htmlspecialchars($message) ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <!-- Add New Book -->
        <div class="card mb-4">
            <div class="card-header bg-primary text-white">
                <h5 class="mb-0">➕ Add New Book</h5>
            </div>
            <div class="card-body">
                <form method="post" enctype="multipart/form-data" class="row g-3">
                    <input type="hidden" name="action" value="add">
                    <div class="col-md-6">
                        <label class="form-label">Title <span class="text-danger">*</span></label>
                        <input type="text" name="title" class="form-control" required>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Author <span class="text-danger">*</span></label>
                        <input type="text" name="author" class="form-control" required>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">ISBN <span class="text-danger">*</span></label>
                        <input type="text" name="isbn" class="form-control" required>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Category</label>
                        <select name="category" class="form-select">
                            <option value="">Select...</option>
                            <option value="Science">Science</option>
                            <option value="Engineering">Engineering</option>
                            <option value="History">History</option>
                            <option value="Literature">Literature</option>
                            <option value="Business">Business</option>
                            <option value="Other">Other</option>
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Quantity</label>
                        <input type="number" name="quantity" class="form-control" value="1" min="1">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Publisher</label>
                        <input type="text" name="publisher" class="form-control">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Year</label>
                        <input type="number" name="year" class="form-control" min="1800" max="2030">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Book Cover Image</label>
                        <input type="file" name="book_image" class="form-control" accept="image/*" onchange="previewImage(this, 'addPreview')">
                        <img id="addPreview" class="preview-img d-none">
                    </div>
                    <div class="col-12">
                        <button type="submit" class="btn btn-primary">Add Book</button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Books List -->
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">📖 All Books (<?= count($books) ?>)</h5>
            </div>
            <div class="card-body p-0">
                <?php if (empty($books)): ?>
                    <div class="p-4 text-center text-muted">No books in catalog yet.</div>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="table table-hover mb-0 align-middle">
                            <thead class="table-light">
                                <tr>
                                    <th>Cover</th>
                                    <th>Title</th>
                                    <th>Author</th>
                                    <th>ISBN</th>
                                    <th>Category</th>
                                    <th>Qty</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($books as $book): ?>
                                    <tr>
                                        <td>
                                            <?php if (!empty($book['image_path'])): ?>
                                                <img src="<?= htmlspecialchars(buildBookImageUrl($book['image_path'])) ?>" class="book-cover" alt="Cover">
                                            <?php else: ?>
                                                <div class="book-cover-placeholder">📕</div>
                                            <?php endif; ?>
                                        </td>
                                        <td><strong><?= htmlspecialchars($book['title']) ?></strong></td>
                                        <td><?= htmlspecialchars($book['author']) ?></td>
                                        <td><code><?= htmlspecialchars($book['isbn']) ?></code></td>
                                        <td><?= htmlspecialchars($book['category'] ?? '—') ?></td>
                                        <td><span class="badge <?= $book['quantity'] > 0 ? 'bg-success' : 'bg-danger' ?>"><?= (int)$book['quantity'] ?></span></td>
                                        <td>
                                            <button class="btn btn-sm btn-outline-primary" data-bs-toggle="modal" data-bs-target="#editModal<?= $book['id'] ?>">Edit</button>
                                            <button class="btn btn-sm btn-outline-info" data-bs-toggle="modal" data-bs-target="#imageModal<?= $book['id'] ?>">📷</button>
                                            <button class="btn btn-sm btn-outline-success" data-bs-toggle="modal" data-bs-target="#addQtyModal<?= $book['id'] ?>">+Qty</button>
                                            <form method="post" style="display:inline" onsubmit="return confirm('Delete this book?')">
                                                <input type="hidden" name="action" value="delete">
                                                <input type="hidden" name="book_id" value="<?= $book['id'] ?>">
                                                <button type="submit" class="btn btn-sm btn-outline-danger">Del</button>
                                            </form>
                                        </td>
                                    </tr>

                                    <!-- Edit Modal -->
                                    <div class="modal fade" id="editModal<?= $book['id'] ?>" tabindex="-1">
                                        <div class="modal-dialog">
                                            <div class="modal-content">
                                                <form method="post" enctype="multipart/form-data">
                                                    <input type="hidden" name="action" value="update">
                                                    <input type="hidden" name="book_id" value="<?= $book['id'] ?>">
                                                    <div class="modal-header">
                                                        <h5 class="modal-title">Edit Book</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                                    </div>
                                                    <div class="modal-body">
                                                        <div class="mb-3"><label class="form-label">Title</label><input type="text" name="title" class="form-control" value="<?= htmlspecialchars($book['title']) ?>" required></div>
                                                        <div class="mb-3"><label class="form-label">Author</label><input type="text" name="author" class="form-control" value="<?= htmlspecialchars($book['author']) ?>"></div>
                                                        <div class="mb-3"><label class="form-label">Category</label>
                                                            <select name="category" class="form-select">
                                                                <option value="">Select...</option>
                                                                <?php foreach (['Science', 'Engineering', 'History', 'Literature', 'Business', 'Other'] as $cat): ?>
                                                                    <option value="<?= $cat ?>" <?= $book['category'] === $cat ? 'selected' : '' ?>><?= $cat ?></option>
                                                                <?php endforeach; ?>
                                                            </select>
                                                        </div>
                                                        <div class="mb-3"><label class="form-label">Quantity</label><input type="number" name="quantity" class="form-control" value="<?= (int)$book['quantity'] ?>" min="0"></div>
                                                        <div class="mb-3"><label class="form-label">Update Cover (optional)</label><input type="file" name="book_image" class="form-control" accept="image/*"></div>
                                                    </div>
                                                    <div class="modal-footer"><button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button><button type="submit" class="btn btn-primary">Save</button></div>
                                                </form>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Image Upload Modal -->
                                    <div class="modal fade" id="imageModal<?= $book['id'] ?>" tabindex="-1">
                                        <div class="modal-dialog modal-sm">
                                            <div class="modal-content">
                                                <form method="post" enctype="multipart/form-data">
                                                    <input type="hidden" name="action" value="update_image">
                                                    <input type="hidden" name="book_id" value="<?= $book['id'] ?>">
                                                    <div class="modal-header">
                                                        <h5 class="modal-title">Update Cover</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                                    </div>
                                                    <div class="modal-body text-center">
                                                        <?php if (!empty($book['image_path'])): ?>
                                                            <img src="<?= htmlspecialchars(buildBookImageUrl($book['image_path'])) ?>" class="img-fluid mb-3" style="max-height:200px">
                                                        <?php else: ?>
                                                            <div class="text-muted mb-3">No cover image</div>
                                                        <?php endif; ?>
                                                        <input type="file" name="book_image" class="form-control" accept="image/*" required>
                                                    </div>
                                                    <div class="modal-footer"><button type="submit" class="btn btn-info">Upload</button></div>
                                                </form>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Add Quantity Modal -->
                                    <div class="modal fade" id="addQtyModal<?= $book['id'] ?>" tabindex="-1">
                                        <div class="modal-dialog modal-sm">
                                            <div class="modal-content">
                                                <form method="post">
                                                    <input type="hidden" name="action" value="add_quantity">
                                                    <input type="hidden" name="book_id" value="<?= $book['id'] ?>">
                                                    <div class="modal-header">
                                                        <h5 class="modal-title">Add Copies</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                                    </div>
                                                    <div class="modal-body">
                                                        <p class="mb-2"><?= htmlspecialchars($book['title']) ?></p>
                                                        <p class="text-muted small">Current: <?= (int)$book['quantity'] ?></p>
                                                        <input type="number" name="add_quantity" class="form-control" value="1" min="1" required>
                                                    </div>
                                                    <div class="modal-footer"><button type="submit" class="btn btn-success">Add</button></div>
                                                </form>
                                            </div>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function previewImage(input, previewId) {
            const preview = document.getElementById(previewId);
            if (input.files && input.files[0]) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    preview.src = e.target.result;
                    preview.classList.remove('d-none');
                };
                reader.readAsDataURL(input.files[0]);
            }
        }
    </script>
</body>

</html>
