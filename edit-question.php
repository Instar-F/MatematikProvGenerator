<?php
require_once "include/header.php";

// Check user authentication and role
if (!$user_obj->checkLoginStatus($_SESSION['user']['id'])) {
    header("Location: login.php");
}

$result = $user_obj->checkUserRole($_SESSION['user']['role'], 100);
if (!$result) {
    echo "You do not have the rights to access this page.";
    exit();
}

// Get the question ID from URL
$questionId = isset($_GET['id']) ? (int)$_GET['id'] : null;
if (!$questionId) {
    die('Invalid question ID.');
}

// Fetch the question
$stmt = $pdo->prepare("SELECT * FROM matteprovgenerator.questions WHERE qu_id = ?");
$stmt->execute([$questionId]);
$question = $stmt->fetch(PDO::FETCH_ASSOC);
if (!$question) {
    die('Question not found.');
}

// Fetch categories and question types
$categories = $pdo->query("SELECT ca_id, ca_name FROM categories")->fetchAll();
$questionTypes = $pdo->query("SELECT qt_id, qt_name FROM questiontypes")->fetchAll();

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $text = $_POST['question'] ?? '';
    $answer = $_POST['answer'] ?? '';
    $total_points = (int)($_POST['total_points'] ?? 0);
    $ca_id = $_POST['ca_id'] ?? null;
    $qt_id = $_POST['qt_id'] ?? null;
    $image_url = $question['image_url']; // Default to the existing image

    // Handle image upload
    if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
        $uploadDir = __DIR__ . '/uploads/';
        $webPathBase = 'uploads/';
        $tmpFile = $_FILES['image']['tmp_name'];
        $originalName = $_FILES['image']['name'];
        $fileExt = strtolower(pathinfo($originalName, PATHINFO_EXTENSION));
        $fileType = mime_content_type($tmpFile);
        $allowedTypes = ['image/jpeg', 'image/png', 'image/gif'];
        $allowedExts = ['jpg', 'jpeg', 'png', 'gif'];

        if (in_array($fileType, $allowedTypes) && in_array($fileExt, $allowedExts)) {
            $uniqueName = uniqid('img_', true) . '.' . $fileExt;
            $uploadFile = $uploadDir . $uniqueName;
            $webPath = $webPathBase . $uniqueName;
            if (move_uploaded_file($tmpFile, $uploadFile)) {
                $image_url = $webPath; // Update the image URL
            } else {
                echo "<p class='alert alert-danger'>Failed to upload image.</p>";
            }
        } else {
            echo "<p class='alert alert-danger'>Invalid file type. Only JPG, PNG, and GIF are allowed.</p>";
        }
    }

    if ($ca_id && $qt_id && !empty($text) && !empty($answer)) {
        $updateStmt = $pdo->prepare("UPDATE matteprovgenerator.questions SET ca_id = ?, qt_id = ?, text = ?, answer = ?, total_points = ?, image_url = ? WHERE qu_id = ?");
        $updateStmt->execute([$ca_id, $qt_id, $text, $answer, $total_points, $image_url, $questionId]);

        header("Location: assignments.php?course_id={$_POST['course_id']}&ca_id={$ca_id}");
        exit;
    } else {
        echo "<p class='alert alert-warning'>Fyll i alla fält korrekt.</p>";
    }
}

// Determine the previous page for highlighting in the sidebar
$previousPage = isset($_SERVER['HTTP_REFERER']) ? basename(parse_url($_SERVER['HTTP_REFERER'], PHP_URL_PATH)) : '';
?>

<div class="container-fluid mt-5">
    <div class="row">
        <!-- Sidebar with links -->
        <div class="col-md-4 ps-0">
            <?php
            $currentPage = $previousPage; // Use the previous page for highlighting
            require_once "sidebar.php";
            ?>
        </div>
        <!-- Main content -->
        <div class="col-md-8">
            <div class="container py-5">
                <h1 class="mb-4">Redigera Fråga</h1>

                <form method="post" enctype="multipart/form-data">
                    <div class="form-group mb-3">
                        <label for="category">Kategori:</label>
                        <select name="ca_id" id="category" class="form-control">
                            <?php foreach ($categories as $category): ?>
                                <option value="<?= $category['ca_id']; ?>" <?= $category['ca_id'] == $question['ca_id'] ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($category['ca_name']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="form-group mb-3">
                        <label for="questiontype">Frågetyp:</label>
                        <select name="qt_id" id="questiontype" class="form-control">
                            <?php foreach ($questionTypes as $qt): ?>
                                <option value="<?= $qt['qt_id']; ?>" <?= $qt['qt_id'] == $question['qt_id'] ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($qt['qt_name']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="form-group mb-3">
                        <label for="question">Fråga:</label>
                        <textarea name="question" id="question" class="form-control"><?= htmlspecialchars($question['text']); ?></textarea>
                    </div>

                    <div class="form-group mb-3">
                        <label for="answer">Svar:</label>
                        <textarea name="answer" id="answer" class="form-control"><?= htmlspecialchars($question['answer']); ?></textarea>
                    </div>

                    <div class="form-group mb-3">
                        <label for="total_points">Poäng:</label>
                        <input type="number" name="total_points" id="total_points" class="form-control" min="0" step="1" value="<?= htmlspecialchars($question['total_points']); ?>" required>
                    </div>

                    <div class="form-group mb-3">
                        <label for="image">Bild:</label>
                        <?php if (!empty($question['image_url'])): ?>
                            <div class="mb-2">
                                <img src="<?= htmlspecialchars($question['image_url']); ?>" alt="Question Image" class="img-fluid" style="max-height: 200px;">
                            </div>
                        <?php endif; ?>
                        <input type="file" name="image" id="image" class="form-control">
                    </div>

                    <input type="hidden" name="course_id" value="<?= $_GET['course_id'] ?? 0 ?>">

                    <button type="submit" class="btn btn-success">Spara ändringar</button>
                </form>
            </div>
        </div>
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

<?php require_once "include/footer.php"; ?>
