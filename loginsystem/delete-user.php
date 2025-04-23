<?php
require_once "include/header.php";

if(isset($_GET['uid'])){
	$userId = $_GET['uid'];
	$currentUserInfo = $user_obj->selectUserInfo($userId);
	print_r($currentUserInfo);
}

if(isset($_POST['confirm']) && $_POST['confirm'] === "delete"){
	$result = $user_obj->deleteUser($userId);
	
	if($result['success']){
		header("Location: user-management.php?deleteduser=1");
	}
	else {
		$userFeedback = $result['message'];
	}
}

if(isset($_POST['confirm']) && $_POST['confirm'] === "back"){
	header("Location: edit-user.php?uid={$userId}");
}

?>

<div class="container mt-2">
    <div class="row">
	<?php if (!isset($userFeedback) && !empty($currentUserInfo['data']['u_id'])):?>
		<h2>Are you sure you want to delete <?= $currentUserInfo['data']['u_name']?></h2>
		<form action="" method="post">
			<button type="submit" class="btn btn-danger" name="confirm" value="delete">Delete</button>
			<button type="submit" class="btn btn-primary" name="confirm" value="back">Back</button>
		</form>
			
	<?php else: 
		if(!isset($userFeedback)){
			$userFeedback = "This user does not seem to exist";
		}
	?>
    <h2>An error has occurred!</h2>
    <p><?= $userFeedback ?></p>
	<?php endif; ?>
	
	</div>
</div>