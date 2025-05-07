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

if (isset($_GET['exid'])) {
    $testId = (int)$_GET['exid'];

    $testDetails = $pdo->prepare("
        SELECT ex_name, created_at, u_uname 
        FROM matteprovgenerator.exams 
        INNER JOIN matteprovgenerator.users ON matteprovgenerator.exams.ex_createdby_fk = matteprovgenerator.users.u_id 
        WHERE ex_id = :testId
    ");
    $testDetails->execute(['testId' => $testId]);
    $test = $testDetails->fetch(PDO::FETCH_ASSOC);

    $questionsQuery = $pdo->prepare("
        SELECT q.text, q.answer, q.total_points 
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

// Manually set the current page to "test-list.php" for sidebar highlighting
$currentPage = 'test-list.php';
?>

<!DOCTYPE html>
<html lang="sv">
<head>
    <meta charset="UTF-8">
    <title>Test Preview</title>

    <!-- MathJax -->
    <script>
        window.MathJax = {
            tex: {
                inlineMath: [['$', '$'], ['\\(', '\\)']],
                displayMath: [['$$', '$$'], ['\\[', '\\]']],
            },
            svg: { fontCache: 'global' }
        };
    </script>
    <script src="https://cdn.jsdelivr.net/npm/mathjax@3/es5/tex-svg.js" async></script>

    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" integrity="sha384-k6RqeWeci5ZR/Lv4MR0sA0FfDOM8V0y5z5l5Z5l5Z5l5Z5l5Z5l5Z5l5Z5l5Z5" crossorigin="anonymous">

    <style>
        body {
            background-color: #eaeaea;
            font-family: sans-serif;
        }

        .container-fluid {
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 15px;
        }

        .row {
            display: flex;
            flex-wrap: wrap;
            margin: 0 -15px;
        }

        .col-md-4, .col-md-8 {
            padding: 0 15px;
        }

        .sidebar-container {
            background-color: #fff;
            padding: 1.5rem;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
            border-radius: 6px;
            margin-top: 1rem;
        }

        .card {
            background-color: #fff;
            border: none;
            border-radius: 6px;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
            margin-bottom: 2rem;
        }

        .card-header {
            background-color: #f8f9fa;
            border-bottom: 1px solid #dee2e6;
            padding: 1rem;
            text-align: center;
        }

        .card-header-centered {
            display: flex;
            justify-content: center;
            align-items: center;
        }

        .card-body {
            padding: 2rem;
        }

        .fw-bold {
            font-weight: 700;
        }

        .fs-3 {
            font-size: 1.75rem;
        }

        .text-center {
            text-align: center;
        }

        .mt-5 {
            margin-top: 3rem;
        }

        .mb-4 {
            margin-bottom: 1.5rem;
        }

        .mb-3 {
            margin-bottom: 1rem;
        }

        .answers-section {
            padding: 1rem;
            margin-top: 2rem;
        }

        .answers-section-title {
            margin-bottom: 1.5rem;
            font-size: 1.2rem;
            text-align: center;
            color: #333;
        }

        .answer-item {
            margin-bottom: 1rem;
        }

        .answer-item-title {
            margin-bottom: 0.5rem;
            font-size: 1.1rem;
            color: #444;
        }

        .answer-item-content {
            font-size: 1rem;
            line-height: 1.5;
        }

        img {
            max-width: 100%;
            height: auto;
        }

        /* MathJax formula styling */
        .mjx-chtml {
            font-weight: normal; /* Ensure the MathJax formulas have normal weight */
            font-family: sans-serif; /* Match the font family with the surrounding text */
        }

        .mjx-chtml span {
            font-weight: normal; /* Override any bold styles from MathJax */
        }

        /* Print rules */
        @media print {
            .no-print {
                display: none !important;
            }

            body {
                margin: 0;
                background: white;
            }

            .container-fluid {
                padding: 0;
            }

            .sidebar-container {
                display: none;
            }

            .card {
                margin: 0;
                border: none;
                box-shadow: none;
            }

            .card-body {
                padding: 0;
            }

            .test-preview-wrapper {
                display: block;
                padding: 0;
            }

            .test-preview-left-panel {
                width: 100%;
                box-shadow: none;
                border: none;
                padding: 0;
                margin: 0;
            }

            .test-preview-right-panel {
                display: none;
            }
        }
    </style>
</head>
<body>

<div class="container-fluid">
    <div class="row">
        <!-- Sidebar with links -->
        <div class="col-md-4 sidebar-container">
            <?php require_once "sidebar.php"; ?>
        </div>
        <!-- Main content -->
        <div class="col-md-8">
            <div class="card card-shadow">
                <div class="card-header card-header-centered">
                    <h1>Test Preview</h1>
                </div>
                <div class="card-body">
                    <div class="test-preview-wrapper">
                        <div class="test-preview-left-panel">
                            <div class="exam-header">
                                <div>
                                    <strong><?= htmlspecialchars($test['ex_name']) ?></strong><br>
                                    <strong>Name:</strong> __________________
                                </div>
                                <div>
                                    <strong>Points:</strong> ________/36p<br>
                                    <strong>Grade:</strong>
                                </div>
                            </div>

                            <?php if (!empty($questions)): ?>
                                <?php foreach ($questions as $index => $question): ?>
                                    <div class="question-item">
                                        <div><strong>Question <?= $index + 1 ?>:</strong></div>
                                        <div><?= strip_tags($question['text'], '<br><ul><ol><li><strong><em>') ?></div>
                                        <div class="question-points">_____/<?= htmlspecialchars($question['total_points']) ?>p</div>
                                    </div>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <div class="alert alert-warning text-center">No questions found for this test.</div>
                            <?php endif; ?>
                        </div>

                        <div class="test-preview-right-panel no-print">
                            <div class="answers-section">
                                <h4 class="answers-section-title">Answers</h4>
                                <?php if (!empty($questions)): ?>
                                    <?php foreach ($questions as $index => $question): ?>
                                        <?php if (!empty($question['answer'])): ?>
                                            <div class="answer-item">
                                                <h5 class="answer-item-title">Question <?= $index + 1 ?>:</h5>
                                                <div class="answer-item-content"><?= nl2br(strip_tags($question['answer'], '<br><ul><ol><li><strong><em>')) ?></div>
                                            </div>
                                        <?php endif; ?>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <p>No answers available.</p>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

</body>
</html>
