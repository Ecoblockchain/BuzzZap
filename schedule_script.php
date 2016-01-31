<?php
	ob_start();
	require("requires.php");

	//link format = index.php?page=home&login_error=lheader-index.php..get variables use ~ not &...

	$users = $db->query("SELECT user_email, user_username FROM users WHERE user_com > 0");
	$pgd = "";
	$gcf = "";

	$popular_global_debates = $db->query("SELECT thread_title, thread_id FROM debating_threads WHERE com_id = 0 AND visible = 1 ORDER BY thread_likes DESC LIMIT 5");
	foreach($popular_global_debates as $row){
		$link = "index.php?page=home&login_error=lheader-index.php?page=view_private_thread~thread_id=".$row['thread_id'];
		$title = $row['thread_title'];
		$html_link = "<a href = '".$link."'>".$title."</a>";
		$pgd.=$html_link."<br>";
	}

	$global_comp_fjudge = $db->query("SELECT comp_title, comp_id FROM competitions WHERE comp_com_id = 0 AND end != 'true' AND SUBSTRING(end, 0,1) != '.' AND 'judges' = 'norm' ORDER BY created DESC LIMIT 5");
	foreach($global_comp_fjudge as $row){
		$link = "index.php?page=home&login_error=lheader-index.php?page=view_comp~comp=1".$row['comp_id'];
		$title = $row['comp_title'];
		$html_link = "<a href = '".$link."'>".$title."</a>";
		$gcf.=$html_link."<br>";
	}

	$headers  = 'MIME-Version: 1.0' . "\r\n";
	$headers .= 'Content-type: text/html; charset=iso-8859-1' . "\r\n";
	$headers .= "From: auto@buzzzap.com" . "\r\n";
	$comp_body = (strlen($gcf)>0)? "<br><br>By judging competitions you can build your reputation, so here are some competitions that you can judge...<br>".$gcf : "";
	$deb_body = (strlen($pgd)>0)? "<br><br>Here are some of the most popular global debates at the moment, take a look!<br>".$pgd : "";
	$ebody = "Hello from BuzzZap!";
	if($comp_body.$deb_body!="d"){
		$ebody.=$deb_body.$comp_body."<br><br>BuzzZap";
		foreach($users as $row){
			$email = $row['user_email'];
			$name = $row['user_username'];
			mail($email,"BuzzZap Weekly Update",$ebody,$headers);
		}

	}
?>