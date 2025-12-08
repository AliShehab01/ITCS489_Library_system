<?php
ini_set("display_errors", 1);
error_reporting(E_ALL);
session_start();

require_once __DIR__ . '/../models/CreateDefaultDBTables.php';

$username = trim($_POST["username"]);
$password = trim($_POST["password"]);
$firstName = trim($_POST["firstName"]);
$lastName = trim($_POST["lastName"]);
$email = trim($_POST["email"]);
$phoneNumber = trim($_POST["phoneNumber"] ?? '');

if (empty($username) || empty($password) || empty($firstName) || empty($lastName)) {
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
        ':firstName' => $firstName,
        ':lastName' => $lastName,
        ':phoneNumber' => $phoneNumber
    ]);

    $_SESSION['username'] = $username;
    $_SESSION['firstName'] = $firstName;
    $_SESSION['lastName'] = $lastName;
    $_SESSION["user_id"] = $conn->lastInsertId(); // get the new user's ID
    $_SESSION["BorrowLimit"] = 3;

    header("Location: ../../public/index.php");
    exit;
}
