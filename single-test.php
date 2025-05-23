<?php
require_once "include/header.php";

// Improved autoWrapLatex to also wrap bare LaTeX expressions (not just inside <p>)
function autoWrapLatex($text) {
    // 1. Wrap each <p>...</p> containing a LaTeX command or math formula as its own display block (not joined)
    $text = preg_replace_callback(
        '/<p>\s*([^\n<]+)\s*<\/p>/i',
        function($matches) {
            $content = trim($matches[1]);
            // Accept any content that contains at least one backslash or equals sign as a candidate for math
            $looksLikeMath = (strpos($content, '\\') !== false || strpos($content, '=') !== false);
            $open = substr_count($content, '{');
            $close = substr_count($content, '}');
            $endsWithIncompleteCommand = preg_match('/\\\\[a-zA-Z]+\s*$/', $content);
            $endsWithOpenBrace = (substr(rtrim($content), -1) === '{');
            // Only wrap if braces are balanced and not obviously incomplete
            if ($looksLikeMath && $open === $close && !$endsWithIncompleteCommand && !$endsWithOpenBrace) {
                return '<p>$$' . $content . '$$</p>';
            }
            return '<p>' . $content . '</p>';
        },
        $text
    );
    // 2. Wrap bare LaTeX commands on their own line (not already inside $$...$$)
    $text = preg_replace_callback(
        '/(^|[\s>])\\\\([a-zA-Z]+(?:\{[^}]+\})+)($|[\s<])/u',
        function($matches) {
            return $matches[1] . '$$\\' . $matches[2] . '$$' . $matches[3];
        },
        $text
    );
    // 3. Remove any accidental double-wrapping of $$...$$ inside <p>...</p>
    $text = preg_replace('/<p>\s*\$\$(.*?)\$\$\s*<\/p>/s', '<p>$$$1$$</p>', $text);
    return $text;
}

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
        SELECT q.text, q.answer, q.total_points, q.image_url, q.image_size, q.image_location, q.image_align, q.image_valign
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

    <!-- MathJax Configuration -->
    <script>
        MathJax = {
            tex: {
                inlineMath: [['$', '$'], ['\\(', '\\)']],
                displayMath: [['$$', '$$'], ['\\[', '\\]']],
                processEscapes: true
            },
            svg: {
                fontCache: 'global'
            }
        };
    </script>
    <script src="https://cdn.jsdelivr.net/npm/mathjax@3/es5/tex-mml-chtml.js"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" 
          integrity="sha512-9usAa10IRO0HhonpyAIVpjrylPvoDwiPUiKdWk5t3PyolY1cOd4DSE0Ga+ri4AuTroPR5aQvXU9xC6qOPnzFeg==" 
          crossorigin="anonymous" referrerpolicy="no-referrer" />

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
            margin-bottom: 2.5em;
        }
        .answer-item-title {
            margin-bottom: 0.7em;
            font-size: 1.1rem;
            color: #444;
        }
        .answer-item-content {
            font-size: 1.1em;
            line-height: 1.6;
        }
        /* Match answer tables to question tables */
        .answer-item-content table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 1.5em;
            margin-bottom: 1.5em;
            /* Fix: Prevent table from being forced to a new page in print */
            page-break-inside: avoid;
            break-inside: avoid;
        }
        .answer-item-content th,
        .answer-item-content td {
            padding: 1em 1.2em;
            border: 1px solid #888;
            font-size: 1.1em;
        }
        .answer-item-content th {
            background: #f2f2f2;
            font-weight: bold;
        }
        .answer-item-content td {
            background: #fff;
        }
        .answer-item-content p,
        .answer-item-content ul,
        .answer-item-content ol {
            margin-top: 1em;
            margin-bottom: 1em;
            font-size: 1.1em;
        }
        @media print {
            .answer-item-content table {
                margin-top: 2em;
                margin-bottom: 2em;
            }
            .answer-item-content th,
            .answer-item-content td {
                padding: 1.3em 1.5em;
            }
            .answer-item-content p,
            .answer-item-content ul,
            .answer-item-content ol {
                margin-top: 1.2em;
                margin-bottom: 1.2em;
            }
        }
        /* Print button styling */
        .print-btn-wrapper {
            width: 100%;
            display: flex;
            justify-content: center;
            margin: 2rem 0 0 0;
        }
        .print-btn {
            padding: 14px 36px;
            font-size: 1.2rem;
            background: #0d6efd;
            color: #fff;
            border: none;
            border-radius: 8px;
            box-shadow: 0 4px 16px rgba(13,110,253,0.13); /* Use button color for shadow */
            cursor: pointer;
            transition: background 0.18s, box-shadow 0.18s;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        .print-btn:hover, .print-btn:focus {
            background: #084298;
            box-shadow: 0 6px 20px rgba(0,0,0,0.18);
        }
        .print-btn i {
            font-size: 1.3em;
            vertical-align: middle;
        }
        .print-btn * {
            background: transparent !important;
            border: none !important;
            box-shadow: none !important;
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
                width: 100% !important;
                min-width: 0 !important;
                max-width: 100% !important;
                box-shadow: none !important;
                border: none !important;
                padding: 0 !important;
                margin: 0 !important;
            }

            /* Remove extra space between questions for handwriting */
            .test-preview-left-panel .question-item {
                margin-bottom: 1em !important;
                min-height: auto;
                page-break-inside: avoid;
                break-inside: avoid;
            }
            @page {
                margin-top: 2cm;
            }
            @page :first {
                margin-top: 2cm;
            }

            .test-preview-right-panel {
                display: none;
            }

            /* Hide header and logout button while printing */
            #header, .logout-btn, .logout, .header, .site-header {
                display: none !important;
            }
        }

        /* --- Sidebar CSS copied from add-question.php for consistency --- */
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
            display: block;
            width: 22px;
            height: 3px;
            background: #0d6efd;
            margin: 2.5px 0;
            border-radius: 2px;
            transition: all 0.25s;
            box-sizing: border-box;
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
        /* --- End sidebar CSS from add-question.php --- */

        /* Change preview text color */
        .card-header.card-header-centered h1,
        .card-header.card-header-centered {
            color: #0d6efd !important;
        }

        /* Stack answers below questions */
        .test-preview-wrapper {
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 1rem;
            padding: 1rem;
            flex-wrap: wrap;
        }
        .test-preview-left-panel {
            background-color: white;
            width: 794px; /* A4 width */
            padding: 1.5rem;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
            border: 1px solid #ccc;
            border-radius: 6px;
            margin-bottom: 1rem;
        }
        .test-preview-right-panel {
            width: 100%;
            max-width: 794px;
            background: #fff;
            padding: 1.5rem;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
            border-radius: 6px;
            overflow-y: auto;
            max-height: 1123px;
            margin-top: 0;
            min-width: 350px;
        }
        @media (min-width: 1200px) {
            .test-preview-wrapper {
                flex-direction: column;
                align-items: center;
            }
            .test-preview-left-panel,
            .test-preview-right-panel {
                margin-left: auto;
                margin-right: auto;
            }
        }

        /* Add more space inside tables and question content */
        .test-preview-left-panel .question-item {
            /* Already has margin-bottom for writing space */
        }
        .test-preview-left-panel .question-item > div,
        .test-preview-left-panel .question-item table {
            margin-top: 1em;
            margin-bottom: 1em;
        }
        .test-preview-left-panel .question-item table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 1.5em;
            margin-bottom: 1.5em;
        }
        .test-preview-left-panel .question-item th,
        .test-preview-left-panel .question-item td {
            padding: 1em 1.2em;
            border: 1px solid #888;
            font-size: 1.1em;
        }
        .test-preview-left-panel .question-item th {
            background: #f2f2f2;
            font-weight: bold;
        }
        .test-preview-left-panel .question-item td {
            background: #fff;
        }
        /* Add more vertical space for all question content */
        .test-preview-left-panel .question-item p,
        .test-preview-left-panel .question-item ul,
        .test-preview-left-panel .question-item ol {
            margin-top: 1em;
            margin-bottom: 1em;
            font-size: 1.1em;
        }
        /* For print, keep the extra space */
        @media print {
            /* ...existing code... */
            .test-preview-left-panel .question-item table {
                margin-top: 2em;
                margin-bottom: 2em;
            }
            .test-preview-left-panel .question-item th,
            .test-preview-left-panel .question-item td {
                padding: 1.3em 1.5em;
            }
            .test-preview-left-panel .question-item p,
            .test-preview-left-panel .question-item ul,
            .test-preview-left-panel .question-item ol {
                margin-top: 1.2em;
                margin-bottom: 1.2em;
            }
            /* ...existing code... */
        }
    </style>
