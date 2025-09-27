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
currentNumOfBorrows INT NOT NULL,
role VARCHAR(20) NOT NULL
)";

$createBorrowsTable = "CREATE TABLE IF NOT EXISTS borrows (
borrow_id INT AUTO_INCREMENT PRIMARY KEY,
bookId INT NOT NULL,
quantity INT NOT NULL,
price INT NOT NULL,
dueDate DATE NOT NULL,
isReturned ENUM('false','true') DEFAULT 'false' NOT NULL,
user_id INT NOT NULL
)";

if(mysqli_query($conn, $createUsersTable)){

}

mysqli_query($conn, $createBorrowsTable);



$insertDefaultAdmin = "INSERT IGNORE INTO users (username,password,firstName,lastName,currentNumOfBorrows,role) values ('admin', 'admin','Admin','Admin',0, 'Admin')";
$insertDefaultStaff = "INSERT IGNORE INTO users (username,password,firstName,lastName,currentNumOfBorrows,role) values ('staff', 'staff','Staff','Staff' ,0,'Staff')";
$insertDefaultVIPStudent = "INSERT IGNORE INTO users (username,password,firstName,lastName,currentNumOfBorrows,role) values ('vipstudent', 'vipstudent','VIPStudent',0,'VIPStudent', 'VIPStudent')";
$insertDefaultStudent = "INSERT IGNORE INTO users (username,password,firstName,lastName,currentNumOfBorrows,role) values ('student', 'student','Student','Student',0, 'Student')";



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