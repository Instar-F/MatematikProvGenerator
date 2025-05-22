<?php
// Determine which page should be highlighted in the sidebar
$sidebarPages = [
    'dashboard.php',
    'user-management.php',
    'test-list.php',
    'assignments.php',
    'add-question.php',
    'add-category.php',
    'add-course.php',
    'create-user.php',
    // add other sidebar-linked pages here
];

// Use $currentPage if set, otherwise fallback to current script
if (!isset($currentPage)) {
    $currentPage = basename($_SERVER['PHP_SELF']);
}

// If $currentPage is not a sidebar page, try to use HTTP_REFERER if available
if (!in_array($currentPage, $sidebarPages) && isset($_SERVER['HTTP_REFERER'])) {
    $referer = basename(parse_url($_SERVER['HTTP_REFERER'], PHP_URL_PATH));
    if (in_array($referer, $sidebarPages)) {
        $currentPage = $referer;
    }
}
?>
<div class="card shadow-lg h-100 d-flex">
    <div class="card-body p-0 d-flex flex-column align-items-stretch">
        <h2 class="text-center py-3 mb-0" style="font-size:1.4rem; font-weight:600; border-bottom:1px solid #eee;">
         Navigation
        </h2>
            <div class="list-group w-100" style="margin-top:0;">
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