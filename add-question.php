<?php
require_once "include/header.php";
?>

<div class="container-fluid mt-5">
    <div class="row">
        <!-- Sidebar with links -->
        <div class="col-md-4 ps-0">
            <?php require_once "sidebar.php"; ?>
        </div>
        <!-- Main content -->
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

                <form method="post" enctype="multipart/form-data">
                    <!-- Fråga -->
                    <div class="mb-3">
                        <label for="question" class="form-label">Fråga:</label>
                        <textarea name="question" id="question" class="form-control" rows="5"></textarea>
                    </div>

                    <!-- Svar -->
                    <div class="mb-3">
                        <label for="answer" class="form-label">Svar:</label>
                        <textarea name="answer" id="answer" class="form-control" rows="5"></textarea>
                    </div>

                    <!-- Bild -->
                    <div class="mb-3">
                        <label for="image" class="form-label">Ladda upp en bild:</label>
                        <input type="file" name="image" id="image" class="form-control">
                    </div>

                    <!-- Poäng -->
                    <div class="mb-3">
                        <label for="points" class="form-label">Poäng för frågan:</label>
                        <input type="number" name="points" id="points" class="form-control">
                    </div>

                    <!-- Svårighetsgrad -->
                    <div class="mb-3">
                        <label for="difficulty" class="form-label">Svårighetsgrad (1-6):</label>
                        <input type="number" name="difficulty" id="difficulty" class="form-control" min="1" max="6">
                    </div>

                    <!-- Buttons -->
                    <div class="mb-4">
                        <button type="button" id="previewButton" class="btn btn-primary">Förhandsgranska</button>
                        <button type="submit" name="save" class="btn btn-success">Spara till databas</button>
                    </div>
                </form>

                <!-- Preview Section -->
                <div id="previewSection" class="card shadow-lg p-4 mt-5" style="display: none;">
                    <h2 class="mb-4 text-center">Förhandsgranskning</h2>
                    <div id="previewError" class="alert alert-danger" style="display: none;">
                        Du måste skriva något i antingen frågan eller svaret för att förhandsgranska.
                    </div>
                    <div class="preview">
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
</div>

<script>
    CKEDITOR.replace('question');
    CKEDITOR.replace('answer');

    document.getElementById('previewButton').addEventListener('click', function () {
        const questionEditor = CKEDITOR.instances.question;
        const answerEditor = CKEDITOR.instances.answer;

        const question = questionEditor.getData().trim();
        const answer = answerEditor.getData().trim();

        const previewSection = document.getElementById('previewSection');
        const previewError = document.getElementById('previewError');
        const previewQuestion = document.getElementById('previewQuestion');
        const previewAnswer = document.getElementById('previewAnswer');

        if (!question && !answer) {
            previewSection.style.display = 'block';
            previewError.style.display = 'block';
            previewQuestion.innerHTML = '';
            previewAnswer.innerHTML = '';
        } else {
            previewError.style.display = 'none';
            previewQuestion.innerHTML = question;
            previewAnswer.innerHTML = answer;
            previewSection.style.display = 'block';

            if (window.MathJax) {
                MathJax.typesetPromise();
            }
        }
    });
</script>

<?php require_once "include/footer.php"; ?>
