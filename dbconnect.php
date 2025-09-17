<?php

$servername = "localhost";
$username = "root";
$password = "";
$dbname = "489";

$conn = mysqli_connect($servername,$username,$password,$dbname);

if(!$conn){
    die(mysqli_connect_error());
}



?>