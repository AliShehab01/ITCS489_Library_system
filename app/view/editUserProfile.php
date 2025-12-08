<?php
session_start();
require_once __DIR__ . '/../../config.php';
require_once __DIR__ . '/../models/dbconnect.php';

// Require login; allow users to edit their own profile (admins can edit any)
if (!isset($_SESSION['user_id'])) {
    $loginUrl = (defined('BASE_URL') ? BASE_URL : '/') . 'app/view/login.php';
    header('Location: ' . $loginUrl);
    exit;
}

$requestedUsername = $_GET['username'] ?? null;
$activeUsername    = $_SESSION['username'] ?? null;
$username          = $requestedUsername ?: $activeUsername;

if (!$username) {
    $loginUrl = (defined('BASE_URL') ? BASE_URL : '/') . 'app/view/login.php';
    header('Location: ' . $loginUrl);
    exit;
}

// Only admins may edit someone else's profile
if ($requestedUsername && (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') && $requestedUsername !== $activeUsername) {
    header('HTTP/1.1 403 Forbidden');
    echo "You are not authorized to edit this profile.";
    exit;
}

$db   = new Database();
$conn = $db->conn;

$stmt = $conn->prepare("
    SELECT
        username,
        role,
        email,
        firstName   AS first_name,
        lastName    AS last_name,
        phoneNumber AS phone_number
    FROM users
    WHERE username = :username
");
$stmt->execute([':username' => $username]);
$row = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$row) {
    $error = "User not found.";
}

// Only admins can pick roles in the form
$roles = ['Admin', 'Librarian', 'Staff', 'VIP Student', 'Student'];
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Account</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="<?= BASE_URL ?>public/css/style.css">
</head>

<body>

<?php include __DIR__ . '/navbar.php'; ?>

<main class="site-content">

    <section class="py-4 bg-white border-bottom">
        <div class="container">
            <h1 class="h4 mb-1">My Account</h1>
            <p class="text-muted mb-0">
                View and update your profile information and contact details.
            </p>
            <?php if (isset($_SESSION['role']) && $_SESSION['role'] === 'admin' && $requestedUsername): ?>
                <p class="small text-muted mt-1">
                    Editing profile for user:
                    <strong><?= htmlspecialchars($row['username'] ?? $username); ?></strong>
                </p>
            <?php endif; ?>
        </div>
    </section>

    <section class="py-4">
        <div class="container" >

            <?php if (!empty($error)): ?>
                <div class="alert alert-danger">
                    <?= htmlspecialchars($error); ?>
                </div>
            <?php else: ?>

                <div class="card shadow-custom">
                    <div class="card-body p-4">
                        <h2 class="h5 mb-3">Profile details</h2>
                        <p class="text-muted small mb-4">
                            Fields marked with <span class="text-danger">*</span> are required.
                        </p>

                   <form action="../controller/UserProfileUpdatedSubmit.php"
      method="post"
      class="d-flex flex-column gap-3">

    <input type="hidden" name="username"
           value="<?= htmlspecialchars($row['username']); ?>">

    <?php if (isset($_SESSION['role']) && $_SESSION['role'] === 'admin'): ?>
        <div>
            <label class="form-label small mb-2">Role</label>
            <select name="new_role" class="form-select form-select-sm">
                <?php foreach ($roles as $role): ?>
                    <?php $selected = ($role == ($row['role'] ?? '')) ? 'selected' : ''; ?>
                    <option value="<?= htmlspecialchars($role); ?>" <?= $selected; ?>>
                        <?= htmlspecialchars($role); ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
    <?php else: ?>
        <div>
            <label class="form-label small mb-3">Role </label> <span class="badge bg-secondary">
                <?= htmlspecialchars($row['role'] ?? 'Student'); ?>
            </span>
        </div>
    <?php endif; ?>

    <div class="row g-3">
        <div class="col-md-6">
            <label for="first_name" class="form-label small mb-3 text-nowrap">
    First name <span class="text-danger">* </span> 
</label>

            <input type="text"
                   class="form-control form-control-sm"
                   id="first_name"
                   name="new_first_name"
                   required
                   value="<?= htmlspecialchars($row['first_name'] ?? ''); ?>">
        </div>

        <div class="col-md-6">
           <label for="last_name" class="form-label small mb-3 text-nowrap">
    Last name <span class="text-danger">* </span> 
</label>


            <input type="text"
                   class="form-control form-control-sm"
                   id="last_name"
                   name="new_last_name"
                   required
                   value="<?= htmlspecialchars($row['last_name'] ?? ''); ?>">
        </div>
    </div>

    <div>
        <label for="email" class="form-label small mb-1">
            Email <span class="text-danger">*</span>
        </label>
        <input type="email"
               class="form-control form-control-sm"
               id="email"
               name="new_email"
               required
               value="<?= htmlspecialchars($row['email'] ?? ''); ?>">
    </div>

    <div>
        <label for="phone_number" class="form-label small mb-1">
            Phone number
        </label>
        <input type="tel"
               class="form-control form-control-sm"
               id="phone_number"
               name="new_phone_number"
               value="<?= htmlspecialchars($row['phone_number'] ?? ''); ?>">
    </div>

    <div class="d-flex justify-content-between align-items-center mt-2">
        <a href="<?= BASE_URL ?>app/view/HomePage-EN.php" class="btn btn-outline-secondary btn-sm">
            Cancel
        </a>
        <button type="submit" class="btn btn-primary">
            Save changes
        </button>
    </div>
</form>

                    </div>
                </div>

            <?php endif; ?>
        </div>
    </section>
</main>

<footer class="py-3 mt-4">
    <div class="container text-center small text-muted">
        &copy; 2025 Library System. All rights reserved.
    </div>
</footer>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
