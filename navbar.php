<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet"
        integrity="sha384-sRIl4kxILFvY47J16cr9ZwB07vP4J8+LH7qKQnuqkuIAvNWLzeN8tE5YBujZqJLB" crossorigin="anonymous">
    <title>Document</title>
</head>
<body>

    <?php
    session_start();

    echo '<div style="border: 40px solid;">

        <header class="d-flex justify-content-center navbar fixed-top navbar-light bg-light">
            <a href="index.php"> <img src="imgs/tlp-logo.svg"></a>
            <ul class="nav nav-tabs">
                <li class="nav-item">
                    <a class="nav-link active" href="index.php">Home</a>
                </li>
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown"
                        aria-expanded="false">
                        Dropdown
                    </a>
                    <ul class="dropdown-menu">
                        <li><a class="dropdown-item" href="#">1</a></li>
                        <li><a class="dropdown-item" href="#">2</a></li>
                        <li><a class="dropdown-item" href="#">3</a></li>
                        <li><a class="dropdown-item" href="#">4</a></li>
                    </ul>
                </li>
            </ul>';

            if(isset($_SESSION['role']) && $_SESSION['role'] == 'admin'){
                echo "<a href=AdminArea.php style='margin-right: 20px; color:red'> Admin Area </a>";
            }

            echo '
            <button type="button" class="btn btn-primary" id="notific">
                Notifications <span class="badge bg-secondary">0</span>
            </button>

        </header>
    </div>';

    ?>

    <div class="dropdown" id="leftlang">
        <button class="btn btn-secondary dropdown-toggle" type="button" id="dropdownMenuButton"
            data-bs-toggle="dropdown" aria-expanded="false">
            Language
        </button>
        <ul class="dropdown-menu" aria-labelledby="dropdownMenuButton">
            <li><a class="dropdown-item" href="index.html">English</a></li>
            <li><a class="dropdown-item" href="index2.html">عربي</a></li>
        </ul>
    </div>
    <div class="alert alert-danger" role="alert"> <!--this need JS and cookies-->
       <?php
       if(isset($_SESSION['first_name'])){
         echo 'Welcome ' . $_SESSION['first_name'] . " !" . ' <a href="logout.php">Logout</a>';
       }else{
        echo 'To access our +20,000 books, please <a href="signup.php">sign up</a> or <a href="login.php">log in</a>';
       }
       ?>
    </div>
</body>
</html>