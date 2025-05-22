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

$courseId = isset($_GET['course_id']) ? (int)$_GET['course_id'] : null;
$categoryId = isset($_GET['category_id']) ? (int)$_GET['category_id'] : null;

if (!function_exists('strip_latex')) {
    function strip_latex($text) {
        // Remove $...$, \[...\], \(...\)
        $text = preg_replace('/\$(.*?)\$/s', '', $text);
        $text = preg_replace('/\\\\\[(.*?)\\\\\]/s', '', $text);
        $text = preg_replace('/\\\\\((.*?)\\\\\)/s', '', $text);
        // Remove <p> and </p> and any other HTML tags
        $text = strip_tags($text);
        return trim($text);
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Assignments</title>
    <!-- Bootstrap CSS should already be linked in your project -->
    <style>
    #sidebarColumn {
        position: fixed;
        top: 0;
        left: 0;
        width: 320px;
        height: 100vh;
        background: #fff;
        z-index: 2000;
        box-shadow: 2px 0 16px rgba(0,0,0,0.12);
        transform: translateX(-100%);
        opacity: 0;
        transition: transform 0.28s cubic-bezier(.4,0,.2,1), opacity 0.18s cubic-bezier(.4,0,.2,1);
        will-change: transform, opacity;
    }
    #sidebarColumn.visible {
        transform: translateX(0);
        opacity: 1;
    }
    #mainColumn {
        transition: none;
    }
    .page-centered-container {
        max-width: 1100px;
        margin: 0 auto;
        padding: 32px 16px 32px 16px;
    }
    /* Sidebar toggle button styling */
    #toggleSidebar {
        position: fixed;
        top: 38%; /* Move above the vertical center */
        left: 0;
        z-index: 2100;
        border-radius: 50%;
        width: 44px;
        height: 44px;
        padding: 0;
        display: flex;
        align-items: center;
        justify-content: center;
        background: #fff;
        border: 2px solid #0d6efd;
        color: #0d6efd;
        box-shadow: 0 2px 8px rgba(0,0,0,0.15);
        transition: left 0.28s cubic-bezier(.4,0,.2,1), background 0.18s, color 0.18s;
        cursor: pointer;
    }
    #toggleSidebar.open {
        left: 320px;
    }
    #toggleSidebar.closed {
        left: 0;
    }
    #toggleSidebar:hover {
        background: #e7f1ff;
        color: #0a58ca;
    }
    #toggleArrow {
        display: flex;
        flex-direction: column;
        justify-content: center;
        align-items: center;
        width: 22px;
        height: 22px;
        font-size: 1.7rem;
        transition: color 0.18s;
        position: relative;
    }
    .hamburger-bar {
        width: 22px;
        height: 3px;
        background: #0d6efd;
        margin: 2.5px 0;
        border-radius: 2px;
        transition: all 0.25s;
    }
    #toggleSidebar.open .hamburger-bar {
        display: none;
    }
    .sidebar-close-icon {
        display: none;
        font-size: 1.7rem;
        color: #0d6efd;
        line-height: 1;
        width: 100%;
        height: 100%;
        align-items: center;
        justify-content: center;
    }
    #toggleSidebar.open .sidebar-close-icon {
        display: flex;
        align-items: center;
        justify-content: center;
    }
    #sidebarOverlay {
        display: none;
        position: fixed;
        z-index: 1999;
        top: 0;
        left: 0;
        width: 100vw;
        height: 100vh;
        background: rgba(0,0,0,0.18);
        transition: opacity 0.18s;
    }
    #sidebarOverlay.visible {
        display: block;
        opacity: 1;
    }
    .course-card {
        min-height: 150px;
        height: 100%;
        display: flex;
        flex-direction: column;
        justify-content: space-between;
        transition: box-shadow 0.18s;
        cursor: pointer;
        padding: 0.5rem 0.75rem;
    }
    .course-card .card-body {
        padding: 0.75rem 0.5rem;
    }
    .course-card .card-title {
        font-size: 1.05rem;
        margin-bottom: 0.75rem;
    }
    .category-list {
        margin: 0;
        padding: 0;
        list-style: none;
    }
    .category-list li {
        padding: 0.15rem 0;
        font-size: 0.97rem;
        border-bottom: 1px solid #f0f0f0;
    }
    .category-list li:last-child {
        border-bottom: none;
    }
    .category-link {
        /* Remove color and underline for plain text look */
        color: inherit;
        text-decoration: none;
        transition: none;
        cursor: pointer;
    }
    .category-link:hover {
        color: inherit;
        text-decoration: none;
    }
    </style>
