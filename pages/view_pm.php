<?php
if($check_valid!="true"){
	header("Location:../index.php?page=home");
	exit();
}
if(loggedin()){
	$pm_id = htmlentities($_GET['pm_id']);
	if(isset($pm_id)&&(valid_pm_id($pm_id, $_SESSION['user_id']))){

		mark_pms_as_seen($_SESSION['user_id'], explode(" ", $pm_id));
		$pm_subject = $db->query("SELECT pm_subject FROM private_messages WHERE pm_id = ".$db->quote($pm_id))->fetchColumn();
		echo "<div class = 'page-path'>".get_user_field($_SESSION['user_id'], 'user_username')." > <a href = 'index.php?page=inbox' style ='color: #40e0d0;'>My Private Messages</a> > ".$pm_subject;
		echo "<div class = 'pm-subject'>".$pm_subject."</div>";
		$get_replies = $db->prepare("SELECT * FROM pm_replies WHERE pm_id=:pm_id ORDER BY time_sent ASC");
		$get_replies->execute(array("pm_id"=>$pm_id));
		$counter = 0;
		while($row = $get_replies->fetch(PDO::FETCH_ASSOC)){
			echo "<div class = 'thread-reply-container'>
				".$row['reply_text']."<br><span style  = 'color:grey;'>-By <a style = 'color: grey;' href = 'index.php?page=profile&user=".$db->query("SELECT user_id FROM users WHERE user_username = ".$db->quote($row['user_replied']))->fetchColumn()."'>".$row['user_replied']."</a>
				<hr size = '1'>
				Sent on ".date("h:ia d M Y ", $row['time_sent'])."<br>
				<span style = 'color:lightblue;cursor:pointer;' id='seen-by-show_".$counter."'>Seen by?</span> 
				<span id = 'seen-by-list_".$counter."' style = 'display:none;color:#7D9EC0;'>"
					.implode(", ",users_seen_pm_reply($row['reply_id'], $pm_id))."
				</span>
			</span></div><br>";
			
			?>
			<script>
			$(document).ready(function(){
				var click_count = 1;
				$("#seen-by-show_<?php echo $counter;?>").click(function(){
					click_count++;
					if(click_count%2==0){
						$("#seen-by-list_<?php echo $counter;?>").fadeIn();
					}else{
						$("#seen-by-list_<?php echo $counter;?>").fadeOut();
					}
					
				});
					
			});
			</script>
			<?php
			$counter++;
		}
		?>
		<br><br>
		<form action = "" method = "POST">	
			<textarea placeholder = "Reply..." class = "textarea-type1" style = "width:84%;" name = "pm_reply_text"></textarea><br>
			<input type = "submit" class = "mreply-submit">
		</form>

		<?php
		if(isset($_POST['pm_reply_text'])){
			$text = htmlentities($_POST['pm_reply_text']);	
			if(strlen($text)>10){
				pm_reply($pm_id, nl2br($text));
				$message = "1Successfully sent!";
			}else{
				$message = "0Reply is too short.";
			}
			setcookie("success", $message, time()+10);
			header("Location: index.php?page=view_pm&pm_id=".$pm_id);
		}
	}else{
		header("Location: index.php?page=home");
	}
}else{
	header("Location: index.php?page=home");
}

?>