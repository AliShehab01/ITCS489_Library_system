<?php

session_start();

require_once __DIR__ . '/checkifadmin.php';
require_once __DIR__ . '/../models/dbconnect.php';
require_once __DIR__ . '/../../config.php';

$db = new Database();
$conn = $db->conn;

$message = '';

// Handle role change
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['change_role'])) {
    $userId = (int)$_POST['user_id'];
    $newRole = $_POST['new_role'];
    $validRoles = ['Student', 'VIPStudent', 'Staff', 'Admin'];

    if (in_array($newRole, $validRoles)) {
        $conn->prepare("UPDATE users SET role = :role WHERE id = :id")->execute([':role' => $newRole, ':id' => $userId]);
        $message = '<div class="alert alert-success">Role updated successfully!</div>';
    }
}

// Handle delete user
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_user'])) {
    $userId = (int)$_POST['user_id'];
    // Don't delete self
    if ($userId != $_SESSION['user_id']) {
        $conn->prepare("DELETE FROM users WHERE id = :id")->execute([':id' => $userId]);
        $message = '<div class="alert alert-success">User deleted.</div>';
    }
}

$users = $conn->query("SELECT * FROM users ORDER BY role, username")->fetchAll(PDO::FETCH_ASSOC);

include __DIR__ . '/../view/navbar.php';
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Management</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            padding-top: 80px;
            background: #f8f9fa;
        }
    </style>
</head>

<body>
    <div class="container py-4">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h1>👥 User Management</h1>
            <a href="<?= BASE_URL ?>view/AdminArea.php" class="btn btn-outline-secondary">← Back to Admin</a>
        </div>

        <?= $message ?>

        <div class="card">
            <div class="card-header">All Users (<?= count($users) ?>)</div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>ID</th>
                                <th>Username</th>
                                <th>Name</th>
                                <th>Email</th>
                                <th>Role</th>
                                <th>Borrows</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($users as $u): ?>
                                <tr>
                                    <td><?= $u['id'] ?></td>
                                    <td><strong><?= htmlspecialchars($u['username']) ?></strong></td>
                                    <td><?= htmlspecialchars($u['firstName'] . ' ' . $u['lastName']) ?></td>
                                    <td><?= htmlspecialchars($u['email'] ?? '—') ?></td>
                                    <td>
                                        <form method="post" class="d-flex gap-1">
                                            <input type="hidden" name="change_role" value="1">
                                            <input type="hidden" name="user_id" value="<?= $u['id'] ?>">
                                            <select name="new_role" class="form-select form-select-sm" style="width:auto" onchange="this.form.submit()">
                                                <?php foreach (['Student', 'VIPStudent', 'Staff', 'Admin'] as $role): ?>
                                                    <option value="<?= $role ?>" <?= $u['role'] === $role ? 'selected' : '' ?>><?= $role ?></option>
                                                <?php endforeach; ?>
                                            </select>
                                        </form>
                                    </td>
                                    <td><?= (int)$u['currentNumOfBorrows'] ?></td>
                                    <td>
                                        <a href="<?= BASE_URL ?>view/editUserProfile.php?username=<?= urlencode($u['username']) ?>" class="btn btn-sm btn-outline-primary">Edit</a>
                                        <?php if ($u['id'] != $_SESSION['user_id']): ?>
                                            <form method="post" style="display:inline" onsubmit="return confirm('Delete this user?')">
                                                <input type="hidden" name="delete_user" value="1">
                                                <input type="hidden" name="user_id" value="<?= $u['id'] ?>">
                                                <button class="btn btn-sm btn-outline-danger">Delete</button>
                                            </form>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>