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

$question = '';
$answer = '';
$points = '';
$difficulty = '';
$ca_id = '';
$co_id = '';
$image_url = null;
$image_size = '';
$image_location = '';

if (isset($_GET['id'])) {
    $questionId = (int)$_GET['id'];
    $stmt = $pdo->prepare("SELECT * FROM questions WHERE qu_id = ?");
    $stmt->execute([$questionId]);
    $existingQuestion = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$existingQuestion) {
        die('Question not found.');
    }

    // Pre-fill form values from existing data
    $question = $_POST['question'] ?? $existingQuestion['text'];
    $answer = $_POST['answer'] ?? $existingQuestion['answer'];
    $points = $_POST['points'] ?? $existingQuestion['total_points'];
    $difficulty = $_POST['difficulty'] ?? $existingQuestion['difficulty'];
    $ca_id = $_POST['ca_id'] ?? $existingQuestion['ca_id'];
    $image_url = isset($_POST['delete_image']) ? null : $existingQuestion['image_url'];
    $image_size = $_POST['image_size'] ?? $existingQuestion['image_size'] ?? '50';
    $image_location = $_POST['image_location'] ?? $existingQuestion['image_location'] ?? '';
    $image_align = $_POST['image_align'] ?? $existingQuestion['image_align'] ?? 'center';
    $image_valign = $_POST['image_valign'] ?? $existingQuestion['image_valign'] ?? 'center';
} else {
    die('No question ID provided.');
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!empty($_POST['question']) && !empty($_POST['answer'])) {
        try {
            // Create uploads directory if it doesn't exist
            $uploadDir = __DIR__ . '/uploads/';
            if (!file_exists($uploadDir)) {
                mkdir($uploadDir, 0777, true);
                chmod($uploadDir, 0777);
            }

            // Handle image deletion on form submission
            if (isset($_POST['delete_image']) && $_POST['delete_image'] === '1' && $existingQuestion['image_url']) {
                if (file_exists(__DIR__ . '/' . $existingQuestion['image_url'])) {
                    unlink(__DIR__ . '/' . $existingQuestion['image_url']);
                }
                $image_url = null;
                $image_size = null;
                $image_location = null;
                $image_align = null;
                $image_valign = null;
            } else {
                // Keep existing image settings if not uploading new image
                $image_size = $_POST['image_size'] ?? $existingQuestion['image_size'];
                $image_location = $_POST['image_location'] ?? $existingQuestion['image_location'];
                $image_align = $_POST['image_align'] ?? $existingQuestion['image_align'];
                $image_valign = $_POST['image_valign'] ?? $existingQuestion['image_valign'];
            }

            // Handle image upload if new image is provided
            if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
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
                    
                    // Delete old image if exists
                    if ($existingQuestion['image_url'] && file_exists(__DIR__ . '/' . $existingQuestion['image_url'])) {
                        unlink(__DIR__ . '/' . $existingQuestion['image_url']);
                    }
                    
                    if (move_uploaded_file($tmpFile, $uploadFile)) {
                        $image_url = $webPath;
                        // Keep the submitted image settings for new uploads
                        $image_size = $_POST['image_size'];
                        $image_location = $_POST['image_location'];
                        $image_align = $_POST['image_align'];
                        $image_valign = $_POST['image_valign'];
                    } else {
                        echo "<p class='alert alert-danger'>Failed to upload image.</p>";
                    }
                }
            }

            // Update question in database
            $stmt = $pdo->prepare("
                UPDATE questions 
                SET ca_id = ?, text = ?, answer = ?, total_points = ?, difficulty = ?,
                    image_url = ?, image_size = ?, image_location = ?, image_align = ?, image_valign = ?
                WHERE qu_id = ?
            ");
            $stmt->execute([
                $ca_id,
                $_POST['question'],
                $_POST['answer'],
                $_POST['points'],
                $_POST['difficulty'],
                $image_url,
                $image_size,
                $image_location,
                $image_align,
                $image_valign,
                $questionId
            ]);
            echo "<p class='alert alert-success'>Question updated successfully!</p>";

            // Refresh page to show updated data
            header("Location: edit-question.php?id=" . $questionId);
            exit;
        } catch (Exception $e) {
            echo "<p class='alert alert-danger'>Error updating question: " . $e->getMessage() . "</p>";
        }
    } else {
        echo "<p class='alert alert-warning'>Please fill in all required fields.</p>";
    }
}
?>

