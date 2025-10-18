<?php

session_start();

if(session_status() === PHP_SESSION_NONE){
    session_start();
}

require 'checkifadmin.php';
require 'dbconnect.php';

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

include 'navbar.php';

$sql = "SELECT id,username,role FROM users";
$result = mysqli_query($conn, $sql);

if(mysqli_num_rows($result) > 0){
    echo "<table style='margin: 0 auto'>";
    echo "<tr><th>ID</th><th>Username</th><th>Role</th></tr>";

    while($row = mysqli_fetch_assoc($result)){

        echo "<tr>";
        echo "<td>" . $row['id'] . "</td>";
        echo "<td>" . $row['username'] . "</td>";
        echo "<td>" . $row['role'] . "</td>";
        echo "<td> <a href='editUserProfile.php?username=" . urlencode($row['username']) . "' style='margin-left: 30px'> Edit </a> </td>";
        echo "</tr>";
    }

    echo "</table>";

}else{
    echo "No users found";
}

?>

</body>
</html>