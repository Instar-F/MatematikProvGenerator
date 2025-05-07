<?php
require_once "include/header.php";

if(!$user_obj->checkLoginStatus($_SESSION['user']['id'])) {
    header("Location: login.php");
}

$result = $user_obj->checkUserRole($_SESSION['user']['role'], 100);

if (!$result) {
    echo "You do not have the rights to access this page.";
    exit(); // Stop the script from continuing
}
?>

<div class="container-fluid mt-5">
    <div class="row">
        <!-- Sidebar with links -->
        <div class="col-md-4 ps-0">
            <?php require_once "sidebar.php"; ?>
        </div>
        <!-- Main content -->
        <div class="col-md-8">
            <div class="card shadow-lg">
                <div class="card-header">
                    Admin Dashboard
                </div>
                <div class="card-body">
                    <h2 class="dashboard-welcome text-center">Welcome to the Admin Dashboard</h2>
                    <p class="dashboard-description mt-4">
                        Here you can manage users, create and assign tests, add courses and categories, and perform other administrative tasks. 
                        Use the links on the left to navigate through the available options.
                    </p>
                </div>
                <div class="card-footer text-center">
                    <small class="dashboard-footer text-muted">Admin Panel - MatematikProvGenerator</small>
                </div>
            </div>
        </div>
    </div>
</div>