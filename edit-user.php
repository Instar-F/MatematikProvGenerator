<?php
require_once "include/header.php";

// Check login and role access
if (!$user_obj->checkLoginStatus($_SESSION['user']['id'])) {
    header("Location: login.php");
    exit();
}

$result = $user_obj->checkUserRole($_SESSION['user']['role'], 900);

if (!$result) {
    echo "You do not have the rights to access this page.";
    exit();
}

// Get roles the current user is allowed to assign
$stmt = $pdo->prepare("SELECT * FROM roles WHERE r_level <= :userRole");
$stmt->execute(['userRole' => $_SESSION['user']['role']]);
$allUserRoles = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Setup message variables
$successMessage = "";
$errorMessage = "";

// Load current user info to edit
if (isset($_GET['uid'])) {
    $userId = $_GET['uid'];
    $currentUserInfo = $user_obj->selectUserInfo($userId);

    if (!$currentUserInfo['success']) {
        $errorMessage = "Could not fetch user data.";
    }
} else {
    $errorMessage = "No user ID specified.";
}

// Handle user deletion redirect
if (isset($_POST['deleteuser-submit'])) {
    header("Location: delete-user.php?uid={$userId}");
    exit();
}

// Handle user edit submission
if (isset($_POST['edituser-submit'])) {
    $uname = cleanInput($_POST["uname"]);
    $umail = trim($_POST["umail"]);
    $upass = $_POST["upass"];
    $upassrpt = $_POST["upassrpt"];
    $urole = cleanInput($_POST["urole"]);

    // Password validation only if filled in
    if (!empty($upass) || !empty($upassrpt)) {
        if ($upass !== $upassrpt) {
            $errorMessage = "Passwords do not match.";
        } else {
            $hashedPassword = password_hash($upass, PASSWORD_DEFAULT);
        }
    }

    if (empty($errorMessage)) {
        // Check for existing user with same username/email (excluding this user)
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM users WHERE (u_uname = :uname OR u_mail = :umail) AND u_id != :uid");
        $stmt->execute(['uname' => $uname, 'umail' => $umail, 'uid' => $userId]);
        $exists = $stmt->fetchColumn();

        if ($exists > 0) {
            $errorMessage = "Another user with this username or email already exists.";
        } else {
            // Build dynamic update query depending on password presence
            if (!empty($hashedPassword)) {
                $stmt = $pdo->prepare("UPDATE users SET u_uname = :uname, u_mail = :umail, password = :upass, u_role_fk = :urole WHERE u_id = :uid");
                $params = [
                    'uname' => $uname,
                    'umail' => $umail,
                    'upass' => $hashedPassword,
                    'urole' => $urole,
                    'uid' => $userId
                ];
            } else {
                $stmt = $pdo->prepare("UPDATE users SET u_uname = :uname, u_mail = :umail, u_role_fk = :urole WHERE u_id = :uid");
                $params = [
                    'uname' => $uname,
                    'umail' => $umail,
                    'urole' => $urole,
                    'uid' => $userId
                ];
            }

            if ($stmt->execute($params)) {
                $successMessage = "User updated successfully.";
                $currentUserInfo = $user_obj->selectUserInfo($userId);
            } else {
                $errorMessage = "Failed to update user.";
            }
        }
    }
}

// Determine previous page (for sidebar highlight)
$previousPage = isset($_SERVER['HTTP_REFERER']) ? basename(parse_url($_SERVER['HTTP_REFERER'], PHP_URL_PATH)) : '';
?>


<!-- Sidebar Toggle Button -->
<style>
#sidebarColumn, #mainColumn {
    transition: none;
}
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
    left: 320px; /* match sidebar width */
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
}
.hamburger-bar {
    width: 22px;
    height: 3px;
    background: #0d6efd;
    margin: 2.5px 0;
    border-radius: 2px;
    transition: all 0.25s;
}
#toggleSidebar.open .hamburger-bar:nth-child(1) {
    transform: translateY(5.5px) rotate(45deg);
}
#toggleSidebar.open .hamburger-bar:nth-child(2) {
    opacity: 0;
}
#toggleSidebar.open .hamburger-bar:nth-child(3) {
    transform: translateY(-5.5px) rotate(-45deg);
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
    </span>
</button>
<div id="sidebarOverlay"></div>

<!-- Main Page Layout -->
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
        <!-- Main Content -->
        <div class="col-md-12" id="mainColumn">
            <div class="card shadow-lg">
                <div class="card-header text-center">
                    <h1 class="fw-bold fs-3">Edit User</h1>
                </div>
                <div class="card-body">

                    <?php if (!empty($successMessage)): ?>
                        <div class="alert alert-success"><?= htmlspecialchars($successMessage); ?></div>
                    <?php elseif (!empty($errorMessage)): ?>
                        <div class="alert alert-danger"><?= htmlspecialchars($errorMessage); ?></div>
                    <?php endif; ?>

                    <form action="" method="POST" class="mb-4">
                        <div class="form-group mb-3">
                            <label for="uname" class="form-label">Username:</label>
                            <input type="text" value="<?= htmlspecialchars($currentUserInfo['data']['u_uname']) ?>" id="uname" name="uname" class="form-control" required>
                        </div>
                        <div class="form-group mb-3">
                            <label for="umail" class="form-label">Email:</label>
                            <input type="email" value="<?= htmlspecialchars($currentUserInfo['data']['u_mail']) ?>" id="umail" name="umail" class="form-control" required>
                        </div>
                        <div class="form-group mb-3">
                            <label for="upass" class="form-label">New Password:</label>
                            <input type="password" id="upass" name="upass" class="form-control" placeholder="Leave blank to keep current password">
                        </div>
                        <div class="form-group mb-3">
                            <label for="upassrpt" class="form-label">Repeat New Password:</label>
                            <input type="password" id="upassrpt" name="upassrpt" class="form-control" placeholder="Leave blank to keep current password">
                        </div>
                        <div class="form-group mb-3">
                            <label for="urole" class="form-label">User Role:</label>
                            <select id="urole" name="urole" class="form-select" required>
                                <?php
                                $currentRole = $currentUserInfo['data']['u_role_fk'];
                                foreach ($allUserRoles as $role) {
                                    $selected = ($role['r_id'] == $currentRole) ? 'selected' : '';
                                    echo "<option value='{$role['r_id']}' $selected>{$role['r_name']}</option>";
                                }
                                ?>
                            </select>
                        </div>
                        <div class="d-grid">
                            <button type="submit" name="edituser-submit" class="btn btn-primary">Save Changes</button>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Delete Block -->
            <div class="card shadow-lg border-danger mt-4">
                <div class="card-header text-center">
                    <h1 class="fw-bold fs-3 text-danger">Delete User</h1>
                </div>
                <div class="card-body">
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

// Hide sidebar by default on load
hideSidebar();
</script>

<?php require_once "include/footer.php"; ?>
