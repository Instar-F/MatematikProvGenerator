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
</head>
<body class="bg-light">

<div class="container py-5">
    <?php
    // Step 1: No course or category selected → show courses
    if (!$courseId && !$categoryId): 
        $courses = $pdo->query("SELECT * FROM matteprovgenerator.courses")->fetchAll(PDO::FETCH_ASSOC);
    ?>

        <h1 class="mb-4">Select a Course</h1>
        <div class="row">
            <?php foreach ($courses as $course): ?>
                <div class="col-md-4 mb-4">
                    <div class="card h-100 shadow-sm" onclick="location.href='?course_id=<?= $course['co_id'] ?>'" style="cursor:pointer;">
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
        <h1 class="mb-4">Select a Category</h1>
        <div class="row">
            <?php foreach ($categories as $category): ?>
                <div class="col-md-4 mb-4">
                    <div class="card h-100 shadow-sm" onclick="location.href='?course_id=<?= $courseId ?>&category_id=<?= $category['ca_id'] ?>'" style="cursor:pointer;">
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
        <h1 class="mb-4">Questions</h1>
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

</body>
</html>
