<?php


session_start();

// Include config.php (in htdocs/)
require_once __DIR__ . '/../config.php';

// Include navbar.php (in app/view/)
include __DIR__ . '/../app/view/navbar.php';





/* 
         note:  public dir and index.php as a file 
         in mvc patterns, this should be only file accessable by 
         the browser

*/

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../../public/css/style.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet"
        integrity="sha384-sRIl4kxILFvY47J16cr9ZwB07vP4J8+LH7qKQnuqkuIAvNWLzeN8tE5YBujZqJLB" crossorigin="anonymous">
    <title>Library System</title>
    <script defer src="scripe.js"></script>
</head>


<body>



    <div class="row row-cols-1 row-cols-sm-2 g-3">
        <div class="col">
            <div class="card">
                <img src="../imgs/kindle.png" id="imgrid" class="card-img-top" alt="card-grid-image">
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
                <img src="../imgs/adding.png" id="imgrid2" class="card-img-top" alt="card-grid-image">
                <div class="card-body">
                    <h5 class="card-title">Upload book(s)</h5>
                    <p class="card-text">Easy steps contribute and make our family grows by adding book(s) to our
                        database </p>
                </div>
            </div>
        </div>
        <div class="col">
            <div class="card" style="text-align: center;">
                <img src="../imgs/kindle.png" id="imgrid" class="card-img-top" alt="card-grid-image">
                <div class="card-body">
                    <h5 class="card-title">This div need idea!!!</h5>
                    <p class="card-text">This is a longer card with supporting text below as a natural lead-in to
                        additional content.</p>
                </div>
            </div>
        </div>
        <div class="col">
            <div class="card" style="text-align: center;">
                <img src="../imgs/kindle.png" id="imgrid" class="card-img-top" alt="card-grid-image">
                <div class="card-body">
                    <h5 class="card-title">This div need idea!!!</h5>
                    <p class="card-text">This is a longer card with supporting text below as a natural lead-in to
                        additional content. This content is a little bit longer.</p>
                </div>
            </div>
        </div>
    </div>
    <!--checking the config-->



    <section class="cta-section py-5 bg-primary text-white text-center">
        <div class="container">
            <h2 class="display-5 fw-bold mb-3">Join Our Library Today!</h2>
            <p class="lead mb-4">
                Explore over 20,000 books, access exclusive resources, and start your reading journey now!
            </p>
            <a href="../app/view/signup.php" class="btn btn-lg btn-warning fw-bold text-dark">
                Sign Up Now &rarr;
            </a>
            <p class="mt-3 fst-italic">Hurry! Don’t miss out on our latest arrivals and special collections.</p>
        </div>
    </section>

    <style>
        .cta-section {
            background: linear-gradient(135deg, #007bff, #00d4ff);
            border-radius: 15px;
            box-shadow: 0 5px 20px rgba(0, 0, 0, 0.2);
        }

        .cta-section .btn-warning:hover {
            transform: scale(1.05);
            transition: 0.3s ease-in-out;
        }
    </style>








    <footer class="bg-dark text-white pt-5 pb-4">
        <div class="container text-center text-md-start">
            <div class="row text-center text-md-start">
                © 2025 University Library. All Rights Reserved.

            </div>
        </div>
    </footer>


    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"
        integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous">
    </script>






</body>

<?php
require_once __DIR__ . '/../app/models/dbconnect.php';
$db = new Database();
$conn = $db->conn; // $conn is your PDO object


?>

</html>