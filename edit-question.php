<?php
require_once "include/header.php";

if(!$user_obj->checkLoginStatus($_SESSION['user']['id'])) {
    header("Location: login.php");
}

$result = $user_obj->checkUserRole($_SESSION['user']['role'], 100);
if (!$result) {
    echo "You do not have the rights to access this page.";
    exit();
}

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

// Fetch categories and question types
$categories = $pdo->query("SELECT ca_id, ca_name FROM categories")->fetchAll();
$questionTypes = $pdo->query("SELECT qt_id, qt_name FROM questiontypes")->fetchAll();

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $text = $_POST['question'] ?? '';
    $answer = $_POST['answer'] ?? '';
    $total_points = (int)($_POST['total_points'] ?? 0);
    $ca_id = $_POST['ca_id'] ?? null;
    $qt_id = $_POST['qt_id'] ?? null;

    if ($ca_id && $qt_id && !empty($text) && !empty($answer)) {
        $updateStmt = $pdo->prepare("UPDATE matteprovgenerator.questions SET ca_id = ?, qt_id = ?, text = ?, answer = ?, total_points = ? WHERE qu_id = ?");
        $updateStmt->execute([$ca_id, $qt_id, $text, $answer, $total_points, $questionId]);

        header("Location: assignments.php?course_id={$_POST['course_id']}&ca_id={$ca_id}");
        exit;
    } else {
        echo "<p class='alert alert-warning'>Fyll i alla fält korrekt.</p>";
    }
}
?>

<div class="container py-5">
    <h1 class="mb-4">Redigera Fråga</h1>

    <form method="post">
        <div class="form-group mb-3">
            <label for="category">Kategori:</label>
            <select name="ca_id" id="category" class="form-control">
                <?php foreach ($categories as $category): ?>
                    <option value="<?= $category['ca_id']; ?>" <?= $category['ca_id'] == $question['ca_id'] ? 'selected' : '' ?>>
                        <?= htmlspecialchars($category['ca_name']); ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>

        <div class="form-group mb-3">
            <label for="questiontype">Frågetyp:</label>
            <select name="qt_id" id="questiontype" class="form-control">
                <?php foreach ($questionTypes as $qt): ?>
                    <option value="<?= $qt['qt_id']; ?>" <?= $qt['qt_id'] == $question['qt_id'] ? 'selected' : '' ?>>
                        <?= htmlspecialchars($qt['qt_name']); ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>

        <div class="form-group mb-3">
            <label for="question">Fråga:</label>
            <textarea name="question" id="question" class="form-control"><?= htmlspecialchars($question['text']); ?></textarea>
        </div>

        <div class="form-group mb-3">
            <label for="answer">Svar:</label>
            <textarea name="answer" id="answer" class="form-control"><?= htmlspecialchars($question['answer']); ?></textarea>
        </div>

        <div class="form-group mb-3">
            <label for="total_points">Poäng:</label>
            <input type="number" name="total_points" id="total_points" class="form-control" min="0" step="1" value="<?= htmlspecialchars($question['total_points']); ?>" required>
        </div>

        <!-- Optional: Hidden fields for redirect -->
        <input type="hidden" name="course_id" value="<?= $_GET['course_id'] ?? 0 ?>">

        <button type="submit" class="btn btn-success">Spara ändringar</button>
    </form>
</div>

<script type="importmap">
{
    "imports": {
        "ckeditor5": "./ckeditor5/ckeditor5.js",
        "ckeditor5/": "./ckeditor5/"
    }
}
</script>
<script type="module" src="./main.js"></script>

<?php require_once "include/footer.php"; ?>
