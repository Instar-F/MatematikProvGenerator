<?php
	
function cleanInput($stringToClean) {
    return htmlspecialchars(trim($stringToClean), ENT_QUOTES, 'UTF-8');
}
	
/*
	
function selectForm($pdo){
	$stmt = $pdo ->query("SELECT * FROM form1 WHERE date >= CURDATE()");
	return $stmt;
}
	
function renderFormCards($allBookings){
	foreach ($allBookings as $singleBooking) {
	echo "<tr><td>{$singleBooking['first_name'] } {$singleBooking['last_name'] }</td> <td>{$singleBooking['Email']}</td>
    <td>{$singleBooking['date']} - {$singleBooking['Time']}</td><td>
	<a href='?delete=1&id={$singleBooking['form_id']}'>Delete</a> 
	<a href='apointment.php?update=1&id={$singleBooking['form_id']}'>Update</a>
	</td></tr>";
	}
}

function addBooking($pdo, $firstName, $lastName, $tel, $Email, $date, $Time){
	
	$firstName = cleanInput($firstName);
	$lastName = cleanInput($lastName);
	$tel = cleanInput($tel);
	$Email = cleanInput($Email);
	$date = cleanInput($date);
	
	$stmt = $pdo ->prepare("INSERT INTO  form1 (first_name, last_name, tel, Email, date, Time) VALUES (:firstName, :lastName, :tel, :Email, :date, :Time)");
	
	$stmt->bindParam(':firstName', $firstName, PDO::PARAM_STR);
	$stmt->bindParam(':lastName', $lastName, PDO::PARAM_STR);
	$stmt->bindParam(':tel', $tel, PDO::PARAM_STR);
	$stmt->bindParam(':Email', $Email, PDO::PARAM_STR);
	$stmt->bindParam(':date', $date, PDO::PARAM_STR);
	$stmt->bindParam(':Time', $Time, PDO::PARAM_STR);

	$stmt->execute();
}

function deleteBooking($pdo, $id){
	
	$stmt = $pdo->prepare("DELETE FROM form1 WHERE form_id = :id");
	
	$stmt->bindParam(':id', $id, PDO::PARAM_INT);

	$stmt->execute();
	
	header("location: index.php");
}

function getSingleBooking($pdo, $id){
	
	$stmt = $pdo->prepare("SELECT * FROM form1 WHERE form_id = :id");
	
	$stmt->bindParam(':id', $id, PDO::PARAM_INT);

	$stmt->execute();
	
	return $stmt->fetch();
}

function populateInput($currentBooking, $arrIndex){
	
	if(!empty($currentBooking)){ 
		return $currentBooking[$arrIndex];
		}
}

function updateBooking($pdo, $id, $firstName, $lastName, $tel, $email, $date, $time){
	$firstName = cleanInput($firstName);
	$lastName = cleanInput($lastName);
	$email = cleanInput($email);
	$tel = cleanInput($tel);
	$date = cleanInput($date);
	$time = cleanInput($time);
	
	$stmt = $pdo ->prepare("UPDATE form1
							SET first_name = :firstName, last_name = :lastName, tel = :tel, Email = :email, date = :date, Time = :time
							WHERE form_id = :id;");
	
	$stmt->bindParam(':firstName', $firstName, PDO::PARAM_STR);
	$stmt->bindParam(':lastName', $lastName, PDO::PARAM_STR);
	$stmt->bindParam(':tel', $tel, PDO::PARAM_STR);
	$stmt->bindParam(':email', $email, PDO::PARAM_STR);
	$stmt->bindParam(':date', $date, PDO::PARAM_STR);
	$stmt->bindParam(':time', $time, PDO::PARAM_STR);
	$stmt->bindParam(':id', $id, PDO::PARAM_INT);

	$stmt->execute();
}
	*/
?>