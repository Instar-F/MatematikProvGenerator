<?php
require_once "include/header.php";
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

<?php
require_once "include/footer.php";
?>	
