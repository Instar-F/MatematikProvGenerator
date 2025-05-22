<?php
require_once "include/header.php";

// Check login and role access
if (!$user_obj->checkLoginStatus($_SESSION['user']['id'])) {
    header("Location: login.php");
    exit();
}

$result = $user_obj->checkUserRole($_SESSION['user']['role'], 300);

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
    $userId = (int)$_GET['uid'];
    // Fetch user info and their r_level and u_role_fk using a JOIN to roles
    $stmt = $pdo->prepare("
        SELECT users.*, roles.r_level, roles.r_id 
        FROM users 
        INNER JOIN roles ON users.u_role_fk = roles.r_id 
        WHERE users.u_id = ?
        LIMIT 1
    ");
    $stmt->execute([$userId]);
    $userRow = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$userRow) {
        $errorMessage = "Could not fetch user data.";
        unset($currentUserInfo);
    } else {
        $currentUserInfo = [
            'success' => true,
            'data' => $userRow
        ];
        $targetRoleLevel = (int)$userRow['r_level'];
        $targetRoleId = (int)$userRow['r_id'];
        // Get current user's r_level and u_role_fk (role id)
        $myRoleLevel = 0;
        $myRoleId = 0;
        // Fetch current user's role id and level from DB for security
        $stmt = $pdo->prepare("SELECT u_role_fk, r_level FROM users INNER JOIN roles ON users.u_role_fk = roles.r_id WHERE users.u_id = ?");
        $stmt->execute([$_SESSION['user']['id']]);
        $myUserRow = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($myUserRow) {
            $myRoleId = (int)$myUserRow['u_role_fk'];
            $myRoleLevel = (int)$myUserRow['r_level'];
        }
        // Allow supergigaadmin (u_role_fk == 3) to access all,
        // or allow user to edit themselves,
        // otherwise block if target user has higher or equal r_level
        if (!($myRoleId == 3) && $userId !== (int)$_SESSION['user']['id'] && $targetRoleLevel >= $myRoleLevel) {
            header("HTTP/1.1 403 Forbidden");
            echo "<!DOCTYPE html><html><head><title>Forbidden</title><meta charset='utf-8'></head><body>";
            echo "<div style='max-width:600px;margin:80px auto;text-align:center;'><h2 style='color:#b00;'>403 Forbidden</h2><p>You do not have permission to view, edit, or delete this user.</p></div>";
            echo "</body></html>";
            exit();
        }
    }
} else {
    $errorMessage = "No user ID specified.";
}

// Handle user deletion redirect
if (isset($_POST['deleteuser-submit'])) {
    // Always re-check permission before allowing deletion
    if (isset($currentUserInfo)) {
        $targetRoleLevel = (int)$currentUserInfo['data']['r_level'];
        $targetRoleId = (int)$currentUserInfo['data']['r_id'];
        $stmt = $pdo->prepare("SELECT u_role_fk, r_level FROM users INNER JOIN roles ON users.u_role_fk = roles.r_id WHERE users.u_id = ?");
        $stmt->execute([$_SESSION['user']['id']]);
        $myUserRow = $stmt->fetch(PDO::FETCH_ASSOC);
        $myRoleId = (int)$myUserRow['u_role_fk'];
        $myRoleLevel = (int)$myUserRow['r_level'];
        // Allow supergigaadmin or user deleting themselves, otherwise block if target user has higher or equal r_level
        if (!($myRoleId == 3) && $userId !== (int)$_SESSION['user']['id'] && $targetRoleLevel >= $myRoleLevel) {
            header("HTTP/1.1 403 Forbidden");
            echo "<!DOCTYPE html><html><head><title>Forbidden</title><meta charset='utf-8'></head><body>";
            echo "<div style='max-width:600px;margin:80px auto;text-align:center;'><h2 style='color:#b00;'>403 Forbidden</h2><p>You do not have permission to delete this user.</p></div>";
            echo "</body></html>";
            exit();
        }
        header("Location: delete-user.php?uid={$userId}");
        exit();
    } else {
        $errorMessage = "You do not have permission to delete this user.";
    }
}

// Handle user edit submission
if (isset($_POST['edituser-submit'])) {
    // Always re-check permission before allowing edit
    if (isset($currentUserInfo)) {
        $targetRoleLevel = (int)$currentUserInfo['data']['r_level'];
        $targetRoleId = (int)$currentUserInfo['data']['r_id'];
        $stmt = $pdo->prepare("SELECT u_role_fk, r_level FROM users INNER JOIN roles ON users.u_role_fk = roles.r_id WHERE users.u_id = ?");
        $stmt->execute([$_SESSION['user']['id']]);
        $myUserRow = $stmt->fetch(PDO::FETCH_ASSOC);
        $myRoleId = (int)$myUserRow['u_role_fk'];
        $myRoleLevel = (int)$myUserRow['r_level'];
        // Allow supergigaadmin or user editing themselves, otherwise block if target user has higher or equal r_level
        if (!($myRoleId == 3) && $userId !== (int)$_SESSION['user']['id'] && $targetRoleLevel >= $myRoleLevel) {
            header("HTTP/1.1 403 Forbidden");
            echo "<!DOCTYPE html><html><head><title>Forbidden</title><meta charset='utf-8'></head><body>";
            echo "<div style='max-width:600px;margin:80px auto;text-align:center;'><h2 style='color:#b00;'>403 Forbidden</h2><p>You do not have permission to edit this user.</p></div>";
            echo "</body></html>";
            exit();
        }
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
    } else {
        $errorMessage = "You do not have permission to edit this user.";
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

                    <?php if (isset($currentUserInfo)): ?>
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
                    <?php endif; ?>
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
