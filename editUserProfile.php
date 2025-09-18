<?php

session_start();

require 'checkifadmin.php';
require 'dbconnect.php';

if(!isset($_GET["username"])){
    echo "no username specified.";
    exit;
}

$user_name = $_GET['username'];

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
</head>
<body>

<?php

include 'navbar.php';

$stmt = $conn->prepare("SELECT username, role FROM users WHERE username=?");
$stmt->bind_param('s', $user_name);
$stmt->execute();
$result = $stmt->get_result();
$row = $result->fetch_assoc();


?>

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
    <button type="submit">Update</button>
    
</form>

</body>
</html>