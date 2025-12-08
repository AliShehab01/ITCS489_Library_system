<?php

session_start();
require_once __DIR__ . '/../../config.php';
require_once __DIR__ . '/../controller/checkifadmin.php';
require_once __DIR__ . '/../models/dbconnect.php';

if(!isset($_GET["username"])){
    echo "no username specified.";
    exit;
}

$user_name = $_GET['username'];

$db = new Database();
$pdo = $db->getPdo();

$stmt = $pdo->prepare("SELECT username, role FROM users WHERE username = :username");
$stmt->execute([':username' => $user_name]);
$row = $stmt->fetch(PDO::FETCH_ASSOC);


// app/view/editUserProfile.php
session_start();

require_once __DIR__ . '/../../config.php';
require_once __DIR__ . '/../models/dbconnect.php';

// تحضير الاتصال
$db  = new Database();
$conn = $db->conn;

// نحدد أي مستخدم نعدّل بياناته:
// - لو فيه ?username=... في الرابط (غالباً أدمن يحرر مستخدم)
// - غير كذا نستخدم المستخدم الحالي من السيشن
$username = $_GET['username'] ?? ($_SESSION['username'] ?? null);

if (!$username) {
    header("Location: login.php");
    exit;
}

// نجيب بيانات المستخدم
$stmt = $conn->prepare("
    SELECT username, role, email, first_name, last_name, phone_number
    FROM users
    WHERE username = :username
");
$stmt->execute([':username' => $username]);
$row = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$row) {
    $error = "User not found.";
}

// الأدوار المتاحة
$roles = ['Admin','Librarian','Staff','VIP Student','Student'];
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Account</title>

    <!-- Bootstrap + الثيم العام -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../../public/css/style.css">
</head>

<body>

<?php include __DIR__ . '/navbar.php'; ?>

<main class="site-content">

    <!-- شريط علوي بسيط -->
    <section class="py-4 bg-white border-bottom">
        <div class="container">
            <h1 class="h4 mb-1">My Account</h1>
            <p class="text-muted mb-0">
                View and update your profile information and contact details.
            </p>
            <?php if (isset($_SESSION['role']) && $_SESSION['role'] === 'admin' && isset($_GET['username'])): ?>
                <p class="small text-muted mt-1">
                    Editing profile for user:
                    <strong><?= htmlspecialchars($row['username'] ?? $username); ?></strong>
                </p>
            <?php endif; ?>
        </div>
    </section>

    <section class="py-4">
        <div class="container" style="max-width: 720px;">

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

                        <form action="../controller/UserProfileUpdatedSubmit.php" method="post" class="row g-3">
                            <!-- نرسل الـ username القديم كـ hidden -->
                            <input type="hidden" name="username"
                                   value="<?= htmlspecialchars($row['username']); ?>">

                            <!-- Role (للأدمن فقط) -->
                            <?php if (isset($_SESSION['role']) && $_SESSION['role'] === 'admin'): ?>
                                <div class="col-12">
                                    <label class="form-label">Role</label>
                                    <select name="new_role" class="form-select">
                                        <?php foreach ($roles as $role): ?>
                                            <?php $selected = ($role == ($row['role'] ?? '')) ? 'selected' : ''; ?>
                                            <option value="<?= htmlspecialchars($role); ?>" <?= $selected; ?>>
                                                <?= htmlspecialchars($role); ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                            <?php else: ?>
                                <!-- لو مو أدمن نخلي الدور ثابت بس نعرضه -->
                                <div class="col-12">
                                    <label class="form-label">Role</label><br>
                                    <span class="badge bg-secondary">
                                        <?= htmlspecialchars($row['role'] ?? 'Student'); ?>
                                    </span>
                                </div>
                            <?php endif; ?>

                            <!-- First / Last name -->
                            <div class="col-md-6">
                                <label for="first_name" class="form-label">
                                    First name <span class="text-danger">*</span>
                                </label>
                                <input type="text"
                                       class="form-control"
                                       id="first_name"
                                       name="new_first_name"
                                       required
                                       value="<?= htmlspecialchars($row['first_name'] ?? ''); ?>">
                            </div>

                            <div class="col-md-6">
                                <label for="last_name" class="form-label">
                                    Last name <span class="text-danger">*</span>
                                </label>
                                <input type="text"
                                       class="form-control"
                                       id="last_name"
                                       name="new_last_name"
                                       required
                                       value="<?= htmlspecialchars($row['last_name'] ?? ''); ?>">
                            </div>

                            <!-- Email -->
                            <div class="col-12">
                                <label for="email" class="form-label">
                                    Email <span class="text-danger">*</span>
                                </label>
                                <input type="email"
                                       class="form-control"
                                       id="email"
                                       name="new_email"
                                       required
                                       value="<?= htmlspecialchars($row['email'] ?? ''); ?>">
                            </div>

                            <!-- Phone -->
                            <div class="col-12">
                                <label for="phone_number" class="form-label">
                                    Phone number
                                </label>
                                <input type="tel"
                                       class="form-control"
                                       id="phone_number"
                                       name="new_phone_number"
                                       value="<?= htmlspecialchars($row['phone_number'] ?? ''); ?>">
                            </div>

                            <div class="col-12 d-flex justify-content-between align-items-center mt-3">
                                <a href="HomePage-EN.php" class="btn btn-outline-secondary btn-sm">
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
        © 2025 Library System. All rights reserved.
    </div>
</footer>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
