<!DOCTYPE html>

<head></head>

<body>
    <?php

    include 'navbar.php';

    ?>

    <form action="RegisterSubmit.php" method="post">
        <label for="">username<span style="color: red;"> * </span></label> <br>
        <input type="text" name="username"> <br>
        <label for="">password<span style="color: red;"> * </span></label> <br>
        <input type="password" name="password"><br>
        <label for="">First Name<span style="color: red;"> * </span></label><br>
        <input type="text" name="first_name"><br>
        <label for="">Last Name<span style="color: red;"> * </span></label><br>
        <input type="text" name="last_name"><br>
                <label for="">Email</label><br>
        <input type="email" name="email"><br>
        <label for="">phone number</label> <br>
        <input type="number" name="phone_number"><br>

        
        <input type="submit" value="Register">
    </form>

</body>

</html>