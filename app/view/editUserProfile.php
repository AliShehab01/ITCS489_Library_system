<?php

session_start();
require_once __DIR__ . '/../../config.php';
require '../controller/checkifadmin.php';
require_once '../models/dbconnect.php';


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

    <title>Document</title>
    <link rel="stylesheet" href="../../public/css/style.css">
</head>
<body>

<?php

include "../view/navbar.php";

$stmt = $pdo->prepare("SELECT username, role FROM users WHERE username = :username");
$stmt->execute([':username' => $user_name]);
$row = $stmt->fetch(PDO::FETCH_ASSOC);

?>
<div class="container mt-5">
<form action="UserProfileUpdatedSubmit.php" method="post">
     <input type='hidden' name='username' value="<?php echo htmlspecialchars($row['username']);?>">
    <h4>username: <?php echo $_GET['username'] ?></h4>
    <label for="">Role</label><br>
    <select name="new_role">
        
        <?php
        $roles = ['Admin','Librarian','Staff','VIP Student','Student'];
        foreach ($roles as $role) {
            
            $selected = ($role == $row['role']) ? 'selected' : '';
            echo "<option value='$role' $selected>$role</option>";
        }
        ?>
    </select>
    <br>
    <label for="">Email</label><br>
    <input type="email" name="new_email"><br>
        <label for="">First Name</label><br>
        <input type="text" name="new_first_name"><br>
        <label for="">Last Name</label><br>
        <input type="text" name="new_last_name"><br>
        <label for="">phone number</label> <br>
        <input type="number" name="new_phone_number"><br>
    <br>
            <button type="submit" class="btn btn-primary btn-sm">Update</button>

    
</form>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>