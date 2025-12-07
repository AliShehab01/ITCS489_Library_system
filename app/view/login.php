<?php
session_start();
require_once __DIR__ . '/../../config.php';
require_once __DIR__ . '/../models/dbconnect.php';
require_once __DIR__ . '/../models/CreateDefaultDBTables.php';
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login</title>
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
                    <p class="text-uppercase text-muted fw-semibold mb-2">Welcome back</p>
                    <h1 class="display-6 mb-1">Sign in</h1>
                    <p class="text-muted mb-0">Access your account to manage borrows, reservations, and alerts.</p>
                </div>
                <div class="text-end">
                    <span class="badge bg-light text-dark border">Secure login</span>
                </div>
            </div>
        </section>
        <div class="row justify-content-center">
            <div class="col-lg-5">
                <div class="form-shell">
                    <h1 class="h3 mb-3 text-center">Login</h1>
                    <p class="text-muted text-center mb-4">Access your account to manage borrows and reservations.</p>
                    <form action="../controller/LoginSubmit.php" method="post" class="d-grid gap-3">
                        <div>
                            <label for="username" class="form-label">Username</label>
                            <input type="text" name="username" id="username" class="form-control" required>
                        </div>

                        <div>
                            <label for="password" class="form-label">Password</label>
                            <input type="password" name="password" id="password" class="form-control" required>
                        </div>

                        <button type="submit" class="btn btn-primary w-100">Log in</button>
                    </form>
                    <p class="mt-3 text-center text-muted">New here? <a href="<?= BASE_URL ?>app/view/signup.php">Create an account</a></p>
                </div>
            </div>
        </div>
    </main>

    <footer class="app-footer text-center">
        <small>&copy; 2025 Library System. All rights reserved.</small>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"
        integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script>
</body>

</html>