<style>
/* Clean up main layout */
.page-centered-container {
    max-width: 1400px;
    margin: 0 auto;
    padding: 32px 16px;
}

/* Preview styling to match single-test */
.preview-panel {
    background: white;
    padding: 2rem;
    border-radius: 6px;
    box-shadow: 0 0 10px rgba(0,0,0,0.1);
}

.preview-page {
    width: 100%;
    background: white;
}

.preview-content {
    font-size: 11pt;
    line-height: 1.6;
}

/* Question preview styling */
.question-preview-header {
    display: flex;
    justify-content: space-between;
    margin-bottom: 2rem;
    font-size: 1.1rem;
    color: #666;
}

.question-preview-item {
    margin-bottom: 2em;
}

.question-preview-points {
    text-align: right;
    margin-top: 0.5rem;
    font-weight: bold;
    color: #666;
}

/* Sidebar styles */
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

/* Toggle button styling */
#toggleSidebar {
    position: fixed;
    top: 38%;
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
        <div class="col-md-3 ps-0" id="sidebarColumn">
            <?php require_once "sidebar.php"; ?>
        </div>
        <!-- Main Content -->
        <div class="col-md-9" id="mainColumn">
            <div class="card shadow-lg">
                <div class="card-header">
                    <h2 class="mb-0">Redigera fråga</h2>
                </div>
                <div class="card-body">
                    <div class="row">
                        <!-- Form Section -->
                        <div class="col-md-6">
                            <form method="POST" enctype="multipart/form-data" id="questionForm">
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

                                <div class="mb-4">
                                    <label for="question" class="form-label">Fråga:</label>
                                    <textarea name="question" id="question"><?= htmlspecialchars($question) ?></textarea>
                                </div>

                                <div class="mb-4">
                                    <label for="answer" class="form-label">Svar:</label>
                                    <textarea name="answer" id="answer"><?= htmlspecialchars($answer) ?></textarea>
                                </div>

                                <div class="mb-4">
                                    <label for="image" class="form-label">Bild (valfritt):</label>
                                    <input type="file" name="image" id="image" class="form-control" accept="image/*">
                                    <?php if ($image_url): ?>
                                        <div class="mt-2">
                                            <img src="<?= htmlspecialchars($image_url) ?>" alt="Current image" style="max-width: 200px; max-height: 100px;">
                                            <button type="button" class="btn btn-warning btn-sm ms-2" onclick="markImageForDeletion()">
                                                <i class="fas fa-eye-slash"></i> Dölj bild
                                            </button>
                                            <input type="hidden" name="delete_image" id="delete_image" value="0">
                                        </div>
                                    <?php endif; ?>
                                    
                                    <div class="image-controls mt-3" id="imageControls" style="display:none;">
                                        <!-- Location buttons -->
                                        <div class="btn-group w-100 mb-3">
                                            <button type="button" class="btn btn-outline-primary location-btn <?= $image_location === '2' ? 'active' : '' ?>" data-location="2">
                                                <i class="fas fa-arrow-left"></i> Vänster
                                            </button>
                                            <button type="button" class="btn btn-outline-primary location-btn <?= $image_location === '1' ? 'active' : '' ?>" data-location="1">
                                                Höger <i class="fas fa-arrow-right"></i>
                                            </button>
                                            <button type="button" class="btn btn-outline-primary location-btn <?= $image_location === '3' ? 'active' : '' ?>" data-location="3">
                                                <i class="fas fa-arrow-up"></i> Över
                                            </button>
                                            <button type="button" class="btn btn-outline-primary location-btn <?= $image_location === '4' ? 'active' : '' ?>" data-location="4">
                                                Under <i class="fas fa-arrow-down"></i>
                                            </button>
                                        </div>
                                        <input type="hidden" name="image_location" id="image_location" value="<?= htmlspecialchars($image_location) ?>">

                                        <!-- Width controls - always shown -->
                                        <div id="widthControl" class="mb-3">
                                            <label class="form-label d-flex justify-content-between">
                                                Bildbredd: <span id="sizeValue">50%</span>
                                            </label>
                                            <input type="range" class="form-range" name="image_size" id="image_size" 
                                                   min="10" max="50" step="5" value="50">
                                        </div>

                                        <!-- Vertical alignment for left/right -->
                                        <div id="verticalAlignControls" class="mb-3" style="display:none;">
                                            <label class="form-label">Vertikal justering:</label>
                                            <div class="btn-group w-100">
                                                <button type="button" class="btn btn-outline-secondary valign-btn" data-valign="flex-start">
                                                    <i class="fas fa-arrow-up"></i> Topp
                                                </button>
                                                <button type="button" class="btn btn-outline-secondary valign-btn active" data-valign="center">
                                                    <i class="fas fa-arrows-alt-v"></i> Mitten
                                                </button>
                                                <button type="button" class="btn btn-outline-secondary valign-btn" data-valign="flex-end">
                                                    <i class="fas fa-arrow-down"></i> Botten
                                                </button>
                                            </div>
                                        </div>

                                        <!-- Horizontal alignment for top/bottom -->
                                        <div id="alignmentControls" class="mb-3" style="display:none;">
                                            <label class="form-label">Justering:</label>
                                            <div class="btn-group w-100">
                                                <button type="button" class="btn btn-outline-secondary align-btn" data-align="flex-start">
                                                    <i class="fas fa-align-left"></i> Vänster
                                                </button>
                                                <button type="button" class="btn btn-outline-secondary align-btn active" data-align="center">
                                                    <i class="fas fa-align-center"></i> Mitten
                                                </button>
                                                <button type="button" class="btn btn-outline-secondary align-btn" data-align="flex-end">
                                                    <i class="fas fa-align-right"></i> Höger
                                                </button>
                                            </div>
                                        </div>

                                        <input type="hidden" name="image_align" id="image_align" value="<?= htmlspecialchars($image_align ?: 'center') ?>">
                                        <input type="hidden" name="image_valign" id="image_valign" value="<?= htmlspecialchars($image_valign ?: 'center') ?>">
                                    </div>
                                </div>

                                <div class="row mb-4">
                                    <div class="col-6">
                                        <label for="points" class="form-label">Poäng:</label>
                                        <input type="number" name="points" id="points" class="form-control" value="<?= htmlspecialchars($points) ?>">
                                    </div>
                                    <div class="col-6">
                                        <label for="difficulty" class="form-label">Svårighetsgrad (1-6):</label>
                                        <input type="number" name="difficulty" id="difficulty" min="1" max="6" class="form-control" value="<?= htmlspecialchars($difficulty) ?>">
                                    </div>
                                </div>

                                <button type="submit" class="btn btn-success">Spara ändringar</button>
                            </form>
                        </div>

                        <!-- Preview Section -->
                        <div class="col-md-6">
                            <div class="preview-panel">
                                <div class="preview-page">
                                    <div class="question-preview-header">
                                        <div>
                                            <strong>Test Preview</strong><br>
                                            <strong>Namn:</strong> __________________
                                        </div>
                                        <div>
                                            <strong>Fråga:</strong> 1<br>
                                            <strong>Poäng:</strong> <span id="previewPoints">0</span>p
                                        </div>
                                    </div>
                                    <div class="question-preview-item">
                                        <div><strong>Fråga 1:</strong></div>
                                        <div id="questionPreview" class="preview-content"></div>
                                        <div class="question-preview-points">_____/<span class="points-value">0</span>p</div>
                                    </div>
                                </div>
                            </div>
                        </div>
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
    .create(document.querySelector('#question'), {
        removePlugins: ['Markdown'],
        toolbar: ['heading', '|', 'bold', 'italic', '|', 'bulletedList', 'numberedList', '|', 'undo', 'redo']
    })
    .then(editor => {
        questionEditor = editor;
        editor.model.document.on('change:data', () => {
            updatePreview();
        });
        // Only run initial preview after editor is ready
        updatePreview();
    })
    .catch(error => { console.error(error); });

