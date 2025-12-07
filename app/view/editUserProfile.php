<?php

session_start();
require_once __DIR__ . '/../../config.php';
require_once __DIR__ . '/../models/dbconnect.php';

if (!isset($_SESSION['username'])) {
    header("Location: " . BASE_URL . "view/login.php");
    exit;
}

$db = new Database();
$conn = $db->conn;

$usernameToEdit = $_GET['username'] ?? $_SESSION['username'];
$isAdmin = in_array(strtolower($_SESSION['role'] ?? ''), ['admin', 'staff']);

if (!$isAdmin && $usernameToEdit !== $_SESSION['username']) {
    header("Location: " . BASE_URL . "view/HomePage-EN.php");
    exit;
}

$stmt = $conn->prepare("SELECT * FROM users WHERE username = :username");
$stmt->execute([':username' => $usernameToEdit]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$user) {
    echo "User not found.";
    exit;
}

$message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $firstName = trim($_POST['firstName'] ?? '');
    $lastName = trim($_POST['lastName'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $phone = trim($_POST['phoneNumber'] ?? '');
    $role = $isAdmin ? ($_POST['role'] ?? $user['role']) : $user['role'];

    try {
        $conn->prepare("UPDATE users SET firstName=:fn, lastName=:ln, email=:email, phoneNumber=:phone, role=:role WHERE id=:id")
            ->execute([':fn' => $firstName, ':ln' => $lastName, ':email' => $email, ':phone' => $phone, ':role' => $role, ':id' => $user['id']]);
        $message = '<div class="alert alert-success">Profile updated!</div>';
        $stmt->execute([':username' => $usernameToEdit]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($usernameToEdit === $_SESSION['username']) $_SESSION['first_name'] = $firstName;
    } catch (PDOException $e) {
        $message = '<div class="alert alert-danger">Error updating profile.</div>';
    }
}

include __DIR__ . '/navbar.php';
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Profile</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            padding-top: 80px;
            background: #f8f9fa;
        }
    </style>
</head>

<body>
    <div class="container py-4" style="max-width: 600px;">
        <h1 class="mb-4">Edit Profile</h1>
        <?= $message ?>
        <div class="card">
            <div class="card-body">
                <form method="post">
                    <div class="mb-3">
                        <label class="form-label">Username</label>
                        <input type="text" class="form-control" value="<?= htmlspecialchars($user['username']) ?>" disabled>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">First Name</label>
                            <input type="text" name="firstName" class="form-control" value="<?= htmlspecialchars($user['firstName']) ?>" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Last Name</label>
                            <input type="text" name="lastName" class="form-control" value="<?= htmlspecialchars($user['lastName']) ?>" required>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Email</label>
                        <input type="email" name="email" class="form-control" value="<?= htmlspecialchars($user['email'] ?? '') ?>">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Phone</label>
                        <input type="tel" name="phoneNumber" class="form-control" value="<?= htmlspecialchars($user['phoneNumber'] ?? '') ?>">
                    </div>
                    <?php if ($isAdmin): ?>
                        <div class="mb-3">
                            <label class="form-label">Role</label>
                            <select name="role" class="form-select">
                                <?php foreach (['Student', 'VIPStudent', 'Staff', 'Admin'] as $r): ?>
                                    <option value="<?= $r ?>" <?= $user['role'] === $r ? 'selected' : '' ?>><?= $r ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    <?php endif; ?>
                    <div class="mb-3">
                        <label class="form-label">Current Borrows</label>
                        <input type="text" class="form-control" value="<?= (int)$user['currentNumOfBorrows'] ?>" disabled>
                    </div>
                    <button type="submit" class="btn btn-primary w-100">Save Changes</button>
                </form>
            </div>
        </div>
        <div class="mt-3">
            <a href="<?= BASE_URL ?>view/HomePage-EN.php" class="btn btn-outline-secondary">← Back</a>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>