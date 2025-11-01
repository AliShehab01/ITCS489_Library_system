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
    include "../view/navbar.php";
    require "../models/dbconnect.php";

    $bookID = $_GET["bookid"];
    $borrowLimit = $_SESSION['BorrowLimit'];

    $sqlGetBook = "SELECT * FROM books WHERE id = " . $bookID;

    $result = mysqli_query($conn, $sqlGetBook);

    if ($row = $result->fetch_assoc()) {
        echo "<center>";
        echo "<div id='bookInfo'>";
        echo "<h1>" . $row['title'] . "</h1>";
        echo "<img src='" . $row["image_path"] . "'> <br>";
        echo "<b><u>Book details:</b></u> <br>";
        echo "<b>Author:</u></b> " . $row["author"] . "<br>";
        echo "<b>ISBN:</u></b> " . $row["isbn"] . "<br>";
        echo "<b>Category:</u></b> " . $row["category"] . "<br>";
        echo "<b>Publisher:</u></b> " . $row["publisher"] . "<br>";
        echo "<b>Year:</u></b> " . $row["year"] . "<br>";
        echo "<b>Quantity:</u></b> " . $row["quantity"] . "<br>";
        echo "<b>Publisher:</u></b> " . $row["publisher"] . "<br>";
        if ($row["quantity"] > 0) {
            echo "<b><span style='color:green'> Book is currently available </span></b>";
        } else {
            echo "<b><span style='color:red'> Book is currently unavailable </span></b>";
        }
        echo "</div>";
        echo "</center>";
    }

    echo '<form action="BorrowBook.php?bookid=' . $_GET["bookid"] . '" method="post" style="text-align: center;">
        <label for="">Quantity:</label>
        <input type="number" name="QuantityWanted" min=1, max=' . $borrowLimit . '> <br>
        <input type="submit" value="Borrow">
    </form>';

    ?>



</body>

</html>