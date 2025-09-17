<?php

    ini_set("display_errors", 0);
   error_reporting(0);
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
 
    if($_SERVER["REQUEST_METHOD"] == "POST"){
        require 'dbconnect.php';
        include 'navbar.php';
        
        $username = trim($_POST["username"]);
        $password = trim($_POST["password"]);

        if(empty ($username) || empty ($password)){
            echo "username or password cannot be empty";
            exit;
        }

        $result = mysqli_query($conn, "SELECT username FROM users");

        $found = false;

        $rows = mysqli_fetch_all($result, MYSQLI_ASSOC);

        for ($i=0; $i < count($rows); $i++) { 
            if($rows[$i]['username'] == $username){
                $found = true;
                break;
            }
        }

$createUsersTable = "CREATE TABLE IF NOT EXISTS users (
id INT AUTO_INCREMENT PRIMARY KEY,
username VARCHAR(50) NOT NULL UNIQUE,
password VARCHAR(255) NOT NULL,
role VARCHAR(20) NOT NULL
)";
$insertDefaultAdmin = "INSERT IGNORE INTO users (username,password,role) values ('admin', 'admin', 'admin')";
$insertDefaultCustomer = "INSERT IGNORE INTO users (username,password,role) values ('customer', 'customer', 'customer')";
$sql = "INSERT IGNORE INTO users (username,password,role) values ('$username', '$password', 'customer')";

if(mysqli_query($conn, $createUsersTable)){

}

if(mysqli_query($conn, $insertDefaultAdmin)){

}

if(mysqli_query($conn, $insertDefaultCustomer)){

}
       
    if($found){
        
        echo "username already exists.";

    }else{
        if(mysqli_query($conn, $sql)){
            $_SESSION['username'] = $username;
            header("Location: index.php");

        }else{
            echo "An error occured";
        }
    }

    }
    ?>

</body>
</html>