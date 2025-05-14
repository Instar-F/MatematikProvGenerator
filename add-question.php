<?php
require_once "include/header.php";

if (!$user_obj->checkLoginStatus($_SESSION['user']['id'])) {
    header("Location: login.php");
    exit();
}

$result = $user_obj->checkUserRole($_SESSION['user']['role'], 100);

if (!$result) {
    echo "You do not have the rights to access this page.";
    exit(); // Stop the script from continuing
}

$courses = $pdo->query("SELECT co_id, co_name FROM courses")->fetchAll();
$categories = $pdo->query("SELECT ca_id, ca_name, ca_co_fk FROM categories")->fetchAll();

$question = $_POST['question'] ?? '';
$answer = $_POST['answer'] ?? '';
$points = $_POST['points'] ?? '';
$difficulty = $_POST['difficulty'] ?? '';
$ca_id = $_POST['ca_id'] ?? '';
$co_id = $_POST['co_id'] ?? '';
$image_url = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
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

    if (!empty($question) && !empty($answer) && !empty($ca_id) && is_numeric($points) && is_numeric($difficulty) && $difficulty >= 1 && $difficulty <= 6) {
        try {
            $stmt = $pdo->prepare("INSERT INTO questions (ca_id, text, answer, image_url, total_points, difficulty, teacher_fk) VALUES (?, ?, ?, ?, ?, ?, ?)");
            $stmt->execute([$ca_id, $question, $answer, $image_url, $points, $difficulty, $_SESSION['user']['id']]);
            echo "<p class='alert alert-success'>Frågan har sparats framgångsrikt!</p>";
        } catch (Exception $e) {
            echo "<p class='alert alert-danger'>Fel vid spara: " . $e->getMessage() . "</p>";
        }
    } else {
        echo "<p class='alert alert-warning'>Fyll i alla fält korrekt.</p>";
    }
}
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

<div class="container-fluid page-centered-container mt-4">
    <div class="row" id="contentRow">
        <!-- Sidebar -->
        <div class="col-md ps-0" id="sidebarColumn">
            <?php require_once "sidebar.php"; ?>
        </div>
        <!-- Main content -->
        <div class="col-md" id="mainColumn">
            <div class="container mt-5">
                <div class="card shadow-lg">
                    <div class="card-header">
                        Lägg till fråga
                    </div>
                    <div class="card-body">
                        <form method="POST" enctype="multipart/form-data">
                            <div class="mb-3">
                                <label for="course" class="form-label">Kurs:</label>
                                <select name="co_id" id="course" class="form-control" onchange="filterCategories()">
                                    <option value="">Välj en kurs</option>
                                    <?php foreach ($courses as $course): ?>
                                        <option value="<?= $course['co_id']; ?>" <?= $course['co_id'] == $co_id ? 'selected' : '' ?>>
                                            <?= htmlspecialchars($course['co_name']); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>

                            <div class="mb-3">
                                <label for="category" class="form-label">Kategori:</label>
                                <select name="ca_id" id="category" class="form-control">
                                    <option value="">Välj en kategori</option>
                                    <?php foreach ($categories as $category): ?>
                                        <option value="<?= $category['ca_id']; ?>" data-course="<?= $category['ca_co_fk']; ?>" <?= $category['ca_id'] == $ca_id ? 'selected' : '' ?>>
                                            <?= htmlspecialchars($category['ca_name']); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>

                            <div class="mb-3">
                                <label for="question" class="form-label">Fråga:</label>
                                <textarea name="question" id="question"><?= htmlspecialchars($question) ?></textarea>
                            </div>

                            <div class="mb-3">
                                <label for="answer" class="form-label">Svar:</label>
                                <textarea name="answer" id="answer"><?= htmlspecialchars($answer) ?></textarea>
                            </div>

                            <div class="mb-3">
                                <label for="image" class="form-label">Ladda upp en bild:</label>
                                <input type="file" name="image" id="image" class="form-control">
                            </div>

                            <div class="mb-3">
                                <label for="points" class="form-label">Poäng för frågan:</label>
                                <input type="number" name="points" id="points" class="form-control" value="<?= htmlspecialchars($points) ?>">
                            </div>

                            <div class="mb-3">
                                <label for="difficulty" class="form-label">Svårighetsgrad (1–6):</label>
                                <input type="number" name="difficulty" id="difficulty" min="1" max="6" class="form-control" value="<?= htmlspecialchars($difficulty) ?>" onkeydown="replaceDifficulty(event, this)">
                            </div>

                            <div class="mb-3 d-flex justify-content-start">
                                <button type="button" id="previewButton" class="btn btn-outline-secondary me-2">Förhandsgranska</button>
                                <button type="submit" class="btn btn-success">Spara till databas</button>
                            </div>
                        </form>
                        <div id="previewCard" class="mt-4 p-3 border rounded" style="display: none; background-color: #f9f9f9;">
                            <h5>Förhandsgranskning</h5>
                            <div id="previewError" class="alert alert-danger" style="display: none;">
                                Du måste skriva något i antingen frågan eller svaret för att förhandsgranska.
                            </div>
                            <h6>Fråga:</h6>
                            <div id="previewQuestion" class="mb-3"></div>
                            <h6>Svar:</h6>
                            <div id="previewAnswer"></div>
                        </div>
                    </div>
                    <div class="card-footer text-muted text-center">
                        <small>MatematikProvGenerator - Lägg till Fråga</small>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- CKEditor -->
<script src="https://cdn.ckeditor.com/ckeditor5/39.0.1/classic/ckeditor.js"></script>
<!-- MathJax -->
<script src="https://cdn.jsdelivr.net/npm/mathjax@3/es5/tex-mml-chtml.js"></script>

<script>
function filterCategories() {
    const selectedCourse = document.getElementById('course').value;
    const categoryOptions = document.querySelectorAll('#category option');
    categoryOptions.forEach(option => {
        option.style.display = option.dataset.course === selectedCourse || option.value === "" ? "block" : "none";
    });
    document.getElementById('category').value = "";
}

function replaceDifficulty(event, input) {
    const allowedKeys = ['Backspace', 'Delete', 'ArrowLeft', 'ArrowRight', 'Tab'];
    if (!allowedKeys.includes(event.key) && !isNaN(event.key)) {
        const newValue = parseInt(event.key, 10);
        if (newValue >= 1 && newValue <= 6) {
            input.value = newValue;
        }
        event.preventDefault();
    }
}

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

<?php require_once "include/footer.php"; ?>
