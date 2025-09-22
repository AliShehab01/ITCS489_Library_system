<?php
$dns = "mysql:host=localhost;dbname=489";
$user = "root";
$pass = "";
$conn = new PDO($dns, $user, $pass);

$conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

$query = "CREATE table if not exists books (
    id INT AUTO_INCREMENT PRIMARY KEY,
    image_path VARCHAR(255) NOT NULL, 
    title VARCHAR(255) NOT NULL,
    author VARCHAR(255) NOT NULL,
    isbn VARCHAR(50) NOT NULL,
    category VARCHAR(100),
    publisher VARCHAR(255),
    year INT(9),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP )";
$stmt = $conn->prepare($query);
$stmt->execute();

//book cover
$query = "CREATE table if not exists bookcover ( 
 id INT AUTO_INCREMENT PRIMARY KEY,
    book_id INT NOT NULL,
    image_path VARCHAR(255) NOT NULL,
    FOREIGN KEY (book_id) REFERENCES books(id) ON DELETE CASCADE)";

$stmt = $conn->prepare($query);
$stmt->execute();
