<?php

session_start();

require_once __DIR__ . '/../models/dbconnect.php';

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

    $user_name = $_POST['username'];
    $new_role = $_POST['new_role'];
    $new_email = $_POST['new_email'];
    $new_first_name = $_POST['new_first_name'];
    $new_last_name = $_POST['new_last_name'];
    $new_phone_number = $_POST['new_phone_number'];



    if ($stmt->execute()) {
        header("location: managingusers.php");
        exit;
    }

    ?>
</body>

</html>