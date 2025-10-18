<?php



/*
   THIS MUST BE DELETED TO USE ONLY "db489.php"

*/






$dns = "mysql:host=localhost;dbname=library_system";
$user = "root";
$pass = "";


$conn = new PDO($dns, $user, $pass);
$conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

// $query = "CREATE TABLE IF NOT EXISTS books (
//     id INT AUTO_INCREMENT PRIMARY KEY,
//     image_path VARCHAR(255) NOT NULL, 
//     title VARCHAR(255) NOT NULL,
//     author VARCHAR(255) NOT NULL,
//     isbn VARCHAR(50) NOT NULL,
//     category ENUM('Science','Engineering','History','Literature','Business','Other') DEFAULT 'Other',
//     publisher VARCHAR(255),
//     year INT(9),
//     quantity INT NOT NULL DEFAULT 0,
//     availability ENUM('available','reserved','issued','unavailable') DEFAULT 'available',
//     created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
// )";