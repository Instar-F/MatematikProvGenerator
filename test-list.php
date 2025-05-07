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

<div class="container-fluid mt-5">
    <div class="row">
        <!-- Sidebar with links -->
        <div class="col-md-4 ps-0">
            <?php require_once "sidebar.php"; ?>
        </div>
        <!-- Main content -->
        <div class="col-md-8">
            <div class="card shadow-lg">
                <div class="card-header text-center">
                    <h1 class="fw-bold fs-3">Test List</h1>
                </div>
                <div class="card-body">
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
            <div class="container mt-4">
                <table class="table table-striped table-bordered">
                    <thead class="table-dark">
                        <tr>
                            <th>Name</th>
                            <th>Created By</th>
                            <th>Timestamp</th>
                            <th>Management</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php 
                        if (!empty($testList["data"])):
                            foreach ($testList["data"] as $userRow): ?>
                                <tr>
                                    <td><?= htmlspecialchars($userRow['ex_name']) ?></td>
                                    <td><?= htmlspecialchars($userRow['u_uname']) ?></td>
                                    <td><?= htmlspecialchars($userRow['created_at']) ?></td>
                                    <td><a href="single-test.php?exid=<?= htmlspecialchars($userRow['ex_id']) ?>" class="btn btn-primary btn-sm">Show</a></td>
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

<?php require_once "include/footer.php"; ?>