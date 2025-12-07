<?php

session_start();

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require 'checkifadmin.php';
require_once __DIR__ . '/../models/dbconnect.php';
require_once __DIR__ . '/../models/CreateDefaultDBTables.php';

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
</head>

<body>

    <?php

    include __DIR__ . '/../view/navbar.php';

    $db = new Database();
    $conn = $db->getPdo();

    $sql = "SELECT id,username,role FROM users";

    $stmt = $conn->query($sql); 
    $users = $stmt->fetchAll(PDO::FETCH_ASSOC);

if ($users) {
    echo "<table style='margin: 0 auto'>";
    echo "<tr><th>ID</th><th>Username</th><th>Role</th><th>Actions</th></tr>";

    foreach ($users as $row) {
        echo "<tr>";
        echo "<td>" . htmlspecialchars($row['id']) . "</td>";
        echo "<td>" . htmlspecialchars($row['username']) . "</td>";
        echo "<td>" . htmlspecialchars($row['role']) . "</td>";
        echo "<td><a href='editUserProfile.php?username=" . urlencode($row['username']) . "' style='margin-left: 30px'>Edit</a></td>";
        echo "</tr>";
    }

    echo "</table>";
} else {
    echo "No users found";
}
?>

</body>

</html>