<?php
require_once "include/header.php";  // Assuming this already includes config.php

// Retrieve courses from the database
$stmt = $pdo->query("SELECT co_id, co_name FROM courses");
$courses = $stmt->fetchAll();

// Retrieve categories and question types from the database
$stmt = $pdo->query("SELECT ca_id, ca_name, ca_co_fk     FROM categories");
$categories = $stmt->fetchAll();

$stmt = $pdo->query("SELECT qt_id, qt_name FROM questiontypes");
$questionTypes = $stmt->fetchAll();

// Handle save to database
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['save'])) {
    // Sanitize and validate inputs
    $question = $_POST['question'] ?? '';
    $answer = $_POST['answer'] ?? '';
    $ca_id = $_POST['ca_id'] ?? '';
    $qt_id = $_POST['qt_id'] ?? '';
    $total_points = $_POST['total_points'] ?? '';
    $difficulty = $_POST['difficulty'] ?? '';
    $co_id = $_POST['co_id'] ?? '';

    if (!empty($question) && !empty($answer) && !empty($ca_id) && !empty($qt_id) && is_numeric($total_points) && is_numeric($difficulty) && $difficulty >= 1 && $difficulty <= 6) {
        try {
            // Insert the question and answer into the database
            $stmt = $pdo->prepare("INSERT INTO questions (ca_id, qt_id, text, answer, total_points, difficulty, teacher_fk) VALUES (?, ?, ?, ?, ?, ?, ?)");
            $stmt->execute([$ca_id, $qt_id, $question, $answer, $total_points, $difficulty, $_SESSION['user']['id']]);

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

    <form method="post" action="" id="questionForm">
        <!-- Course Dropdown -->
<!-- Course Dropdown -->
<div class="form-group">
    <label for="course">Kurs:</label>
    <select name="co_id" id="course" class="form-control" onchange="filterCategories()">
        <option value="">Välj en kurs</option>
        <?php foreach ($courses as $course): ?>
            <option value="<?= $course['co_id']; ?>"><?= htmlspecialchars($course['co_name']); ?></option>
        <?php endforeach; ?>
    </select>
</div>

<!-- Category Dropdown -->
<div class="form-group">
    <label for="category">Kategori:</label>
    <select name="ca_id" id="category" class="form-control">
        <option value="">Välj en kategori</option>
        <?php foreach ($categories as $category): ?>
            <option value="<?= $category['ca_id']; ?>" data-course="<?= $category['ca_co_fk']; ?>"><?= htmlspecialchars($category['ca_name']); ?></option>
        <?php endforeach; ?>
    </select>
</div>

<script>
    function filterCategories() {
        const selectedCourse = document.getElementById('course').value;
        const categoryOptions = document.querySelectorAll('#category option');

        categoryOptions.forEach(option => {
            if (option.dataset.course === selectedCourse || option.value === "") {
                option.style.display = "block";
            } else {
                option.style.display = "none";
            }
        });

        document.getElementById('category').value = ""; // Reset category selection
    }
</script>
        <!-- Question Type Dropdown -->
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

        <!-- Total Points Input -->
        <div class="form-group">
            <label for="total_points">Poäng för frågan:</label>
            <input type="number" name="total_points" id="total_points" class="form-control" min="0" step="1" required>
        </div>

        <!-- Difficulty Input -->
        <div class="form-group">
    <label for="difficulty">Svårighetsgrad (1-6):</label>
    <input type="number" name="difficulty" id="difficulty" class="form-control" min="1" max="6" step="1" required  onkeydown="replaceDifficulty(event, this)">
</div>

<script>
    function replaceDifficulty(event, input) {
        const allowedKeys = ['Backspace', 'Delete', 'ArrowLeft', 'ArrowRight', 'Tab'];
        if (!allowedKeys.includes(event.key) && !isNaN(event.key)) {
            const newValue = parseInt(event.key, 10);
            if (newValue >= 1 && newValue <= 6) {
                input.value = newValue; // Replace the current value with the new input
            }
            event.preventDefault(); // Prevent appending
        }
    }
</script>

        <!-- Buttons -->
        <button type="submit" name="preview" formaction="preview.php" class="btn btn-primary mt-4">Förhandsgranska</button>
        <button type="submit" name="save" class="btn btn-success mt-4">Spara till databas</button>
    </form>

    
    <script type="importmap">
    {
        "imports": {
            "ckeditor5": "./ckeditor5/ckeditor5.js",
            "ckeditor5/": "./ckeditor5/"
        }
    }
    </script>
    <script type="module" src="./main.js"></script>
    <script>
        window.onload = function () {
            if (window.location.protocol === "file:") {
                alert("This sample requires an HTTP server. Please serve this file with a web server.");
            }
        };
    </script>

    <script type="module" src="./main.js"></script>
    <script>
        function filterCategories() {
            const selectedCourse = document.getElementById('course').value;
            const categoryOptions = document.querySelectorAll('#category option');

            categoryOptions.forEach(option => {
                if (option.dataset.course === selectedCourse || option.value === "") {
                    option.style.display = "block";
                } else {
                    option.style.display = "none";
                }
            });

            document.getElementById('category').value = ""; // Reset category selection
        }
    </script>

<?php require_once "include/footer.php"; ?>
