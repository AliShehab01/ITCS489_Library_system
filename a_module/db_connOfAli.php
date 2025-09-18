<?php
$dns = "mysql:host=localhost;dbname=apitry";
$user = "root";
$pass = "";
$conn = new PDO($dns, $user, $pass);

$conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

$query = "CREATE table if not exists books (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(255) NOT NULL,
    author VARCHAR(255) NOT NULL,
    isbn VARCHAR(50) NOT NULL,
    category VARCHAR(100),
    publisher VARCHAR(255),
    year INT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP )";
$stmt = $conn->prepare($query);
$stmt->execute();
