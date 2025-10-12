<?php
$servername = "localhost";   // XAMPP local server
$username   = "root";        // default MySQL username
$password   = "";            // default MySQL password (empty)
$dbname     = "library_system"; // ✅ your real database name

$conn = mysqli_connect($servername, $username, $password, $dbname);

if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}

mysqli_set_charset($conn, "utf8mb4");
?>