</head>
<body class="bg-light">

<button id="toggleSidebar" aria-label="Toggle Sidebar" type="button" class="closed">
    <span id="toggleArrow">
        <span class="hamburger-bar"></span>
        <span class="hamburger-bar"></span>
        <span class="hamburger-bar"></span>
        <span class="sidebar-close-icon">
            <svg width="22" height="22" viewBox="0 0 22 22" fill="none" style="display:block;margin:auto;" xmlns="http://www.w3.org/2000/svg">
                <rect x="6" y="10" width="10" height="2" rx="1" fill="#0d6efd"/>
            </svg>
        </span>
    </span>
</button>
<div id="sidebarOverlay"></div>

<div class="container-fluid page-centered-container mt-5">
    <div class="row" id="contentRow">
        <!-- Sidebar with links -->
        <div class="col-md ps-0" id="sidebarColumn">
            <?php require_once "sidebar.php"; ?>
        </div>
        <!-- Main content -->
        <div class="col-md" id="mainColumn">
            <div class="container mt-5">
                <div class="card shadow-lg">
                    <div class="card-header">
                        Select a Course
                    </div>
                    <div class="card-body">
                        <?php
                        // Step 1: No course or category selected → show courses
                        if (!$courseId && !$categoryId): 
                            $courses = $pdo->query("SELECT * FROM matteprovgenerator.courses")->fetchAll(PDO::FETCH_ASSOC);
                        ?>
                            <div class="row">
                                <?php foreach ($courses as $course): ?>
                                    <?php
                                    // Fetch categories for this course
                                    $stmt = $pdo->prepare("SELECT * FROM matteprovgenerator.categories WHERE ca_co_fk = ?");
                                    $stmt->execute([$course['co_id']]);
                                    $categories = $stmt->fetchAll(PDO::FETCH_ASSOC);
                                    ?>
                                    <div class="col-md-4 mb-4 d-flex">
                                        <div class="card course-card h-100 w-100"
                                             onclick="location.href='?course_id=<?= $course['co_id'] ?>'"
                                             style="cursor:pointer;">
                                            <div class="card-body d-flex flex-column">
                                                <h5 class="card-title mb-3"><?= htmlspecialchars($course['co_name']) ?></h5>
                                                <ul class="category-list flex-grow-1">
                                                    <?php if (count($categories) > 0): ?>
                                                        <?php foreach ($categories as $category): ?>
                                                            <li>
                                                                <a class="category-link" href="?course_id=<?= $course['co_id'] ?>&category_id=<?= $category['ca_id'] ?>">
                                                                    <?= htmlspecialchars($category['ca_name']) ?>
                                                                </a>
                                                            </li>
                                                        <?php endforeach; ?>
                                                    <?php else: ?>
                                                        <li class="text-muted">No categories</li>
                                                    <?php endif; ?>
                                                </ul>
                                            </div>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        <?php
                        // Step 2: Course selected, show categories
                        elseif ($courseId && !$categoryId): 
                            $stmt = $pdo->prepare("SELECT * FROM matteprovgenerator.categories WHERE ca_co_fk = ?");
                            $stmt->execute([$courseId]);
                            $categories = $stmt->fetchAll(PDO::FETCH_ASSOC);
                        ?>
                            <a href="assignments.php" class="btn btn-secondary mb-4">⬅️ Back to Courses</a>
                            <h1 class="centered-header">Select a Category</h1>
                            <div class="row">
                                <?php foreach ($categories as $category): ?>
                                    <?php
                                    // Fetch 5 latest questions for this category
                                    $qstmt = $pdo->prepare("SELECT * FROM matteprovgenerator.questions WHERE ca_id = ? ORDER BY qu_id DESC LIMIT 5");
                                    $qstmt->execute([$category['ca_id']]);
                                    $latestQuestions = $qstmt->fetchAll(PDO::FETCH_ASSOC);
                                    ?>
                                    <div class="col-md-4 mb-4 d-flex">
                                        <div class="card category-card h-100 w-100"
                                             onclick="location.href='?course_id=<?= $courseId ?>&category_id=<?= $category['ca_id'] ?>';"
                                             style="cursor:pointer; min-height:120px; display:flex; flex-direction:column; justify-content:space-between;">
                                            <div class="card-body d-flex flex-column" style="padding:0.6rem 0.5rem;">
                                                <h5 class="card-title mb-2" style="font-size:1rem;"><?= htmlspecialchars($category['ca_name']) ?></h5>
                                                <ul class="list-group list-group-flush flex-grow-1" style="margin-bottom:0.3rem;">
                                                    <?php if (count($latestQuestions) > 0): ?>
                                                        <?php foreach ($latestQuestions as $question): ?>
                                                            <?php
                                                            $plain = strip_latex($question['text']);
                                                            $preview = mb_substr($plain, 0, 40);
                                                            if (mb_strlen($plain) > 40) {
                                                                $preview .= '...';
                                                            }
                                                            ?>
                                                            <li class="list-group-item px-0 py-1"
                                                                style="border:0; background:transparent; white-space:nowrap; overflow:hidden; text-overflow:ellipsis;">
                                                                <a href="edit-question.php?id=<?= $question['qu_id'] ?>"
                                                                   style="text-decoration:none; color:inherit; font-size:0.95rem; white-space:nowrap; overflow:hidden; text-overflow:ellipsis; display:block;"
                                                                   onclick="event.stopPropagation();">
                                                                    <?= htmlspecialchars($preview) ?>
                                                                </a>
                                                            </li>
                                                        <?php endforeach; ?>
                                                    <?php else: ?>
                                                        <li class="list-group-item px-0 py-1 text-muted"
                                                            style="border:0; background:transparent; font-size:0.95rem;">
                                                            No questions yet
                                                        </li>
                                                    <?php endif; ?>
                                                </ul>
                                            </div>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        <?php
                        // Step 3: Course + Category selected, show questions
                        elseif ($courseId && $categoryId):
                            $stmt = $pdo->prepare("SELECT * FROM matteprovgenerator.questions WHERE ca_id = ?");
                            $stmt->execute([$categoryId]);
                            $questions = $stmt->fetchAll(PDO::FETCH_ASSOC);
                        ?>
                            <a href="assignments.php?course_id=<?= $courseId ?>" class="btn btn-secondary mb-4">⬅️ Back to Categories</a>
                            <h1 class="centered-header">Questions</h1>
                            <ul class="list-group">
                                <?php foreach ($questions as $question): ?>
                                    <li class="list-group-item d-flex justify-content-between align-items-center">
                                        <?= htmlspecialchars($question['text']) ?>
                                        <a href="edit-question.php?id=<?= $question['qu_id'] ?>" class="btn btn-primary btn-sm">Edit</a>
                                    </li>
                                <?php endforeach; ?>
                            </ul>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
const sidebar = document.getElementById('sidebarColumn');
const main = document.getElementById('mainColumn');
const toggleBtn = document.getElementById('toggleSidebar');
const overlay = document.getElementById('sidebarOverlay');
let sidebarVisible = false;

function showSidebar() {
    sidebar.classList.add('visible');
    overlay.classList.add('visible');
    toggleBtn.classList.remove('closed');
    toggleBtn.classList.add('open');
}

function hideSidebar() {
    sidebar.classList.remove('visible');
    overlay.classList.remove('visible');
    toggleBtn.classList.remove('open');
    toggleBtn.classList.add('closed');
}

toggleBtn.addEventListener('click', function () {
    sidebarVisible = !sidebarVisible;
    if (sidebarVisible) {
        showSidebar();
    } else {
        hideSidebar();
    }
});

overlay.addEventListener('click', function () {
    sidebarVisible = false;
    hideSidebar();
});

hideSidebar();
</script>
<?php require_once "include/footer.php"; ?>
</body>
</html>
