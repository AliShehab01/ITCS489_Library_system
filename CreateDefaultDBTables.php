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
        
      

        $createUsersTable = "CREATE TABLE IF NOT EXISTS users (
id INT AUTO_INCREMENT PRIMARY KEY,
username VARCHAR(50) NOT NULL UNIQUE,
password VARCHAR(255) NOT NULL,
firstName VARCHAR(50) NOT NULL,
lastName VARCHAR(50) NOT NULL,
email VARCHAR(100),
phoneNumber VARCHAR(30),
role VARCHAR(20) NOT NULL
)";

if(mysqli_query($conn, $createUsersTable)){

}

$insertDefaultAdmin = "INSERT IGNORE INTO users (username,password,firstName,lastName,role) values ('admin', 'admin','Admin','Admin', 'Admin')";
$insertDefaultStaff = "INSERT IGNORE INTO users (username,password,firstName,lastName,role) values ('staff', 'staff','Staff','Staff' ,'Staff')";
$insertDefaultVIPStudent = "INSERT IGNORE INTO users (username,password,firstName,lastName,role) values ('vipstudent', 'vipstudent','VIPStudent', 'VIPStudent', 'VIPStudent')";
$insertDefaultStudent = "INSERT IGNORE INTO users (username,password,firstName,lastName,role) values ('student', 'student','Student','Student', 'Student')";



if(mysqli_query($conn, $insertDefaultAdmin)){

}

if(mysqli_query($conn, $insertDefaultStaff)){

}

if(mysqli_query($conn, $insertDefaultVIPStudent)){

}

if(mysqli_query($conn, $insertDefaultStudent)){

}
}
?>

</body>
</html>