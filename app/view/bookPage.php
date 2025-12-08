<?php
session_start();

/*
  Book management page (Admin/Staff)
  Uses BookApi.php and changestatus.php for backend operations.
*/

require_once __DIR__ . '/../models/dbconnect.php';
$db   = new Database();
$conn = $db->conn;

// نفس التحقق القديم (مستخدم مسجل دخول)
if (!isset($_SESSION['username'])) {
    header("Location: ../../public.php");
    exit;
}

// جلب جميع الكتب لعرضها في الجدول
$stmt  = $conn->query("
    SELECT id, image_path, title, author, isbn, category, publisher, year, quantity, status, created_at
    FROM books
    ORDER BY created_at DESC
");
$books = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Books – Admin</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="<?= BASE_URL ?>public/css/style.css">
</head>

<body>

<?php include __DIR__ . '/navbar.php'; ?>

<main class="site-content">

    <!-- Header -->
    <section class="py-4 bg-white border-bottom">
        <div class="container">
            <h1 class="h4 mb-1">Manage books</h1>
            <p class="text-muted mb-0">
                Add new books, update details, change status, and remove books from the catalog.
            </p>
        </div>
    </section>

    <section class="py-4">
        <div class="container my-2">
            <div class="admin-wrapper mx-auto">

                <!-- Forms row -->
                <div class="row g-4 mb-4">

                    <!-- Add new book -->
                    <div class="col-12 col-lg-6">
                        <div class="card shadow-custom h-100">
                            <div class="card-header">
                                <h2 class="h6 mb-0">Add new book</h2>
                            </div>
                            <div class="card-body">
                                <form id="bookForm" method="post" enctype="multipart/form-data" class="row g-3">
                                    <div class="col-md-6">
                                        <label class="form-label small">Title</label>
                                        <input type="text" name="title" class="form-control" required>
                                    </div>

                                    <div class="col-md-6">
                                        <label class="form-label small">Author</label>
                                        <input type="text" name="author" class="form-control" required>
                                    </div>

                                    <div class="col-md-6">
                                        <label class="form-label small">ISBN</label>
                                        <input type="text" name="isbn" class="form-control" required>
                                    </div>

                                    <div class="col-md-6">
                                        <label class="form-label small">Category</label>
                                        <select name="category" class="form-select">
                                            <option value="Science">Science</option>
                                            <option value="Engineering">Engineering</option>
                                            <option value="History">History</option>
                                            <option value="Literature">Literature</option>
                                            <option value="Business">Business</option>
                                        </select>
                                    </div>

                                    <div class="col-md-6">
                                        <label class="form-label small">Publisher</label>
                                        <input type="text" name="publisher" class="form-control">
                                    </div>

                                    <div class="col-md-6">
                                        <label class="form-label small">Year</label>
                                        <input type="number" name="year" class="form-control">
                                    </div>

                                    <div class="col-md-6">
                                        <label class="form-label small">Available quantity</label>
                                        <input type="number" name="quantity" class="form-control">
                                    </div>

                                    <div class="col-md-6">
                                        <label class="form-label small">Book cover</label>
                                        <input type="file" name="image" accept="image/*" class="form-control">
                                    </div>

                                    <div class="col-12">
                                        <button type="submit" class="btn btn-primary btn-sm">Add book</button>
                                    </div>
                                </form>

                                <p id="message" class="mt-2 small"></p>
                            </div>
                        </div>
                    </div>

                    <!-- Update book -->
                    <div class="col-12 col-lg-6">
                        <div class="card shadow-custom h-100">
                            <div class="card-header">
                                <h2 class="h6 mb-0">Update book (by ISBN)</h2>
                            </div>
                            <div class="card-body">
                                <form id="updateBookForm" method="post" class="row g-3">
                                    <div class="col-md-6">
                                        <label class="form-label small">ISBN (book to update)</label>
                                        <input type="text" name="isbn" class="form-control" required>
                                    </div>

                                    <div class="col-md-6">
                                        <label class="form-label small">New title</label>
                                        <input type="text" name="title" class="form-control" required>
                                    </div>

                                    <div class="col-md-6">
                                        <label class="form-label small">New author</label>
                                        <input type="text" name="author" class="form-control" required>
                                    </div>

                                    <div class="col-md-6">
                                        <label class="form-label small">New category</label>
                                        <input type="text" name="category" class="form-control">
                                    </div>

                                    <div class="col-md-6">
                                        <label class="form-label small">New publisher</label>
                                        <input type="text" name="publisher" class="form-control">
                                    </div>

                                    <div class="col-md-6">
                                        <label class="form-label small">New year</label>
                                        <input type="number" name="year" class="form-control">
                                    </div>

                                    <div class="col-12">
                                        <button type="submit" class="btn btn-success btn-sm">Update book</button>
                                    </div>
                                </form>

                                <p id="updateMessage" class="mt-2 small"></p>
                            </div>
                        </div>
                    </div>

                </div><!-- /row -->

                <!-- Status + Delete -->
                <div class="row g-4 mb-4">
                    <!-- Change status -->
                    <div class="col-12 col-lg-6">
                        <div class="card shadow-custom h-100">
                            <div class="card-header">
                                <h2 class="h6 mb-0">Change book status (by ISBN)</h2>
                            </div>
                            <div class="card-body">
                                <div class="row g-3">
                                    <div class="col-md-6">
                                        <label for="isbn" class="form-label small">ISBN</label>
                                        <input type="text" id="isbn" class="form-control" placeholder="Enter ISBN">
                                    </div>
                                    <div class="col-md-6">
                                        <label for="options" class="form-label small">Status</label>
                                        <select id="options" class="form-select">
                                            <option value="">--Select an option--</option>
                                            <option value="available">Available</option>
                                            <option value="reserved">Reserved</option>
                                            <option value="unavailable">Unavailable</option>
                                        </select>
                                    </div>
                                    <div class="col-12">
                                        <button type="button" id="b1" class="btn btn-outline-primary btn-sm">
                                            Update status
                                        </button>
                                    </div>
                                </div>
                                <p id="status_res" class="mt-2 small"></p>
                            </div>
                        </div>
                    </div>

                    <!-- Delete book -->
                    <div class="col-12 col-lg-6">
                        <div class="card shadow-custom h-100">
                            <div class="card-header">
                                <h2 class="h6 mb-0">Remove book (by ISBN)</h2>
                            </div>
                            <div class="card-body">
                                <div class="mb-3">
                                    <label class="form-label small">ISBN of book</label>
                                    <input type="text" id="isbnToDelete" class="form-control" placeholder="Enter ISBN">
                                </div>
                                <button type="button" id="deleteBtn" class="btn btn-danger btn-sm">
                                    Delete book
                                </button>
                                <p id="delRes" class="mt-2 small"></p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Books table -->
                <div class="card shadow-custom">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <div>
                            <h2 class="h6 mb-0">Books catalog</h2>
                            <small class="text-muted">All books in the system</small>
                        </div>
                        <span class="badge bg-secondary">
                            Total: <?= $books ? count($books) : 0; ?>
                        </span>
                    </div>
                    <div class="card-body p-0">
                        <?php if (!$books): ?>
                            <div class="p-3 text-muted small">
                                No books found. Use the form above to add the first book.
                            </div>
                        <?php else: ?>
                            <div class="table-responsive">
                                <table class="table align-middle mb-0">
                                    <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th>Title</th>
                                        <th>Author</th>
                                        <th>ISBN</th>
                                        <th>Category</th>
                                        <th>Status</th>
                                        <th>Qty</th>
                                        <th>Details</th>
                                    </tr>
                                    </thead>
                                    <tbody>
                                    <?php foreach ($books as $book): ?>
                                        <tr>
                                            <td><?= htmlspecialchars($book['id']); ?></td>
                                            <td><?= htmlspecialchars($book['title']); ?></td>
                                            <td class="small text-muted">
                                                <?= htmlspecialchars($book['author']); ?>
                                            </td>
                                            <td class="small">
                                                <?= htmlspecialchars($book['isbn']); ?>
                                            </td>
                                            <td class="small">
                                                <?= htmlspecialchars($book['category'] ?? '—'); ?>
                                            </td>
                                            <td>
                                                <span class="badge bg-secondary text-capitalize">
                                                    <?= htmlspecialchars($book['status'] ?? 'available'); ?>
                                                </span>
                                            </td>
                                            <td><?= (int)($book['quantity'] ?? 0); ?></td>
                                            <td>
                                                <a href="bookDetails.php?id=<?= urlencode($book['id']); ?>"
                                                   class="btn btn-sm btn-outline-primary">
                                                    View
                                                </a>
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

<script>
// --- Change book status (changestatus.php) ---
const statusResEl = document.getElementById('status_res');
const statusBtn   = document.getElementById('b1');

if (statusBtn) {
    statusBtn.addEventListener('click', function (e) {
        e.preventDefault();
        const isbnVal   = document.getElementById('isbn').value.trim();
        const statusVal = document.getElementById('options').value;

        if (!isbnVal || !statusVal) {
            alert('Please enter ISBN and select a status.');
            return;
        }

        fetch('../controller/changestatus.php', {
            method: 'PUT',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({
                isbn: isbnVal,
                status: statusVal
            })
        })
        .then(res => res.json())
        .then(data => {
            statusResEl.textContent = data.message || data.error || '';
        })
        .catch(err => {
            statusResEl.textContent = 'Error: ' + err;
        });
    });
}

// --- Delete book (BookApi.php DELETE) ---
const delBtn   = document.getElementById('deleteBtn');
const delResEl = document.getElementById('delRes');

if (delBtn) {
    delBtn.addEventListener('click', function () {
        const isbnToDel = document.getElementById('isbnToDelete').value.trim();
        if (!isbnToDel) {
            alert('Please enter ISBN to delete.');
            return;
        }

        fetch('../controller/BookApi.php?isbn=' + encodeURIComponent(isbnToDel), {
            method: 'DELETE'
        })
        .then(res => res.json())
        .then(data => {
            delResEl.textContent = data.message || data.error || '';
        })
        .catch(err => {
            delResEl.textContent = 'Error: ' + err;
        });
    });
}

// --- Add book (BookApi.php POST) ---
const bookForm = document.getElementById('bookForm');
const addMsgEl = document.getElementById('message');

if (bookForm) {
    bookForm.addEventListener('submit', function (e) {
        e.preventDefault();
        const formData = new FormData(bookForm);

        fetch('../controller/BookApi.php', {
            method: 'POST',
            body: formData
        })
        .then(res => res.json())
        .then(data => {
            addMsgEl.textContent = data.message || data.error || '';
        })
        .catch(err => {
            addMsgEl.textContent = 'Error: ' + err;
        });
    });
}

// --- Update book (BookApi.php PUT) ---
const updateForm  = document.getElementById('updateBookForm');
const updateMsgEl = document.getElementById('updateMessage');

if (updateForm) {
    updateForm.addEventListener('submit', function (e) {
        e.preventDefault();
        const formData = new FormData(updateForm);
        const book     = {};

        formData.forEach((value, key) => {
            book[key] = value;
        });

        fetch('../controller/BookApi.php', {
            method: 'PUT',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify(book)
        })
        .then(res => res.json())
        .then(data => {
            updateMsgEl.textContent = data.message || data.error || '';
        })
        .catch(err => {
            updateMsgEl.textContent = 'Error: ' + err;
        });
    });
}
</script>
</body>
</html>
