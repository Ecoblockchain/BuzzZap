<?php
if($check_valid!="true"){
	header("Location: index.php?page=home");
	exit();
}
if(loggedin()){
	?>

	<script>
	$(document).ready(function(){
		var form_open = 0;
		function open_form(){
			$("#start-new-pm").animate({marginTop:"30px"});
			<?php
				if(in_array(user_browser(), array("Safari", "Chrome", "Opera"))){
			?>
					$("#start-new-pm").animate({backgroundColor:"#62b1cf"});
			<?php
				}else{
					?>
						$("#start-new-pm").css("background", "#62b1cf");
					<?php
				}
			?>
			$("#start-new-pm").animate({padding:"4px"}).css("text-align", "center")
			.animate({marginLeft:"13%"}).animate({width:"45%"}).animate({height:"430px"});
			setTimeout(function(){
				//$("#start-new-pm").css("text-decoration","underline");
				$("#all-messages").slideUp(1200);
			}, 1000);	
			setTimeout(function(){
				$("#start-pm-form").fadeIn();
				$("#close-form").fadeIn();
			}, 2500);		
		}	
		
		
		$("#start-new-pm").click(function(){
			if(form_open==0){
				open_form(form_open);
				form_open=1
			}
		});	
		
		
		

		$("#close-form").click(function(){
			form_open=0;
			$("#start-pm-form").fadeOut();
			$("#close-form").fadeOut();
			$("#snm-text").fadeOut();
			$("#start-new-pm").css("text-decoration","none");
			$("#start-new-pm").animate({width:"30px"}).animate({height:"25px"}).animate({marginLeft:"-10px"})
			.animate({width:"200px"});
			setTimeout(function(){
			$("#snm-text").fadeIn();
			$("#start-new-pm").animate({marginTop:"-4px"}).css({backgroundColor:"transparent"});
			$("#all-messages").slideDown(1200);
			}, 1500);
		});

	});
	
	</script>
	<div class = 'page-path'><?php echo get_user_field($_SESSION['user_id'], "user_username"); ?> > My Private Messages
	<div class = "note_title">All Messages</div>
	<br>
	<div class = 'close-spm-form' id = 'close-form'>x</div>
	<span class = 'inbox-menu'>
		<a style = 'color:grey;' href = "index.php?page=inbox&maas=true">
			Mark All As Read
		</a>
		 &middot;
		<div id ='start-new-pm' style ="position:absolute;border-radius:3px;display:inline;">
			<span id = 'snm-text'>Start New Message</span>
			<div id = 'start-pm-form' style = "display:none;">
				<form action = "" method = "POST"><br>
					Subject: <input name = "pm_subject" type = "text" class = "loggedout-form-fields" style = "box-shadow:none;height:25px;width:69%;margin-left:-2px;font-size:70%;letter-spacing:1px;" placeholder = "Brief description of message"><br><br>
					To:<input name = "pm_to" type = "text" id = "desto" class = "loggedout-form-fields" style = "box-shadow:none;height:25px;width:80%;margin-left:-2px;font-size:70%;letter-spacing:1px;" placeholder = "Usernames or Groups, separated by commas">
					<div id = "pred_results" style = 'text-decoration: none;'></div>
					<br>Message:<textarea id = 'pm_body_' name = "pm_body" class= "textarea-type1" style = "border-radius:3px;width:90%;font-size:80%;letter-spacing:1px;" placeholder="Your message..."></textarea><br><br>
					<input type = "submit" class = "edit-profile-submit" style = "display:block;margin-left:10%;position:absolute;">
				</form>
			</div>
			
		</div>
		
	</span>
	<hr size = '1'>
	<div id = "all-messages">
		<br>
		<?php
		$user_ident = $_SESSION['user_id'];	
		$get_rel_messages = $db->prepare("SELECT pm_id FROM pm_members WHERE user_id = :user_ident AND visible = 1 ORDER BY pm_id DESC");
		$get_rel_messages->execute(array("user_ident"=>$user_ident));
		if($get_rel_messages->rowCount()>0){
			while($row = $get_rel_messages->fetch(PDO::FETCH_ASSOC)){
				$pm_id = $row['pm_id'];
				$get_members = $db->prepare("SELECT user_id FROM pm_members WHERE pm_id = :pm_id AND visible = 1");
				$get_members->execute(array("pm_id"=>$pm_id));
				$members = array();
				while($member_row = $get_members->fetch(PDO::FETCH_ASSOC)){
					
						$members[]= $member_row['user_id'];
					
				}
				
				$member_string = "you,";
				$counter = 0;
				foreach($members as &$id){
					$id = $db->query("SELECT user_username FROM users WHERE user_id = ".$db->quote($id))->fetchColumn();
					$member_name = $id;
					if($member_name==get_user_field($_SESSION['user_id'], "user_username")){
						$member_name = "";
						$type = "";	
						$comma = "";
					}else{
						($counter==count($members)||$counter==0)?$comma = "" : $comma = ",";
					}
					$member_string.=$comma.$member_name;
					$counter++;
				}
				
				
				$get_messages = $db->prepare("SELECT * FROM private_messages WHERE pm_id = :pm_id");
				$get_messages->execute(array("pm_id"=>$pm_id));
				while($row_info = $get_messages->fetch(PDO::FETCH_ASSOC)){
					$get_first_post_info = $db->prepare("SELECT * FROM pm_replies WHERE pm_id = :pm_id AND first_post = 1");
					$get_first_post_info->execute(array("pm_id"=>$pm_id));
					while($first_post_info = $get_first_post_info->fetch(PDO::FETCH_ASSOC)){
						$text = $first_post_info['reply_text'];
						$starter = $first_post_info['user_replied'];
						$time = date("h:ia d M Y ", $first_post_info['time_sent']);	
					}
					if(user_not_seen_latest_pm($_SESSION['user_id'], $pm_id)){
						$bold = "<b>";
						$bold_ = "</b>";	
					}else{
						$bold = "";
						$bold_="";	
					}
					
					echo "<a class = 'pm-delete-link' href = 'index.php?page=inbox&d_pm=".$pm_id."'>X</a>
					<a href = 'index.php?page=view_pm&pm_id=".$pm_id."'><div class = 'pm-row'>
					
					<span style = 'color:#66CDAA;font-size:130%;'>
					".$bold.$row_info['pm_subject'].$bold_."
					</span><br>
						
					<span style = 'font-size:70%;color:grey;'>
						<div style = 'width:70%;'>".$text."- by <span style = 'color:black;'>".$starter."</div>
						
						<hr size = '1'>
						Time Created: ".$time."
						<br>
						In message: <span style = 'color:#87CEFA;'>".$member_string."</span>
					</span>
					
					</div></a><br>"; 
				}	
			}
		}else{
			echo "<div id = 'no-threads-message'>You have no private messages.</div>";	
		}	
		if(isset($_POST['pm_subject'], $_POST['pm_to'], $_POST['pm_body'])){
			$subject = htmlentities($_POST['pm_subject']);
			$to = htmlentities($_POST['pm_to']);
			$body = $_POST['pm_body'];
			$header_link = "index.php?page=inbox";
			if(!empty($to)&&!empty($subject)&&!empty($body)){
				$returned_list = strlist_to_array($to);
			    $invalid = false;
				if($returned_list[count($returned_list)-1]=="ERROR"){
					$counter = 0;
					$valid_groups = array();
					unset($returned_list[count($returned_list)-1]);
					foreach($returned_list as $value){
						$check_group = $db->query("SELECT group_id FROM private_groups WHERE group_name = ".$db->quote($value))->fetchColumn();
						if(!empty($check_group)){
							
							$valid_groups[] = $returned_list[$counter];
							unset($returned_list[$counter]);	
						}
						$counter++;
					}
					
					if(count($returned_list)>0){
					
						$invalid = true;
						
					}else{
						
						$valid_users = array_diff(strlist_to_array($to, false), $valid_groups);
					
						$valid_user_ids = array();
						foreach($valid_users as $username){
							$valid_user_ids[] = $db->query("SELECT user_id FROM users WHERE user_username = ".$db->quote($username))->fetchColumn();
						}
						foreach($valid_groups as $group_name){
							$group_id = $db->query("SELECT group_id FROM private_groups WHERE group_name = ".$db->quote($group_name))->fetchColumn();
							$group_members = $db->prepare("SELECT user_id FROM group_members WHERE group_id = :group_id AND active = 1");
							$group_members->execute(array("group_id"=>$group_id));
						
							while($row = $group_members->fetch(PDO::FETCH_ASSOC)){
								$valid_user_ids[] = $row['user_id'];
							}
						}		
					}
				}else{
					$valid_user_ids = array();
					foreach($returned_list as $username){
						$valid_user_ids[] = $db->query("SELECT user_id FROM users WHERE user_username = ".$db->quote($username))->fetchColumn();
					}
					
				}
				if($invalid==false){
					
					if((strlen($subject)>0)&&(strlen($subject)<400)&&(strlen($body)>0)){
						$valid_user_ids[] = $_SESSION['user_id'];
						$valid_user_ids = array_unique($valid_user_ids);
						if($pm_id = send_pm($valid_user_ids, nl2br($body), $subject)){
							
							$header_link = "index.php?page=view_pm&pm_id=".$pm_id;	
							$message = "1Successfully sent message!";
						}else{
							$message = "0Unknown error.";		
						}
					}else{
						$message = "0The field lengths are invalid.";
					}
				}else{
					$invalid_names = $returned_list;
					$message = "0The following usernames/group names are invalid:<br>".implode(",<br>", $invalid_names);	
				}
				setcookie("success", $message, time()+10);
			}else{
				setcookie("success", "0All fields are required.", time()+10);
			}	
			header("Location: ".$header_link);
				
			
		}
		
		if(isset($_GET['maas'])){
			$get_pms = $db->prepare("SELECT pm_id FROM pm_members WHERE user_id = :user_id");
			$get_pms->execute(array("user_id"=>$_SESSION['user_id']));
			$pm_ids = array();
			while($row = $get_pms->fetch(PDO::FETCH_ASSOC)){
				$pm_ids[] = $row['pm_id'];	
			}
			mark_pms_as_seen($_SESSION['user_id'], $pm_ids);
			setcookie("success", "1Successfully marked all messages as read.", time()+10);
			header("Location: index.php?page=inbox");
		}
		
		if(isset($_GET['d_pm'])){
			$d_pm_id = htmlentities($_GET['d_pm']);
			
			if(valid_pm_id($d_pm_id, $_SESSION['user_id'])){
				$update = $db->prepare("UPDATE pm_members SET visible = 0 WHERE user_id = :user_id AND pm_id = :pm_id")->execute(array("pm_id"=>$d_pm_id, "user_id"=>$_SESSION['user_id']));
				
				
				$get_ids = $db->prepare("SELECT user_id FROM pm_members WHERE pm_id=:pm_id AND visible = 1");
				$count = 0;
				$get_ids->execute(array("pm_id"=>$d_pm_id));
				$get_pm_name = $db->query("SELECT pm_subject FROM private_messages WHERE pm_id=".$db->quote($d_pm_id))->fetchColumn();
				while($row = $get_ids->fetch(PDO::FETCH_ASSOC)){
					add_note($row['user_id'], get_user_field($_SESSION['user_id'], "user_username")." has left the private conversation '".$get_pm_name."'.", "index.php?page=view_pm&pm_id=".$d_pm_id);
					$count++;
				}
				if($count==0){
					delete_pm($d_pm_id);
				}
				setcookie("success", "1Successfully left conversation.", time()+10);
			}else{
				setcookie("success", "0Failed to leave conversation.", time()+10);
			}
			header("Location: index.php?page=inbox");
		}
		?>
	</div>
	<?php
}else{
	header("Location: index.php?page=home");	
}

?>