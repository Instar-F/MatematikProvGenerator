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
    #toggleSidebar {
        position: fixed;
        top: 50%;
        left: 0;
        transform: translateY(-50%);
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
        font-size: 2rem;
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
        transition: transform 0.25s cubic-bezier(.4,0,.2,1);
        font-size: 2rem;
        line-height: 1;
        display: flex;
        align-items: center;
        justify-content: center;
        font-family: inherit;
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
    </style>
</head>
<body class="bg-light">

<button id="toggleSidebar" aria-label="Toggle Sidebar" type="button" class="closed">
    <span id="toggleArrow">+</span>
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
                                    <div class="col-md-4 mb-4">
                                        <div class="card course-card" onclick="location.href='?course_id=<?= $course['co_id'] ?>'">
                                            <div class="card-body">
                                                <h5 class="card-title"><?= htmlspecialchars($course['co_name']) ?></h5>
                                                <p class="card-text"><?= htmlspecialchars($course['description']) ?></p>
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
                                    <div class="col-md-4 mb-4">
                                        <div class="card category-card" onclick="location.href='?course_id=<?= $courseId ?>&category_id=<?= $category['ca_id'] ?>'">
                                            <div class="card-body">
                                                <h5 class="card-title"><?= htmlspecialchars($category['ca_name']) ?></h5>
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
const toggleArrow = document.getElementById('toggleArrow');
const overlay = document.getElementById('sidebarOverlay');
let sidebarVisible = false;

function showSidebar() {
    sidebar.classList.add('visible');
    overlay.classList.add('visible');
    toggleArrow.textContent = "-";
    toggleBtn.classList.remove('closed');
    toggleBtn.classList.add('open');
}

function hideSidebar() {
    sidebar.classList.remove('visible');
    overlay.classList.remove('visible');
    toggleArrow.textContent = "+";
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
