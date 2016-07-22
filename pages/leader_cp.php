<?php
if($check_valid!="true"){
	header("Location: index.php?page=home");
	exit();
}
if(loggedin()){
	if(user_rank($_SESSION['user_id'], 3, "just")){
		$com_id = get_user_field($_SESSION['user_id'], "user_com");
		?>
		
		<script>
		$(document).ready(function(){
			samount = 3;
			counter = 0;
			while(counter <= samount){
				var c = 1;
				$("#t"+counter).click(function(){					
					c++;
					if(c%2==0){
						$("#s"+$(this).attr("id").substring(1)).css("height", "auto");
						
					}else{
						$("#s"+$(this).attr("id").substring(1)).css("height", "30px");
					}
				});
				counter++;
			}
			
			<?php
				$valid_secs = array("1", "2", "3");
				if((isset($_GET['go_to']))&&(in_array($_GET['go_to'], $valid_secs))){
					?>
						$("#s<?php echo $_GET['go_to']; ?>").css("height", "auto");
					<?php
				}
			?>
			
			var see_u_list_click = 1;
			$("#see-all-users-opt").click(function(){
				see_u_list_click++;
				if(see_u_list_click%2==0){
					$("#lcp-userlist-container").slideDown();
				}else{
					$("#lcp-userlist-container").slideUp();
				}
			});
			
			$(".sel-u-edit").click(function(){
				var name = $(this).attr("id").substring(3);
				$("#act-on-user").val(name).css("border","3px dashed lightgreen");
			});
		});
		</script>
			<div class = "leader-cp-title">Welcome To The Community Manager<br>
			<span style = 'font-size:60%;'>Any major action you may need to do such as deleting your community<br>
			must be requested manually via contacting BuzzZap administration.<br>
			It is important as a communtity leader you frequently check this <br>
			manager in order to approve posts and attend to reported users, etc.</span>
			</div><br>
			<div class= "leader-cp-section" id = "s3">
				<b> <span style = "font-size:25px;cursor:pointer;color:salmon;" id = 't3'>General Management</span></b>
				<br><br>
				<hr size = "1">
				<b><u>Post Community News</u></b>
				<br>
				Community news feeds appear on all home pages of each member.
				<br><br>
				<form method = "POST">
					<textarea name = "ncf" id = "about-me-textarea" placeholder = "News...(500 charaters max)" style = "height:100px;"></textarea>	
					<br><input type = "submit" value = "Post" class = "leader-cp-submit">
				</form>
				<?php
					if(isset($_POST['ncf'])){
						$text = htmlentities($_POST['ncf']);
						if(strlen($text)<500){
							if(strlen($text)>40){
								add_com_feed($com_id, $text);
								setcookie("success", "1Successfully posted.", time()+10);
							}else{
								setcookie("success", "0Your news feed is too short.", time()+10);
							}
						}else{
							setcookie("success", "0Your news feed is too long.", time()+10);
						}
						header("Location: index.php?page=leader_cp&go_to=3");
					}
				?>
				<br>
				<hr size = "1">
				<b><u>Contact BuzzZap Administration</u></b>
				<br>
				Contact administration for any help or to request a major community action:
				<br><br>
				<form method = "POST">
					<textarea name = "ca-body" id = "about-me-textarea" placeholder = "Message..." style = "height:100px;"></textarea>	
					<br><input type = "submit" value = "Send" class = "leader-cp-submit">
				</form>
				<?php
					if(isset($_POST['ca-body'])){
						$text = nl2br(htmlentities($_POST['ca-body']));
						if(strlen($text)>10){
							$text = "Community name: ".get_user_community($_SESSION['user_id'], "com_name")." ;Community ID:".get_user_community($_SESSION['user_id'], "com_id")."; <br>".$text;
							$headers  = 'MIME-Version: 1.0' . "\r\n";
							$headers .= 'Content-type: text/html; charset=iso-8859-1' . "\r\n";
							$headers .= "From: ".get_user_field($_SESSION['user_id'], "user_email")."\r\n";
							mail("admin@buzzzap.com","Community Manager Help",$text,$headers);
							setcookie("success", "1Successfully sent.", time()+10);
						}else{
							setcookie("success", "0Your message is too short.", time()+10);
						}
					header("Location: index.php?page=leader_cp&go_to=3");
					}
				?>
				<br>
				<hr size = "1">
				<b><u>Change Community Passcode</u></b>
				<br><br> 
				<form action = "" method = "POST">
 					<input type = 'password' name = "new_passcode" placeholder = "New Passcode..." class = "leader-cp-fields" style = 'width:300px;'><br>
					<input type = 'password' name = "vnew_passcode" placeholder = "Verify New Passcode..." class = "leader-cp-fields" style = 'width:300px;'><br>
					<input type = "submit" value = "Change" class = "leader-cp-fields">
				</form>
				<br><br>
				<hr size = "1">
				<b><u>Invite Communities You Know To BuzzZap</u></b>
				<br><br> 


				Community Emails
				<form action = '' method = 'POST'>
					<input type = 'text' class = 'loggedout-form-fields-snc' name = 'invite_coms' placeholder = 'e.g email1, email2, email3'>
					<input type = 'submit' value = 'Invite' class = "leader-cp-fields" style = 'width: 100px;'>
				</form>

				<?php

					if(isset($_POST['invite_coms'])){
						$com_emails = htmlentities($_POST['invite_coms']);
						$com_emails = strlist_to_array($com_emails, false);
						foreach($com_emails as $e){
							$parse_vars = array("ename"=>trim($e), "com_name"=>get_user_community($user_id, "com_name"));
							$body = nl2br(static_cont_rec_vars(get_static_content("invite_coms_email"), $parse_vars));
							send_mail($e,"BuzzZap Invitation",$body,"auto@buzzzap.com");
						}
						setcookie("success", "1Successfully sent.", time()+10);
						header("Location: index.php?page=leader_cp&go_to=3");
					}

					if(isset($_POST['new_passcode'], $_POST['vnew_passcode'])){
						$passcode = htmlentities($_POST['new_passcode']);
						$vpasscode = htmlentities($_POST['vnew_passcode']);
						$errors  = array();
						if(strlen($password)<=3){
							$errors[]="Your password must be over 3 characters long.";
						}
						if($passcode!=$vpasscode){
							$errors[] = "Your passcodes do not match.";
						}

						$passcode = encrypt($passcode);

						if(count($errors)==0){
							$db->query("UPDATE communities SET com_password = ".$db->quote($passcode)." WHERE com_id = ".$db->quote($com_id));
							setcookie("success", "1Successfully updated passcode! Make sure to inform all members.", time()+10);	
						}else{
							setcookie("success", "0".implode(" ",$errors), time()+10);
						}
						header("Location: index.php?page=leader_cp&go_to=3");

					}
				?>
			</div>
			<br>
			<div class= "leader-cp-section" id = "s1">
				<b> <span style = "font-size:25px;cursor:pointer;color:salmon;" id = 't1'>User Management</span></b>
				<br><br>
				<hr size = "1">
				<b><u> User actions</u></b>
				<br>
				Use this system to alter a user in anyway.
				<br><br>
				 
				 <form action = "index.php?page=leader_cp&go_to=1" method = "POST">
				 	Enter the relevant user:<br>
				 	<input type = "text" name = "username_action" placeholder = "Enter Username..." class = "leader-cp-fields" id ='act-on-user'>
				 	<div id = "pred_results"></div>
				 	<input type = "hidden" name = "from-c-repo" value = "false" id = "fcr">
				 	<select class = "leader-cp-fields" name = "user_action" id ="spec-act">
				 		<option value = "na">Select Action</option>
				 		<option value = "del_user">Delete User (deletes the user for good)</option>
				 		<option value = "ban_user">Ban/suspend User (make account unaccessable)</option>
				 		<option value = "unban_user">Unban User (make account accessable)</option>
				 		<option value = "edit_user">Edit User (edit users infomation)</option>
				 		<option value = "reset_user">Reset User (deletes posts, reputation, votes, etc)</option>
				 		<option value = "cm_user">Turn on close moderation (every post made by this user will have to be approved by a leader before it is visible)</option>
				 		<option value = "tcm_user">Turn off close moderation (the user can post freely, without the need of a leader approving it)</option>
				 		<option value = "viewc_user">View User Content (view all a users posts, etc)</option>
				 	</select>
				 	<input type = "submit" value = "Submit" class = "leader-cp-submit" id = 'submit-act'>	
				 </form>
				 <span style = 'color:green;cursor:pointer;' id = 'see-all-users-opt'><u>See All Users</u></span>
				 <div id = "lcp-userlist-container">
				 <span style = 'font-size: 70%'>Click user to edit them</span>
				 	<br><br>
				 	<?php
				 		$users = $db->query("SELECT user_username, user_firstname, user_lastname FROM users WHERE user_id != ".$db->quote($user_id)." AND user_com = ".$db->quote($com_id));
				 		foreach($users as $user){
				 			echo "<span style = 'cursor:pointer;' id = 'eu-".$user['user_username']."' class = 'sel-u-edit'>".$user['user_username'].
				 			"</span><span style = 'float:right'>".$user['user_firstname']." ".$user['user_lastname']. "</span><hr size = '1'>";
				 		}
				 	?>
				 </div>
				 <?php
				 if(isset($_POST['username_action'],$_POST['user_action'])){
					if($_POST['user_action']!=="na"){
						$user_com = get_user_field($_SESSION['user_id'], "user_com");
						$username = htmlentities($_POST['username_action']);
						$action = htmlentities($_POST['user_action']);
						$valid_user_check = $db->query("SELECT user_username FROM users WHERE user_com = '$user_com' AND user_rank < 3 AND user_username = ".$db->quote($username))->fetchColumn();
						if(!empty($valid_user_check)){
							if($action=="edit_user"){
								// real column => display name
								$allowed_columns = array("user_username"=>"Username", "user_password"=>"Password", "user_firstname"=>"Firstname", "user_lastname"=>"Lastname", "user_email"=>"Email", "user_rank"=>"Rank");
								$options = "<option val = ''>-----</option>";
								foreach($allowed_columns as $column=>$display_name){
									$options .= "<option value = '".$column."'>".$display_name."</option>";
								}
								$html = "
									<span style = 'color:grey;'>
										<span id = 'close_edit_form' style = 'float:right;'>x</span><br>
										<center><b><u>Edit User</b></u></center><br><br>
										<form action = '' method = 'POST'>
											Change ".$username."'s...<br><br>
											 <select name = 'edit_column' id ='edit_column' class = 'leader-cp-fields' style = 'background:white;'>
												".$options."
											</select><br><br>
											<input type = 'text' name = 'new_val' placeholder = 'New Value...' class = 'leader-cp-fields' id = 'edit_new_value'>
											<input type = 'hidden' value = '".$username."' name = 'username'><br><br>
											<span id = 'ex_info_cuser' style = 'font-size: 70%'></span><br>
											<input type = 'submit' class = 'leader-cp-sumbit'>
										
										</form>
									</span>
									<script>
										$(document).ready(function(){
											$('#close_edit_form').click(function(){
												$('#quick-msg').fadeOut();	
											});
											$('#edit_column').change(function(){
												if($(this).val()!='-----'){
													var dis_name = $(this).val().substring(5);
												}else{
													var dis_name = '...';
												}	
												if($(this).val()=='user_password'){
													$('#edit_new_value').attr('type', 'password');
												}else{
													$('#edit_new_value').attr('type', 'text');
												}
												if($(this).val()=='user_rank'){
													$('#ex_info_cuser').html('Enter \'leader\' or \'member\'. Remember, giving a user the leader rank will give them full access to this community manager.<br>');
												}else{
													$('#ex_info_cuser').html('');
												}
												$('#edit_new_value').attr('placeholder', 'New '+dis_name);
											});
										});
									</script>
								";
								setcookie("success", "2".$html, time()+10);
								
							}else{
								$user_id = $db->query("SELECT user_id FROM users WHERE user_username = '$username'")->fetchColumn();
								$result= action_user($user_id, $action);
								if($action=="viewc_user"){
									echo "<hr size = '1'><br><b>Results:</b><br>";
									foreach($result as $txt=>$link){
										echo "<a href = '".$link."'>".$txt."</a><hr size = '1'>";
									}
								}else{
									setcookie("success", "1Successfully altered user!", time()+10);	
								}
							}
						}else{
							setcookie("success", "0The user entered is invalid", time()+10);
						}
					}
					if($action!="viewc_user"){
						header("Location: index.php?page=leader_cp&go_to=1");	
					}	
				}
				?>
				 <br><hr size = "1">
				 <b><u>Add User</u></b>
				 <br>Add a new user to your community.<br><br>
				 <form action = "" method = "POST">
					
					<input type= "text" maxlength = "11" autocomplete="off" spellcheck="false" name = "au_username"  class = "leader-cp-fields" placeholder = "Username"><br>													
					<input type= "text" autocomplete="off" spellcheck="false" name = "au_firstname"  style  = "width:100px;" class = "leader-cp-fields" placeholder = "Firstname">
					<input type= "text" autocomplete="off" spellcheck="false" name = "au_lastname"  style  = "width:96px;" class = "leader-cp-fields" placeholder = "Lastname"><br>
					<input type= "text" autocomplete="off" spellcheck="false" name = "au_email"  class = "leader-cp-fields" placeholder = "Email"><br>													
					<input type= "password" autocomplete="off" spellcheck="false" name = "au_password"  class = "leader-cp-fields" placeholder = "Password"><br>													
					<input type= "password" autocomplete="off" spellcheck="false" name = "au_vpassword"  class = "leader-cp-fields" placeholder = "Verify Password"><br>


					<input type = "submit" value = "Add User" class = "leader-cp-fields"><br><br>
				 </form>
				 <?php
				 	
				 	if(isset($_POST['au_username'], $_POST['au_password'], $_POST['au_vpassword'],$_POST['au_firstname'], $_POST['au_lastname'],$_POST['au_email'])){
				 		$reg = register_user($_POST['au_username'], $_POST['au_password'], $_POST['au_vpassword'],$_POST['au_firstname'], $_POST['au_lastname'],"","",$_POST['au_email'], get_user_community($_SESSION['user_id'], "com_id"));

				 		if($reg=="true"){
				 			setcookie("success", "1Successfully added user!", time()+10);
				 			$parse_vars = array("au_firstname"=>$_POST['au_firstname'], 
				 				"lusername"=>get_user_field($_SESSION['user_id'], "user_username"),
				 				"au_username"=>$_POST['au_username'], "au_password"=>$_POST['au_password'],
				 				"lemail"=>get_user_field($_SESSION['user_id'], "user_email"));
				 			$body = static_cont_rec_vars(get_static_content("email_added_user"), $parse_vars);
				 			send_mail($_POST['au_email'],"You are on BuzzZap!",$body,"auto@buzzzap.com");
				 			header("Location: index.php?page=leader_cp&go_to=1");
				 		}else{
				 			header("Location: index.php?page=leader_cp&go_to=1&reg_ue=v62sd56".implode(",",$reg));
				 		}	
				 	}
			 		if(isset($_GET['reg_ue'])){	
			 			$reg = $_GET['reg_ue'];
			 			if(substr($reg, 0,7)=="v62sd56"){
				 			$reg = substr($reg, 7);
				 			$reg = explode(",",$reg);
				 			foreach($reg as $e){
				 				if(substr($e, 0, 4)=="Your"){
				 					$e = "The ".substr($e, 4, strlen($e));
				 				}
				 				echo "<span style= 'color: salmon;'>-".$e."</span><br>";
				 			}
			 			}
			 		}

				 ?>
				 <br><hr size = "1">
				 <b><u>Banned users</u></b>
				 <br>
				 Users who can not access there account because they have been banned. <br>
				 To Unban them, use the user actions section above.
				 <br><br>
				 
				<?php
					
					$banned_users = $db->prepare("SELECT user_username FROM users WHERE user_rank = 0 AND user_com = :com_id");
					$banned_users->execute(array("com_id"=>$com_id));
					if($banned_users->rowCount()!==0){
						while($row = $banned_users->fetch(PDO::FETCH_ASSOC)){
							echo $row['user_username']."<br>";	
						}
					}else{
						echo "There are no banned users. ";	
					}
				?>	
				 <br><br><hr size = "1">
				 <b><u>Reported Users</u></b>
				 <br>
				 Users who have been reported by other users due to spamming, trolling, offensive content, etc.
				 <br><br>
				<?php
				$get_reported = $db->prepare("SELECT * FROM reported_users WHERE com_id = :com_id");
				$get_reported->execute(array("com_id"=>$com_id));
				//0 = ban
				//1 = close mod
				//2 = delete user
				//3 = dismiss report
				if($get_reported->rowCount()>0){
					while($row = $get_reported->fetch(PDO::FETCH_ASSOC)){
						echo "<br>&middot; ".$row['reported_user']." <span style = 'color:black;'> has been reported by </span>".$row['reported_by']."  <span style = 'color:black;'> because: </span>".$row['reason']."<br>
						- <a class = 'act-on-rep' href = 'index.php?page=leader_cp&go_to=1&resrepo=0".$row['reported_user']."&ex1=".$row['reported_by']."&ex2=".$row['time']."&ex3=".$row['fc']."'>Ban User</span>
						- <a class = 'act-on-rep' href = 'index.php?page=leader_cp&go_to=1&resrepo=1".$row['reported_user']."&ex1=".$row['reported_by']."&ex2=".$row['time']."&ex3=".$row['fc']."'>Put user on close moderation</span>
						- <a class = 'act-on-rep' href = 'index.php?page=leader_cp&go_to=1&resrepo=2".$row['reported_user']."&ex1=".$row['reported_by']."&ex2=".$row['time']."&ex3=".$row['fc']."'>Delete User</span>
						- <a class = 'act-on-rep' href = 'index.php?page=leader_cp&go_to=1&resrepo=3".$row['reported_user']."&ex1=".$row['reported_by']."&ex2=".$row['time']."&ex3=".$row['fc']."'>Dismiss Report</a>
						<br><br>";
					}
				}else{
					echo "No reported users.";
				}	
				?>
				
				<br><br><hr size = "1">
				 <b><u>Users On Close Moderation</u></b><br>
				 <a href = "index.php?page=leader_cp&go_to=1&all_ucm=true" style = "color: salmon;">Put All Users on Close Moderation</a><br>
				 Users who cannot post any content without it being approved by you.<br>
				 To take a user off close moderation use the user actions section above.
				 <br><br>
				<?php

				$get_cm = $db->prepare("SELECT user_username FROM users WHERE user_com = :com_id AND close_mod = 1");
				$get_cm->execute(array("com_id"=>$com_id));

				if($get_cm->rowCount()>0){
					while($row = $get_cm->fetch(PDO::FETCH_ASSOC)){
						echo $row['user_username']."<br>";
					}
				}else{
					echo "No users are on close moderation";
				}	

				if(isset($_GET['all_ucm'])){
					$db->query("UPDATE users SET close_mod = 1 WHERE user_com = ".$db->quote($com_id)."AND user_rank = 1");
					header("Location: index.php?page=leader_cp&go_to=1");
				}

		
				?>
				
				<?php
				if(isset($_GET['resrepo'], $_GET['ex1'], $_GET['ex2'])){
					$r = htmlentities($_GET['resrepo']);
					$name = substr($r ,1, strlen($r));
					$by = htmlentities($_GET['ex1']);
					$time = htmlentities($_GET['ex2']);
					$fc = htmlentities($_GET['ex3']);
					$action = substr($r, 0,1);
					switch($action){
						case "0":
							$action = "ban_user";
							break;
						case "1":
							$action = "cm_user";
							break;
						case "2":
							$action = "del_user";
							break;	
						case "3":
							$action = "d";
							break;		
					}
					if($action!="d"){
						$uid = $db->query("SELECT user_id FROM users WHERE user_username = ".$db->quote($name))->fetchColumn();
						action_user($uid, $action);
					}
					if($fc=="1"){
						if($action!="d"){
							$cid = $db->query("SELECT `cid` FROM `reported_content` WHERE `by` = '$by' AND `reported` = '$name' AND `time` = '$time'")->fetchColumn();
							$c = $db->query("SELECT `id_c_name` FROM `reported_content` WHERE `by` = '$by' AND `reported` = '$name' AND `time` = '$time'")->fetchColumn();
							$t = $db->query("SELECT `c_table` FROM `reported_content` WHERE `by` = '$by' AND `reported` = '$name' AND `time` = '$time'")->fetchColumn();
							$db->query("DELETE FROM `".$t."` WHERE `".$c."` = '$cid'");
						}
						$db->query("DELETE FROM `reported_content` WHERE `by` = '$by' AND `reported` = '$name' AND `time` = '$time'");
					}
					$delete = $db->prepare("DELETE FROM reported_users WHERE reported_by = :by AND reported_user = :reported AND time = :time");
					$delete->execute(array("by"=>$by, "reported"=>$name, "time"=>$time));
					
					setcookie("success", "1Successful!", time()+10);	
					header("Location: index.php?page=leader_cp&go_to=1");	
				}
				
				?>
					<br><br>
			</div><br>
			<div class= "leader-cp-section" id = 's2'>
				<b> <span style = "font-size:25px;cursor:pointer;color:salmon;" id = 't2'>Content Management</span></b>
				<br><br>To delete/manipulate existing posts, visit the actual post.
				<hr size = "1">
				<b><u>Debates/I Wonder questions awaiting approvel</u></b>
				<br><br>
				<div style = "overflow:scroll;border:1px solid;">
					<?php
					$get_debates = $db->prepare("SELECT * FROM debating_threads WHERE visible = 0 AND user_com_id = :com_id");
					$get_debates->execute(array("com_id"=>$com_id));
					
					$get_iw = $db->prepare("SELECT * FROM iwonder_threads WHERE active = 0");
					$get_iw->execute();
					
					if($get_debates->rowCount()+$get_iw->rowCount()!=0){
						while($row = $get_debates->fetch(PDO::FETCH_ASSOC)){
							echo "<span style = 'color:black;'>".$row['thread_title']."</span> - created by <span style = 'color:black;'>".$row['thread_starter']."</span>
						 <a href = 'index.php?page=leader_cp&d_id=".$row['thread_id']."&d_act=allow&t=d' style = 'color:#8FBC8F;'>Allow</a>
						  &middot;
						  <a href = 'index.php?page=leader_cp&d_id=".$row['thread_id']."&d_act=delete&t=d' style = 'color:#CD9B9B'> Delete</a>
						  <br><hr size = '1'><br>";
						}
						while($row = $get_iw->fetch(PDO::FETCH_ASSOC)){
							if(get_user_com_by_name($row['thread_starter'])==$com_id){
								echo "<span style = 'color:black;'>".$row['thread_title']."</span> - created by <span style = 'color:black;'>".$row['thread_starter']."</span>
								 <a href = 'index.php?page=leader_cp&d_id=".$row['thread_id']."&d_act=allow&t=i' style = 'color:#8FBC8F;'>Allow</a>
							 	 &middot;
							 	 <a href = 'index.php?page=leader_cp&d_id=".$row['thread_id']."&d_act=delete&t=i' style = 'color:#CD9B9B'> Delete</a>
							 	 <br><hr size = '1'><br>";
							 } 
						}
					}else{
						echo "No debates or I Wonder questions are awaiting your approvel.";	
					}
					
					?>
				</div>	
				<br><br>
				<hr size = "1">
				<b><u>Posts awaiting approvel</u></b>
				<br><br>
				<div style = "overflow:scroll;border:1px solid;">
					<?php
					$get_posts = $db->prepare("SELECT * FROM thread_replies WHERE visible = 0");
					$get_posts->execute();
					$get_iw_posts = $db->prepare("SELECT * FROM iwonder_replies WHERE visible = 0");
					$get_iw_posts->execute();
					if(($get_posts->rowCount() + $get_iw_posts->rowCount()) !=0){
						while($row = $get_posts->fetch(PDO::FETCH_ASSOC)){				
							$com_id = get_user_com_by_name($row['user_replied']);
							if($com_id == get_user_community($_SESSION['user_id'], "com_id")){
								echo "<span style = 'color:black;'>".$row['reply_text']."</span> - created by <span style = 'color:black;'>".$row['user_replied']."</span>
							 <a href = 'index.php?page=leader_cp&p_id=".$row['reply_id']."&p_act=allow&t=d' style = 'color:#8FBC8F;'>Allow</a>
							  &middot;
							  <a href = 'index.php?page=leader_cp&p_id=".$row['reply_id']."&p_act=delete&t=d' style = 'color:#CD9B9B'> Delete</a>
							  <br><hr size = '1'><br>";
							} 
						}
						while($row = $get_iw_posts->fetch(PDO::FETCH_ASSOC)){				
							$com_id = get_user_com_by_name($row['user_replied']);
							if($com_id == get_user_community($_SESSION['user_id'], "com_id")){
								echo "<span style = 'color:black;'>".$row['reply_text']."</span> - created by <span style = 'color:black;'>".$row['user_replied']."</span>
							 <a href = 'index.php?page=leader_cp&p_id=".$row['reply_id']."&p_act=allow&t=i' style = 'color:#8FBC8F;'>Allow</a>
							  &middot;
							  <a href = 'index.php?page=leader_cp&p_id=".$row['reply_id']."&p_act=delete&t=i' style = 'color:#CD9B9B'> Delete</a>
							  <br><hr size = '1'><br>";
							} 
						}
					}else{
						echo "No posts are awaiting your approvel.";	
					}
					
					?>
				</div>
				<br><br>
				<hr size = "1">

			</div>
		<?php
		
		if(isset($_POST['edit_column'], $_POST['new_val'], $_POST['username'])){
			$username = htmlentities($_POST['username']);
			$euser_id = $db->query("SELECT user_id FROM users WHERE user_username = ".$db->quote($username))->fetchColumn();
			$new_val = htmlentities($_POST['new_val']);
			$field = htmlentities($_POST['edit_column']);
			$users_com = get_user_community($euser_id, "com_id");
			$leader_com = get_user_community($_SESSION['user_id'], "com_id");
			if($users_com == $leader_com){
				$check_email = $db->query("SELECT user_email FROM users WHERE user_email = ".$db->quote($new_val));
				if((($field=="user_email")&&(filter_var($new_val, FILTER_VALIDATE_EMAIL)))||($field!="user_email")){
					if($field == "user_password"){
						$new_val = encrypt($new_val);
					}
					if(($field=="user_email"&&$check_email->rowCount()==0)||($field!="user_email")){
						$valid_rank_opts = array("leader", "member");
						if( ( ($field=="user_rank" ) && (in_array($new_val, $valid_rank_opts) ) ) || ($field!="user_rank") ){

						
							if(update_user_field($euser_id, $new_val, $field)){
								setcookie("success", "1Successfully altered user!", time()+10);	
							}else{
								setcookie("success", "0Unknown error.", time()+10);
							}

						}else{
							setcookie("success", "0Invalid rank, must be 'leader' or 'member'.", time()+10);
						}
					}else{
						setcookie("success", "0That email is already taken.", time()+10);
					}
				}else{
					setcookie("success", "0Invalid email.", time()+10);	
				}
			}else{
				setcookie("success", "0You cannot edit this user.", time()+10);	
			}	
							
			header("Location: index.php?page=leader_cp&go_to=1");
		}
		
		if(isset($_GET['d_act'], $_GET['d_id'], $_GET['t'])){
			
			$id = htmlentities($_GET['d_id']);
			$get_starter_username = ($_GET['t']=="i")?$db->query("SELECT thread_starter FROM iwonder_threads WHERE thread_id=".$db->quote($id))->fetchColumn():$db->query("SELECT thread_starter FROM debating_threads WHERE thread_id=".$db->quote($id))->fetchColumn();
			$_u_id = $db->query("SELECT user_id FROM users WHERE user_username = ".$db->quote($get_starter_username))->fetchColumn();
			$thread_name = ($_GET['t']=="i")?$db->query("SELECT thread_title FROM iwonder_threads WHERE thread_id = ".$db->quote($id))->fetchColumn():$db->query("SELECT thread_title FROM debating_threads WHERE thread_id = ".$db->quote($id))->fetchColumn();
					
			$user_com = get_user_community($_SESSION['user_id'], "com_id");
			if($_GET['d_act']=="delete"){
				$action_word = "deleted";
				$worked_word = "1Successfully ";
				if($_GET['t']=="d"){
					//dis_debate
					
					$delete = $db->prepare("DELETE FROM debating_threads WHERE thread_id = :id AND com_id = :com_id");
					$delete->execute(array("id"=>$id, "com_id"=>$user_com));
					
					
					add_note($_u_id, "Your debate '".$thread_name."' was disapproved by a leader. ", "");
				}else{
					
					if(get_user_com_by_name($get_starter_username)==$com_id){
						//dis_iwonder
						
						$thread_name = $db->query("SELECT thread_title FROM debating_threads WHERE thread_id = ".$db->quote($id))->fetchColumn();
			
						$delete = $db->prepare("DELETE FROM iwonder_threads WHERE thread_id = :id");
						$delete->execute(array("id"=>$id));
						add_note($_u_id, "Your I Wonder question '".$thread_name."' was disapproved by a leader. ", "");
					}
					
					$action_word = "disapproved";
				}
				
			}else{
				$user_com_id = $db->query("SELECT com_id FROM debating_threads WHERE thread_id = ".$db->quote($id))->fetchColumn();
				
				
				if((get_user_com_by_name($get_starter_username)==$user_com)){
					if($_GET['t']=="d"){
						make_visible($id, "debating_threads");
						add_note($_u_id, "Your debate '".$thread_name."' was approved by a leader, and now is visible to the public. ", "index.php?page=view_private_thread&thread_id=".$id);
						
					}else{
						$delete = $db->prepare("UPDATE iwonder_threads SET active = 1 WHERE thread_id = :id");
						$delete->execute(array("id"=>$id));
						add_note($_u_id, "Your I Wonder question '".$thread_name."' was approved by a leader, and now is visible to the public. ", "");
						
					}
					$action_word = "approved";
					$worked_word = "1Successfully ";
				}else{
					$worked_word = "0Failed to alter the";
					$action_word = "";
				}
			}
			setcookie("success", $worked_word.$action_word." debate/I Wonder question.", time()+10);
			header("Location:index.php?page=leader_cp&go_to=2");
		}
		
		if(isset($_GET['p_act'], $_GET['p_id'], $_GET['t'])){
			$id = htmlentities($_GET['p_id']);
			$user_com = get_user_community($_SESSION['user_id'], "com_id");
			$t = htmlentities($_GET['t']);
			if($t=="d"||$t=="i"){
				$table = ($t=="i")? "iwonder_replies" : "thread_replies";
				$user_replied = $db->query("SELECT user_replied FROM `".$table."` WHERE reply_id = ".$db->quote($id))->fetchColumn();
				$com_id = get_user_com_by_name($user_replied);
				if($com_id==$user_com){
					if($_GET['p_act']=="delete"){
						$action_word = "deleted";
						$worked_word = "1Successfully ";
						$delete = $db->prepare("DELETE FROM `".$table."` WHERE reply_id = :id");
						$delete->execute(array("id"=>$id));
					}else{
						make_visible($id, $table);
						$action_word = "approved";
						$worked_word = "1Successfully ";
					}
				}else{
					$worked_word = "0Failed to alter ";	
				}	
				setcookie("success", $worked_word.$action_word." post.", time()+10);
				header("Location:index.php?page=leader_cp&go_to=2");
			}else{
				setcookie("success", "0Unknown error.", time()+10);
				header("Location:index.php?page=leader_cp&go_to=2");
			}	
		}
	}else{
		header("Location: index.php?page=home");
	}
}else{
	header("Location: index.php?page=home");
}
?>