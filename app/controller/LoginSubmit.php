<?php
session_start();
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login Submit</title>
</head>

<body>

    <?php
require '../models/dbconnect.php'; // your PDO Database class
include '../view/navbar.php';
require '../models/CreateDefaultDBTables.php';

$username = $_POST['username'] ?? '';
$password = $_POST['password'] ?? '';
$first_name = null;

$found = false;

try {
    $db = new Database();
    $pdo = $db->conn;

    // Fetch all users
    $stmt = $pdo->query("SELECT * FROM users");
    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

    foreach ($rows as $row) {
        if ($row['username'] === $username && $row['password'] === $password) {
            $found = true;
            $first_name = $row['firstName'];
            $_SESSION['first_name'] = $first_name;
            $_SESSION['user_id'] = $row['id'];

            // Set borrow limit
            if ($row['role'] !== 'Student') {
                $_SESSION['BorrowLimit'] = 5;
            } else {
                $_SESSION['BorrowLimit'] = 3;
            }

            // Set role
            if ($row['role'] === 'Admin') {
                $_SESSION['role'] = 'admin';
            } else {
                $_SESSION['role'] = 'Student';
            }

            break;
        }
    }

    if ($found) {
        $_SESSION['username'] = $username;
        echo "Login successful";

        // Redirect based on role
        if ($_SESSION['role'] === 'admin') {
            header("Location: ../view/AdminArea.php");
            exit;
        } else {
            header("Location: ../view/HomePage-EN.php");
            exit;
        }
    } else {
        echo "<div class='alert alert-danger'>Invalid username or password</div>";
    }

} catch (PDOException $e) {
    echo "<div class='alert alert-danger'>Database error: " . htmlspecialchars($e->getMessage()) . "</div>";
}
?>

</body>

</html>