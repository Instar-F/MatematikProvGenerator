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

if(isset($_POST['register-submit'])){
	echo "<h2>Form submitted</h2>";
	
	$uname = cleanInput($_POST["uname"]);
	$umail = trim($_POST["umail"]);
	$upass = $_POST["upass"];
	$upassrpt = $_POST["upassrpt"];
	$urole = cleanInput($_POST["urole"]);
	
	$result = $user_obj->checkUserRegisterInfo($uname, $umail, $upass, $upassrpt, "create");

	if (!$result['success']) {
		echo "Error: " . $result['error'];
	} 
	else {
		$result = $user_obj->createUser($uname, $umail, $upass, $urole);
		if (!$result['success']) {
			echo "Error: " . $result['error'];
		} 
		else {
			echo "User created";
		}
	}
}
?>
<div class="container mt-5">
    <div class="row justify-content-center">
        <div class="col-md-6">
            <div class="card shadow-lg p-4">
                <h2 class="text-center mb-4">Register</h2>
                <form action="" method="POST">
                    
                    <div class="mb-3">
                        <label for="uname" class="form-label">Username:</label>
                        <input type="text" id="uname" name="uname" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label for="umail" class="form-label">Email:</label>
                        <input type="email" id="umail" name="umail" class="form-control" required>
                    </div>

                    <div class="mb-3">
                        <label for="upass" class="form-label">Password:</label>
                        <input type="password" id="upass" name="upass" class="form-control" required>
                    </div>

                    <div class="mb-3">
                        <label for="upassrpt" class="form-label">Repeat Password:</label>
                        <input type="password" id="upassrpt" name="upassrpt" class="form-control" required>
                    </div>

                    <div class="mb-3">
                        <label for="urole" class="form-label">User Role:</label>
                        <select id="urole" name="urole" class="form-select" required>
                            <?php
							foreach($allUserRoles as $role){
								echo "<option value='{$role['r_id']}'>{$role['r_name']}</option>";
							}
							?>
                        </select>
                    </div>

                    <div class="d-grid">
                        <button type="submit" name="register-submit" class="btn btn-primary">Submit</button>
                    </div>

                </form>
            </div>
        </div>
    </div>
</div>
