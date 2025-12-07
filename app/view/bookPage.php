<?php

/*
  This file need to divided to controller and view parts which is how mvc do

*/

require_once __DIR__ . '/../../config.php';
require_once __DIR__ . '/../models/dbconnect.php';
$db = new Database();
$conn = $db->conn; // $conn is your PDO object

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['username'])) {
    header('Location: ' . BASE_URL . 'app/view/login.php');
    exit;
}

$stmt = $conn->query("SELECT * FROM books");
$books = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Add New Book</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet"
        integrity="sha384-0pUGZvbkm6XF6gxjEnlmuGrJXVbNuzT9qBBavbLwCsOGabYfZo0T0to5eqruptLy" crossorigin="anonymous">
    <link rel="stylesheet" href="<?= BASE_URL ?>public/css/style.css">
    <?php if (!defined('STYLE_LOADED')) { define('STYLE_LOADED', true); } ?>
    <script defer src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"
        integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous">
    </script>
</head>

<body>
    <?php include __DIR__ . '/navbar.php'; ?>

    <main class="page-shell">
        <section class="page-hero mb-4">
            <div class="row align-items-center g-3">
                <div class="col-lg-8">
                    <p class="text-uppercase text-muted fw-semibold mb-2">Admin</p>
                    <h1 class="display-6 mb-1">Book Management</h1>
                    <p class="text-muted mb-0">Add, update, or adjust inventory with the same layout used throughout the app.</p>
                </div>
                <div class="col-lg-4 text-lg-end">
                    <a href="<?= BASE_URL ?>app/view/AdminArea.php" class="btn btn-outline-primary">Back to admin</a>
                </div>
            </div>
        </section>

        <div class="row g-4">
            <div class="col-lg-6">
                <div class="form-shell h-100">
                    <h2 class="h5 mb-3">Add New Book</h2>

                    <form id="bookForm" method="post" class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label">Title:</label>
                            <input type="text" name="title" class="form-control" required>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label">Author:</label>
                            <input type="text" name="author" class="form-control" required>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label">ISBN:</label>
                            <input type="text" name="isbn" class="form-control" required>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label">Category:</label>
                            <select name="category" id="" class="form-select">
                                <option value="Science">Science</option>
                                <option value="Engineering">Engineering</option>
                                <option value="History">History</option>
                                <option value="Literature">Literature</option>
                                <option value="Business">Business</option>
                            </select>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label">Publisher:</label>
                            <input type="text" name="publisher" class="form-control">
                        </div>

                        <div class="col-md-6">
                            <label class="form-label">Year:</label>
                            <input type="number" name="year" class="form-control">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Available Quantity:</label>
                            <input type="number" name="quantity" class="form-control">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Book Cover:</label>
                            <input type="file" name="image" accept="image/*" class="form-control">
                        </div>

                        <div class="col-12">
                            <button type="submit" class="btn btn-primary">Add Book</button>
                        </div>
                    </form>

                    <p id="message" class="mt-3 mb-0"></p>
                </div>
            </div>

            <div class="col-lg-6">
                <div class="form-shell h-100">
                    <h2 class="h5 mb-3">Update Book</h2>

                    <form id="updateBookForm" method="post" class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label">ISBN (Book to update):</label>
                            <input type="text" name="isbn" class="form-control" required>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label">New Title:</label>
                            <input type="text" name="title" class="form-control" required>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label">New Author:</label>
                            <input type="text" name="author" class="form-control" required>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label">New Category:</label>
                            <input type="text" name="category" class="form-control">
                        </div>

                        <div class="col-md-6">
                            <label class="form-label">New Publisher:</label>
                            <input type="text" name="publisher" class="form-control">
                        </div>

                        <div class="col-md-6">
                            <label class="form-label">New Year:</label>
                            <input type="number" name="year" class="form-control">
                        </div>

                        <div class="col-12">
                            <button type="submit" class="btn btn-success">Update Book</button>
                        </div>
                    </form>

                    <p id="updateMessage" class="mt-3 mb-0"></p>
                </div>
            </div>
        </div>

        <div class="card shadow-custom mt-4">
            <div class="card-body">
                <h2 class="h5 mb-3">Remove Book by ISBN</h2>
                <div class="row g-3 align-items-end">
                    <div class="col-md-6">
                        <form>
                            <label class="form-label">ISBN of book:</label>
                            <input type="number" name="isbntodel" id="isbnToDelete" class="form-control">
                        </form>
                    </div>
                    <div class="col-md-3 d-grid">
                        <button id="deleteBtn" class="btn btn-danger mt-md-2">Delete Book</button>
                    </div>
                </div>
                <p id="delRes" class="mt-3 mb-0"></p>
            </div>
        </div>

        <div class="form-shell mt-4">
            <form method="POST" class="row g-3">
                <h2 class="h5">Change Status of a Book by ISBN</h2>

                <div class="col-md-6">
                    <label for="isbn" class="form-label">ISBN:</label>
                    <input type="text" id="isbn" name="isbn" class="form-control" placeholder="Enter ISBN" required>
                </div>


                <div class="col-md-6">
                    <label for="options" class="form-label">Status:</label>
                    <select id="options" name="status" class="form-select" required>
                        <option value="">--Select an option--</option>
                        <option value="available">Available</option>
                        <option value="unavailable">Unavailable</option>
                    </select>
                </div>

                <!-- Submit Button -->
                <div class="col-12">
                    <button type="submit" id="b1" class="btn btn-primary">Submit</button>
                    <p id="status_res" class="mt-3 mb-0"></p>
                </div>
            </form>
        </div>

        <div class="card shadow-custom mt-4">
            <div class="card-body">
                <div class="section-title">
                    <span class="pill">&#128214;</span>
                    <span>Current catalog</span>
                </div>
                <div class="row">
                    <?php foreach ($books as $book): ?>
                        <div class="col-md-3 mb-4">
                            <div class="card h-100">
                                <img src="<?= $book['image_path'] ?? 'placeholder.jpg' ?>" class="card-img-top"
                                    alt="<?= htmlspecialchars($book['title']) ?>">
                                <div class="card-body">
                                    <h5 class="card-title"><?= htmlspecialchars($book['title']) ?></h5>
                                    <p class="card-text"><?= htmlspecialchars($book['author']) ?></p>
                                    <a href="bookDetails.php?id=<?= $book['id'] ?>" class="btn btn-primary btn-sm">View Details</a>
                                </div>
                                <!--maybe up a path problem-->
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    </main>

    <footer class="app-footer text-center">
        <small>&copy; 2025 Library System. All rights reserved.</small>
    </footer>

    <script>
        const resmsg = document.getElementById('delRes');
        document.getElementById('deleteBtn').addEventListener('click', function() {
            const isbn = document.getElementById('isbnToDelete').value.trim();
            if (!isbn) {
                resmsg.textContent = "Please enter an ISBN.";
                return;
            }


            fetch('../controller/deleterecord.php', {
                method: 'Post',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({
                    isbn: isbn
                })
            }).then(res => res.json()).then(data => {
                resmsg.textContent = data.success || data.error;
            }).catch(err => {
                resmsg.textContent = "Error: " + err;
            });
        });
    </script>



    <script>
        const res = document.getElementById("status_res");


        document.getElementById("b1").addEventListener("click", function(e) {
            e.preventDefault();
            const status = document.getElementById("options").value;
            const isbn = document.getElementById("isbn").value.trim();
            if (!isbn || !status) {
                alert("Please fill in all fields");
            }

            fetch("../controller/changestatus.php", {
                method: "PUT",
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({
                    isbn: isbn,
                    status: status
                })
            }).then(res => res.json()).then(data => {
                res.textContent = data.message || data.error;
            }).catch(err => {
                document.getElementById("status_res").textContent = "Error: " + err;
            });
        });
    </script>

    <script>
        document.getElementById("updateBookForm").addEventListener("submit", function(e) {
            e.preventDefault();
            const formdata = new FormData(this);
            const book = {}

            formdata.forEach((value, key) => {
                book[key] = value;
            });

            fetch("../controller/BookApi.php", {
                method: "PUT",
                headers: {
                    "Content-Type": "application/json"
                },
                body: JSON.stringify(book)
            }).then(res => res.json()).then(data => {
                document.getElementById("updateMessage").textContent = data.message || data.error;
            })
        })
    </script>

    <script>
        document.getElementById("bookForm").addEventListener("submit", function(e) {
            e.preventDefault();
            const formData = new FormData(this);

            fetch("../controller/BookApi.php", {
                    method: "POST",
                    body: formData // no headers needed for FormData
                })
                .then(res => res.json())
                .then(data => {
                    document.getElementById("message").textContent = data.message || data.error;
                });
        });
    </script>
</body>

</html>
