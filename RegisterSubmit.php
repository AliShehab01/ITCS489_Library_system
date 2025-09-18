<?php

    ini_set("display_errors", 1);
   error_reporting(E_ALL);
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
 
require 'CreateDefaultDBTables.php';



  $username = trim($_POST["username"]);
        $password = trim($_POST["password"]);
        $first_name = trim($_POST["first_name"]);
        $last_name = trim($_POST["last_name"]);
        $email = trim($_POST["email"]);
        $phone_number = trim($_POST["phone_number"]);

         if(empty ($username) || empty ($password) || empty($first_name) || empty($last_name)){
            echo "Please fill all the required fields";
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
 
            if($found){
        
        echo "username already exists.";
        exit;

    }else{
        $sql = "INSERT IGNORE INTO users (username,password,email,firstName,lastName,phoneNumber,role) values ('$username', '$password', '$email','$first_name','$last_name','$phone_number', 'Student')";
        if(mysqli_query($conn, $sql)){
             $_SESSION['username'] = $username;
            $_SESSION['first_name'] = $first_name;
header("Location: index.php");
exit;


        }else{
            echo "An error occured";
        }
    }


       



       


       

    
    ?>

</body>
</html>