<?php
require_once "include/header.php";

$courses = $pdo->query("SELECT co_id, co_name FROM courses")->fetchAll();
$categories = $pdo->query("SELECT ca_id, ca_name, ca_co_fk FROM categories")->fetchAll();
$questionTypes = $pdo->query("SELECT qt_id, qt_name FROM questiontypes")->fetchAll();

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['save'])) {
    $question = $_POST['question'] ?? '';
    $answer = $_POST['answer'] ?? '';
    $ca_id = $_POST['ca_id'] ?? '';
    $qt_id = $_POST['qt_id'] ?? '';
    $total_points = $_POST['total_points'] ?? '';
    $difficulty = $_POST['difficulty'] ?? '';
    $co_id = $_POST['co_id'] ?? '';
    $image_url = null;

    if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
        $uploadDir = __DIR__ . '/uploads/';
        $webPathBase = 'uploads/';
        $tmpFile = $_FILES['image']['tmp_name'];
        $originalName = $_FILES['image']['name'];
        $fileExt = strtolower(pathinfo($originalName, PATHINFO_EXTENSION));
        $fileType = mime_content_type($tmpFile);
        $allowedTypes = ['image/jpeg', 'image/png', 'image/gif'];
        $allowedExts = ['jpg', 'jpeg', 'png', 'gif'];

        if (in_array($fileType, $allowedTypes) && in_array($fileExt, $allowedExts)) {
            $uniqueName = uniqid('img_', true) . '.' . $fileExt;
            $uploadFile = $uploadDir . $uniqueName;
            $webPath = $webPathBase . $uniqueName;
            if (move_uploaded_file($tmpFile, $uploadFile)) {
                $image_url = $webPath;
            } else {
                echo "<div class='alert alert-danger'>Failed to upload image.</div>";
            }
        } else {
            echo "<div class='alert alert-danger'>Invalid file type. Only JPG, PNG, and GIF are allowed.</div>";
        }
    }

    if (!empty($question) && !empty($answer) && !empty($ca_id) && !empty($qt_id) &&
        is_numeric($total_points) && is_numeric($difficulty) && $difficulty >= 1 && $difficulty <= 6) {
        try {
            $stmt = $pdo->prepare("INSERT INTO questions (ca_id, qt_id, text, answer, image_url, total_points, difficulty, teacher_fk) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
            $stmt->execute([$ca_id, $qt_id, $question, $answer, $image_url, $total_points, $difficulty, $_SESSION['user']['id']]);
            echo "<div class='alert alert-success'>Frågan har sparats framgångsrikt!</div>";
        } catch (Exception $e) {
            echo "<div class='alert alert-danger'>Fel vid spara: " . $e->getMessage() . "</div>";
        }
    } else {
        echo "<div class='alert alert-warning'>Fyll i alla fält korrekt.</div>";
    }
}
?>

<div class="container-fluid mt-5">
    <div class="row">
        <div class="col-md-4 ps-0">
            <?php require_once "sidebar.php"; ?>
        </div>
        <div class="col-md-8">
            <div class="card shadow-lg p-4 mb-5">
                <h2 class="mb-4 text-center">Matematisk Frågeredigerare</h2>

                <form method="post" action="" id="questionForm" enctype="multipart/form-data">
                    <div class="form-group mb-3">
                        <label for="course">Kurs:</label>
                        <select name="co_id" id="course" class="form-control" onchange="filterCategories()">
                            <option value="">Välj en kurs</option>
                            <?php foreach ($courses as $course): ?>
                                <option value="<?= $course['co_id']; ?>"><?= htmlspecialchars($course['co_name']); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="form-group mb-3">
                        <label for="category">Kategori:</label>
                        <select name="ca_id" id="category" class="form-control">
                            <option value="">Välj en kategori</option>
                            <?php foreach ($categories as $category): ?>
                                <option value="<?= $category['ca_id']; ?>" data-course="<?= $category['ca_co_fk']; ?>"><?= htmlspecialchars($category['ca_name']); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="form-group mb-3">
                        <label for="questiontype">Frågetyp:</label>
                        <select name="qt_id" id="questiontype" class="form-control">
                            <option value="">Välj en frågetyp</option>
                            <?php foreach ($questionTypes as $questionType): ?>
                                <option value="<?= $questionType['qt_id']; ?>"><?= htmlspecialchars($questionType['qt_name']); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="form-group mb-3">
                        <label for="question">Fråga:</label>
                        <textarea name="question" id="question"></textarea>
                    </div>

                    <div class="form-group mb-3">
                        <label for="answer">Svar:</label>
                        <textarea name="answer" id="answer"></textarea>
                    </div>

                    <div class="form-group mb-3">
                        <label for="image">Ladda upp en bild:</label>
                        <input type="file" name="image" id="image" class="form-control">
                    </div>

                    <div class="form-group mb-3">
                        <label for="total_points">Poäng för frågan:</label>
                        <input type="number" name="total_points" id="total_points" class="form-control" min="0" step="1" required>
                    </div>

                    <div class="form-group mb-4">
                        <label for="difficulty">Svårighetsgrad (1-6):</label>
                        <input type="number" name="difficulty" id="difficulty" class="form-control" min="1" max="6" step="1" required onkeydown="replaceDifficulty(event, this)">
                    </div>

                    <div class="d-flex justify-content-between">
                        <button type="button" id="previewButton" class="btn btn-primary">Förhandsgranska</button>
                        <button type="submit" name="save" class="btn btn-success">Spara till databas</button>
                    </div>
                </form>
            </div>

            <!-- Preview container -->
            <div class="preview-container mt-4"></div>
        </div>
    </div>
</div>

<script>
document.getElementById('previewButton').addEventListener('click', function () {
    const question = document.getElementById('question').value.trim();
    const answer = document.getElementById('answer').value.trim();
    const container = document.querySelector('.preview-container');

    container.innerHTML = ""; // Clear previous content

    if (!question && !answer) {
        container.innerHTML = `
            <div class="alert alert-warning">
                Du måste fylla i antingen en fråga, ett svar, eller båda för att förhandsgranska.
            </div>
        `;
        return;
    }

    container.innerHTML = `
        <div class="card shadow-lg p-4">
            <h2 class="mb-4 text-center">Förhandsgranskning</h2>
            <div class="preview">
                ${question ? `<h3>Fråga:</h3><div>${escapeHtml(question)}</div><br>` : ""}
                ${answer ? `<h3>Svar:</h3><div>${escapeHtml(answer)}</div>` : ""}
            </div>
        </div>
    `;
});

function escapeHtml(str) {
    const div = document.createElement('div');
    div.textContent = str;
    return div.innerHTML;
}
</script>

<!-- Import CKEditor (optional if you're using it in main.js) -->
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
