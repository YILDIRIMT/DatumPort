<?php
$ADMIN_IP = $_SERVER["REMOTE_ADDR"];

if ($ADMIN_IP == "::1") {
	try {
	$connect = new PDO("mysql:host=localhost;dbname=datumport_msql;charset=utf8","root","");
	} catch (PDOException $e) {
	echo $e->getMessage();
	} 
} else {
	header("location: ../index.php");
	exit;
}
?>
