<?php
//if($_SERVER['PHP_SELF']!="/buzzzap/index.php"){ 
//	header("Location: index.php?page=home");
//}
$host = "localhost";
$dbname = "buzzzapc_buzzzap";
$username = "buzzzapc_buzzzap";
$password = "buz1236";
try{
	$db = new PDO("mysql:host=localhost;dbname=buzzzapc_buzzzap", $username, $password);
	$db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
	echo 'Connection failed: ' . $e->getMessage();
}
?>