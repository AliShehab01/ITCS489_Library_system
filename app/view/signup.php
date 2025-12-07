<?php
session_start();
require_once __DIR__ . '/../../config.php';
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="<?= BASE_URL ?>public/css/style.css">
    <?php if (!defined('STYLE_LOADED')) { define('STYLE_LOADED', true); } ?>
</head>

<body>
    <?php include __DIR__ . '/navbar.php'; ?>

    <main class="page-shell">
        <div class="row justify-content-center">
            <div class="col-lg-6 col-xl-5">
                <div class="form-shell">
                    <h1 class="h3 mb-3 text-center">Create your account</h1>
                    <p class="text-muted text-center mb-4">Join the library to reserve, borrow, and get updates.</p>
                    <form action="../controller/RegisterSubmit.php" method="post" class="row g-3">
                        <div class="col-12">
                            <label for="username" class="form-label">Username <span class="text-danger">*</span></label>
                            <input type="text" name="username" id="username" class="form-control" required>
                        </div>

                        <div class="col-12">
                            <label for="password" class="form-label">Password <span class="text-danger">*</span></label>
                            <input type="password" name="password" id="password" class="form-control" required>
                        </div>

                        <div class="col-md-6">
                            <label for="first_name" class="form-label">First Name <span class="text-danger">*</span></label>
                            <input type="text" name="first_name" id="first_name" class="form-control" required>
                        </div>

                        <div class="col-md-6">
                            <label for="last_name" class="form-label">Last Name <span class="text-danger">*</span></label>
                            <input type="text" name="last_name" id="last_name" class="form-control" required>
                        </div>

                        <div class="col-md-6">
                            <label for="email" class="form-label">Email</label>
                            <input type="email" name="email" id="email" class="form-control">
                        </div>

                        <div class="col-md-6">
                            <label for="phone_number" class="form-label">Phone Number</label>
                            <input type="number" name="phone_number" id="phone_number" class="form-control">
                        </div>

                        <div class="col-12 d-grid">
                            <button type="submit" class="btn btn-primary w-100">Register</button>
                        </div>
                    </form>
                    <p class="mt-3 text-center text-muted">Already have an account? <a href="<?= BASE_URL ?>app/view/login.php">Log in</a></p>
                </div>
            </div>
        </div>
    </main>

    <footer class="app-footer text-center">
        <small>© 2025 Library System.</small>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>
