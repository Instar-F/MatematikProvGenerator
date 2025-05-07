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
//made it so that only users with a role lower than the one they are creating can be created.
// Get all user roles that are less than or equal to the current user's role
$stmt = $pdo->prepare("SELECT * FROM roles WHERE r_level <= :userRole");
$stmt->execute(['userRole' => $_SESSION['user']['role']]);
$allUserRoles = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Get the role ID of the currently logged-in user
$currentUserRole = $_SESSION['user']['role'];

if (isset($_POST['register-submit'])) {
    $uname = cleanInput($_POST["uname"]);
    $umail = trim($_POST["umail"]);
    $upass = $_POST["upass"];
    $upassrpt = $_POST["upassrpt"];
    $urole = cleanInput($_POST["urole"]);
}
?>

<div class="container-fluid mt-5">
    <div class="row">
        <!-- Sidebar with links -->
        <div class="col-md-4 ps-0">
            <?php require_once "sidebar.php"; ?>
        </div>
        <!-- Main content -->
        <div class="col-md-8">
            <div class="card shadow-lg p-4">
                <h2 class="text-center mb-4">Create new user</h2>
                <form action="" method="POST">
                    
                    <div class="mb-3">
                        <label for="uname" class="form-label">Username:</label>
                        <input type="text" id="uname" name="uname" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label for="umail" class="form-label">Email:</label>
                        <input type="email" id="umail" name="umail" class="form-control" required>
                    </div>

                    <div class="mb-3">
                        <label for="upass" class="form-label">Password:</label>
                        <input type="password" id="upass" name="upass" class="form-control" required>
                    </div>

                    <div class="mb-3">
                        <label for="upassrpt" class="form-label">Repeat Password:</label>
                        <input type="password" id="upassrpt" name="upassrpt" class="form-control" required>
                    </div>

                    <div class="mb-3">
                        <label for="urole" class="form-label">User Role:</label>
                        <select id="urole" name="urole" class="form-select" required>
                            <?php
                            foreach ($allUserRoles as $role) {
                                echo "<option value='{$role['r_id']}'>{$role['r_name']}</option>";
                            }
                            ?>
                        </select>
                    </div>

                    <div class="d-grid">
                        <button type="submit" name="register-submit" class="btn btn-primary">Submit</button>
                    </div>

                </form>
            </div>
        </div>
    </div>
</div>

<?php require_once "include/footer.php"; ?>