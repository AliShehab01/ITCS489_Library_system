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

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet" />

    <title>Edit User</title>
    <link rel="stylesheet" href="<?= BASE_URL ?>public/css/style.css">
    <?php if (!defined('STYLE_LOADED')) { define('STYLE_LOADED', true); } ?>
</head>
<body>

<?php

include __DIR__ . '/navbar.php';

$stmt = $pdo->prepare("SELECT username, role FROM users WHERE username = :username");
$stmt->execute([':username' => $user_name]);
$row = $stmt->fetch(PDO::FETCH_ASSOC);

?>
<main class="page-shell">
    <div class="section-title">
        <span class="pill">🧑</span>
        <span>Edit user profile</span>
    </div>
    <div class="form-shell">
        <form action="UserProfileUpdatedSubmit.php" method="post" class="row g-3">
            <input type='hidden' name='username' value="<?php echo htmlspecialchars($row['username']);?>">
            <div class="col-12">
                <label class="form-label">Username</label>
                <div class="fw-semibold"><?= htmlspecialchars($_GET['username']) ?></div>
            </div>
            <div class="col-md-6">
                <label for="new_role" class="form-label">Role</label>
                <select name="new_role" id="new_role" class="form-select">
                    
                    <?php
                    $roles = ['Admin','Librarian','Staff','VIP Student','Student'];
                    foreach ($roles as $role) {
                        
                        $selected = ($role == $row['role']) ? 'selected' : '';
                        echo "<option value='$role' $selected>$role</option>";
                    }
                    ?>
                </select>
            </div>
            <div class="col-md-6">
                <label for="new_email" class="form-label">Email</label>
                <input type="email" id="new_email" name="new_email" class="form-control">
            </div>
            <div class="col-md-6">
                <label for="new_first_name" class="form-label">First Name</label>
                <input type="text" id="new_first_name" name="new_first_name" class="form-control">
            </div>
            <div class="col-md-6">
                <label for="new_last_name" class="form-label">Last Name</label>
                <input type="text" id="new_last_name" name="new_last_name" class="form-control">
            </div>
            <div class="col-md-6">
                <label for="new_phone_number" class="form-label">Phone number</label>
                <input type="number" id="new_phone_number" name="new_phone_number" class="form-control">
            </div>
            <div class="col-12 d-flex gap-2">
                <button type="submit" class="btn btn-primary">Update</button>
                <a href="<?= BASE_URL ?>app/view/AdminArea.php" class="btn btn-outline-primary">Back</a>
            </div>
        </form>
    </div>
</main>
<footer class="app-footer text-center">
    <small>© 2025 Library System.</small>
</footer>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
