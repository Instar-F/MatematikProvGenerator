<?php
// Turn on error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once "include/header.php"; // Must include a valid PDO connection in $pdo

if (!isset($pdo)) {
    die("Database connection not established.");
}

$successMessage = $errorMessage = "";

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $categoryName = $_POST['category_name'] ?? '';
    $courseId = $_POST['course_id'] ?? '';

    if (!empty($categoryName) && !empty($courseId)) {
        try {
            $stmt = $pdo->prepare("INSERT INTO categories (ca_name, ca_co_fk) VALUES (:name, :course_id)");
            $stmt->execute([
                ':name' => $categoryName,
                ':course_id' => $courseId
            ]);
            $successMessage = "Category added successfully!";
        } catch (PDOException $e) {
            $errorMessage = "Database error: " . $e->getMessage();
        }
    } else {
        $errorMessage = "Please fill in all fields.";
    }
}

// Fetch courses for dropdown
$courses = [];
try {
    $stmt = $pdo->query("SELECT co_id, co_name FROM courses");
    $courses = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $errorMessage = "Failed to load courses: " . $e->getMessage();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add Category</title>
    <!-- Bootstrap CSS already linked in the project -->
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
    /* Make centered form buttons medium-sized and professional */
    .form-center-btn-lg {
        display: flex;
        justify-content: center;
        margin-top: 1.2rem;
    }
    .form-center-btn-lg .btn {
        font-size: 1.08rem;
        padding: 0.55rem 1.8rem;
        border-radius: 0.4rem;
    }
    </style>
</head>
<body>
<button id="toggleSidebar" aria-label="Toggle Sidebar" type="button" class="closed">
    <span id="toggleArrow">+</span>
</button>
<div id="sidebarOverlay"></div>

<div class="container-fluid page-centered-container">
    <div class="row" id="contentRow">
        <!-- Sidebar with links -->
        <div class="col-md ps-0" id="sidebarColumn">
            <?php require_once "sidebar.php"; ?>
        </div>
        <!-- Main content -->
        <div class="col-md" id="mainColumn">
            <div class="container mt-5">
                <div class="card shadow-lg">
                    <div class="card-header">
                        Add New Category
                    </div>
                    <div class="card-body">
                        <?php if (!empty($successMessage)): ?>
                            <div class="alert alert-success"><?= htmlspecialchars($successMessage); ?></div>
                        <?php elseif (!empty($errorMessage)): ?>
                            <div class="alert alert-danger"><?= htmlspecialchars($errorMessage); ?></div>
                        <?php endif; ?>
                        <form method="POST" action="">
                            <div class="form-group mb-3">
                                <label for="category_name" class="form-label">Category Name</label>
                                <input type="text" class="form-control" id="category_name" name="category_name" required>
                            </div>
                            <div class="form-group mb-3">
                                <label for="course_id" class="form-label">Course</label>
                                <select class="form-select" id="course_id" name="course_id" required>
                                    <option value="" disabled selected>Select a course</option>
                                    <?php if (!empty($courses)): ?>
                                        <?php foreach ($courses as $course): ?>
                                            <option value="<?= htmlspecialchars($course['co_id']) ?>"><?= htmlspecialchars($course['co_name']) ?></option>
                                        <?php endforeach; ?>
                                    <?php else: ?>
                                        <option disabled>No courses found</option>
                                    <?php endif; ?>
                                </select>
                            </div>
                            <div class="form-center-btn-lg">
                                <button type="submit" class="btn btn-primary">Add Category</button>
                            </div>
                        </form>
                    </div>
                </div>
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
<?php require_once "include/footer.php"; ?>
</body>
</html>
