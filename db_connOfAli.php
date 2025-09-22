<?php
$dns = "mysql:host=localhost;dbname=489";
$user = "root";
$pass = "";
$conn = new PDO($dns, $user, $pass);

$conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

$query = "CREATE TABLE IF NOT EXISTS books (
    id INT AUTO_INCREMENT PRIMARY KEY,
    image_path VARCHAR(255) NOT NULL, 
    title VARCHAR(255) NOT NULL,
    author VARCHAR(255) NOT NULL,
    isbn VARCHAR(50) NOT NULL,
    category ENUM('Science','Engineering','History','Literature','Business','Other') DEFAULT 'Other',
    publisher VARCHAR(255),
    year INT(9),
    availability ENUM('available','reserved','issued') DEFAULT 'available',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
)";
$stmt = $conn->prepare($query);
$stmt->execute();

