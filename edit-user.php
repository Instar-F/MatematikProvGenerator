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

$allUserRoles = $pdo->query("SELECT * FROM roles")->fetchAll();

if(isset($_GET['uid'])){
	$userId = $_GET['uid'];
	$currentUserInfo = $user_obj->selectUserInfo($userId);
	//print_r($currentUserInfo);
}
//print_r($currentUserInfo);


if(isset($_POST['deleteuser-submit'])){
	header("Location: delete-user.php?uid={$userId}");
}

if(isset($_POST['edituser-submit'])){
	echo "<h2>Form submitted</h2>";
	
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
?>


<div class="container mt-5">
    <div class="row justify-content-center">
        <div class="col-md-6">
            <div class="card shadow-lg p-4">
                <h2 class="text-center mb-4">Edit user</h2>
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
				<h2 class="text-center mb-4">Delete user</h2>
                <form action="" method="POST">
                    <div class="d-grid">
                        <button type="submit" name="deleteuser-submit" class="btn btn-danger">Delete</button>
                    </div>
                </form>
            </div>
    </div>

    </div>
</div>
