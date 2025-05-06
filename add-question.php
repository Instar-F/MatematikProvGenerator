<?php
require_once "include/header.php"; // Assuming this already includes PDO $pdo and session

// Retrieve data
$courses = $pdo->query("SELECT co_id, co_name FROM courses")->fetchAll();
$categories = $pdo->query("SELECT ca_id, ca_name, ca_co_fk FROM categories")->fetchAll();
$questionTypes = $pdo->query("SELECT qt_id, qt_name FROM questiontypes")->fetchAll();

// Handle POST
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['save'])) {
    $question = $_POST['question'] ?? '';
    $answer = $_POST['answer'] ?? '';
    $ca_id = $_POST['ca_id'] ?? '';
    $qt_id = $_POST['qt_id'] ?? '';
    $total_points = $_POST['total_points'] ?? '';
    $difficulty = $_POST['difficulty'] ?? '';
    $co_id = $_POST['co_id'] ?? '';
    $image_url = null;

    // Handle image upload
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
                echo "<p class='alert alert-danger'>Failed to upload image.</p>";
            }
        } else {
            echo "<p class='alert alert-danger'>Invalid file type. Only JPG, PNG, and GIF are allowed.</p>";
        }
    }

    if (!empty($question) && !empty($answer) && !empty($ca_id) && !empty($qt_id) && is_numeric($total_points) && is_numeric($difficulty) && $difficulty >= 1 && $difficulty <= 6) {
        try {
            $stmt = $pdo->prepare("INSERT INTO questions (ca_id, qt_id, text, answer, image_url, total_points, difficulty, teacher_fk) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
            $stmt->execute([$ca_id, $qt_id, $question, $answer, $image_url, $total_points, $difficulty, $_SESSION['user']['id']]);
            echo "<p class='alert alert-success'>Frågan har sparats framgångsrikt!</p>";
        } catch (Exception $e) {
            echo "<p class='alert alert-danger'>Fel vid spara: " . $e->getMessage() . "</p>";
        }
    } else {
        echo "<p class='alert alert-warning'>Fyll i alla fält korrekt.</p>";
    }
}
?>

<body>
<h1>Matematisk Frågeredigerare</h1>
<form method="post" action="" id="questionForm" enctype="multipart/form-data">
    <div class="form-group">
        <label for="course">Kurs:</label>
        <select name="co_id" id="course" class="form-control" onchange="filterCategories()">
            <option value="">Välj en kurs</option>
            <?php foreach ($courses as $course): ?>
                <option value="<?= $course['co_id']; ?>"><?= htmlspecialchars($course['co_name']); ?></option>
            <?php endforeach; ?>
        </select>
    </div>

    <div class="form-group">
        <label for="category">Kategori:</label>
        <select name="ca_id" id="category" class="form-control">
            <option value="">Välj en kategori</option>
            <?php foreach ($categories as $category): ?>
                <option value="<?= $category['ca_id']; ?>" data-course="<?= $category['ca_co_fk']; ?>"><?= htmlspecialchars($category['ca_name']); ?></option>
            <?php endforeach; ?>
        </select>
    </div>

    <div class="form-group">
        <label for="questiontype">Frågetyp:</label>
        <select name="qt_id" id="questiontype" class="form-control">
            <option value="">Välj en frågetyp</option>
            <?php foreach ($questionTypes as $questionType): ?>
                <option value="<?= $questionType['qt_id']; ?>"><?= htmlspecialchars($questionType['qt_name']); ?></option>
            <?php endforeach; ?>
        </select>
    </div>

    <div class="form-group">
        <label for="question">Fråga:</label>
        <textarea name="question" id="question"></textarea>
    </div>

    <div class="form-group">
        <label for="answer">Svar:</label>
        <textarea name="answer" id="answer"></textarea>
    </div>

    <div class="form-group">
        <label for="image">Ladda upp en bild:</label>
        <input type="file" name="image" id="image" class="form-control">
    </div>

    <div class="form-group">
        <label for="total_points">Poäng för frågan:</label>
        <input type="number" name="total_points" id="total_points" class="form-control" min="0" step="1" required>
    </div>

    <div class="form-group">
        <label for="difficulty">Svårighetsgrad (1-6):</label>
        <input type="number" name="difficulty" id="difficulty" class="form-control" min="1" max="6" step="1" required onkeydown="replaceDifficulty(event, this)">
    </div>

    <button type="submit" name="preview" formaction="preview.php" class="btn btn-primary mt-4">Förhandsgranska</button>
    <button type="submit" name="save" class="btn btn-success mt-4">Spara till databas</button>
</form>

<script>
function filterCategories() {
    const selectedCourse = document.getElementById('course').value;
    const categoryOptions = document.querySelectorAll('#category option');
    categoryOptions.forEach(option => {
        option.style.display = option.dataset.course === selectedCourse || option.value === "" ? "block" : "none";
    });
    document.getElementById('category').value = "";
}

function replaceDifficulty(event, input) {
    const allowedKeys = ['Backspace', 'Delete', 'ArrowLeft', 'ArrowRight', 'Tab'];
    if (!allowedKeys.includes(event.key) && !isNaN(event.key)) {
        const newValue = parseInt(event.key, 10);
        if (newValue >= 1 && newValue <= 6) {
            input.value = newValue;
        }
        event.preventDefault();
    }
}
</script>

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
