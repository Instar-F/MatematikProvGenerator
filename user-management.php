<?php
require_once "include/header.php";

if(!$user_obj->checkLoginStatus($_SESSION['user']['id'])) {
    header("Location: login.php");
}

$result = $user_obj->checkUserRole($_SESSION['user']['role'], 300);

if (!$result) {
    echo "You do not have the rights to access this page.";
    exit(); // Stop the script from continuing
}

$userList["data"] = $pdo->query("
			SELECT u_id, u_uname, u_mail, r_name, r_level 
			FROM users 
			INNER JOIN roles 
			ON users.u_role_fk = roles.r_id
			LIMIT 10")->fetchAll();
			
//print_r($userList["data"]);

if(isset($_POST['searchuser-submit'])){
	
	$userName = $_POST['uname'];
	$userList = $user_obj->searchUsers($userName);
	//print_r($userList);
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
</style>

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
                        User Management
                    </div>
                    <div class="card-body">
                        <?php if(isset($_GET['deleteduser'])): ?>
                        <div class="alert alert-success text-center mb-4">User was successfully deleted</div>
                        <?php endif; ?>
                        <form action="" method="POST" class="mb-4">
                            <div class="row g-2 align-items-end">
                                <div class="col">
                                    <label for="uname" class="form-label">Username:</label>
                                    <input type="text" value="" id="uname" name="uname" class="form-control" required>
                                </div>
                                <div class="col-auto">
                                    <button type="submit" name="searchuser-submit" class="btn btn-primary">Search</button>
                                </div>
                            </div>
                        </form>
                        <div class="table-responsive">
                            <table class="table table-bordered align-middle">
                                <thead class="table-light">
                                    <tr>
                                        <th>Username</th>
                                        <th>Email</th>
                                        <th>Role</th>
                                        <th class="text-center">Management</th>
                                    </tr>
                                </thead>
                                <tbody>
                                <?php 
                                if(!empty($userList["data"])):
                                foreach ($userList["data"] as $userRow): 
                                    if ($_SESSION['user']['role'] == 300 && $userRow['r_level'] == 900) {
                                        continue;
                                    }
                                ?>
                                    <tr>
                                        <td><?= htmlspecialchars($userRow['u_uname']) ?></td>
                                        <td><?= htmlspecialchars($userRow['u_mail']) ?></td>
                                        <td><?= htmlspecialchars($userRow['r_name']) ?></td>
                                        <td class="text-center">
                                            <a href="edit-user.php?uid=<?= htmlspecialchars($userRow['u_id']) ?>" class="btn btn-sm btn-outline-primary" style="display:inline-block;min-width:70px;">Show</a>
                                        </td>
                                    </tr>
                                <?php
                                endforeach; 
                                else:
                                    echo "<tr><td colspan='4' class='text-center'>No result</td></tr>";
                                endif;
                                ?>
                                </tbody>
                            </table>
                        </div>
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