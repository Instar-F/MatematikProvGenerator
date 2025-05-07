<?php
require_once "include/header.php";

if(!$user_obj->checkLoginStatus($_SESSION['user']['id'])) {
    header("Location: login.php");
}

$result = $user_obj->checkUserRole($_SESSION['user']['role'], 900);

if (!$result) {
    echo "You do not have the rights to access this page.";
    exit(); // Stop the script from continuing
}

$stmt = $pdo->prepare("SELECT * FROM roles WHERE r_level <= :userRole");
$stmt->execute(['userRole' => $_SESSION['user']['role']]);
$allUserRoles = $stmt->fetchAll(PDO::FETCH_ASSOC);

if(isset($_GET['uid'])){
    $userId = $_GET['uid'];
    $currentUserInfo = $user_obj->selectUserInfo($userId);
}

if(isset($_POST['deleteuser-submit'])){
    header("Location: delete-user.php?uid={$userId}");
}

if(isset($_POST['edituser-submit'])){
    $uname = cleanInput($_POST["uname"]);
    $umail = trim($_POST["umail"]);
    $upass = $_POST["upass"];
    $upassrpt = $_POST["upassrpt"];
    $urole = cleanInput($_POST["urole"]);
    
    $result = $user_obj->checkUserRegisterInfo($uname, $umail, $upass, $upassrpt, "edit", $userId);

    if (!$result['success']) {
        echo "Error: " . $result['error'];
    } 
    else {
        $result = $user_obj->editUser($userId, $uname, $umail, $upass, $urole);
        if (!$result['success']) {
            echo "Error: " . $result['error'];
        } 
        else {
            echo "User Edited";
        }
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
            <div class="card shadow-lg p-4">
                <h2 class="text-center mb-4">Edit User</h2>
                <form action="" method="POST" class="mb-4">
                    <div class="mb-3">
                        <label for="uname" class="form-label">Username:</label>
                        <input type="text" value="<?php echo $currentUserInfo['data']['u_uname']; ?>" id="uname" name="uname" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label for="umail" class="form-label">Email:</label>
                        <input type="email" value="<?php echo $currentUserInfo['data']['u_mail']; ?>" id="umail" name="umail" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label for="upass" class="form-label">Password:</label>
                        <input type="password" id="upass" name="upass" class="form-control">
                    </div>
                    <div class="mb-3">
                        <label for="upassrpt" class="form-label">Repeat Password:</label>
                        <input type="password" id="upassrpt" name="upassrpt" class="form-control">
                    </div>
                    <div class="mb-3">
                        <label for="urole" class="form-label">User Role:</label>
                        <select id="urole" name="urole" class="form-select" required>
                            <?php
                            $currentRole = $currentUserInfo['data']['u_role_fk']; 
                            foreach ($allUserRoles as $role) {
                                $selected = ($role['r_id'] == $currentRole) ? 'selected' : ''; 
                                echo "<option value='{$role['r_id']}' {$selected}>{$role['r_name']}</option>";
                            }
                            ?>
                        </select>
                    </div>
                    <div class="d-grid">
                        <button type="submit" name="edituser-submit" class="btn btn-primary">Edit</button>
                    </div>
                </form>
            </div>
            <div class="card shadow-lg p-4 border-danger mt-4">
                <h2 class="text-center text-danger mb-4">Delete User</h2>
                <p class="text-center text-muted">
                    Are you sure you want to delete this user? This action cannot be undone.
                </p>
                <form action="" method="POST" class="text-center">
                    <button type="submit" name="deleteuser-submit" class="btn btn-danger btn-lg">Delete User</button>
                </form>
            </div>
        </div>
    </div>
</div>

<?php require_once "include/footer.php"; ?>