ClassicEditor
    .create(document.querySelector('#answer'), {
        removePlugins: ['Markdown'],
        toolbar: ['heading', '|', 'bold', 'italic', '|', 'bulletedList', 'numberedList', '|', 'undo', 'redo']
    })
    .then(editor => {
        answerEditor = editor;
    })
    .catch(error => { console.error(error); });

// Show/hide image controls based on image presence
function updateImageControls() {
    const controls = document.getElementById('imageControls');
    const imageInput = document.getElementById('image');
    const existingImage = '<?= $image_url ?>';
    
    if ((imageInput.files && imageInput.files[0]) || existingImage) {
        controls.style.display = 'block';
    } else {
        controls.style.display = 'none';
    }
}

// Handle image input changes
document.getElementById('image').addEventListener('change', function() {
    updateImageControls();
    updatePreview();
});

// Initialize image controls and settings
document.addEventListener('DOMContentLoaded', function() {
    const existingImage = '<?= $image_url ?>';
    const location = '<?= $image_location ?>';
    const size = '<?= $image_size ?>';
    const align = '<?= $image_align ?>';
    const valign = '<?= $image_valign ?>';
    
    // Update controls visibility
    updateImageControls();
    
    // Set image location if exists
    if (location) {
        const locationBtn = document.querySelector(`.location-btn[data-location="${location}"]`);
        if (locationBtn) {
            locationBtn.click();
        }
        document.getElementById('image_location').value = location;
    }
    
    // Set image size
    if (size) {
        document.getElementById('image_size').value = size;
        document.getElementById('sizeValue').textContent = size + '%';
    }
    
    // Setup alignment controls based on location
    if (location) {
        const isHorizontal = location === '1' || location === '2';
        document.getElementById('alignmentControls').style.display = isHorizontal ? 'none' : 'block';
        document.getElementById('verticalAlignControls').style.display = isHorizontal ? 'block' : 'none';
        
        // Set alignment buttons
        if (isHorizontal) {
            const valignBtn = document.querySelector(`.valign-btn[data-valign="${valign || 'center'}"]`);
            if (valignBtn) {
                document.querySelectorAll('.valign-btn').forEach(b => b.classList.remove('active'));
                valignBtn.classList.add('active');
                document.getElementById('image_valign').value = valign || 'center';
            }
        } else {
            const alignBtn = document.querySelector(`.align-btn[data-align="${align || 'center'}"]`);
            if (alignBtn) {
                document.querySelectorAll('.align-btn').forEach(b => b.classList.remove('active'));
                alignBtn.classList.add('active');
                document.getElementById('image_align').value = align || 'center';
            }
        }
    }
    
    // Reset delete_image value when page loads
    if (document.getElementById('delete_image')) {
        document.getElementById('delete_image').value = '0';
    }
});

