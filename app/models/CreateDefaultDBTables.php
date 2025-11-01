<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once 'dbconnect.php';

// connect using your PDO class
$db = new Database();
$conn = $db->conn;

// --- 1. Create tables ---

// USERS Table Definition
$createUsersTable = "CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    firstName VARCHAR(50) NOT NULL,
    lastName VARCHAR(50) NOT NULL,
    email VARCHAR(100),
    phoneNumber VARCHAR(30),
    currentNumOfBorrows INT NOT NULL DEFAULT 0,
    role VARCHAR(20) NOT NULL
)";

// BORROWS Table Definition
$createBorrowsTable = "CREATE TABLE IF NOT EXISTS borrows (
    borrow_id INT AUTO_INCREMENT PRIMARY KEY,
    bookId INT NOT NULL,
    quantity INT NOT NULL,
    price INT NOT NULL,
    dueDate DATE NOT NULL,
    isReturned ENUM('false','true') DEFAULT 'false' NOT NULL,
    user_id INT NOT NULL
)";

// BOOKS Table Definition (Matches your schema image and application needs)
$createBooksTable = "CREATE TABLE IF NOT EXISTS books (
    id INT(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
    image_path VARCHAR(255) DEFAULT 'placeholder.jpg',
    title VARCHAR(255) NOT NULL,
    author VARCHAR(255) NOT NULL,
    isbn VARCHAR(50) NOT NULL UNIQUE,
    category ENUM('Science', 'Engineering', 'History', 'Literature', 'Business'),
    publisher VARCHAR(255),
    year INT(9),
    quantity INT(11) DEFAULT 0, -- Crucial for tracking available copies
    status ENUM('available', 'reserved', 'unavailable') DEFAULT 'available',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
)";


try {
    // Execute all table creation queries
    $conn->exec($createUsersTable);
    $conn->exec($createBorrowsTable);
    $conn->exec($createBooksTable); // ⭐️ Added Books table here

    echo "<h3>✅ All tables created successfully!</h3>";

    // --- 2. Insert default users ---
    // Note: In a real application, you must hash the passwords (e.g., using password_hash()).
    $insertDefaultAdmin = "INSERT IGNORE INTO users (username,password,firstName,lastName,currentNumOfBorrows,role)
        VALUES ('admin', 'admin','Admin','Admin',0,'Admin')";
    $insertDefaultStaff = "INSERT IGNORE INTO users (username,password,firstName,lastName,currentNumOfBorrows,role)
        VALUES ('staff', 'staff','Staff','Staff',0,'Staff')";
    $insertDefaultVIPStudent = "INSERT IGNORE INTO users (username,password,firstName,lastName,currentNumOfBorrows,role)
        VALUES ('vipstudent', 'vipstudent','VIPStudent','VIPStudent',0,'VIPStudent')";
    $insertDefaultStudent = "INSERT IGNORE INTO users (username,password,firstName,lastName,currentNumOfBorrows,role)
        VALUES ('student', 'student','Student','Student',0,'Student')";

    $conn->exec($insertDefaultAdmin);
    $conn->exec($insertDefaultStaff);
    $conn->exec($insertDefaultVIPStudent);
    $conn->exec($insertDefaultStudent);

    echo "<h3>✅ Default users created/verified.</h3>";
} catch (PDOException $e) {
    echo "<h3>❌ Error creating tables or inserting data:</h3> " . $e->getMessage();
}
