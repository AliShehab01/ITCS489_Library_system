<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="style.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet"
        integrity="sha384-sRIl4kxILFvY47J16cr9ZwB07vP4J8+LH7qKQnuqkuIAvNWLzeN8tE5YBujZqJLB" crossorigin="anonymous">
    <title>Library System</title>
    <script defer src="scripe.js"></script>
</head>


<body>


    <div style="border: 40px solid;">

        <header class="d-flex justify-content-center navbar fixed-top navbar-light bg-light">
            <a href="index.html"> <img src="imgs/tlp-logo.svg"></a>
            <ul class="nav nav-tabs">
                <li class="nav-item">
                    <a class="nav-link active" href="#!">Home</a>
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
            </ul>
            <button type="button" class="btn btn-primary" id="notific">
                Notifications <span class="badge bg-secondary">0</span>
            </button>

        </header>
    </div>

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
        To access our +20,000 books, please <a href="#">sign up</a> or <a href="#">log in</a>
    </div>

    <div class="row row-cols-1 row-cols-sm-2 g-3">
        <div class="col">
            <div class="card">
                <img src="imgs/kindle.png" id="imgrid" class="card-img-top" alt="card-grid-image">
                <div class="card-body" style="text-align: center;">
                    <h5 class="card-title">Ease Book Formatting</h5>
                    <p class="card-text">No need to navigate between websites for changing the format to AZW3 for your
                        kindle or
                        device, quick start for reading!</p>
                </div>
            </div>
        </div>
        <div class="col">
            <div class="card" style="text-align: center;">
                <img src="imgs/adding.png" id="imgrid2" class="card-img-top" alt="card-grid-image">
                <div class="card-body">
                    <h5 class="card-title">Upload book(s)</h5>
                    <p class="card-text">Easy steps contribute and make our family grows by adding book(s) to our
                        database </p>
                </div>
            </div>
        </div>
        <div class="col">
            <div class="card" style="text-align: center;">
                <img src="imgs/kindle.png" id="imgrid" class="card-img-top" alt="card-grid-image">
                <div class="card-body">
                    <h5 class="card-title">This div need idea!!!</h5>
                    <p class="card-text">This is a longer card with supporting text below as a natural lead-in to
                        additional content.</p>
                </div>
            </div>
        </div>
        <div class="col">
            <div class="card" style="text-align: center;">
                <img src="imgs/kindle.png" id="imgrid" class="card-img-top" alt="card-grid-image">
                <div class="card-body">
                    <h5 class="card-title">This div need idea!!!</h5>
                    <p class="card-text">This is a longer card with supporting text below as a natural lead-in to
                        additional content. This content is a little bit longer.</p>
                </div>
            </div>
        </div>
    </div>
    <!--checking the config-->

    <footer class="bg-dark text-white pt-5 pb-4">
        <div class="container text-center text-md-start">
            <div class="row text-center text-md-start">
                © 2025 XXXXXX. All Rights Reserved.

            </div>
        </div>
    </footer>


    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"
        integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz"
        crossorigin="anonymous"></script>
</body>

</html>