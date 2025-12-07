<?php
ini_set('display_errors', 0);
ini_set('display_startup_errors', 0);
error_reporting(0);

require_once 'dbconnect.php';

$user = "root";
$host = "127.0.0.1"; // use IP to avoid socket issues
$pass = "";
$dbname = "library_system";

// Try creating/connecting without breaking the page if MySQL is down
try {
    $pdo = new PDO("mysql:host={$host}", $user, $pass, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
    ]);
} catch (PDOException $e) {
    // MySQL service not available; skip initialization silently
    return;
}

try {
    $pdo->exec("CREATE DATABASE IF NOT EXISTS `$dbname`");
    $pdo = new PDO("mysql:host={$host};dbname={$dbname}", $user, $pass, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
    ]);
    $pdo->exec("USE `$dbname`");
} catch (PDOException $e) {
    // Could not create/select DB; skip silently
    return;
}

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

// RESERVATIONS
$createReservationsTable = "CREATE TABLE IF NOT EXISTS reservations (
    reservation_id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    book_id INT NOT NULL,
    status ENUM('active','notified','fulfilled','cancelled') DEFAULT 'active',
    reserved_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
)";

// NOTIFICATIONS
$createNotificationsTable = "CREATE TABLE IF NOT EXISTS notifications (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NULL,
    book_id INT NULL,
    type ENUM('due','overdue','reservation','announcement') NOT NULL,
    title VARCHAR(150) NOT NULL,
    message TEXT NOT NULL,
    due_date DATE DEFAULT NULL,
    is_read TINYINT(1) NOT NULL DEFAULT 0,
    context_type VARCHAR(40) DEFAULT NULL,
    context_id INT DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NULL DEFAULT NULL,
    KEY idx_notifications_user (user_id),
    KEY idx_notifications_type (type)
)";

try {
    $conn->exec($createUsersTable);
    $conn->exec($createBorrowsTable);
    $conn->exec($createBooksTable);
    $conn->exec($createReservationsTable);
    $conn->exec($createNotificationsTable);

    // Default users (silent)
    $conn->exec("INSERT IGNORE INTO users (username,password,firstName,lastName,currentNumOfBorrows,role)
        VALUES ('admin', 'admin','Admin','Admin',0,'Admin')");
    $conn->exec("INSERT IGNORE INTO users (username,password,firstName,lastName,currentNumOfBorrows,role)
        VALUES ('staff', 'staff','Staff','Staff',0,'Staff')");
    $conn->exec("INSERT IGNORE INTO users (username,password,firstName,lastName,currentNumOfBorrows,role)
        VALUES ('vipstudent', 'vipstudent','VIPStudent','VIPStudent',0,'VIPStudent')");
    $conn->exec("INSERT IGNORE INTO users (username,password,firstName,lastName,currentNumOfBorrows,role)
        VALUES ('student', 'student','Student','Student',0,'Student')");
} catch (PDOException $e) {
    // swallow errors for page safety
    return;
}
