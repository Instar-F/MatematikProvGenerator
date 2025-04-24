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

<div class="container mt-5">
    <div class="row">
        <div class="col-12 text-center mb-2">
            <h1 class="display-4">Admin Dashboard</h1>
            <p class="lead">Manage the system using the options below.</p>
        </div>
    </div>
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card shadow-lg">
                <div class="card-body">
                    <div class="row text-center">
                        <!-- Existing Links -->
                        <div class="col-md-6 mb-3">
                            <a href="create-user.php" class="btn btn-outline-primary btn-lg w-100">
                                <i class="bi bi-person-plus"></i> Create User
                            </a>
                        </div>
                        <div class="col-md-6 mb-3">
                            <a href="add-question.php" class="btn btn-outline-success btn-lg w-100">
                                <i class="bi bi-question-circle"></i> Add Question
                            </a>
                        </div>
                        <div class="col-md-6 mb-3">
                            <a href="test-list.php" class="btn btn-outline-warning btn-lg w-100">
                                <i class="bi bi-people"></i> Test list
                            </a>
                        </div>
                        <div class="col-md-6 mb-3">
                            <a href="generate-test.php" class="btn btn-outline-primary btn-lg w-100">
                                <i class="bi bi-person-plus"></i> generate-test
                            </a>
                        </div>
                        <div class="col-md-6 mb-3">
                            <a href="assignments.php" class="btn btn-outline-success btn-lg w-100">
                                <i class="bi bi-question-circle"></i> Assignments
                            </a>
                        </div>
                        <div class="col-md-6 mb-3">
                            <a href="user-management.php" class="btn btn-outline-warning btn-lg w-100">
                                <i class="bi bi-people"></i> User Management
                            </a>
                        </div>
                    </div>
                </div>
                <div class="card-footer text-center">
                    <small class="text-muted">Admin Panel - MatematikProvGenerator</small>
                </div>
            </div>
        </div>
    </div>
</div>