<!DOCTYPE html>

<head></head>

<body>
    <?php

    include 'navbar.php';

    ?>

    <form action="RegisterSubmit.php" method="post">
        <label for="">username</label>
        <input type="text" name="username"> <br>
        <label for="">password</label>
        <input type="password" name="password"><br>
        <input type="submit" value="Register">
    </form>

</body>

</html>