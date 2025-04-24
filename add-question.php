<?php
require_once "include/header.php";  // Assuming this already includes config.php

// Retrieve categories and question types from the database
$stmt = $pdo->query("SELECT ca_id, ca_name FROM categories");
$categories = $stmt->fetchAll();

$stmt = $pdo->query("SELECT qt_id, qt_name FROM questiontypes");
$questionTypes = $stmt->fetchAll();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Sanitize and validate inputs
    $question = $_POST['question'] ?? '';
    $answer = $_POST['answer'] ?? '';
    $ca_id = $_POST['ca_id'] ?? '';
    $qt_id = $_POST['qt_id'] ?? '';

    if (!empty($question) && !empty($answer) && !empty($ca_id) && !empty($qt_id)) {
        try {
            // Insert the question and answer into the database
            $stmt = $pdo->prepare("INSERT INTO questions (ca_id, qt_id, text, svar) VALUES (?, ?, ?, ?)");
            $stmt->execute([$ca_id, $qt_id, $question, $answer]);

            echo "<p class='alert alert-success'>Frågan har sparats framgångsrikt!</p>";
        } catch (Exception $e) {
            echo "<p class='alert alert-danger'>Fel vid spara: " . $e->getMessage() . "</p>";
        }
    } else {
        echo "<p class='alert alert-warning'>Fyll i alla fält korrekt.</p>";
    }
}
?>  

<!doctype html>
<html lang="en">
	<head>
		<meta charset="utf-8">
		<title>CKEditor 5 Sample</title>
		<link rel="icon" type="image/png" href="https://ckeditor.com/assets/images/favicons/32x32.png" sizes="32x32">
		<link rel="icon" type="image/png" href="https://ckeditor.com/assets/images/favicons/96x96.png" sizes="96x96">
		<link rel="apple-touch-icon" type="image/png" href="https://ckeditor.com/assets/images/favicons/120x120.png" sizes="120x120">
		<link rel="apple-touch-icon" type="image/png" href="https://ckeditor.com/assets/images/favicons/152x152.png" sizes="152x152">
		<link rel="apple-touch-icon" type="image/png" href="https://ckeditor.com/assets/images/favicons/167x167.png" sizes="167x167">
		<link rel="apple-touch-icon" type="image/png" href="https://ckeditor.com/assets/images/favicons/180x180.png" sizes="180x180">
		<link rel="stylesheet" href="style.css">
		<link rel="stylesheet" href="ckeditor5/ckeditor5.css">


  <!-- MathJax -->
  <script src="https://cdn.jsdelivr.net/npm/mathjax@3/es5/tex-mml-chtml.js" defer></script>	

	</head>
	<body>
	<h1>Matematisk Frågeredigerare</h1>
	<form method="post" action="preview.php" id="questionForm">
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

    <button type="submit" class="btn btn-primary">Förhandsgranska</button>
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
		<!-- A friendly reminder to run on a server, remove this during the integration. -->
		<script>
			window.onload = function() {
				if ( window.location.protocol === "file:" ) {
					alert( "This sample requires an HTTP server. Please serve this file with a web server." );
				}
			};
		</script>
		
<?php
require_once "include/footer.php";
?>	
