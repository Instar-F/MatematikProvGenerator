<?php
require_once "include/class_user.php";
require_once "include/config.php";
require_once "include/functions.php";

// Instead of using the User class directly, let's check if session data exists
// This approach avoids the User constructor issue

// Create a simplified check for login status
function is_logged_in() {
    return isset($_SESSION['user']['id']);
}

if (isset($_GET['logout']) && $_GET['logout'] == "true") {
    // Simple logout without using the User class
    session_start();
    session_unset();
    session_destroy();
    header("Location: login.php");
    exit;
}
?>
<!DOCTYPE html>
<html>
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Mattakundproj</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<link rel="stylesheet" href="style.css">
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" defer></script>
<link rel="stylesheet" href="ckeditor5/ckeditor5.css">
<script src="https://cdn.jsdelivr.net/npm/mathjax@3/es5/tex-mml-chtml.js" defer></script>	
</head>
<body>

<header>
    <nav class="navbar navbar-expand-lg navbar-dark py-4" style="background-color: #e0040e;">
        <div class="container">
            <a class="navbar-brand" href="index.php" style="font-size: 2em;">Axxell Ab</a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav"
                aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>
            <?php if (is_logged_in()): ?>
                <form action="" class="d-flex" method="get" role="search">
                    <button class="btn btn-outline-light" name="logout" value="true" type="submit">Log out</button>
                </form>
            <?php endif ?>
        </div>
    </nav>
</header>
