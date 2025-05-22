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
$image_size = $_POST['image_size'] ?? '';
$image_location = $_POST['image_location'] ?? '';

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

    // Add image_align to the SQL insert
    if (!empty($question) && !empty($answer)) {
        try {
            $stmt = $pdo->prepare("INSERT INTO questions (ca_id, text, answer, image_url, total_points, difficulty, teacher_fk, image_size, image_location, image_align, image_valign) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
            $stmt->execute([
                $ca_id, 
                $question, 
                $answer, 
                $image_url, 
                $points, 
                $difficulty, 
                $_SESSION['user']['id'],
                $image_url ? $image_size : null,
                $image_url ? $image_location : null,
                $image_url ? $_POST['image_align'] : null,
                $image_url ? $_POST['image_valign'] : null
            ]);
            echo "<p class='alert alert-success'>Frågan har sparats framgångsrikt!</p>";
            // Clear all fields after successful insert
            $question = '';
            $answer = '';
            $points = '';
            $difficulty = '';
            $ca_id = '';
            $co_id = '';
            $image_url = null;
            $image_size = '';
            $image_location = '';
        } catch (Exception $e) {
            echo "<p class='alert alert-danger'>Fel vid spara: " . $e->getMessage() . "</p>";
        }
    } else {
        echo "<p class='alert alert-warning'>Fyll i alla fält korrekt.</p>";
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
                    <h2 class="mb-0">Lägg till fråga</h2>
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
                                    
                                    <div class="image-controls mt-3" id="imageControls" style="display:none;">
                                        <!-- Location buttons -->
                                        <div class="btn-group w-100 mb-3">
                                            <button type="button" class="btn btn-outline-primary location-btn" data-location="2">
                                                <i class="fas fa-arrow-left"></i> Vänster
                                            </button>
                                            <button type="button" class="btn btn-outline-primary location-btn" data-location="1">
                                                Höger <i class="fas fa-arrow-right"></i>
                                            </button>
                                            <button type="button" class="btn btn-outline-primary location-btn" data-location="3">
                                                <i class="fas fa-arrow-up"></i> Över
                                            </button>
                                            <button type="button" class="btn btn-outline-primary location-btn" data-location="4">
                                                Under <i class="fas fa-arrow-down"></i>
                                            </button>
                                        </div>
                                        <input type="hidden" name="image_location" id="image_location" value="">

                                        <!-- Width controls - always shown -->
                                        <div id="widthControl" class="mb-3">
                                            <label class="form-label d-flex justify-content-between">
                                                Bildbredd: <span id="sizeValue">50%</span>
                                            </label>
                                            <input type="range" class="form-range" name="image_size" id="image_size" 
                                                   min="10" max="100" step="5" value="50">
                                        </div>

                                        <!-- Vertical alignment for left/right -->
                                        <div id="verticalAlignControls" class="mb-3" style="display:none;">
                                            <label class="form-label">Vertikal justering:</label>
                                            <div class="btn-group w-100">
                                                <button type="button" class="btn btn-outline-secondary valign-btn active" data-valign="flex-start">
                                                    <i class="fas fa-arrow-up"></i> Topp
                                                </button>
                                                <button type="button" class="btn btn-outline-secondary valign-btn" data-valign="center">
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
                                                <button type="button" class="btn btn-outline-secondary align-btn active" data-align="flex-start">
                                                    <i class="fas fa-align-left"></i> Vänster
                                                </button>
                                                <button type="button" class="btn btn-outline-secondary align-btn" data-align="center">
                                                    <i class="fas fa-align-center"></i> Mitten
                                                </button>
                                                <button type="button" class="btn btn-outline-secondary align-btn" data-align="flex-end">
                                                    <i class="fas fa-align-right"></i> Höger
                                                </button>
                                            </div>
                                        </div>

                                        <input type="hidden" name="image_align" id="image_align" value="flex-start">
                                        <input type="hidden" name="image_valign" id="image_valign" value="flex-start">
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

                                <button type="submit" class="btn btn-success">Spara till databas</button>
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

// Show image controls when an image is selected
document.getElementById('image').addEventListener('change', function() {
    const controls = document.getElementById('imageControls');
    if (this.files.length > 0) {
        controls.style.display = 'block';
        // Don't set any default location
        document.getElementById('image_location').value = '';
        
        // Reset all buttons and controls
        document.querySelectorAll('.location-btn').forEach(btn => {
            btn.classList.remove('active');
        });
        document.getElementById('verticalAlignControls').style.display = 'none';
        document.getElementById('alignmentControls').style.display = 'none';
    } else {
        controls.style.display = 'none';
    }
    updatePreview();
});

// Add debouncing function before the event handlers
function debounce(func, wait) {
    let timeout;
    return function executedFunction(...args) {
        const later = () => {
            clearTimeout(timeout);
            func(...args);
        };
        clearTimeout(timeout);
        timeout = setTimeout(later, wait);
    };
}

// Update size handler with proper binding for 'this'
document.getElementById('image_size').addEventListener('input', debounce(function(e) {
    document.getElementById('sizeValue').textContent = e.target.value + '%';
    updatePreview();
}, 50)); // 50ms delay

// Add button handlers (replace existing align-btn handlers)
document.querySelectorAll('.align-btn').forEach(btn => {
    btn.addEventListener('click', function() {
        document.querySelectorAll('.align-btn').forEach(b => b.classList.remove('active'));
        this.classList.add('active');
        const align = this.dataset.align;
        document.getElementById('image_align').value = align;
        updatePreview();
    });
});

// Add vertical alignment button handler
document.querySelectorAll('.valign-btn').forEach(btn => {
    btn.addEventListener('click', function() {
        document.querySelectorAll('.valign-btn').forEach(b => b.classList.remove('active'));
        this.classList.add('active');
        document.getElementById('image_valign').value = this.dataset.valign;
        updatePreview();
    });
});

// Fix width value display on load
document.getElementById('sizeValue').textContent = document.getElementById('image_size').value + '%';

// Update preview function
function updatePreview() {
    if (!questionEditor) return;
    
    const questionContent = questionEditor.getData();
    const imageInput = document.getElementById('image');
    const imageLocation = document.getElementById('image_location').value;
    const imageSize = document.getElementById('image_size').value;
    const imageAlign = document.getElementById('image_align').value || 'left';
    const imageValign = document.getElementById('image_valign').value || 'flex-start';
    
    let imgHtml = '';
    if (imageInput.files && imageInput.files[0]) {
        const imgUrl = URL.createObjectURL(imageInput.files[0]);
        imgHtml = `<img src="${imgUrl}" style="width:100%;height:auto;">`;
    }

    let previewHtml = '';
    if (imgHtml && imageLocation) {
        if (imageLocation === '1' || imageLocation === '2') { // Left or Right
            const questionWidth = 100 - parseInt(imageSize);
            const flexStyle = `display:flex;align-items:${imageValign};gap:1em;margin:0;`;
            
            if (imageLocation === '1') { // Right
                previewHtml = `<div style="${flexStyle}">
                    <div style="width:${questionWidth}%;">${questionContent}</div>
                    <div style="width:${imageSize}%;">${imgHtml}</div>
                </div>`;
            } else { // Left
                previewHtml = `<div style="${flexStyle}">
                    <div style="width:${imageSize}%;">${imgHtml}</div>
                    <div style="width:${questionWidth}%;">${questionContent}</div>
                </div>`;
            }
        } else if (imageLocation === '3' || imageLocation === '4') { // Top or Bottom
            const margin = {
                'flex-start': '0 auto 0 0',     // Left align
                'center': '0 auto',             // Center align
                'flex-end': '0 0 0 auto'        // Right align
            };
            const imgStyle = `width:${imageSize}%;margin:${margin[imageAlign]};display:block;`;
            const wrappedImg = `<img src="${imageInput.files[0] ? URL.createObjectURL(imageInput.files[0]) : ''}" style="${imgStyle}">`;
            previewHtml = `<div>
                ${imageLocation === '3' ? wrappedImg : ''}
                <div>${questionContent}</div>
                ${imageLocation === '4' ? wrappedImg : ''}
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

// Update location button handler
document.querySelectorAll('.location-btn').forEach(btn => {
    btn.addEventListener('click', function() {
        document.querySelectorAll('.location-btn').forEach(b => b.classList.remove('active'));
        this.classList.add('active');
        const location = this.dataset.location;
        document.getElementById('image_location').value = location;
        
        const isHorizontal = location === '1' || location === '2';
        const sizeInput = document.getElementById('image_size');
        const currentSize = parseInt(sizeInput.value);
        
        document.getElementById('alignmentControls').style.display = isHorizontal ? 'none' : 'block';
        document.getElementById('verticalAlignControls').style.display = isHorizontal ? 'block' : 'none';
        
        // Update max width and adjust current value if needed
        if (isHorizontal && currentSize > 50) {
            sizeInput.max = '50';
            sizeInput.value = '50';
            document.getElementById('sizeValue').textContent = '50%';
        } else if (!isHorizontal && sizeInput.max === '50') {
            sizeInput.max = '100';
            // Keep the current value as it will be valid for vertical
        }
        
        // Reset alignments
        if (isHorizontal) {
            document.querySelector('.valign-btn[data-valign="flex-start"]').click();
        } else {
            document.querySelector('.align-btn[data-align="flex-start"]').click();
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
