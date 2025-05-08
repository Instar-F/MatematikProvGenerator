<?php
require_once "include/header.php";

$courses = $pdo->query("SELECT co_id, co_name FROM courses")->fetchAll();
$categories = $pdo->query("SELECT ca_id, ca_name, ca_co_fk FROM categories")->fetchAll();
$questionTypes = $pdo->query("SELECT qt_id, qt_name FROM questiontypes")->fetchAll();
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
                        <textarea name="question" id="question" class="form-control"></textarea>
                    </div>

                    <div class="form-group mb-3">
                        <label for="answer">Svar:</label>
                        <textarea name="answer" id="answer" class="form-control"></textarea>
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
                        <input type="number" name="difficulty" id="difficulty" class="form-control" min="1" max="6" step="1" required>
                    </div>

                    <div class="d-flex justify-content-between">
                        <button type="button" id="previewButton" class="btn btn-primary">Förhandsgranska</button>
                        <button type="submit" name="save" class="btn btn-success">Spara till databas</button>
                    </div>
                </form>
            </div>

            <!-- Preview Section -->
            <div class="card shadow-lg p-4 mt-5 d-none" id="previewCard">
                <h2 class="mb-4 text-center">Förhandsgranskning</h2>
                <div class="preview">
                    <div id="previewError" class="alert alert-danger d-none">Du måste skriva något i antingen frågan eller svaret för att förhandsgranska.</div>

                    <h3>Fråga:</h3>
                    <div id="previewQuestion"></div>
                    <br>
                    <h3>Svar:</h3>
                    <div id="previewAnswer"></div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- MathJax -->
<script src="https://cdn.jsdelivr.net/npm/mathjax@3/es5/tex-mml-chtml.js"></script>

<script>
document.getElementById('previewButton').addEventListener('click', function () {
    const question = document.getElementById('question').value.trim();
    const answer = document.getElementById('answer').value.trim();
    const previewCard = document.getElementById('previewCard');
    const previewQuestion = document.getElementById('previewQuestion');
    const previewAnswer = document.getElementById('previewAnswer');
    const previewError = document.getElementById('previewError');

    if (question === '' && answer === '') {
        previewCard.classList.remove('d-none');
        previewError.classList.remove('d-none');
        previewQuestion.innerHTML = '';
        previewAnswer.innerHTML = '';
    } else {
        previewError.classList.add('d-none');
        previewCard.classList.remove('d-none');
        previewQuestion.textContent = question;
        previewAnswer.textContent = answer;
        MathJax.typeset(); // Render math
    }
});
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