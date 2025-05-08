<?php
$question = $_POST['question'] ?? '';
$answer = $_POST['answer'] ?? '';
$points = $_POST['points'] ?? '';
$difficulty = $_POST['difficulty'] ?? '';
?>

<!DOCTYPE html>
<html lang="sv">
<head>
  <meta charset="UTF-8">
  <title>Lägg till fråga</title>

  <!-- CKEditor 5 CDN -->
  <script src="https://cdn.ckeditor.com/ckeditor5/39.0.1/classic/ckeditor.js"></script>

  <!-- MathJax -->
  <script src="https://cdn.jsdelivr.net/npm/mathjax@3/es5/tex-mml-chtml.js"></script>

  <style>
    body {
      font-family: sans-serif;
      padding: 2rem;
    }
    textarea {
      width: 100%;
      height: 120px;
    }
    .form-section {
      margin-bottom: 1rem;
    }
    .preview {
      padding: 1rem;
      margin-top: 2rem;
      background: #f9f9f9;
      border: 1px solid #ccc;
    }
    .error {
      background-color: #f8d7da;
      color: #721c24;
      border: 1px solid #f5c6cb;
      padding: 1rem;
      margin-bottom: 1rem;
    }
    button {
      padding: 0.5rem 1rem;
      margin-right: 1rem;
    }
  </style>
</head>
<body>

  <h1>Lägg till fråga</h1>

  <form method="POST" enctype="multipart/form-data">
    <div class="form-section">
      <label for="question">Fråga:</label><br>
      <textarea name="question" id="question"><?= htmlspecialchars($question) ?></textarea>
    </div>

    <div class="form-section">
      <label for="answer">Svar:</label><br>
      <textarea name="answer" id="answer"><?= htmlspecialchars($answer) ?></textarea>
    </div>

    <div class="form-section">
      <label for="image">Ladda upp en bild:</label><br>
      <input type="file" name="image" id="image">
    </div>

    <div class="form-section">
      <label for="points">Poäng för frågan:</label><br>
      <input type="number" name="points" id="points" value="<?= htmlspecialchars($points) ?>">
    </div>

    <div class="form-section">
      <label for="difficulty">Svårighetsgrad (1-6):</label><br>
      <input type="number" name="difficulty" id="difficulty" min="1" max="6" value="<?= htmlspecialchars($difficulty) ?>">
    </div>

    <button type="button" id="previewButton">Förhandsgranska</button>
    <button type="submit">Spara till databas</button>
  </form>

  <!-- Preview Section -->
  <div id="previewCard" class="preview" style="display: none;">
    <h2>Förhandsgranskning</h2>
    <div id="previewError" class="error" style="display: none;">
      Du måste skriva något i antingen frågan eller svaret för att förhandsgranska.
    </div>

    <h3>Fråga:</h3>
    <div id="previewQuestion"></div><br>

    <h3>Svar:</h3>
    <div id="previewAnswer"></div>
  </div>

  <!-- JavaScript -->
  <script>
    let questionEditor, answerEditor;

    ClassicEditor
      .create(document.querySelector('#question'))
      .then(editor => { questionEditor = editor; })
      .catch(error => { console.error(error); });

    ClassicEditor
      .create(document.querySelector('#answer'))
      .then(editor => { answerEditor = editor; })
      .catch(error => { console.error(error); });

    document.getElementById('previewButton').addEventListener('click', function () {
      const question = questionEditor.getData().trim();
      const answer = answerEditor.getData().trim();

      const previewCard = document.getElementById('previewCard');
      const previewQuestion = document.getElementById('previewQuestion');
      const previewAnswer = document.getElementById('previewAnswer');
      const previewError = document.getElementById('previewError');

      previewCard.style.display = 'block';

      if (question === '' && answer === '') {
        previewError.style.display = 'block';
        previewQuestion.innerHTML = '';
        previewAnswer.innerHTML = '';
      } else {
        previewError.style.display = 'none';
        previewQuestion.innerHTML = question;
        previewAnswer.innerHTML = answer;
        MathJax.typeset();
      }
    });
  </script>

</body>
</html>
