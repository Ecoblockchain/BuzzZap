<?php
session_destroy();
$e = "";
if(isset($_GET['sub_p'])){
	$e = "&go_to=".$_GET['sub_p'];
}
header("Location: index.php?page=home".$e);
?>
