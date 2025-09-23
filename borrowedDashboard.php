<?php

session_start();

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
    require 'dbconnect.php';

    $userid = $_SESSION['user_id'];
    
    $sqlGetBorrows = 'SELECT * FROM borrows WHERE user_id = ' . $userid;
    $result = mysqli_query($conn, $sqlGetBorrows);

    while($row = $result->fetch_assoc()){
        echo "<div class='borrowsInfo'>";
        echo "<form action=''>";
        echo "<table>";
        echo '<tr><th>Borrow ID: ' . $row['borrow_id'] . "</th></tr>";
        echo '<tr><td>Book ID: ' . $row['bookId'] . "</td></tr>";
        echo '<tr><td>Number of copies: ' . $row['quantity'] . "</td></tr>";
        echo '<tr><td> <input type="Submit" value="Return book"></td></tr>';
        echo "</form>";
        echo "</table>";
        echo "</div> <br>";
    }

    ?>
    
</body>
</html>