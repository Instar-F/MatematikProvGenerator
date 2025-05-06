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

    <style>
        body {
            background-color: #eaeaea;
            font-family: sans-serif;
        }

        .main-wrapper {
            display: flex;
            justify-content: center;
            align-items: flex-start;
            gap: 2rem;
            padding: 2rem;
            flex-wrap: wrap;
        }

        .left-panel {
            background-color: white;
            width: 794px; /* A4 width */
            min-height: 1123px; /* A4 height */
            padding: 2rem;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
            border: 1px solid #ccc;
            border-radius: 6px;
        }

        .right-panel {
            flex: 1;
            max-width: 400px;
            background: #fff;
            padding: 1.5rem;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
            border-radius: 6px;
            overflow-y: auto;
            max-height: 1123px;
            margin-top: 1rem;
        }

        .exam-header {
            display: flex;
            justify-content: space-between;
            margin-bottom: 2rem;
            font-size: 1.1rem;
        }

        .question {
            margin-bottom: 2.5rem;
            font-size: 1.05rem;
            page-break-inside: avoid;
        }

        .question .points {
            text-align: right;
            margin-top: 0.5rem;
            font-weight: bold;
        }

        .answers-section {
            padding: 1rem;
            margin-top: 2rem;
        }

        .answers-section h4 {
            margin-bottom: 1.5rem;
            font-size: 1.2rem;
            text-align: center;
            color: #333;
        }

        .answer-item {
            margin-bottom: 1rem;
        }

        .answer-item h5 {
            margin-bottom: 0.5rem;
            font-size: 1.1rem;
            color: #444;
        }

        .answer-item div {
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

            .main-wrapper {
                display: block;
                padding: 0;
            }

            .left-panel {
                width: 100%;
                box-shadow: none;
                border: none;
                padding: 0;
                margin: 0;
            }

            .right-panel {
                display: none;
            }
        }
    </style>
</head>
<body>

<!-- Info Card -->
<div class="container mt-5 no-print">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card shadow-lg p-4">
                <h2 class="text-center mb-4">Test Details</h2>
                <div class="mb-3"><h4>Test Name:</h4><p><?= htmlspecialchars($test['ex_name']) ?></p></div>
                <div class="mb-3"><h4>Created By:</h4><p><?= htmlspecialchars($test['u_uname']) ?></p></div>
                <div class="mb-3"><h4>Created At:</h4><p><?= htmlspecialchars($test['created_at']) ?></p></div>
                <button class="btn btn-primary mt-3" onclick="window.print()">Skriv ut</button>
            </div>
        </div>
    </div>
</div>

<!-- Main Layout -->
<div class="main-wrapper">
    <!-- A4-sized Preview -->
    <div class="left-panel">
        <div class="exam-header">
            <div>
                <strong><?= htmlspecialchars($test['ex_name']) ?></strong><br>
                <strong>Namn:</strong> __________________
            </div>
            <div>
                <strong>Poäng:</strong> ________/36p<br>
                <strong>Vitsord:</strong>
            </div>
        </div>

        <?php if (!empty($questions)): ?>
            <?php foreach ($questions as $index => $question): ?>
                <div class="question">
                    <div><strong>Uppgift <?= $index + 1 ?>:</strong></div>
                    <div><?= strip_tags($question['text'], '<br><ul><ol><li><strong><em>') ?></div>
                    <div class="points">_____/<?= htmlspecialchars($question['total_points']) ?>p</div>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <div class="alert alert-warning text-center">Inga frågor hittades för detta prov.</div>
        <?php endif; ?>
    </div>

    <!-- Answer Panel -->
    <div class="right-panel no-print">
        <div class="answers-section">
            <h4>Svar</h4>
            <?php if (!empty($questions)): ?>
                <?php foreach ($questions as $index => $question): ?>
                    <?php if (!empty($question['answer'])): ?>
                        <div class="answer-item">
                            <h5>Uppgift <?= $index + 1 ?>:</h5>
                            <div><?= nl2br(strip_tags($question['answer'], '<br><ul><ol><li><strong><em>')) ?></div>
                        </div>
                    <?php endif; ?>
                <?php endforeach; ?>
            <?php else: ?>
                <p>Inga svar tillgängliga.</p>
            <?php endif; ?>
        </div>
    </div>
</div>

</body>
</html>
