<?php

require("requires.php");



if(isset($_POST['pass'])){
	$pass = htmlentities($_POST['pass']);
	$get_true_pass = $db->query("SELECT pass FROM feature_activation WHERE feature='site'")->fetchColumn();
	if($pass===$get_true_pass){
		$_SESSION['pass_site_d']="true";
		header("Location: index.php?page=home");
	}else{
		echo "error";
	}
}


echo $message = $db->query("SELECT message FROM feature_activation WHERE feature='site'")->fetchColumn();
?>
<br><br>
To bypass this, enter the passcode:
<br>

<form action = "" method = "POST">
	<input type = "text" name = "pass">
	<input type = "submit">
</form>