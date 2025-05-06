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

<div class="container mt-5">
    <div class="row justify-content-center">
		<?php if(isset($_GET['deleteduser'])): ?>
		<div class="user-feedback bg-success text-white m-4"><p class="text-center m-2">User was successfully deleted</p></div>
		<?php endif; ?>
        <div class="col-md-6">
            <div class="card shadow-lg p-4">
                <h2 class="text-center mb-4">Edit user</h2>
                <form action="" method="POST">
                    
                    <div class="mb-3">
                        <label for="uname" class="form-label">Username:</label>
                        <input type="text" value="" id="uname" name="uname" class="form-control" required>
                    </div>

                    <div class="d-grid">
                        <button type="submit" name="searchuser-submit" class="btn btn-primary">Search</button>
                    </div>

                </form>
            </div>
        </div>
    </div>
	<div class="row">
		<div class="container mt-4">
			<div class="row fw-bold border-bottom pb-2 mb-2">
				<div class="col-3">Username</div>
				<div class="col-3">Email</div>
				<div class="col-3">Role</div>
				<div class="col-3">Management</div>

			</div>
	

    <?php 
	if(!empty($userList["data"])):
	foreach ($userList["data"] as $userRow): 
		// Skip users with r_level of 900 if the current user has r_level of 300
		if ($_SESSION['user']['role'] == 300 && $userRow['r_level'] == 900) {
			continue;
		}
	?>
        <div class="row mb-2">
            <div class="col"><?= htmlspecialchars($userRow['u_uname']) ?></div>
            <div class="col"><?= htmlspecialchars($userRow['u_mail']) ?></div>
            <div class="col"><?= htmlspecialchars($userRow['r_name']) ?></div>
            <div class="col"><a href="edit-user.php?uid=<?= htmlspecialchars($userRow['u_id']) ?>">Edit</a></div>
        </div>
    <?php
	endforeach; 
	else:
		  echo "<div class='col text-center'>No result</div>";
	endif;
	?>
		</div>
	</div>
</div>