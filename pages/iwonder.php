<?php
if($check_valid!="true"){
	header("Location: index.php?page=home");
	exit();
}
if(loggedin()){
	?>
	<script>
	
	$(document).ready(function(){
		var curr_open = "";
		$("#im1").click(function(){
			$("#d-sticky").slideDown(1000);
			setTimeout(function(){
				$("#new-sticky-text").focus();
			}, 1000);
			
			$("#new-sticky-text").keyup(function(){
				
				if($(this).val().substring(0, 11)!=="I wonder..."){
					$(this).val("I wonder...");
				}
				
			});
			
			$("#new-sticky-text").blur(function(){
					if($(this).val().length>11){
						window.location="index.php?page=iwonder&start_t="+$(this).val();
					}
			});
		});
		
		$("#im2").click(function(){
			
		});
	});	
	</script>
	<div id = "iwonder-t">The I Wonder Sticky Board</div>
	<div id = "iwonder-menu">
		<span id = "im1">
			Add Sticky Note 
		</span>
	</div>
	<?php
	$get_threads = $db->prepare("SELECT * FROM iwonder_threads ORDER BY time_created DESC");
	$get_threads->execute();
	$quant = $get_threads->rowCount();
	echo "<div id = 'iwonder-board'>";
	
	?>
	<div id = "d-sticky" class = "iwonder-sticky" style="display:none;">
		<textarea id = "new-sticky-text" maxlength="80">I wonder...</textarea>
	</div>
	<?php
	while($row = $get_threads->fetch(PDO::FETCH_ASSOC)){
	
		$username = get_user_field($_SESSION['user_id'], "user_username");
		$user_idp = $db->query("SELECT user_id FROM users WHERE user_username = ".$db->quote($row['thread_starter']))->fetchColumn();
		if($row['thread_starter']==$username||$row['active']=="1"){
			if($row['thread_starter']==$username&&$row['active']=="0"){
				$not_act_msg = true;
				$attr_style_bg = "style = 'background-color:#FF6A6A;'";
				$sticky_footer_bg = "#FFFFFF";
			}else{
				$sticky_footer_bg = "#71C671";
				$attr_style_bg = "";
			}
		?>
		<div class = "iwonder-sticky" id = "iws<?php echo $row['thread_id']; ?>" <?php echo $attr_style_bg; ?> >
			<?php
				$perm_to_delete = false;
				if((user_rank($_SESSION['user_id'], "3")==true)&&(get_user_community($_SESSION['user_id'], "com_id")==get_user_community($user_idp, "com_id"))){
					$perm_to_delete = true;
				}else if($_SESSION['user_id']==$user_idp){
					$perm_to_delete = true;
				}
				if($perm_to_delete==true){
					echo "<a style = 'font-size: 50%;color:salmon;' href = 'index.php?page=iwonder&del_q=".$row['thread_id']."'>Delete</a><br>";
				}
				
	
				?>
			
			<span style = "display:none;font-size:60%;float:right;color:green;" id = "min-opt<?php echo $row['thread_id']; ?>">Minimize<br></span>
			<span id = "iwst<?php echo $row['thread_id']; ?>"><?php echo $row['thread_title']; ?></span>
			<?php echo "<br><span style = 'color:".$sticky_footer_bg.";font-size:60%;>'>by  <a style = 'color: #71C671;' href = 'index.php?page=profile&user=".$db->query("SELECT user_id FROM users WHERE user_username = ".$db->quote($row['thread_starter']))->fetchColumn()."'>".$row['thread_starter']."</a><br>
			".date("h:ia d M Y ", $row['time_created'])."</span>";?>
			<br><br>
			<div id = "sticky-content<?php echo $row['thread_id']; ?>" class = "sticky-content">
				<form action = "" method = "POST">
						<textarea placeholder= "I think..." name = "reply_thread" class = "textarea-type2"></textarea>
						<input type = "hidden" value = "<?php echo $row['thread_id']; ?>" name = "thread_id">
						<input type = "submit" class = "mreply-submit" value = "Submit">
				</form>
				<?php 
					
					$get_replies = $db->prepare("SELECT * FROM iwonder_replies WHERE thread_id = :thread_id ORDER BY time_created DESC");
					$get_replies->execute(array("thread_id"=>$row['thread_id']));
					if($get_replies->rowCount()>0){
						while($row_ = $get_replies->fetch(PDO::FETCH_ASSOC)){
							$muser_idp = $db->query("SELECT user_id FROM users WHERE user_username = ".$db->quote($row_['user_replied']))->fetchColumn();
							$mperm_to_delete = false;
							if((user_rank($_SESSION['user_id'], "3")==true)&&(get_user_community($_SESSION['user_id'], "com_id")==get_user_community($muser_idp, "com_id"))){
								$mperm_to_delete = true;
							}else if($_SESSION['user_id']==$muser_idp){
								$mperm_to_delete = true;
							}
							if(($_SESSION['user_id']!=$muser_idp)&&(!check_c_reported($row_['reply_id'], "reply_id", "iwonder_replies"))){
								?>
									<a style = "color: salmon;font-size:50%;" href = "index.php?page=iwonder&keep_o=<?php echo $row['thread_id']; ?>&repo-c=<?php echo $row_['reply_id']; ?>">
										-Report Abuse
									</a>
								<?php
							}else if(check_c_reported($row_['reply_id'], "reply_id", "iwonder_replies")){
								?>
								<span style = "color: salmon;font-size:50%;" href = "index.php?page=iwonder&keep_o=<?php echo $thread_id; ?>&repo-c=<?php echo $row_['reply_id']; ?>">
										-This comment has been reported.
								</span>
								<?php
							}
				
							if($row_['visible']==1){
								$dis = "norm";
							}else if($row_['user_replied']==get_user_field($_SESSION['user_id'], "user_username")){
								$dis = "red";
								//unapproved but user owner can see.
							}else{
								$dis = false;
							}
							if($dis!=false){
								if($dis=="red"){
									$reply_red_style = "<div style = 'border: 3px solid salmon;'>";
									$red_text = "<span style = 'color: salmon;font-size:11px;'>*NOTE: Untill your community leader has approved this post, only you can see it.</span><br><br>";
								}else{
									$reply_red_style = "<div>";
									$red_text = "";
								}
								echo $reply_red_style;
								echo $red_text;
								if($mperm_to_delete==true){
									echo "<a style = 'font-size: 50%;color:salmon;' href = 'index.php?page=iwonder&del_r=".$row_['reply_id']."&keep_o=".$row_['thread_id']."'>-Delete</a><br>";
								}
								echo $row_['reply_text']."<span style = 'color:#71C671;font-size:70%;'>- by <a style = 'color: #71C671;' href = 'index.php?page=profile&user=".$db->query("SELECT user_id FROM users WHERE user_username = ".$db->quote($row_['user_replied']))->fetchColumn()."'>".$row_['user_replied']."</a> ".date("h:ia d M Y ", $row_['time_created'])."</span></div><hr size = '1'>";
							}
						}
					}else{
						echo "<br><br>No Discussion so far.";
					}
				?>
				
				
			</div>
			
		</div>
		
	<?php
		}
	}
	
	echo "</div>";
		
	if(isset($_GET['keep_o'])){
		$id = htmlentities($_GET['keep_o']);
		
	
		?>
		<script>
		$(document).ready(function(){
			curr_open = "<?php echo $id; ?>";
		//	alert(curr_open);
			$("#iws<?php echo $id; ?>").css("width","470px").css("height","430px");
			$("#min-opt<?php echo $id; ?>").show();
			$("#sticky-content<?php echo $id;?>").show();
			
		
		});
		</script>
		<?php
	
	}else{
		?>
		<script>
		$(document).ready(function(){
			curr_open = 0;
		});
		</script>
		<?php
	}
	
	?>
		
	
	
	<script>
	$(document).ready(function(){

		<?php
		$get_thread_ids = $db->query("SELECT thread_id FROM iwonder_threads ORDER BY time_created DESC");
		foreach($get_thread_ids as $i){
			$i = $i[0];
		?>
			
			$("#iwst<?php echo $i; ?>").click(function(){
				
				$("#iws"+curr_open).animate({width:"220px"}, 1000).animate({height:"200px"}, 1000);
				$("#min-opt"+curr_open).fadeOut();
				$("#sticky-content"+curr_open).hide();
				curr_open = "<?php echo $i; ?>";
				
				$("#iws<?php echo $i; ?>").animate({width:"470px"}, 1000).animate({height:"430px"}, 1000);
				$("#min-opt<?php echo $i; ?>").fadeIn();
				$("#sticky-content<?php echo $i;?>").show();
				
				
			});	
			$("#min-opt<?php echo $i; ?>").click(function(){
				
				$(this).fadeOut();
				$("#sticky-content<?php echo $i; ?>").hide();
				$("#iws<?php echo $i; ?>").animate({width:"220px"}, 1000).animate({height:"200px"}, 1000);
			});
			
			
		<?php	
		}
		?>
	});
	</script>
	
	<?php
	
	$username = get_user_field($_SESSION['user_id'], "user_username");
	
	if(isset($_GET['del_q'])){
		$q_id = htmlentities($_GET['del_q']);
		$user_idp = $db->query("SELECT thread_starter FROM iwonder_threads WHERE thread_id = ".$db->quote($q_id))->fetchColumn();
		$user_idp = $db->query("SELECT user_id FROM users WHERE user_username = ".$db->quote($user_idp))->fetchColumn();
		$perm_to_delete = false;
		if((user_rank($_SESSION['user_id'], "3")==true)&&(get_user_community($_SESSION['user_id'], "com_id")==get_user_community($user_idp, "com_id"))){
			$perm_to_delete = true;
		}else if($_SESSION['user_id']==$user_idp){
			$perm_to_delete = true;
		}
		if($perm_to_delete==true){
			$db->query("DELETE FROM iwonder_threads WHERE thread_id = ".$db->quote($q_id));
			$db->query("DELETE FROM iwonder_replies WHERE thread_id = ".$db->quote($q_id));
			setcookie("success", "1Deleted Successfully!", time()+10);
		}else{
			setcookie("success", "0You do not have permission to delete this question.", time()+10);
		}
		header("Location: index.php?page=iwonder");
	}
	if(isset($_GET['del_r'], $_GET['keep_o'])){
		$r_id = htmlentities($_GET['del_r']);
		$keep_o = htmlentities($_GET['keep_o']);
		$muser_idp = $db->query("SELECT user_replied FROM iwonder_replies WHERE reply_id = ".$db->quote($r_id))->fetchColumn();
		$muser_idp = $db->query("SELECT user_id FROM users WHERE user_username = ".$db->quote($muser_idp))->fetchColumn();
		$mperm_to_delete = false;
		if((user_rank($_SESSION['user_id'], "3")==true)&&(get_user_community($_SESSION['user_id'], "com_id")==get_user_community($muser_idp, "com_id"))){
			$mperm_to_delete = true;
		}else if($_SESSION['user_id']==$muser_idp){
			$mperm_to_delete = true;
		}
		if($mperm_to_delete==true){
			$db->query("DELETE FROM iwonder_replies WHERE reply_id = ".$db->quote($r_id));
			setcookie("success", "1Deleted Successfully!", time()+10);
		}else{
			setcookie("success", "0You do not have permission to delete this comment.", time()+10);
		}
		header("Location: index.php?page=iwonder&keep_o=".$keep_o);
	}
	if(isset($_GET['start_t'])){
		$text = htmlentities($_GET['start_t']);
		if(strlen($text)>20){
			if(substr($text, 0, 11)=="I wonder..."){
				$active = (user_moderation_status($_SESSION['user_id'])>1)? 0:1;	
				$insert = $db->prepare("INSERT INTO iwonder_threads VALUES('', :text, :username, UNIX_TIMESTAMP(), :active)");
				$insert->execute(array("text"=>$text, "username"=>$username, "active"=>$active));
				if(user_moderation_status($_SESSION['user_id'])>1){
					setcookie("success", "1Your question has been sent, however will be invisible to public (appear to you as red) untill approved by a leader.", time()+10);
				}
			}else{
				setcookie("success", "0Your question must start with 'I wonder...'.", time()+10);
			}
			
		}else{
			setcookie("success", "0Your question must be longer.", time()+10);
		}
		
		header("Location: index.php?page=iwonder");
	}
	if(isset($_GET['repo-c'])){
		$reply_id = htmlentities($_GET['repo-c']);
		$reported_by = get_user_field($_SESSION['user_id'], "user_username");
		if(substr($reply_id, strlen($reply_id)-1, strlen($reply_id))=="-"){
			$reply_id = substr($reply_id, 0, strlen($reply_id)-1);
			$reported_by = "BuzzZap Filtering";
		}
		$reported_user = $db->query("SELECT user_replied FROM iwonder_replies WHERE reply_id = ".$db->quote($reply_id))->fetchColumn();
		$reason = "--This content posted by ".$reported_user." is abusive: ". $db->query("SELECT reply_text FROM iwonder_replies WHERE reply_id = ".$db->quote($reply_id))->fetchColumn();
		if(!check_c_reported($reply_id, "reply_id", "iwonder_replies")){
			report_user($reported_by,$reported_user, $reason, array(true,$reply_id,"reply_id", "iwonder_replies"));
			setcookie("success", "1Successfully reported content.", time()+10);
		}else{
			setcookie("success", "1This post has already been reported.", time()+10);
		}
		header("Location: index.php?page=iwonder&keep_o=".$_GET['keep_o']);
	}	
	if(isset($_POST['reply_thread'])){
		$reply_text = htmlentities($_POST['reply_thread']);
		$thread_id = htmlentities($_POST['thread_id']);
		$report_header = "";
		$check_abuse = contains_blocked_word($reply_text);
		if($check_abuse[0]==true){
			$reply_text = $check_abuse[1];
			$report_header = true;
		}
		if(strlen($reply_text)>10){
			$active = (user_moderation_status($_SESSION['user_id'])==3)? 0:1;	
			if(user_not_posted(get_user_field($_SESSION['user_id'], "user_username"))){
				add_badge("Posting for the first time", $_SESSION['user_id'], "you posted for the first time!");
			}
			re_for_p_count_on_post(get_user_field($_SESSION['user_id'], "user_username"));	
			$insert = $db->prepare("INSERT INTO iwonder_replies VALUES('', :thread_id, :reply, :user, UNIX_TIMESTAMP(), :visible)");
			$insert->execute(array("thread_id"=>$thread_id, "reply"=>$reply_text, "user"=>$username, "visible"=>$active));
			if($report_header==true){
				$report_header = "&repo-c=".$db->lastInsertId()."-";
			}
			$thread_starter = $db->query("SELECT thread_starter FROM iwonder_threads WHERE thread_id=".$db->quote($thread_id))->fetchColumn();
			$starter_id =$db->query("SELECT user_id FROM users WHERE user_username=".$db->quote($thread_starter))->fetchColumn();
			$link = "index.php?page=iwonder&keep_o=".$thread_id;
			$note_m = "The user '".get_user_field($_SESSION['user_id'], "user_username")."' has replied to an I Wonder question you made.";
			add_note($starter_id, $note_m, $link);
			
			if($active==0){
				$message = "1Your comment will not be visible untill it is approved by your community leader.";

				setcookie("success", $message, time()+10);
			}
		}else{
			$message = "0Your reply must be longer.";

			setcookie("success", $message, time()+10);
		}
		
		header("Location: index.php?page=iwonder&keep_o=".$thread_id.$report_header);
	}
}else{
	header("Location: index.php?page=home");	
}

?>