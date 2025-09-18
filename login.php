<!DOCTYPE html>

<head></head>

<body>
    <?php

    include 'navbar.php';
    require 'dbconnect.php';
require 'CreateDefaultDBTables.php';

    ?>

    <form action="LoginSubmit.php" method="post">
        <label for="">username</label><br>
        <input type="text" name="username"> <br>
        <label for="">password</label><br>
        <input type="password" name="password"><br>
        <input type="submit" value="Log in">
    </form>

</body>

</html>