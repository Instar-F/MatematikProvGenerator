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

$successMessage = "";
$errorMessage = "";


if (isset($_POST['register-submit'])) {
    $uname = cleanInput($_POST["uname"]);
    $umail = trim($_POST["umail"]);
    $upass = $_POST["upass"];
    $upassrpt = $_POST["upassrpt"];
    $urole = cleanInput($_POST["urole"]);

if ($upass !== $upassrpt) {
    $errorMessage = "Passwords do not match.";
} else {
    // Hash the password
    $hashedPassword = password_hash($upass, PASSWORD_DEFAULT);

    // Optional: check if username or email already exists
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM users WHERE u_uname = :uname OR u_mail = :umail");
    $stmt->execute(['uname' => $uname, 'umail' => $umail]);
    $exists = $stmt->fetchColumn();

    if ($exists > 0) {
        $errorMessage = "A user with this username or email already exists.";
    } else {
        // Insert the new user
        $stmt = $pdo->prepare("INSERT INTO users (u_uname, u_mail, u_password, u_role_fk) VALUES (:uname, :umail, :upass, :urole)");
        $success = $stmt->execute([
            'uname' => $uname,
            'umail' => $umail,
            'upass' => $hashedPassword,
            'urole' => $urole
        ]);

        if ($success) {
            $successMessage = "User created successfully!";
        } else {
            $errorMessage = "An error occurred. Please try again.";
        }
    }
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

<button id="toggleSidebar" aria-label="Toggle Sidebar" type="button" class="closed">
    <span id="toggleArrow">
        <span class="hamburger-bar"></span>
        <span class="hamburger-bar"></span>
        <span class="hamburger-bar"></span>
    </span>
</button>
<div id="sidebarOverlay"></div>

<div class="container-fluid mt-5">
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
                        Create New User
                    </div>
                    <div class="card-body">
                        <?php if (!empty($successMessage)): ?>
                            <div class="alert alert-success"><?= htmlspecialchars($successMessage); ?></div>
                        <?php elseif (!empty($errorMessage)): ?>
                            <div class="alert alert-danger"><?= htmlspecialchars($errorMessage); ?></div>
                        <?php endif; ?>
                        <form action="" method="POST">
                            <div class="form-group mb-3">
                                <label for="uname" class="form-label">Username:</label>
                                <input type="text" id="uname" name="uname" class="form-control" required>
                            </div>
                            <div class="form-group mb-3">
                                <label for="umail" class="form-label">Email:</label>
                                <input type="email" id="umail" name="umail" class="form-control" required>
                            </div>
                            <div class="form-group mb-3">
                                <label for="upass" class="form-label">Password:</label>
                                <input type="password" id="upass" name="upass" class="form-control" required>
                            </div>
                            <div class="form-group mb-3">
                                <label for="upassrpt" class="form-label">Repeat Password:</label>
                                <input type="password" id="upassrpt" name="upassrpt" class="form-control" required>
                            </div>
                            <div class="form-group mb-3">
                                <label for="urole" class="form-label">User Role:</label>
                                <select id="urole" name="urole" class="form-select" required>
                                    <?php
                                    foreach ($allUserRoles as $role) {
                                        echo "<option value='{$role['r_id']}'>{$role['r_name']}</option>";
                                    }
                                    ?>
                                </select>
                            </div>
                            <div class="form-center-btn-lg">
                                <button type="submit" name="register-submit" class="btn btn-primary">Submit</button>
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