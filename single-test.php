<?php
require_once "include/header.php";

if (!$user_obj->checkLoginStatus($_SESSION['user']['id'])) {
    header("Location: login.php");
    exit();
}

$result = $user_obj->checkUserRole($_SESSION['user']['role'], 100);

if (!$result) {
    echo "You do not have the rights to access this page.";
    exit();
}

// Check if test ID is passed
if (isset($_GET['exid'])) {
    $testId = (int)$_GET['exid'];

    // Fetch test details
    $testDetails = $pdo->prepare("
        SELECT ex_name, created_at, u_uname 
        FROM matteprovgenerator.exams 
        INNER JOIN matteprovgenerator.users ON matteprovgenerator.exams.ex_createdby_fk = matteprovgenerator.users.u_id 
        WHERE ex_id = :testId
    ");
    $testDetails->execute(['testId' => $testId]);
    $test = $testDetails->fetch(PDO::FETCH_ASSOC);

    // Fetch questions for the test (through exam_questions table)
    $questionsQuery = $pdo->prepare("
        SELECT q.text 
        FROM matteprovgenerator.exam_questions eq
        INNER JOIN matteprovgenerator.questions q ON eq.qu_id = q.qu_id
        WHERE eq.ex_id = :testId
        ORDER BY eq.question_order ASC
    ");
    $questionsQuery->execute(['testId' => $testId]);
    $questions = $questionsQuery->fetchAll(PDO::FETCH_ASSOC);

} else {
    echo "No test selected.";
    exit();
}
?>

<div class="container mt-5">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card shadow-lg p-4">
                <h2 class="text-center mb-4">Test Details</h2>

                <?php if ($test): ?>
                    <div class="mb-3">
                        <h4>Test Name:</h4>
                        <p><?= htmlspecialchars($test['ex_name']) ?></p>
                    </div>
                    <div class="mb-3">
                        <h4>Created By:</h4>
                        <p><?= htmlspecialchars($test['u_uname']) ?></p>
                    </div>
                    <div class="mb-3">
                        <h4>Created At:</h4>
                        <p><?= htmlspecialchars($test['created_at']) ?></p>
                    </div>
                <?php else: ?>
                    <div class="alert alert-danger text-center">
                        Test not found.
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <div class="row mt-4">
        <div class="col-md-8 mx-auto">
            <div class="card shadow-lg p-4">
                <h3 class="text-center mb-4">Questions</h3>

                <?php if (!empty($questions)): ?>
                    <ul class="list-group">
                        <?php foreach ($questions as $question): ?>
                            <li class="list-group-item">
                                <?= htmlspecialchars($question['text']) ?>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                <?php else: ?>
                    <div class="alert alert-warning text-center">
                        No questions found for this test.
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>
