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

// Prefill fields
$prefillText = $_SERVER['REQUEST_METHOD'] === 'GET' ? $question['text'] : '';
$prefillAnswer = $_SERVER['REQUEST_METHOD'] === 'GET' ? $question['answer'] : '';

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $text = $_POST['question'] ?? '';
    $answer = $_POST['answer'] ?? '';
    $total_points = (int)($_POST['total_points'] ?? 0);
    $ca_id = $_POST['ca_id'] ?? null;
    $qt_id = $_POST['qt_id'] ?? null;
    $image_url = $question['image_url']; // Default to existing image

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
                $image_url = $webPath;
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

// Determine the previous page
$previousPage = isset($_SERVER['HTTP_REFERER']) ? basename(parse_url($_SERVER['HTTP_REFERER'], PHP_URL_PATH)) : '';
?>

<style>
#sidebarColumn {
    position: fixed;
    top: 0;
    left: 0;
    width: 320px;
    height: 100vh;
    background: #fff;
    z-index: 2000;
    box-shadow: 2px 0 16px rgba(0,0,0,0.12);
    transform: translateX(-100%);
    opacity: 0;
    transition: transform 0.28s cubic-bezier(.4,0,.2,1), opacity 0.18s cubic-bezier(.4,0,.2,1);
    will-change: transform, opacity;
}
#sidebarColumn.visible {
    transform: translateX(0);
    opacity: 1;
}
#mainColumn {
    transition: none;
}
.page-centered-container {
    max-width: 1100px;
    margin: 0 auto;
    padding: 32px 16px 32px 16px;
}
#toggleSidebar {
    position: fixed;
    top: 50%;
    left: 0;
    transform: translateY(-50%);
    z-index: 2100;
    border-radius: 50%;
    width: 44px;
    height: 44px;
    padding: 0;
    display: flex;
    align-items: center;
    justify-content: center;
    background: #fff;
    border: 2px solid #0d6efd;
    color: #0d6efd;
    box-shadow: 0 2px 8px rgba(0,0,0,0.15);
    transition: left 0.28s cubic-bezier(.4,0,.2,1), background 0.18s, color 0.18s;
    cursor: pointer;
    font-size: 2rem;
}
#toggleSidebar.open {
    left: 320px;
}
#toggleSidebar.closed {
    left: 0;
}
#toggleSidebar:hover {
    background: #e7f1ff;
    color: #0a58ca;
}
#toggleArrow {
    transition: transform 0.25s cubic-bezier(.4,0,.2,1);
    font-size: 2rem;
    line-height: 1;
    display: flex;
    align-items: center;
    justify-content: center;
    font-family: inherit;
}
#sidebarOverlay {
    display: none;
    position: fixed;
    z-index: 1999;
    top: 0;
    left: 0;
    width: 100vw;
    height: 100vh;
    background: rgba(0,0,0,0.18);
    transition: opacity 0.18s;
}
#sidebarOverlay.visible {
    display: block;
    opacity: 1;
}
</style>

<button id="toggleSidebar" aria-label="Toggle Sidebar" type="button" class="closed">
    <span id="toggleArrow">+</span>
</button>
<div id="sidebarOverlay"></div>

<div class="container-fluid page-centered-container">
    <div class="row" id="contentRow">
        <!-- Sidebar -->
        <div class="col-md ps-0" id="sidebarColumn">
            <?php
            // Set $currentPage to previous page if not a sidebar page
            $sidebarPages = [
                'dashboard.php',
                'user-management.php',
                'test-list.php',
                'assignments.php',
                'add-question.php',
                'add-category.php',
                'add-course.php',
                'create-user.php',
                // add other sidebar-linked pages here
            ];
            $currentPage = in_array(basename($_SERVER['PHP_SELF']), $sidebarPages)
                ? basename($_SERVER['PHP_SELF'])
                : $previousPage;
            require_once "sidebar.php";
            ?>
        </div>
        <!-- Main content -->
        <div class="col-md" id="mainColumn">
            <div class="container py-5">
                <h1 class="page-title">Redigera Fråga</h1>

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
                        <label for="question">Fråga:</label>
                        <textarea name="question" id="question" class="form-control"><?= htmlspecialchars_decode($prefillText); ?></textarea>
                    </div>

                    <div class="form-group mb-3">
                        <label for="answer">Svar:</label>
                        <textarea name="answer" id="answer" class="form-control"><?= htmlspecialchars_decode($prefillAnswer); ?></textarea>
                    </div>

                    <div class="form-group mb-3">
                        <label for="total_points">Poäng:</label>
                        <input type="number" name="total_points" id="total_points" class="form-control" min="0" step="1" value="<?= htmlspecialchars($question['total_points']); ?>" required>
                    </div>

                    <div class="form-group mb-3">
                        <label for="image">Bild:</label>
                        <?php if (!empty($question['image_url'])): ?>
                            <div class="image-preview">
                                <img src="<?= htmlspecialchars($question['image_url']); ?>" alt="Question Image" class="img-fluid">
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

<script>
const sidebar = document.getElementById('sidebarColumn');
const main = document.getElementById('mainColumn');
const toggleBtn = document.getElementById('toggleSidebar');
const toggleArrow = document.getElementById('toggleArrow');
const overlay = document.getElementById('sidebarOverlay');
let sidebarVisible = false;

function showSidebar() {
    sidebar.classList.add('visible');
    overlay.classList.add('visible');
    toggleArrow.textContent = "-";
    toggleBtn.classList.remove('closed');
    toggleBtn.classList.add('open');
}

function hideSidebar() {
    sidebar.classList.remove('visible');
    overlay.classList.remove('visible');
    toggleArrow.textContent = "+";
    toggleBtn.classList.remove('open');
    toggleBtn.classList.add('closed');
}

toggleBtn.addEventListener('click', function () {
    sidebarVisible = !sidebarVisible;
    if (sidebarVisible) {
        showSidebar();
    } else {
        hideSidebar();
    }
});

overlay.addEventListener('click', function () {
    sidebarVisible = false;
    hideSidebar();
});

hideSidebar();
</script>

<!-- Import CKEditor and delay init to fix disappearing bug -->
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
    // Delay CKEditor initialization to ensure textareas are visible
    setTimeout(() => {
        if (typeof window.initCkEditor === 'function') {
            window.initCkEditor();
        }
    }, 100);
</script>

<?php require_once "include/footer.php"; ?>