function markImageForDeletion() {
    if (confirm('Är du säker på att du vill dölja bilden?')) {
        document.getElementById('delete_image').value = '1';
        // Hide image preview and controls but don't destroy data
        document.querySelector('.mt-2').style.display = 'none';
        document.getElementById('imageControls').style.display = 'none';
        // Update preview without the image
        updatePreview();
    }
}

// Modify updatePreview to respect temporary image clearing
function updatePreview() {
    if (!questionEditor) return;
    
    const questionContent = questionEditor.getData();
    const imageInput = document.getElementById('image');
    const existingImage = '<?= $image_url ?>';
    const isImageCleared = document.getElementById('delete_image')?.value === '1';
    const imageLocation = document.getElementById('image_location').value;
    const imageSize = document.getElementById('image_size').value;
    const imageAlign = document.getElementById('image_align').value;
    const imageValign = document.getElementById('image_valign').value;
    
    let imgSrc = '';
    if (imageInput.files && imageInput.files[0]) {
        imgSrc = URL.createObjectURL(imageInput.files[0]);
    } else if (existingImage && !isImageCleared) {
        imgSrc = existingImage;
    }

    let previewHtml = '';
    if (imgSrc && imageLocation) {
        if (imageLocation === '1' || imageLocation === '2') {
            const questionWidth = 100 - parseInt(imageSize);
            const flexStyle = `display:flex;align-items:${imageValign};gap:1em;margin:0;`;
            const imgHtml = `<img src="${imgSrc}" style="width:100%;height:auto;">`;
            
            previewHtml = `<div style="${flexStyle}">
                ${imageLocation === '2' 
                    ? `<div style="width:${imageSize}%;">${imgHtml}</div><div style="width:${questionWidth}%;">${questionContent}</div>`
                    : `<div style="width:${questionWidth}%;">${questionContent}</div><div style="width:${imageSize}%;">${imgHtml}</div>`}
            </div>`;
        } else {
            const margin = imageAlign === 'center' ? '0 auto' : 
                          imageAlign === 'flex-end' ? '0 0 0 auto' : '0';
            const imgStyle = `width:${imageSize}%;margin:${margin};display:block;`;
            
            previewHtml = `<div>
                ${imageLocation === '3' ? `<img src="${imgSrc}" style="${imgStyle}">` : ''}
                <div>${questionContent}</div>
                ${imageLocation === '4' ? `<img src="${imgSrc}" style="${imgStyle}">` : ''}
            </div>`;
        }
    } else {
        previewHtml = questionContent;
    }

    document.getElementById('questionPreview').innerHTML = previewHtml;
    if (window.MathJax) {
        MathJax.typesetPromise([document.getElementById('questionPreview')]);
    }
}