</head>
<body>

<button id="toggleSidebar" aria-label="Toggle Sidebar" type="button" class="closed no-print">
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

<div class="container-fluid page-centered-container">
    <div class="row" id="contentRow">
        <!-- Sidebar with links -->
        <div class="col-md ps-0" id="sidebarColumn">
            <?php require_once "sidebar.php"; ?>
        </div>
        <!-- Main content -->
        <div class="col-md" id="mainColumn">
            <div class="card card-shadow">
                <div class="card-header card-header-centered no-print">
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
                                    <?php 
                                    $totalPoints = array_sum(array_column($questions, 'total_points')); 
                                    ?>
                                    <strong>Points:</strong> ________/<?= $totalPoints ?>p<br>
                                    <strong>Grade:</strong>
                                </div>
                            </div>

                            <?php if (!empty($questions)): ?>
                                <?php foreach ($questions as $index => $question): ?>
                                    <div class="question-item" style="margin-bottom:1em;">
                                        <div style="margin-bottom:0.5em;">
                                            <strong>Question <?= $index + 1 ?>:</strong>
                                        </div>
                                        <div class="question-content">
                                            <?php 
                                            $questionContent = $question['text'];
                                            
                                            if ($question['image_url']) {
                                                $imageUrl = htmlspecialchars($question['image_url']);
                                                $imageSize = $question['image_size'] ?? '50';
                                                $imageAlign = $question['image_align'] ?? 'flex-start';
                                                $imageValign = $question['image_valign'] ?? 'flex-start';
                                                
                                                // Handle image positioning
                                                switch($question['image_location']) {
                                                    case '1': // Right
                                                        $questionWidth = 100 - intval($imageSize);
                                                        echo "<div style='display:flex;align-items:{$imageValign};gap:1em;margin:0;'>";
                                                        echo "<div style='width:{$questionWidth}%;'>{$questionContent}</div>";
                                                        echo "<div style='width:{$imageSize}%;'><img src='{$imageUrl}' style='width:100%;height:auto;'></div>";
                                                        echo "</div>";
                                                        break;
                                                        
                                                    case '2': // Left
                                                        $questionWidth = 100 - intval($imageSize);
                                                        echo "<div style='display:flex;align-items:{$imageValign};gap:1em;margin:0;'>";
                                                        echo "<div style='width:{$imageSize}%;'><img src='{$imageUrl}' style='width:100%;height:auto;'></div>";
                                                        echo "<div style='width:{$questionWidth}%;'>{$questionContent}</div>";
                                                        echo "</div>";
                                                        break;
                                                        
                                                    case '3': // Top
                                                        $alignStyle = $imageAlign === 'flex-start' ? 'margin-right:auto;' : 
                                                                    ($imageAlign === 'flex-end' ? 'margin-left:auto;' : 'margin:0 auto;');
                                                        echo "<div>";
                                                        echo "<img src='{$imageUrl}' style='width:{$imageSize}%;{$alignStyle}display:block;'>";
                                                        echo "<div style='margin-top:1em;'>{$questionContent}</div>";
                                                        echo "</div>";
                                                        break;
                                                        
                                                    case '4': // Bottom
                                                        $alignStyle = $imageAlign === 'flex-start' ? 'margin-right:auto;' : 
                                                                    ($imageAlign === 'flex-end' ? 'margin-left:auto;' : 'margin:0 auto;');
                                                        echo "<div>";
                                                        echo "<div>{$questionContent}</div>";
                                                        echo "<img src='{$imageUrl}' style='width:{$imageSize}%;{$alignStyle}display:block;margin-top:1em;'>";
                                                        echo "</div>";
                                                        break;
                                                        
                                                    default:
                                                        echo $questionContent;
                                                        echo "<div style='margin-top:1em;'>";
                                                        echo "<img src='{$imageUrl}' style='max-width:100%;height:auto;'>";
                                                        echo "</div>";
                                                }
                                            } else {
                                                echo $questionContent;
                                            }
                                            ?>
                                        </div>
                                        <div style="text-align: right; margin-top: 0.5em;">
                                            <span class="question-points">_____/<?= htmlspecialchars($question['total_points']) ?>p</span>
                                        </div>
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
                                            <?php
                                            // Ensure the answer HTML is well-formed by using DOMDocument to auto-close tags
                                            $allowed = '<br><ul><ol><li><strong><em><table><tbody><tr><td><th><thead><tfoot><figure><p>';
                                            $rawAnswer = strip_tags($question['answer'], $allowed);
                                            // Use DOMDocument to fix broken HTML
                                            $dom = new DOMDocument();
                                            libxml_use_internal_errors(true);
                                            $dom->loadHTML('<?xml encoding="utf-8" ?><div>' . $rawAnswer . '</div>', LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD);
                                            $fixedAnswer = $dom->saveHTML($dom->getElementsByTagName('div')->item(0));
                                            libxml_clear_errors();
                                            ?>
                                            <div class="answer-item">
                                                <h5 class="answer-item-title">Question <?= $index + 1 ?>:</h5>
                                                <div class="answer-item-content">
                                                    <?= autoWrapLatex($fixedAnswer) ?>
                                                </div>
                                            </div>
                                        <?php endif; ?>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <p>No answers available.</p>
                                <?php endif; ?>
                            </div>
                            <!-- Print Button (visible only on screen, hidden when printing) -->
                        </div>
                    </div>
                    <div class="print-btn-wrapper">
                        <button class="no-print print-btn" onclick="window.print()">
                            <i class="fas fa-print"></i>
                            <span>Print</span>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<script>
const sidebar = document.getElementById('sidebarColumn');
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
</body>
</html>
