<?php
include("DB_CONNECT.php");

	$USERNAME_q = $_SESSION["USERNAME"];
    $stmt = $connect->prepare("SELECT * FROM usrdta001 WHERE username_dataB = :username_dataB");
    $stmt->execute(array(":username_dataB" => $USERNAME_q));
    $USERNAMEECHO = $stmt->fetch();
	
	if($USERNAMEECHO > 0){
		TRUE;
	}
	else {
	 session_destroy();
	 header("location: index.php");
	 exit;
	}

?>