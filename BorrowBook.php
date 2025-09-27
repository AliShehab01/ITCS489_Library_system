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

    $quantityWanted = $_POST['QuantityWanted'];
    $bookid = $_GET['bookid'];
    $userid = $_SESSION['user_id'];
    $borrowLimit = $_SESSION['BorrowLimit'];
    $dueDate = $_POST['dueDate'];

    $priceInBD = $quantityWanted * 1;

    $checkQuantitySql = "SELECT quantity FROM books WHERE id = " . $bookid;
    $checkNumOfBorrows = "SELECT currentNumOfBorrows FROM users WHERE id = " . $userid;

    $result = mysqli_query($conn, $checkQuantitySql);
    $resultNumOfBorrows = mysqli_query($conn, $checkNumOfBorrows);

    if($row = $result->fetch_assoc()){

    if($row['quantity'] > 0){

        $currentDate = date("Y-m-d");

        $difference = strtotime($currentDate) - strtotime($dueDate);

        if($difference < 0){
if($row = $resultNumOfBorrows->fetch_assoc()){
            if($row['currentNumOfBorrows'] >= $borrowLimit || $row['currentNumOfBorrows'] + $quantityWanted > $borrowLimit || $quantityWanted > $borrowLimit){
 echo "Enter a valid number according to your role";
            }else{
                 $stmt = $conn->prepare('UPDATE books SET quantity = quantity - ' . $quantityWanted .  ' WHERE id = ' . $bookid);

        $stmt->execute();

        $stmt = 'SELECT quantity FROM books WHERE id = ' . $bookid;

        $result = mysqli_query($conn,$stmt);

        if($row = $result->fetch_assoc()){
            if($row['quantity'] == 0){
                $stmt = $conn->prepare('UPDATE books SET availability = "issued" WHERE id = ' . $bookid);
                $stmt->execute();
            }
        }

        $stmt = $conn->prepare('UPDATE users SET currentNumOfBorrows = currentNumOfBorrows + ' . $quantityWanted .  ' WHERE id = ' . $userid);

        $stmt->execute();
        
        $stmt = $conn->prepare('INSERT INTO borrows (bookId,quantity,price,user_id,dueDate) VALUES (?,?,?,?,?)');

        $stmt->bind_param("iiiis", $bookid, $quantityWanted,$priceInBD, $userid, $dueDate);

        $stmt->execute();
            }
           
            
        }else{

        }
        }else{
            echo "Date should be in the future";
        }

        

       

    }

    }

    ?>

</body>
</html>