<?php
if(loggedin()){
	include("includes/loggedin_home.php");
}else{
	include("includes/loggedout_home.php");
	

	if(isset($_POST['username'], $_POST['password'], $_POST['com_pass'])){
		$lheader = "index.php?page=home";
		if(isset($_POST['lheader_post'])&&$_POST['lheader_post']!=""){
			$lheader = htmlentities($_POST['lheader_post']);
			$lheader = str_replace("~", "&", $lheader);
		}
		if(login_user($_POST['username'], $_POST['password'], $_POST['com_pass'])===false){
			header("location: index.php?page=home&login_error=true");

		}else if(login_user($_POST['username'], $_POST['password'], $_POST['com_pass'])==="banned"){
			header("location: index.php?page=home&login_error=banned");
		}else if(login_user($_POST['username'], $_POST['password'], $_POST['com_pass'])==="disabled"){
			header("location: index.php?page=home&login_error=disabled");
		}else if(login_user($_POST['username'], $_POST['password'], $_POST['com_pass'])==="discom"){
			header("location: index.php?page=home&login_error=discom");
		}else{
			$link = (user_rank($_SESSION['user_id'], "4"))? "index.php?page=home&sp=0":$lheader;
			header("Location: ".$link);
		}
	}
	if((get_feature_status("new_users")=="0")||(isset($_SESSION['pass_dnu']))){
		if(isset($_POST['username_'], $_POST['password_'], $_POST['vpassword_'],$_POST['firstname_'], $_POST['lastname_'], $_POST['com_pass_'], $_POST['com_name_'],$_POST['email_'])){

			if(register_user($_POST['username_'], $_POST['password_'], $_POST['vpassword_'],$_POST['firstname_'], $_POST['lastname_'], $_POST['com_pass_'], $_POST['com_name_'],$_POST['email_'])==="true"){
				header("location: index.php?page=home&reg_error=false");
				setcookie("reg_errors", "no_errors", time()+60);
			}else{
				$errors = register_user($_POST['username_'], $_POST['password_'], $_POST['vpassword_'],$_POST['firstname_'], $_POST['lastname_'], $_POST['com_pass_'], $_POST['com_name_'],$_POST['email_']);

				setcookie("reg_errors", serialize($errors), time()+60);
				header("location: index.php?page=home&reg_error=true");
			}
		}
	}
}

?>