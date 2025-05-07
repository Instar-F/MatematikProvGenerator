<?php
$currentPage = basename($_SERVER['PHP_SELF']);
?>
<div class="card shadow-lg h-100">
                <div class="card-body p-0">
                    <div class="list-group">
                        <!-- Styled Links -->
                        <a href="generate-test.php" class="list-group-item list-group-item-action btn-outline-primary <?= $currentPage === 'generate-test.php' ? 'active' : '' ?>">
                            Generate Test
                        </a>
                        <a href="add-question.php" class="list-group-item list-group-item-action btn-outline-success <?= $currentPage === 'add-question.php' ? 'active' : '' ?>">
                            Add Question
                        </a>
                        <a href="assignments.php" class="list-group-item list-group-item-action btn-outline-success <?= $currentPage === 'assignments.php' ? 'active' : '' ?>">
                            Assignments
                        </a>
                        <a href="test-list.php" class="list-group-item list-group-item-action btn-outline-warning <?= $currentPage === 'test-list.php' ? 'active' : '' ?>">
                            Test List
                        </a>
                        <a href="user-management.php" class="list-group-item list-group-item-action btn-outline-warning <?= $currentPage === 'user-management.php' ? 'active' : '' ?>">
                            User Management
                        </a>
                        <a href="create-user.php" class="list-group-item list-group-item-action btn-outline-primary <?= $currentPage === 'create-user.php' ? 'active' : '' ?>">
                            Create User
                        </a>
                        <a href="add-course.php" class="list-group-item list-group-item-action btn-outline-primary <?= $currentPage === 'add-course.php' ? 'active' : '' ?>">
                            Add Course
                        </a>
                        <a href="add-category.php" class="list-group-item list-group-item-action btn-outline-success <?= $currentPage === 'add-category.php' ? 'active' : '' ?>">
                            Add Category
                        </a>
                    </div>
                </div>
            </div>