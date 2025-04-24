<?php
require_once "include/header.php";
?>	


	<h1>Matematisk Frågeredigerare</h1>
  <form method="post" action="preview.php">
    <textarea name="question" id="editor"></textarea>
    <br>
    <input type="submit" value="Förhandsgranska">
  </form>

		<div class="main-container">
			<div class="editor-container editor-container_classic-editor" id="editor-container">
				<div class="editor-container__editor"><div id="editor"></div></div>
			</div>
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
