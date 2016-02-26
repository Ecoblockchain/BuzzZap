<?php
if($check_valid!="true"){
	header("Location: index.php?page=home");
	exit();
}
if(loggedin()){
	if((isset($_GET['user']))&&(!empty($_GET['user']))){
		$view_user_id = htmlentities($_GET['user']);
		if(get_user_field($view_user_id, "user_username")==""){
			header("Location: index.php?page=home");
		}
		$user_id = $_SESSION['user_id'];
		$user_info = array("user_username"=>"", "user_firstname"=>"", "user_lastname"=>"", "user_com"=>"","user_rep"=>"", "reg_beliefs"=>"", "pol_views"=>"");
		$label_format = array("user_username"=>"Username", "user_firstname"=>"Firstname", "user_lastname"=>"Lastname",
		 "user_com"=>"Community", "user_rep"=>"Reputation", "user_email"=>"Email", "reg_beliefs"=>"Religious Beliefs", "pol_views"=>"Political Views");

		$views_firstname = $db->query("SELECT user_firstname FROM users WHERE user_id =".$db->quote($view_user_id)."")->fetchColumn();
		$views_lastname = $db->query("SELECT user_lastname FROM users WHERE user_id =".$db->quote($view_user_id)."")->fetchColumn();
		
		$valid_edit = array("edit_user_firstname","edit_user_lastname","edit_user_email", "edit_reg_beliefs", "edit_pol_views");
		
		if($view_user_id == $user_id){
			$user_info["user_email"] = "";
			$title = "My ";
			$path_page_name = "Me";
		}else{
			$title = $views_firstname." ".$views_lastname."'s ";
			$path_page_name = substr($title, 0,strlen($title)-3);
			if(check_not_rep_inc($_SESSION['user_id']."-viewed_profile", $view_user_id)){
				add_rep(2,$view_user_id);
				$db->query("INSERT INTO only_once_rep_inc VALUES('".$_SESSION['user_id']."-viewed_profile', '$view_user_id', 1)");
			}	
		}	
		
		if(has_full_profile($user_id)&&check_not_rep_inc("full_profile", $user_id)){
			add_rep(5, $user_id);
			$db->query("INSERT INTO only_once_rep_inc VALUES('full_profile', '$user_id', 1)");
		}

		?>
		<div class = 'page-path'>User Profile > <?php echo $path_page_name; ?> </div><br>
		<div id = 'all-boxes' style = "">
			
			<span class = "profile-title"><?php echo $title. "Profile"; ?></span>
			<?php
			if((isset($_GET['rul']))&&($_GET['rul']=="true")){
			?>
				<div id = "report-user-form">
					<form action = "" method = "POST">
						<textarea maxlength = "330" name = "rep_reason" id = "report-textarea" placeholder= "Reason for reporting this user..."></textarea>
						<input type = "submit" class = "mreply-submit" value = "Report User" style = "width:140px;">
					</form>
				</div>
			<?php
			
				if(isset($_POST['rep_reason'])){
					$reason = htmlentities($_POST['rep_reason']);
					if(strlen($reason)>330){
						setcookie("success", "0Your reason must be shorter.", time()+10);
					}else if(strlen($reason)<20){
						setcookie("success", "0Your reason must be longer.", time()+10);
					}else{
						report_user(get_user_field($_SESSION['user_id'],"user_username"),get_user_field($view_user_id,"user_username"), $reason);
						setcookie("success", "1Successfully reported user.", time()+10);
					}
					header("Location:index.php?page=profile&user=".$view_user_id);
				}
			}
			?>	
			<div class = "profile-info-container" style = "width: 96.5%;">
				<?php
					if($user_id!==$view_user_id){
				?>
				<a href = "index.php?page=profile&user=<?php echo $view_user_id; ?>&rul=true" class = "action-uprofile-link"> &middot; Report This User</a>
				
				<?php
				
					}
				?>
			<?php
				if($view_user_id !=$user_id){
					$f_st = get_friend_status(get_user_field($_SESSION['user_id'], "user_username"), get_user_field($view_user_id, "user_username"));
					if($f_st=="none"){
						$friend_opt = "<a class = 'action-uprofile-link' href = 'index.php?page=profile&user=".$view_user_id."&add_friend=true'>Add as friend </a>";
					}else if($f_st=="pending"){
						$friend_opt = "<span class = 'action-uprofile-link'>Friend request sent </span>";
					}else if($f_st=="friends"){
						$friend_opt = "<span class = 'action-uprofile-link'>Friends </span>";
					}else{
						$friend_opt = "<a class = 'action-uprofile-link' href = 'index.php?page=profile&user=".$view_user_id."&acc_friend=true'>Accept friend request </a>";
					}
					echo $friend_opt."<br>";
				}
			
			?>
				<div id = "profile-info-container1" style  = "display:none;width:50%;float:left;margin-top:30px;">
					<br>
						<form action = "" method = "POST" style = "margin-left: 50px;">
							<input type = "password" class= "loggedout-form-fields" placeholder = " New Password" name = "new_pass"><br><br>
							<input type = "password" class = "loggedout-form-fields" placeholder = " Confirm New Password" name = "new_pass_v"><br><br>
							<input type = "submit" class = "loggedout-form-submit" style = "margin-left:20px;">
							<span id = "back-info2" style = "font-size:80%;cursor:pointer;margin-left: 20px;"><< back</span>
						</form>
						
				</div>
				<div id = "profile-info-container2" style = "margin-top:10px;float: left;width:50%;">
					<span id = "sub-profile-title">General</span><br><br>
					<?php
						
					foreach($user_info as $column => &$value){
						if(substr($column, 0, 4)=="user"){
							$table = "users";
							$width = "";
						}else{
							$table = "about_user";
							$width = "width:200px;";
						}
						$result = $db->query("SELECT ".$column." FROM ".$table." WHERE user_id = ".$db->quote($view_user_id)."")->fetchColumn();
						$value = $result;
						if($column == "user_com"){
							$value = $db->query("SELECT com_name FROM communities WHERE com_id = '$result'")->fetchColumn();	
						}
						$label = $label_format[$column];
						if(strlen($value)==0){
							$value = "---";
						}
						if(($view_user_id == $user_id)&&(in_array("edit_".$column, $valid_edit))){
							
						
							echo $label.": <input type = 'text' style='".$width."color:#06e5b1;' value = '".$value."' name = 'edit_".$column."' 
							class = 'profile-edit-textfield'  autocomplete='off' spellcheck='false' maxlength='40' id = '".$column."'><br>";
							
							
							
							?>
							
								<script>
									$(document).ready(function(){
										var old_val = $("#<?php echo $column; ?>").val();
										$("#<?php echo $column; ?>").focus(function(){
											$(this).css("border-bottom", "2px dashed lightblue");
										}).blur(function(){
											$(this).css("border", "none");
											var new_val = $(this).val();
											if(new_val!=old_val){
												window.location="index.php?page=profile&user=<?php echo $view_user_id; ?>&edit_<?php echo $column; ?>=" + $(this).val();
											}
										});
										
									});
								</script>
							<?php
						}else{
							echo $label.": <span style = 'color:#06e5b1;'>".$value."</span><br>";
						}
					}
					?>
						
					<?php
					if($view_user_id == $user_id){
						echo "<div class = 'edit-instructions'>Click a field to edit it, or
				<span style = 'color:lightblue;cursor:pointer;' id = 'change-pass-link'><b>change password</b></span></div>";
						foreach($valid_edit as $column){
							if(isset($_GET[$column])){
								
								$value = htmlentities($_GET[$column]);
								if((strlen($value)>1)||(substr($column, 0,4)!="user")){
									$column = substr($column, 5);
									if(substr($column, 0,4)!="user"){

										if(strlen($value)<22){
											$update = $db->prepare("UPDATE about_user SET `$column` = :value WHERE user_id = :user_id");
											$update->execute(array("value"=>$value,"user_id"=>$user_id));
											setcookie("success", "1Successfully updated!", time()+10);
										}else{
											setcookie("success", "0Field must be shorter.", time()+10);
										}	
									}else if((($column=="user_email")&&(filter_var($value, FILTER_VALIDATE_EMAIL)))||($column!=="user_email")){		
										$check_email = $db->query("SELECT user_email FROM users WHERE user_email = ".$db->quote($value));
										if($check_email->rowCount()==0){
											$update = $db->prepare("UPDATE users SET `$column` = :value WHERE user_id = :user_id");
											$update->execute(array("value"=>$value,"user_id"=>$user_id));
											setcookie("success", "1Successfully updated!", time()+10);
										}else{
											setcookie("success", "0That email is already taken.", time()+10);
										}
									}else{
										setcookie("success", "0Invalid email.", time()+10);
									}		
								}else{
									setcookie("success", "0Field must be longer.", time()+10);
								}
								header("Location: index.php?page=profile&user=".$user_id);		
							}	
						}	
						
						if(isset($_GET['edit_aboutme'])){
							echo $text = htmlentities($_GET['edit_aboutme']);
							if(strlen($text)>10){
								$check_exists = $db->query("SELECT general FROM about_user WHERE user_id = ".$db->quote($user_id))->fetchColumn();
								if(isset($check_exists)){
									$query = "UPDATE about_user SET general = :text WHERE user_id = :user_id";
								}else{
									$query = "INSERT INTO about_user VALUES(:user_id, :text, '','')";
								}
								$update = $db->prepare($query);
								$update->execute(array("text"=>$text, "user_id"=>$user_id));
							}else{
								setcookie("success", "0Too short.");
							}
							
							header("Location: index.php?page=profile&user=".$view_user_id);
							
						}
						
						$textarea_content = "";
					}else{
						$textarea_content = "This user has not written anything about him/herself yet.";
						?>
						<script>
						$(document).ready(function(){
							$(".amtxt").attr("disabled", "disabled");
						});
						</script>
						<?php
					}
					
					?>
					
				</div> 
				<?php
				$get_about_user = $db->query("SELECT general FROM about_user WHERE user_id=".$db->quote($view_user_id))->fetchColumn();
					if(!empty($get_about_user)){
						$textarea_content = $get_about_user;
					}
				
					
					?>
					<div id = "about-me-container">
						<span id = "sub-profile-title" style = "margin-top: -10px;position:absolute;">About Me</span><br>
						<textarea id = "about-me-textarea" class = "amtxt" placeholder = "Where are you from? What do you do? What do you like to do? Which subjects do you study?"><?php echo $textarea_content; ?></textarea>
					</div>
					<script>
						$(document).ready(function(){
							var old_val_ = $(".amtxt").val();
							$(".amtxt").blur(function(){
								var new_val = $(this).val();
								if(old_val_!==new_val){
									window.location="index.php?page=profile&user=<?php echo $view_user_id; ?>&edit_aboutme=" + $(this).val();
								}
							});
							$("#p_f_close").click(function(){
								$("#quick-msg").fadeOut();
							});
						});
					</script>
					<br>
					<?php
					if($view_user_id == $user_id){	
				?>
				
				<script>
					$("#change-pass-link").click(function(){
						$(".edit-instructions").fadeOut();
						$("#profile-info-container2").fadeOut();
						setTimeout(function(){$("#profile-info-container1").fadeIn();}, 1000);
					});
					
					$("#back-info2").click(function(){
						$("#profile-info-container1").fadeOut();
						setTimeout(function(){$("#profile-info-container2").fadeIn();$(".edit-instructions").fadeIn();}, 1000);
					});
				</script>
				<?php
					
						if(isset($_POST['new_pass'], $_POST['new_pass_v'])){
							$new_pass = htmlentities($_POST['new_pass']);
							$new_passv = htmlentities($_POST['new_pass_v']);
							
							if(strlen($new_pass)>3){
								if($new_pass==$new_passv){
									$pass = encrypt($new_pass);
									$update = $db->prepare("UPDATE users SET user_password = :pass WHERE user_id = :user_id");
									$update->execute(array("pass"=>$pass, "user_id"=>$user_id));
									setcookie("success", "1Successfully changed password!", time()+10);
								}else{
									setcookie("success", "0Passwords do not match.", time()+10);
								}
							}else{
								setcookie("success", "0Password must be longer.", time()+10);
							}	
							header("Location: index.php?page=profile&user=".$user_id);	
						}
					}
					if(isset($_GET['add_friend'])&&$_GET['add_friend']=="true"){
						if(!add_friend(get_user_field($_SESSION['user_id'], "user_username"), get_user_field($view_user_id, "user_username"))){
							setcookie("success", "0Uknown error.", time()+10);
						}
						header("Location: index.php?page=profile&user=".$view_user_id);
					}
					
					if(isset($_GET['acc_friend'])&&($_GET['acc_friend']=="true")){
						if(!accept_f_req(get_user_field($_SESSION['user_id'], "user_username"), get_user_field($view_user_id, "user_username"))){
							setcookie("success", "0Uknown error.", time()+10);
						}
						header("Location: index.php?page=profile&user=".$view_user_id);
						
					}
					
				?>
			</div>
			<?php
				$username = get_user_field($view_user_id, "user_username");
				if(count(get_pending_friends($username))>0&&$view_user_id==$user_id){
				
				?>
					<div id = 'quick-msg' class = 'frb' style = "">
							<script>
							$(document).ready(function(){
								$("#p_f_close").click(function(){
									$("#quick-msg").fadeOut();
								});
							});
							</script>
							<span id = "p_f_close" style = "color:grey;cursor: pointer;">X</span>
					
							<center>Friend request(s)</center>
							<div class= 'note-bubble' id = 'note-bubble3' style = 'margin-top: -20px;margin-left: 250px;'>
								<?php
								 $friend_p_q = count(get_pending_friends(get_user_field($user_id, "user_username")));
								 if($friend_p_q>0){
									echo $friend_p_q;
								 }
								 ?>
							</div>
							<br>
							<span style = "letter-spacing:1px;color:grey;">
								<?php 
								foreach(get_pending_friends($username) as $user){
									echo "<div style = ''> ".$user."</div>
									 <div style = 'float:right;margin-top:-17px;'><a href = 'index.php?page=profile&user=".$_GET['user']."&acc_friend=".$user."' style = 'color:#43CD80;'>Accept </a>|
									 <span style = 'color:#FF6A6A;'> Decline</span></div>";
								}
								?>
							</span>	
				
					</div>
					<?php
			
					if(isset($_GET["acc_friend"])){
						$friend_id = htmlentities($_GET['acc_friend']);
						if(get_friend_status($friend_id, $username)=="pending"){
							accept_f_req($username, $friend_id);
							setcookie("success", "1Accepted friend request.", time()+10);
						}else{
							setcookie("success", "0Uknown error.", time()+10);
						}
				
						header("Location: index.php?page=profile&user=".$_GET['user']);
					}
				}	
			?>
		
		<br>
			<div id = "profile-bottom-container">
				<div class = "profile-bottom-inner-container" style = 'overflow: auto;'>
					<div id = "profile-info-container3" style = "width: 100%;height: 10px;padding:10px;">
						<div id = "sub-profile-title">Badge Collection</div>
						<?php
							$get_badges = $db->prepare("SELECT text FROM badges WHERE user_id = :id");
							$get_badges->execute(array("id"=>$view_user_id));
							echo "<span style = 'font-size: 60%;'><br>".$get_badges->rowCount()." badge(s) in total</span><br><br>";
							while($row = $get_badges->fetch(PDO::FETCH_ASSOC)){
								echo "<div class = 'badge-body' id = 'badge-c-".rand(1,6)."' style = 'float: left;'><br><br>".$row['text']."</div>";
							}
							
						?>
					</div>
				</div>
				<div class = "profile-bottom-inner-container" style = "margin-left: 2%">
					
					
					<?php
						$get_friends = $db->prepare("SELECT * FROM friends WHERE (accepter = :username OR requester = :username) AND accepted = 1");
						$get_friends->execute(array("username"=>$username));
						echo "<div class = 'friend-list' >
						<div id = 'sub-profile-title'>Friends</div>";
						if($get_friends->rowCount()>0){
							?>
							
							
							<?php
				
							while($row = $get_friends->fetch(PDO::FETCH_ASSOC)){
								if($row['accepter']==$username){
									$user_to_show = $row['requester'];
								}else{
									$user_to_show = $row['accepter'];
								}
								$get_info = $db->prepare("SELECT * FROM users WHERE user_username = :username");
								$get_info->execute(array("username"=>$user_to_show));
								$row = $get_info->fetch(PDO::FETCH_ASSOC);
								$friend_info = array("fullname"=>$row['user_firstname']." ".$row['user_lastname'], "user_rep"=>"Rep: ".$row['user_rep']);
								if($_GET['user']==$_SESSION['user_id']){
									$friend_info["email"]=$row['user_email'];
								}
								echo "
								<a style = 'color:grey;' href = 'index.php?page=profile&user=".$row['user_id']."'><div class = 'friend-list-block'><span style = 'font-size:120%;font-weight:bold;'>"
				
								.$user_to_show."</span><br><span style = 'font-size:100%;'>".implode($friend_info, "<br>")."</span></div>";
				
							}
							echo "</div>";
						}else{
							echo "<span style = 'margin-top:100px;margin-left:50px;position:absolute;'>Friend list is empty.</span>";
						}
						?>
				</div>
			</div>
		</div>
		
		
		<?php
		
	}else{
		header("Location: index.php?page=home");
	}
}else{
	header("Location: index.php?page=home");
}
?>