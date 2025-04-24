<?php
$content = $_POST['question'] ?? '';
?>

<!DOCTYPE html>
<html lang="sv">
<head>
  <meta charset="UTF-8">
  <title>Förhandsgranskning</title>

  <!-- MathJax -->
  <script src="https://cdn.jsdelivr.net/npm/mathjax@3/es5/tex-mml-chtml.js"></script>

  <style>
    body {
      font-family: sans-serif;
      padding: 2rem;
    }
    .preview {
      padding: 1rem;
      border: 1px solid #ccc;
      background: #f9f9f9;
    }
    img {
      max-width: 100%;
      height: auto;
      display: block;
    }
  </style>
</head>
<body>

  <h1>Förhandsgranskning</h1>
  <div class="preview">
    <?= $content ?>
  </div>

  <script>
    MathJax.typeset(); // Renders math expressions after load
  </script>

</body>
</html>
