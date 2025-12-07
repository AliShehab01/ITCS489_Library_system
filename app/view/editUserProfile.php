<?php

session_start();
require_once __DIR__ . '/../../config.php';
require_once __DIR__ . '/../models/dbconnect.php';

include __DIR__ . '/navbar.php';

if (!isset($_SESSION['username'])) {
    header("Location: " . BASE_URL . "view/login.php");
    exit;
}

$db = new Database();
$conn = $db->conn;

// Get username to edit (from URL or session)
$usernameToEdit = $_GET['username'] ?? $_SESSION['username'];

// Check if current user is admin or editing own profile
$isAdmin = isset($_SESSION['role']) && strtolower($_SESSION['role']) === 'admin';
if (!$isAdmin && $usernameToEdit !== $_SESSION['username']) {
    header("Location: " . BASE_URL . "view/HomePage-EN.php");
    exit;
}

// Fetch user data
$stmt = $conn->prepare("SELECT * FROM users WHERE username = :username");
$stmt->execute([':username' => $usernameToEdit]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$user) {
    echo "<div class='container mt-5'><div class='alert alert-danger'>User not found.</div></div>";
    exit;
}

$message = '';

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $firstName = trim($_POST['firstName'] ?? '');
    $lastName = trim($_POST['lastName'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $phoneNumber = trim($_POST['phoneNumber'] ?? '');
    $role = $_POST['role'] ?? $user['role'];

    // Only admin can change roles
    if (!$isAdmin) {
        $role = $user['role'];
    }

    try {
        $updateStmt = $conn->prepare("UPDATE users SET firstName = :firstName, lastName = :lastName, email = :email, phoneNumber = :phoneNumber, role = :role WHERE username = :username");
        $updateStmt->execute([
            ':firstName' => $firstName,
            ':lastName' => $lastName,
            ':email' => $email,
            ':phoneNumber' => $phoneNumber,
            ':role' => $role,
            ':username' => $usernameToEdit
        ]);

        $message = '<div class="alert alert-success">Profile updated successfully.</div>';

        // Refresh user data
        $stmt->execute([':username' => $usernameToEdit]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        // Update session if editing own profile
        if ($usernameToEdit === $_SESSION['username']) {
            $_SESSION['first_name'] = $firstName;
        }
    } catch (PDOException $e) {
        $message = '<div class="alert alert-danger">Failed to update profile.</div>';
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Profile</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="<?= BASE_URL ?>public/css/style.css">
</head>

<body>
    <div class="container" style="max-width: 600px; margin-top: 120px;">
        <h2 class="mb-4 text-center">Edit Profile</h2>

        <?= $message ?>

        <form method="post" class="p-4 border rounded shadow-sm bg-light">
            <div class="mb-3">
                <label class="form-label">Username</label>
                <input type="text" class="form-control" value="<?= htmlspecialchars($user['username']) ?>" disabled>
            </div>

            <div class="row">
                <div class="col-md-6 mb-3">
                    <label for="firstName" class="form-label">First Name</label>
                    <input type="text" name="firstName" id="firstName" class="form-control" value="<?= htmlspecialchars($user['firstName']) ?>" required>
                </div>
                <div class="col-md-6 mb-3">
                    <label for="lastName" class="form-label">Last Name</label>
                    <input type="text" name="lastName" id="lastName" class="form-control" value="<?= htmlspecialchars($user['lastName']) ?>" required>
                </div>
            </div>

            <div class="mb-3">
                <label for="email" class="form-label">Email</label>
                <input type="email" name="email" id="email" class="form-control" value="<?= htmlspecialchars($user['email'] ?? '') ?>">
            </div>

            <div class="mb-3">
                <label for="phoneNumber" class="form-label">Phone Number</label>
                <input type="tel" name="phoneNumber" id="phoneNumber" class="form-control" value="<?= htmlspecialchars($user['phoneNumber'] ?? '') ?>">
            </div>

            <?php if ($isAdmin): ?>
                <div class="mb-3">
                    <label for="role" class="form-label">Role</label>
                    <select name="role" id="role" class="form-select">
                        <option value="Student" <?= $user['role'] === 'Student' ? 'selected' : '' ?>>Student</option>
                        <option value="VIPStudent" <?= $user['role'] === 'VIPStudent' ? 'selected' : '' ?>>VIP Student</option>
                        <option value="Staff" <?= $user['role'] === 'Staff' ? 'selected' : '' ?>>Staff</option>
                        <option value="Admin" <?= $user['role'] === 'Admin' ? 'selected' : '' ?>>Admin</option>
                    </select>
                </div>
            <?php endif; ?>

            <div class="mb-3">
                <label class="form-label">Current Borrows</label>
                <input type="text" class="form-control" value="<?= (int)$user['currentNumOfBorrows'] ?>" disabled>
            </div>

            <button type="submit" class="btn btn-primary w-100">Update Profile</button>
        </form>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>