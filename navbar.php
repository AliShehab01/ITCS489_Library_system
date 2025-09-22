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
    <!-- nav url (#) do not working-->
    <?php
    session_start();

    echo '<div style="border: 40px solid;">

        <header class="d-flex justify-content-center navbar fixed-top navbar-light bg-light">
            <a href="HomePage-EN.php"> <img src="imgs/tlp-logo.svg"></a>
            <ul class="nav nav-tabs">
                <li class="nav-item">
                    <a class="nav-link active" href="index.php">Home</a>
                </li>
<div class="btn-group">
  <button class="btn btn-secondary dropdown-toggle" data-bs-toggle="dropdown" style="border: 5px white solid ; ">Menu</button>
  <ul class="dropdown-menu">
    <li><a class="dropdown-item" href="Catalog Search and Browsing-EN.html">Catalog</a></li>
              <li><a class="dropdown-item" href="#">My Account</a></li>
              <li><a class="dropdown-item" href="#">Borrowed</a></li>
              <li><a class="dropdown-item" href="#">Reservations</a></li>
  </ul>
</div>
            </ul>';

    if (isset($_SESSION['role']) && $_SESSION['role'] == 'admin') {
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

    <div class="alert 
     <?php echo isset($_SESSION['first_name']) ? 'alert-success' : 'alert-danger'; ?>" role="alert">

        <?php
        if (isset($_SESSION['first_name'])) {
            echo 'Welcome ' . $_SESSION['first_name'] . " !" . ' <a href="logout.php">Logout</a>';
        } else {
            echo 'To access our +20,000 books, please <a href="signup.php">sign up</a> or <a href="login.php">log in</a>';
        }
        ?>
    </div>
</body>

</html>