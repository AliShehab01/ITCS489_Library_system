<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Setup Database</title>
</head>
<body>

<?php
// show any errors so we can see what happens
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// connect to database
require 'dbconnect.php';

// --- Create tables ---
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

// run the queries
mysqli_query($conn, $createUsersTable);
mysqli_query($conn, $createBorrowsTable);

// --- insert default users ---
$insertDefaultAdmin = "INSERT IGNORE INTO users (username,password,firstName,lastName,currentNumOfBorrows,role)
VALUES ('admin', 'admin','Admin','Admin',0, 'Admin')";
$insertDefaultStaff = "INSERT IGNORE INTO users (username,password,firstName,lastName,currentNumOfBorrows,role)
VALUES ('staff', 'staff','Staff','Staff',0, 'Staff')";
$insertDefaultVIPStudent = "INSERT IGNORE INTO users (username,password,firstName,lastName,currentNumOfBorrows,role)
VALUES ('vipstudent', 'vipstudent','VIPStudent','VIPStudent',0, 'VIPStudent')";
$insertDefaultStudent = "INSERT IGNORE INTO users (username,password,firstName,lastName,currentNumOfBorrows,role)
VALUES ('student', 'student','Student','Student',0, 'Student')";

mysqli_query($conn, $insertDefaultAdmin);
mysqli_query($conn, $insertDefaultStaff);
mysqli_query($conn, $insertDefaultVIPStudent);
mysqli_query($conn, $insertDefaultStudent);

echo "<h3>✅ Tables created and default users added successfully!</h3>";
?>

</body>
</html>
