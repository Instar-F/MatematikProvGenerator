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
</head>
<body>
<div class="container-fluid mt-5">
    <div class="row">
        <!-- Sidebar with links -->
        <div class="col-md-4 ps-0">
            <?php require_once "sidebar.php"; ?>
        </div>
        <!-- Main content -->
        <div class="col-md-8">
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
                            <button type="submit" class="btn btn-primary">Add Category</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once "include/footer.php"; ?>
</body>
</html>
