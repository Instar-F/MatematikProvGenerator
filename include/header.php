<?php
require_once "include/class_user.php";
require_once "include/config.php";
require_once "include/functions.php";

if(isset($_GET['logout']) && $_GET['logout'] == "true"){
  $user_obj->logout();
  header("Location: login.php");
}

?>
<!DOCTYPE html>
<html>
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Mattakundproj    </title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
<link rel="stylesheet" href="style.css">
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script>
<link rel="stylesheet" href="ckeditor5/ckeditor5.css">
<script src="https://cdn.jsdelivr.net/npm/mathjax@3/es5/tex-mml-chtml.js" defer></script>	
<!-- <script defer src="script.js"></script> -->
</head>
<body>

<header>
    <nav class="navbar navbar-expand-lg navbar-dark py-4" style="background-color: #e0040e;">
        <div class="container">
            <a class="navbar-brand" href="dashboard.php" style="font-size: 2em;">Axxell Ab</a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav"
                aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>

            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav me-auto mb-2 mb-lg-0">
                    <li class="nav-item">
                        <a class="nav-link" href="/contact">Contact</a>
                    </li>
                </ul>

                <?php if ((isset($_SESSION['user']['id'])) && $user_obj->checkLoginStatus($_SESSION['user']['id'])): ?>
                <form action="" class="d-flex" method="get" role="search">
                    <button class="btn btn-outline-light" name="logout" value="true" type="submit">Log out</button>
                </form>
                <?php endif ?>
            </div>
        </div>
    </nav>
</header>
