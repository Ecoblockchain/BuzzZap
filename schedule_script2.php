<?php
	//daily notification
	ob_start();
	require("requires.php");
	$users = $db->query("SELECT * FROM `notifications` WHERE `seen` = 0");
	$ebodys = array();
	foreach($users as $user){
		$uid = $user['to'];
		if($uid!="norm"&&substr($uid,0,1)!="o"){
			$link = $user['link'];
			$pre_link = "https://www.buzzzap.com/";
			if($link!=""){
				$link = $pre_link."index.php?page=home&login_error=lheader-".str_replace("&", "~", $link);
			}else{
				$link = $pre_link."index.php?page=home&login_error=lheader-index.php?page=notifications";
			}
			if(!in_array($uid, array_keys($ebodys))){
				$ebodys[$uid]=date("d/M/Y H:i",$user['time'])."<a href = '".$link."'>".$user['text']."</a><br>";
			}else{
				$ebodys[$uid].="<br>".date("d/M/Y H:i",$user['time']).": <a href = '".$link."'>".$user['text']."</a><br>";
			}
		}
	}
	foreach($ebodys as $uid=>$body){
		
		$email = get_user_field($uid, "user_email");
		$name = get_user_field($uid, "user_username");
		$body = "Dear ".$name.", <br>You have new notification(s): <br>".$body. "<br><span style = 'font-size: 70%;'>To stop these notifications being sent repeatly, visit them on BuzzZap.</span>";
		send_mail($email,"BuzzZap Activity",$body,"auto@buzzzap.com");
	}

?>