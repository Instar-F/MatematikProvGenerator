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
<?php require_once "include/footer.php"; ?>