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

// Get the question ID from the URL
$questionId = isset($_GET['id']) ? (int)$_GET['id'] : null;

if (!$questionId) {
    die('Invalid question ID.');
}

// Fetch the existing question data
$stmt = $pdo->prepare("SELECT * FROM matteprovgenerator.questions WHERE qu_id = ?");
$stmt->execute([$questionId]);
$question = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$question) {
    die('Question not found.');
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $text = $_POST['text'] ?? '';
    $answer = $_POST['answer'] ?? '';
    $total_points = (int)($_POST['total_points'] ?? 0);

    $updateStmt = $pdo->prepare("UPDATE matteprovgenerator.questions SET text = ?, answer = ?, total_points = ? WHERE qu_id = ?");
    $updateStmt->execute([$text, $answer, $total_points, $questionId]);

    // Redirect back to assignments.php (you can also redirect to previous page)
    header("Location: assignments.php?course_id={$_POST['co_id']}&ca_id={$_POST['ca_id']}");
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Edit Question</title>
    <!-- Bootstrap CSS already linked in your project -->
</head>
<body class="bg-light">

<div class="container py-5">
    <h1 class="mb-4">Edit Question</h1>

    <form method="post">
        <div class="mb-3">
            <label for="text" class="form-label">Question Text</label>
            <textarea class="form-control" id="text" name="text" rows="4" required><?= htmlspecialchars($question['text']) ?></textarea>
        </div>

        <div class="mb-3">
            <label for="answer" class="form-label">Answer</label>
            <input type="text" class="form-control" id="answer" name="answer" value="<?= htmlspecialchars($question['answer']) ?>" >
        </div>

        <div class="mb-3">
            <label for="total_points" class="form-label">Total Points</label>
            <input type="number" class="form-control" id="total_points" name="total_points" value="<?= htmlspecialchars($question['total_points']) ?>" required>
        </div>

        <button type="submit" class="btn btn-primary">Save Changes</button>
   </form>
</div>

</body>
</html>
