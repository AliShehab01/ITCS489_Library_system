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

    $quantity;
    $bookId = $_POST["book_id"];
    $user_id;

    $borrow_id = $_POST['borrow_id'];

    $stmt = "SELECT * FROM borrows WHERE borrow_id = " . $borrow_id;

    $result = mysqli_query($conn,$stmt);

    if($_POST['RenewReturnAction'] == "Return book"){

    if($row = $result->fetch_assoc()){

        $quantity = $row['quantity'];
        $bookId = $row['bookId'];
        $user_id = $row['user_id'];

        $decreaseUserBorrows = "UPDATE users SET currentNumOfBorrows = currentNumOfBorrows - " . $quantity . " WHERE id = " . $user_id;
        $checkReturnStatus = "UPDATE borrows SET isReturned = 'true' WHERE borrow_id = " . $borrow_id;
        $increaseBookQuantity = "UPDATE books SET quantity = quantity + " . $quantity . " WHERE id = " . $bookId;

        mysqli_query($conn, $decreaseUserBorrows);
        mysqli_query($conn, $checkReturnStatus);
        mysqli_query($conn, $increaseBookQuantity);

        $checkAvailability = "SELECT availability FROM books WHERE id = " . $bookId;
        
        $result = mysqli_query($conn,$checkAvailability);

        if($row = $result->fetch_assoc()){
            if($row["availability"] == "issued"){
                $changeStatusToAvailable = "UPDATE books SET availability = 'available' WHERE id = " . $bookId;
                mysqli_query($conn, $changeStatusToAvailable);
            }
        }

    }
}else{

    $newDueDate = $_POST['newDueDate'];

    $checkIfReserved = "SELECT availability FROM books WHERE id = " . $bookId;

    $result = mysqli_query($conn,$checkIfReserved);
    
    if($row = $result->fetch_assoc()){
        if($row["availability"] != "reserved"){
        $updateDBDueDate = $conn->prepare("UPDATE borrows SET dueDate = ? WHERE borrow_id = ?");
        $updateDBDueDate->bind_param("si",$newDueDate,$borrow_id);

        $updateDBDueDate->execute();
        }else{

            echo "Book is already reserved by other users";

        }
    }

    

}

    ?>

</body>
</html>