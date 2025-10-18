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
    require 'CreateDefaultDBTables.php';

    $userid = $_SESSION['user_id'];
    
    $sqlGetBorrows = 'SELECT * FROM borrows WHERE user_id = ' . $userid;
    $result = mysqli_query($conn, $sqlGetBorrows);

    while($row = $result->fetch_assoc()){

              $currentDate = strtotime(date("Y-m-d"));
        $dueDate = strtotime(date($row["dueDate"]));
        $datediff = $currentDate - $dueDate;
        $datediff = round($datediff / (60 * 60 * 24));
         

        echo "<div class='borrowsInfo'>";
        echo '<form action="bookReturnAndRenew.php" method="post">';
        echo '<input type="hidden" value="' . $row['borrow_id'] . '" name="borrow_id">';
        echo '<input type="hidden" value="' . $row['bookId'] . '" name="book_id">';
        echo "<table border='solid 1px black'>";
        echo '<tr><th>Borrow ID: ' . $row['borrow_id'] . "</th></tr>";
        echo '<tr><td>Book ID: ' . $row['bookId'] . "</td></tr>";
        echo '<tr><td>Number of copies: ' . $row['quantity'] . "</td></tr>";
        echo '<tr><td>Price: ' . $row['price'] . " BD</td></tr>";
        if($datediff >= 0){

            $fines = $datediff * $row["quantity"];

            echo "<tr><td>Fines: " . $fines . " BD (1 BD Per day)</td></tr>";
            echo '<tr><td>Total price: ' . $row['price'] + $fines . " BD</td></tr>";
        }

        if($row["isReturned"] == "false"){
            echo '<tr><td>Due date: ' . $row['dueDate'] . "</td></tr>";
            echo '<tr><td><input type="date" name="newDueDate"></tr></td>';
            
            echo '<tr><td><input type="Submit" value="Renew" name="RenewReturnAction">';
            echo '<input type="Submit" value="Return book" name="RenewReturnAction"></td></tr>';
            echo '<tr><td style="color: gray"> <b>Borrow status: Issued</b> </td></tr>';
        }else{
            echo '<tr><td style="color: green"> <b>Borrow status: Returned </b></td></tr>';
            
        }
echo "</table>";
        echo "</form>";
        
        
        echo "</div> <br>";
    }

    ?>
    
</body>
</html>