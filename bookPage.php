<?php
include "navbar.php";
session_start();
if (!isset($_SESSION['username'])) {
    header("Location: index.php");
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Add New Book</title>
    <script defer src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"
        integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz"
        crossorigin="anonymous"></script>
</head>

<body>
    <div class="container mt-5">
        <h1 class="mb-4">Add New Book</h1>

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
                <input type="text" name="category" class="form-control">
            </div>

            <div class="col-md-6">
                <label class="form-label">Publisher:</label>
                <input type="text" name="publisher" class="form-control">
            </div>

            <div class="col-md-6">
                <label class="form-label">Year:</label>
                <input type="number" name="year" class="form-control">
            </div>

            <div class="col-12">
                <button type="submit" class="btn btn-primary">Add Book</button>
            </div>
        </form>

        <p id="message" class="mt-3"></p>

        <hr class="my-5">

        <h1 class="mb-4">Update Book</h1>

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

        <p id="updateMessage" class="mt-3"></p>
    </div>

    <script>
        document.getElementById("updateBookForm").addEventListener("submit", function e() {
            e.preventDefault();
            const formdata = new FormData(this);
            const book = {}

            formdata.forEach((value, key) => {
                book[key] = value;
            });

            fetch("BookApi.php", {
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
            const book = {};
            formData.forEach((value, key) => {
                book[key] = value;
            });

            fetch("BookApi.php", {
                method: "POST",
                headers: {
                    "Content-Type": "application/json"
                },
                body: JSON.stringify(book)
            }).then(res => res.json()).then(
                data => {
                    document.getElementById("message").textContent = data.message || data.error;
                }
            )
        });
    </script>
</body>

</html>