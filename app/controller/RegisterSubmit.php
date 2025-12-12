<?php
ini_set("display_errors", 1);
error_reporting(E_ALL);
session_start();

require_once __DIR__ . '/../../config.php';
require '../models/CreateDefaultDBTables.php';

$username = trim($_POST["username"]);
$password = trim($_POST["password"]);
$first_name = trim($_POST["first_name"]);
$last_name = trim($_POST["last_name"]);
$email = trim($_POST["email"]);
$phone_number = trim($_POST["phone_number"]);

if (empty($username) || empty($password) || empty($first_name) || empty($last_name)) {
    echo "Please fill all the required fields";
    exit;
}

// Use PDO connection
$db = new Database();
$conn = $db->conn;

// Check if username exists
$stmt = $conn->prepare("SELECT id FROM users WHERE username = :username");
$stmt->execute([':username' => $username]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

if ($user) {
    echo "<h1>Username already exist, Please use another one.</h1>";
    exit;
} else {
    // Insert new user (plain password for now, later use password_hash)
    $sql = "INSERT INTO users (username, password, email, firstName, lastName, phoneNumber, currentNumOfBorrows, role)
            VALUES (:username, :password, :email, :firstName, :lastName, :phoneNumber, 3, 'Student')";
    $stmt = $conn->prepare($sql);
    $stmt->execute([
        ':username' => $username,
        ':password' => $password,
        ':email' => $email,
        ':firstName' => $first_name,
        ':lastName' => $last_name,
        ':phoneNumber' => $phone_number
    ]);

    $_SESSION['username'] = $username;
    $_SESSION['first_name'] = $first_name;
    $_SESSION["user_id"] = $conn->lastInsertId(); // get the new user's ID
    $_SESSION["BorrowLimit"] = 3;

    header("Location: " . PUBLIC_URL . "index.php");
    exit;
}
