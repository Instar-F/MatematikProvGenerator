<?php
require_once "include/header.php";

if(!$user_obj->checkLoginStatus($_SESSION['user']['id'])) {
    header("Location: login.php");
}

$result = $user_obj->checkUserRole($_SESSION['user']['role'], 100);

if (!$result) {
    echo "You do not have the rights to access this page.";
    exit(); // Stop the script from continuing
}

$testList["data"] = $pdo->query("
			SELECT ex_id, ex_name, created_at, u_uname
			FROM exams INNER JOIN users
            ON exams.ex_createdby_fk = users.u_id
			LIMIT 10")->fetchAll();
			
//print_r($userList["data"]);

if(isset($_POST['searchuser-submit'])){
	
	$testName = $_POST['testname'];
	$testList = $user_obj->searchTests($testName);
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
    box-shadow: none;
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
        <!-- Sidebar with links -->
        <div class="col-md ps-0" id="sidebarColumn">
            <?php require_once "sidebar.php"; ?>
        </div>
        <!-- Main content -->
        <div class="col-md" id="mainColumn">
            <div class="card shadow-lg">
                <div class="card-header text-center">
                    <h1 class="fw-bold fs-3">Test List</h1>
                </div>
                <div class="card-body">
                    <form action="" method="POST">
                        <div class="row g-2 align-items-end mb-3">
                            <div class="col">
                                <label for="testname" class="form-label">Name:</label>
                                <input type="text" value="" id="testname" name="testname" class="form-control" required>
                            </div>
                            <div class="col-auto">
                                <button type="submit" name="searchuser-submit" class="btn btn-primary btn-sm">Search</button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
            <div class="container mt-4">
                <table class="table table-striped table-bordered">
                    <thead class="table-dark">
                        <tr>
                            <th>Name</th>
                            <th>Created By</th>
                            <th>Timestamp</th>
                            <th class="text-center">Management</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php 
                        if (!empty($testList["data"])):
                            foreach ($testList["data"] as $userRow): ?>
                                <tr class="align-middle">
                                    <td><?= htmlspecialchars($userRow['ex_name']) ?></td>
                                    <td><?= htmlspecialchars($userRow['u_uname']) ?></td>
                                    <td><?= htmlspecialchars($userRow['created_at']) ?></td>
                                    <td class="text-center">
                                        <a href="single-test.php?exid=<?= htmlspecialchars($userRow['ex_id']) ?>" class="btn btn-sm btn-outline-primary" style="display:inline-block;min-width:70px;">Show</a>
                                    </td>
                                </tr>
                            <?php endforeach; 
                        else: ?>
                            <tr>
                                <td colspan="4" class="text-center">No results found</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
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