<?php
require_once "include/header.php";  // Assuming this already includes config.php

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $courseName = $_POST['course_name'];
    $description = $_POST['description'];

    if (!empty($courseName)) {
        $stmt = $conn->prepare("INSERT INTO courses (co_name, description) VALUES (?, ?)");
        $stmt->bind_param("ss", $courseName, $description);

        if ($stmt->execute()) {
            $successMessage = "Course added successfully!";
        } else {
            $errorMessage = "Error: " . $stmt->error;
        }

        $stmt->close();
    } else {
        $errorMessage = "Please fill in the course name.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add Course</title>
    <!-- Bootstrap CSS already linked -->
</head>
<body>
<div class="container-fluid mt-5">
    <div class="row">
        <!-- Sidebar with links -->
        <div class="col-md-4 sidebar-container">
            <?php require_once "sidebar.php"; ?>
        </div>
        <!-- Main content -->
        <div class="col-md-8">
            <div class="container mt-5">
                <div class="card shadow-lg">
                    <div class="card-header">
                        Add New Course
                    </div>
                    <div class="card-body">
                        <?php if (!empty($successMessage)): ?>
                            <div class="alert alert-success"><?= htmlspecialchars($successMessage); ?></div>
                        <?php elseif (!empty($errorMessage)): ?>
                            <div class="alert alert-danger"><?= htmlspecialchars($errorMessage); ?></div>
                        <?php endif; ?>
                        <form method="POST" action="">
                            <div class="form-group mb-3">
                                <label for="course_name" class="form-label">Course Name</label>
                                <input type="text" class="form-control" id="course_name" name="course_name" required>
                            </div>
                            <div class="form-group mb-3">
                                <label for="description" class="form-label">Description</label>
                                <textarea class="form-control" id="description" name="description" rows="3"></textarea>
                            </div>
                            <button type="submit" class="btn btn-primary">Add Course</button>
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
