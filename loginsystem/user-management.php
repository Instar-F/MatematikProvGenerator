<?php
require_once "include/header.php";

$userList["data"] = $pdo->query("
			SELECT u_id, u_name, u_fname, u_lname, u_email, r_name 
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
				<div class="col">Username</div>
				<div class="col">First Name</div>
				<div class="col">Last Name</div>
				<div class="col">Email</div>
				<div class="col">Role</div>
			</div>
	

    <?php 
	if(!empty($userList["data"])):
	foreach ($userList["data"] as $userRow): ?>
        <div class="row mb-2">
            <div class="col"><a href="edit-user.php?uid=<?= htmlspecialchars($userRow['u_id']) ?>"><?= htmlspecialchars($userRow['u_name']) ?></a></div>
            <div class="col"><?= htmlspecialchars($userRow['u_fname']) ?></div>
            <div class="col"><?= htmlspecialchars($userRow['u_lname']) ?></div>
            <div class="col"><?= htmlspecialchars($userRow['u_email']) ?></div>
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