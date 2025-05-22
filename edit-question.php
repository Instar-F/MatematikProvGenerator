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

// Prefill fields (do not decode HTML here, keep raw for CKEditor)
$prefillText = $_SERVER['REQUEST_METHOD'] === 'GET' ? $question['text'] : ($_POST['question'] ?? $question['text']);
$prefillAnswer = $_SERVER['REQUEST_METHOD'] === 'GET' ? $question['answer'] : ($_POST['answer'] ?? $question['answer']);

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Strip all HTML tags from input before saving
    $text = trim(strip_tags($_POST['question'] ?? $question['text']));
    $answer = trim(strip_tags($_POST['answer'] ?? $question['answer']));
    $total_points = (int)($_POST['total_points'] ?? $question['total_points']);
    $ca_id = $_POST['ca_id'] ?? $question['ca_id'];
    $qu_id = $_POST['qu_id'] ?? $question['qu_id'];
    $image_url = $question['image_url']; // Default to existing image
    $image_size = $_POST['image_size'] ?? $question['image_size'];
    $image_location = $_POST['image_location'] ?? $question['image_location'];

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

    // Fix: Use $question values as fallback for all fields, not just text/answer
    if ($ca_id && $qu_id && strlen($text) > 0 && strlen($answer) > 0) {
        $updateStmt = $pdo->prepare("UPDATE matteprovgenerator.questions SET ca_id = ?, qu_id = ?, text = ?, answer = ?, total_points = ?, image_url = ?, image_size = ?, image_location = ? WHERE qu_id = ?");
        $updateStmt->execute([
            $ca_id, $qu_id, $text, $answer, $total_points, $image_url, 
            $image_url ? $image_size : null,
            $image_url ? $image_location : null,
            $questionId
        ]);

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
/* Sidebar toggle button styling */
#toggleSidebar {
    position: fixed;
    top: 38%; /* Move above the vertical center */
    left: 0;
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
    display: flex;
    flex-direction: column;
    justify-content: center;
    align-items: center;
    width: 22px;
    height: 22px;
    font-size: 1.7rem;
    transition: color 0.18s;
    position: relative;
}
.hamburger-bar {
    width: 22px;
    height: 3px;
    background: #0d6efd;
    margin: 2.5px 0;
    border-radius: 2px;
    transition: all 0.25s;
}
#toggleSidebar.open .hamburger-bar {
    display: none;
}
.sidebar-close-icon {
    display: none;
    font-size: 1.7rem;
    color: #0d6efd;
    line-height: 1;
    width: 100%;
    height: 100%;
    align-items: center;
    justify-content: center;
}
#toggleSidebar.open .sidebar-close-icon {
    display: flex;
    align-items: center;
    justify-content: center;
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
    <span id="toggleArrow">
        <span class="hamburger-bar"></span>
        <span class="hamburger-bar"></span>
        <span class="hamburger-bar"></span>
        <span class="sidebar-close-icon">
            <svg width="22" height="22" viewBox="0 0 22 22" fill="none" style="display:block;margin:auto;" xmlns="http://www.w3.org/2000/svg">
                <rect x="6" y="10" width="10" height="2" rx="1" fill="#0d6efd"/>
            </svg>
        </span>
    </span>
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
                        <textarea name="question" id="question" class="form-control"><?= htmlspecialchars($prefillText); ?></textarea>
                    </div>

                    <div class="form-group mb-3">
                        <label for="answer">Svar:</label>
                        <textarea name="answer" id="answer" class="form-control"><?= htmlspecialchars($prefillAnswer); ?></textarea>
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
                    <div class="form-group mb-3">
                        <label for="image_size">Bildstorlek (% av container):</label>
                        <input type="number" name="image_size" id="image_size" class="form-control" min="1" max="100" value="<?= htmlspecialchars($question['image_size'] ?? '') ?>">
                        <small class="form-text text-muted">Ange en procentsats, t.ex. 25 för 25%.</small>
                    </div>
                    <div class="form-group mb-3">
                        <label for="image_location">Bildens placering:</label>
                        <select name="image_location" id="image_location" class="form-control">
                            <option value="">Välj placering</option>
                            <option value="1" <?= ($question['image_location'] ?? '') == '1' ? 'selected' : '' ?>>Höger om frågan</option>
                            <option value="2" <?= ($question['image_location'] ?? '') == '2' ? 'selected' : '' ?>>Vänster om frågan</option>
                            <option value="3" <?= ($question['image_location'] ?? '') == '3' ? 'selected' : '' ?>>Ovanför frågan</option>
                            <option value="4" <?= ($question['image_location'] ?? '') == '4' ? 'selected' : '' ?>>Under frågan</option>
                        </select>
                        <small class="form-text text-muted">
                            1 = Höger, 2 = Vänster, 3 = Ovanför, 4 = Under
                        </small>
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
const overlay = document.getElementById('sidebarOverlay');
let sidebarVisible = false;

function showSidebar() {
    sidebar.classList.add('visible');
    overlay.classList.add('visible');
    toggleBtn.classList.remove('closed');
    toggleBtn.classList.add('open');
}

function hideSidebar() {
    sidebar.classList.remove('visible');
    overlay.classList.remove('visible');
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

<!-- CKEditor -->
<script src="https://cdn.ckeditor.com/ckeditor5/39.0.1/classic/ckeditor.js"></script>
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

// Optional: If you want to strip HTML tags before submitting (client-side as well)
document.querySelector('form').addEventListener('submit', function(e) {
    // Get plain text from CKEditor before submit
    if (questionEditor && answerEditor) {
        document.querySelector('#question').value = questionEditor.getData().replace(/<[^>]+>/g, '').trim();
        document.querySelector('#answer').value = answerEditor.getData().replace(/<[^>]+>/g, '').trim();
    }
});
</script>

<?php require_once "include/footer.php"; ?>
