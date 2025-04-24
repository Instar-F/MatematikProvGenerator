<?php
require_once "include/header.php";

if(isset($_POST['login-submit'])){
	$userName = $_POST['username'];
	$password = $_POST["password"];
	$result = $user_obj->login($userName, $password);
	
	if($result['success']){
		header("Location: dashboard.php");
	}
	else {
		echo $result['error'];
	}
}

?>
<div class="container">
<?php if(isset($result['error'])) { echo "<div class='user-feedback bg-danger text-white m-4'><p class='text-center m-2'>{$result['error']}</p></div>";} ?>
</div> 
<div class="container d-flex justify-content-center align-items-center min-vh-100">
	<div class="card shadow p-4" style="width: 100%; max-width: 400px;">
		<h2 class="text-center mb-4">Login</h2>
		<form action="" method="POST">
			<div class="mb-3">
				<label for="username" class="form-label">Username</label>
				<input type="text" class="form-control" id="username" name="username" required>
			</div>

			<div class="mb-3">
				<label for="password" class="form-label">Password</label>
				<input type="password" class="form-control" id="password" name="password" required>
			</div>

			<div class="d-grid">
				<button type="submit" name="login-submit" class="btn btn-primary">Login</button>
			</div>
		</form>
	</div>
</div>