// Update location button handler - modify this section
document.querySelectorAll('.location-btn').forEach(btn => {
    btn.addEventListener('click', function() {
        document.querySelectorAll('.location-btn').forEach(b => b.classList.remove('active'));
        this.classList.add('active');
        const location = this.dataset.location;
        const previousLocation = document.getElementById('image_location').value;
        document.getElementById('image_location').value = location;
        
        const isHorizontal = location === '1' || location === '2';
        const wasHorizontal = previousLocation === '1' || previousLocation === '2';
        const sizeInput = document.getElementById('image_size');
        const currentSize = parseInt(sizeInput.value);
        
        document.getElementById('alignmentControls').style.display = isHorizontal ? 'none' : 'block';
        document.getElementById('verticalAlignControls').style.display = isHorizontal ? 'block' : 'none';
        
        // Update max width based on position
        sizeInput.max = isHorizontal ? '50' : '100';
        if (currentSize > 50 && isHorizontal) {
            sizeInput.value = '50';
            document.getElementById('sizeValue').textContent = '50%';
        }
        
        // Only reset alignment if switching between horizontal and vertical layouts
        if (isHorizontal !== wasHorizontal) {
            if (isHorizontal) {
                document.getElementById('image_valign').value = 'center';
                document.querySelectorAll('.valign-btn').forEach(b => b.classList.remove('active'));
                document.querySelector('.valign-btn[data-valign="center"]').classList.add('active');
            } else {
                document.getElementById('image_align').value = 'center';
                document.querySelectorAll('.align-btn').forEach(b => b.classList.remove('active'));
                document.querySelector('.align-btn[data-align="center"]').classList.add('active');
            }
        }
        
        updatePreview();
    });
});

// Update points in preview when changed
document.getElementById('points').addEventListener('input', function() {
    const points = this.value || '0';
    document.getElementById('previewPoints').textContent = points;
    document.querySelector('.points-value').textContent = points;
});

// Add event handlers for alignment buttons
document.querySelectorAll('.align-btn').forEach(btn => {
    btn.addEventListener('click', function() {
        document.querySelectorAll('.align-btn').forEach(b => b.classList.remove('active'));
        this.classList.add('active');
        document.getElementById('image_align').value = this.dataset.align;
        updatePreview();
    });
});

document.querySelectorAll('.valign-btn').forEach(btn => {
    btn.addEventListener('click', function() {
        document.querySelectorAll('.valign-btn').forEach(b => b.classList.remove('active'));
        this.classList.add('active');
        document.getElementById('image_valign').value = this.dataset.valign;
        updatePreview();
    });
});

// Fix image size handler
document.getElementById('image_size').addEventListener('input', function() {
    const size = this.value;
    document.getElementById('sizeValue').textContent = size + '%';
    updatePreview();
});

// Initial preview
updatePreview();
</script>

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

<?php require_once "include/footer.php"; ?>
