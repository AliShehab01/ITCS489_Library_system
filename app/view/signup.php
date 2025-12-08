<?php
session_start();

// Ensure BASE_URL is available before any output
$configPath = __DIR__ . '/../../config.php';
if (file_exists($configPath)) {
    require_once $configPath;
} elseif (!defined('BASE_URL')) {
    define('BASE_URL', '/');
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Create account – Library System</title>

    <!-- Bootstrap + الثيم العام -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="<?= BASE_URL ?>public/css/style.css">
</head>

<body>

<?php include __DIR__ . '/navbar.php'; ?>

<main class="site-content">

    <!-- هيدر الصفحة -->
    <section class="py-4 bg-white border-bottom">
        <div class="container">
            <h1 class="h4 mb-1">Create your account</h1>
            <p class="text-muted mb-0">
                Sign up to borrow books, manage your reservations, and track your reading history.
            </p>
        </div>
    </section>

    <!-- كرت التسجيل -->
    <section class="py-5">
        <div class="container my-3">
            <div class="row justify-content-center">
                <div class="col-12 col-md-8 col-lg-8">

                    <div class="card shadow-custom">
                        <div class="card-body p-4">
                            <h2 class="h5 mb-3 text-center">Register</h2>
                            <p class="text-muted small text-center mb-4">
                                Please fill in the required fields to create your library account.
                            </p>

                            <?php if (!empty($_GET['error'])): ?>
                                <div class="alert alert-danger py-2 small">
                                    <?= htmlspecialchars($_GET['error']); ?>
                                </div>
                            <?php endif; ?>

                            <form action="../controller/RegisterSubmit.php"
                                  method="post"
                                  class="d-flex flex-column gap-3">

                                <div class="row g-3">
    <div class="col-md-6">
        <label for="firstName" class="form-label small mb-1">
            First name <span class="text-danger">*</span>
        </label>
        <input type="text"
               name="firstName"
               id="firstName"
               class="form-control"
               required>
    </div>

    <div class="col-md-6">
        <label for="lastName" class="form-label small mb-1">
            Last name <span class="text-danger">*</span>
        </label>
        <input type="text"
               name="lastName"
               id="lastName"
               class="form-control"
               required>
    </div>
</div>

                                <div>
                                    <label for="username" class="form-label small mb-1">
                                        Username <span class="text-danger">*</span>
                                    </label>
                                    <input type="text"
                                           name="username"
                                           id="username"
                                           class="form-control"
                                           required>
                                </div>

                                <div>
                                    <label for="password" class="form-label small mb-1">
                                        Password <span class="text-danger">*</span>
                                    </label>
                                    <input type="password"
                                           name="password"
                                           id="password"
                                           class="form-control"
                                           required>
                                </div>

                                <div>
                                    <label for="email" class="form-label small mb-1">
                                        Email
                                    </label>
                                    <input type="email"
                                           name="email"
                                           id="email"
                                           class="form-control">
                                </div>

                                <div>
                                    <label for="phoneNumber" class="form-label small mb-1">
                                        Phone number
                                    </label>
                                    <input type="tel"
                                           name="phoneNumber"
                                           id="phoneNumber"
                                           class="form-control">
                                </div>

                                <button type="submit" class="btn btn-primary w-100 mt-2">
                                    Create account
                                </button>
                            </form>

                            <div class="mt-3 text-center small text-muted">
                                Already have an account?
                                <a href="login.php" class="link-primary">Log in</a>
                            </div>

                            <div class="mt-1 text-center small text-muted">
                                If you need help, please contact the library staff.
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
</main>

<footer class="py-3 mt-4">
    <div class="container text-center small text-muted">
        © 2025 Library System. All rights reserved.
    </div>
</footer>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
