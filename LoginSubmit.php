<?php
session_start();
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
    require 'dbconnect.php';
    include 'navbar.php';
    require 'CreateDefaultDBTables.php';
    
    $username = $_POST['username'];
    $password = $_POST['password'];
    $first_name = null;
    

    $result = mysqli_query($conn, "SELECT * FROM users");

    $found = false;

    $rows = mysqli_fetch_all($result, MYSQLI_ASSOC);

    for ($i=0; $i < count($rows); $i++) { 
        if($rows[$i]['username'] == $username && $rows[$i]['password'] == $password){
            $found = true;
            $first_name = $rows[$i]['firstName'];
            $_SESSION["first_name"] = $first_name;

            if($rows[$i]['role'] == 'Admin'){
                $_SESSION['role'] = 'admin';
            }

            break;
        }
    }

    if($found){
        $_SESSION["username"] = $username;
        
        
        echo "Login successful";

        if($_SESSION['username'] == 'admin'){
            $_SESSION['role'] = 'admin';
        }

      header("Location: index.php");
    }else{
        echo "Invalid username or password";
    }

    mysqli_close($conn);
    ?>

</body>
</html>