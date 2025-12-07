<?php

session_start();

require_once __DIR__ . '/checkifadmin.php';
require_once __DIR__ . '/../models/dbconnect.php';
require_once __DIR__ . '/../models/CreateDefaultDBTables.php';
require_once __DIR__ . '/../../config.php';

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Users</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="<?= BASE_URL ?>public/css/style.css">
</head>

<body>
    <?php include __DIR__ . '/../view/navbar.php'; ?>

    <div class="container mt-5">
        <h1 class="mb-4">User Management</h1>

        <?php
        $db = new Database();
        $conn = $db->getPdo();

        $stmt = $conn->query("SELECT id, username, firstName, lastName, email, role FROM users ORDER BY username");
        $users = $stmt->fetchAll(PDO::FETCH_ASSOC);

        if ($users):
        ?>
            <div class="table-responsive">
                <table class="table table-striped">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Username</th>
                            <th>Name</th>
                            <th>Email</th>
                            <th>Role</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($users as $row): ?>
                            <tr>
                                <td><?= (int)$row['id'] ?></td>
                                <td><?= htmlspecialchars($row['username']) ?></td>
                                <td><?= htmlspecialchars($row['firstName'] . ' ' . $row['lastName']) ?></td>
                                <td><?= htmlspecialchars($row['email'] ?? '') ?></td>
                                <td><span class="badge bg-secondary"><?= htmlspecialchars($row['role']) ?></span></td>
                                <td>
                                    <a href="<?= BASE_URL ?>view/editUserProfile.php?username=<?= urlencode($row['username']) ?>" class="btn btn-sm btn-primary">Edit</a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php else: ?>
            <div class="alert alert-info">No users found.</div>
        <?php endif; ?>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>