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

<div class="container mt-5">
    <div class="row justify-content-center">
		<?php if(isset($_GET['deleteduser'])): ?>
		<div class="user-feedback bg-success text-white m-4"><p class="text-center m-2">User was successfully deleted</p></div>
		<?php endif; ?>
        <div class="col-md-6">
            <div class="card shadow-lg p-4">
                <h2 class="text-center mb-4">Test List</h2>
                <form action="" method="POST">
                    
                    <div class="mb-3">
                        <label for="testname" class="form-label">Name</label>
                        <input type="text" value="" id="testname" name="testname" class="form-control" required>
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
				<div class="col-3">Name</div>
				<div class="col-3">Created by</div>
				<div class="col-3">Timestamp</div>
				<div class="col-3">Management</div>

			</div>
	

    <?php 
	if(!empty($testList["data"])):
	foreach ($testList["data"] as $userRow): ?>
        <div class="row mb-2">
            <div class="col"><?= htmlspecialchars($userRow['ex_name']) ?></div>
            <div class="col"><?= htmlspecialchars($userRow['u_uname']) ?></div>
            <div class="col"><?= htmlspecialchars($userRow['created_at']) ?></div>
            <div class="col"><a href="single-test.php?exid=<?= htmlspecialchars($userRow['ex_id']) ?>">Show</a></div>
    <?php
	endforeach; 
	else:
		  echo "<div class='col text-center'>No result</div>";
	endif;
	?>
		</div>
	</div>
</div>