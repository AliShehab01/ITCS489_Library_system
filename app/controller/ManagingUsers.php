<?php
session_start();

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// فقط الأدمن
require 'checkifadmin.php';

// اتصال قاعدة البيانات (PDO)
require_once __DIR__ . '/../models/dbconnect.php';
require_once __DIR__ . '/../models/CreateDefaultDBTables.php';

$db   = new Database();
$conn = $db->getPdo();

// جلب المستخدمين
$sql  = "SELECT id, username, role FROM users ORDER BY username ASC";
$stmt = $conn->query($sql);
$users = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Users – Admin</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../public/css/style.css">
</head>

<body>

<?php include __DIR__ . '/../view/navbar.php'; ?>

<main class="site-content">
    <!-- هيدر -->
    <section class="py-4 bg-white border-bottom">
        <div class="container">
            <h1 class="h4 mb-1">Manage users</h1>
            <p class="text-muted mb-0">
                View all users, their roles, and edit their profiles and permissions.
            </p>
        </div>
    </section>

    <section class="py-4">
        <div class="container my-2">
            <div class="admin-wrapper mx-auto">

                <div class="card shadow-custom">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h2 class="h6 mb-0">Users &amp; roles</h2>
                        <small class="text-muted">
                            Total: <?= $users ? count($users) : 0; ?>
                        </small>
                    </div>

                    <div class="card-body p-0">
                        <?php if (!$users): ?>
                            <div class="p-3 text-muted small">
                                No users found.
                            </div>
                        <?php else: ?>
                            <div class="table-responsive">
                                <table class="table align-middle mb-0">
                                    <thead>
                                    <tr>
                                        <th style="width: 80px;">ID</th>
                                        <th>Username</th>
                                        <th style="width: 140px;">Role</th>
                                        <th style="width: 120px;">Actions</th>
                                    </tr>
                                    </thead>
                                    <tbody>
                                    <?php foreach ($users as $row): ?>
                                        <tr>
                                            <td><?= htmlspecialchars($row['id']); ?></td>
                                            <td><?= htmlspecialchars($row['username']); ?></td>
                                            <td>
                                                <span class="badge bg-secondary text-capitalize">
                                                    <?= htmlspecialchars($row['role']); ?>
                                                </span>
                                            </td>
                                            <td>
                                                <a href="../view/editUserProfile.php?username=<?= urlencode($row['username']); ?>"
                                                   class="btn btn-sm btn-outline-primary">
                                                    Edit
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
</body>
</html>
