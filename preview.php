<?php
require_once "include/header.php";
$content = $_POST['question'] ?? '';
?>


<head>
  <meta charset="UTF-8">
  <title>Förhandsgranskning</title>

  <!-- MathJax -->
  <script src="https://cdn.jsdelivr.net/npm/mathjax@3/es5/tex-mml-chtml.js"></script>

  <style>
  </style>
</head>

  <h1>Förhandsgranskning</h1>
  <div class="preview">
    <?= $content ?>
  </div>

  <script>
    MathJax.typeset(); // Renders math expressions after load
  </script>


<?php
require_once "include/footer.php";
?>	
