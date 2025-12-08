<?php
session_start();

require_once __DIR__ . '/../models/dbconnect.php';
require_once __DIR__ . '/../models/CreateDefaultDBTables.php';
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login – Library System</title>

    <!-- Bootstrap + الثيم العام -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="<?= BASE_URL ?>public/css/style.css">
</head>

<body>

<?php include __DIR__ . '/navbar.php'; ?>

<main class="site-content">

    <!-- شريط علوي بسيط -->
    <section class="py-4 bg-white border-bottom">
        <div class="container">
            <h1 class="h4 mb-1">Welcome back</h1>
            <p class="text-muted mb-0">
                Sign in to access your library account, borrowed items, and reservations.
            </p>
        </div>
    </section>

    <!-- كرت تسجيل الدخول -->
    <section class="py-5">
        <div class="container my-3">
            <div class="row justify-content-center">
                <div class="col-12 col-md-8 col-lg-5">
                    <div class="card shadow-custom">
                        <div class="card-body p-4">
                            <h2 class="h5 mb-3 text-center">Log in</h2>
                            <p class="text-muted small text-center mb-4">
                                Use your library username and password to continue.
                            </p>

                            <?php if (!empty($_GET['error'])): ?>
                                <div class="alert alert-danger py-2 small">
                                    <?= htmlspecialchars($_GET['error']); ?>
                                </div>
                            <?php elseif (!empty($_GET['msg'])): ?>
                                <div class="alert alert-info py-2 small">
                                    <?= htmlspecialchars($_GET['msg']); ?>
                                </div>
                            <?php endif; ?>

                   <form action="../controller/LoginSubmit.php" method="post" class="d-flex flex-column gap-3">

    <div>
        <label for="username" class="form-label small mb-1">Username</label>
        <input type="text"
               name="username"
               id="username"
               class="form-control"
               required
               autocomplete="username">
    </div>

    <div>
        <label for="password" class="form-label small mb-1">Password</label>
        <input type="password"
               name="password"
               id="password"
               class="form-control"
               required
               autocomplete="current-password">
    </div>

    <button type="submit" class="btn btn-primary w-100 mt-2">
        Log in
    </button>
</form>

<div class="mt-3 text-center small text-muted">
    Don’t have an account yet?
    <a href="signup.php" class="link-primary">Create one</a>
</div>

<div class="mt-1 text-center small text-muted">
    Having trouble signing in? Contact the library staff.
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
