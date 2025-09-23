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
    include "navbar.php";
    require "dbconnect.php";
    
    $sqlBooks = "SELECT * FROM books";
    $result = mysqli_query($conn,$sqlBooks);

    if($result->num_rows > 0){
        while($row = $result->fetch_assoc()){
            echo "<div class='book'> <table style='border: 2px solid'> <tr><th><a href='book.php?bookid=" . $row["id"] . "'>" . $row["title"] . "</a></th></tr>";
            echo "<tr><td>Author: " . $row["author"] . "</td></tr>";
            echo "<tr><td>ISBN: " . $row["isbn"] . "</td>";
            echo "<tr><td>Category: " . $row["category"] . "</td>";
            echo "<tr><td>Publisher: " . $row["publisher"] . "</td>";
            echo "<tr><td>Year: " . $row["year"] . "</td>";
            echo "<tr><td>Quantity: " . $row["quantity"] . "</td>";
            echo "<tr><td>Publisher: " . $row["publisher"] . "</td></tr>";
            echo "<tr><td><img src='". $row["image_path"] . "' width='100px' height:'100px'> </td></tr></table> </div>";
        }
    }


    ?>

</body>
</html>