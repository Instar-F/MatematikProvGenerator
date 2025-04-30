<?php
require_once "include/header.php";  // Assuming this already includes config.php

// Retrieve categories and question types from the database
$stmt = $pdo->query("SELECT ca_id, ca_name FROM categories");
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

    if (!empty($question) && !empty($answer) && !empty($ca_id) && !empty($qt_id) && is_numeric($total_points)) {
        try {
            // Insert the question and answer into the database
            $stmt = $pdo->prepare("INSERT INTO questions (ca_id, qt_id, text, answer, total_points, teacher_fk) VALUES (?, ?, ?, ?, ?, ?)");
            $stmt->execute([$ca_id, $qt_id, $question, $answer, $total_points, $_SESSION['user']['id']]);

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
        <div class="form-group">
            <label for="category">Kategori:</label>
            <select name="ca_id" id="category" class="form-control">
                <option value="">Välj en kategori</option>
                <?php foreach ($categories as $category): ?>
                    <option value="<?= $category['ca_id']; ?>"><?= htmlspecialchars($category['ca_name']); ?></option>
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
            <label for="total_points">Poäng för frågan:</label>
            <input type="number" name="total_points" id="total_points" class="form-control" min="0" step="1" required>
        </div>

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

<?php require_once "include/footer.php"; ?>
