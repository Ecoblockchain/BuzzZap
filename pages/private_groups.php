<?php
if($check_valid!="true"){
	header("Location:../index.php?page=home");
	exit();
}
if(loggedin()){
	$view_com_id = (isset($_GET['com']))? htmlentities($_GET['com']) : get_user_field($_SESSION['user_id'], "user_com");

	$check = $db->query("SELECT com_id FROM communities WHERE com_id =".$db->quote($view_com_id))->fetchColumn();
	$own = false;
	if(get_user_field($_SESSION['user_id'], "user_com")==$view_com_id){
		$own = true;
	}
	if(!empty($check)){
		$com_profile = $db->prepare("SELECT * FROM com_profile WHERE com_id = :com_id");
		$com_profile->execute(array("com_id"=>$view_com_id));
		$com_profile = $com_profile->fetch(PDO::FETCH_ASSOC);
		$com_profile["com_comp_stat"] = str_replace(",", "/", $com_profile["com_comp_stat"]);
		$can_edit = (get_user_field($_SESSION['user_id'], "user_rank")==3&&$own==true)? true:false;
		?>
		<div class = 'page-path'>Community Profile > <?php echo $com_profile['com_name'];?></div><br>
		<div class = 'p-groups-title'><b>
			<?php
				echo $com_profile["com_name"]. "'s Profile";
			

				if($can_edit==true){
						$label_format = array("1com_type"=>"Community Type", "1com_location"=>"Location", "1com_about"=>"About ".$com_profile['com_name'],
		 				"0com_comp_stat"=>"Global Competitions Won","0com_leaders"=>"Community Leaders", "1com_website"=>"Community Website");
					}else{
							$label_format = array("0com_type"=>"Community Type", "0com_location"=>"Location", "0com_about"=>"About ".$com_profile['com_name'],
		 				"0com_comp_stat"=>"Global Competitions Won","0com_leaders"=>"Community Leaders","0com_website"=>"Community Website");
					}
			?>
		</b></div>
		<script>
		$(document).ready(function(){
			var click_view_mem = 0;
			$("#view-member-opt").click(function(){
				if(click_view_mem%2==0){
					$("#lcp-userlist-container").slideDown();
				}else{
					$("#lcp-userlist-container").slideUp();
				}	
				click_view_mem++;
			});
		});
		</script>
		<div class = "profile-info-container" style = "width: 97%;padding-top:10px;white-space:normal;">
			<div id = 'profile-fields' style = "text-align: left;margin-left: 20%">
			<?php
			foreach($label_format as $col=>$dis){
				$field = substr($col,1);
				$editable = substr($col, 0,1);
				$val_dis = $com_profile[$field];
				if($val_dis==""){
					$val_dis = "Not specified";
				}
				if($editable==1){
					$val_dis = "<input id = '".$field."' type = 'text' value = '".$com_profile[$field]."' style = 'width: 400px;color:lightgreen;' class = 'profile-edit-textfield' placeholder = 'Click to edit'>";
				}
				if($field=="com_leaders"){
					$vals = explode(",",$com_profile[$field]);
					foreach($vals as &$val){
						$val = add_profile_link($val, 0,"color:lightgreen;");
					}
					$val_dis = implode(",",$vals);
				}

				echo $dis.": <span style = 'color:lightgreen;'>".$val_dis."</span><br>";

				if($can_edit==true&&$editable==1){
				?>
				<script>
				$(document).ready(function(){
					var old_val = $("#<?php echo $field; ?>").val();
					$("#<?php echo $field; ?>").focus(function(){
						$(this).css("border-bottom", "2px dashed lightblue");
					}).blur(function(){
						$(this).css("border", "none");
						var new_val = $(this).val();
						if(new_val!=old_val){
							window.location="index.php?page=private_groups&com=<?php echo $view_com_id; ?>&edit_<?php echo $field; ?>=" + $(this).val();
						}
					});
				});
				</script>
				<?php
					if(isset($_GET['edit_'.$field])){
						$newval = htmlentities($_GET['edit_'.$field]);
						update_com_profile($view_com_id,$field,$newval);
						setcookie("success", "1Successfully updated!", time()+10);
						header("Location: index.php?page=private_groups&com=".$view_com_id);
					}
				}
				
			}
			echo "Member Count: <span style = 'color:lightgreen;cursor: pointer;' id = 'view-member-opt'>".$db->query("SELECT user_id FROM users WHERE user_com=".$db->quote($view_com_id))->rowCount()." &ensp;&ensp;&ensp;<u>(View Members)</u></span><br>"; 
			echo "Community Reputation: <span style = 'color:lightgreen;'>".get_com_rep($view_com_id)."</span><br>"; 
			echo "</div>";
		?>

			<div id = "lcp-userlist-container" style = "font-size: 80%;margin: 0 auto;">

			 	<br><br>
			 	<?php
			 		$users = $db->query("SELECT user_username, user_firstname, user_lastname FROM users WHERE user_id != ".$db->quote($user_id)." AND user_com = ".$db->quote($view_com_id));
			 		foreach($users as $user){
			 			echo "<span style = 'cursor:pointer;' id = 'eu-".$user['user_username']."'>".add_profile_link($user['user_username'],1,'style:lightblue;').
			 			"</span><span style = 'float:right'>".$user['user_firstname']." ".$user['user_lastname']. "</span><hr size = '1'>";
			 		}
			 	?>
			</div>
		</div>
		<?php

		

		?>
		<hr size = "1" id = 'start-group-list'><br>
		<div class = 'p-groups-title'>
			<b>
			<?php echo $com_profile['com_name']; ?>
				Private Groups
			</b>
			<br>
		</div>
			
		<?php

			if(isset($_GET['group_users'])){
				$vgroup_id = htmlentities($_GET['group_users']);
				
				?>
				
				<script>
				$(document).ready(function(){
					$(this).click(function(){
						$(".p-group-users").fadeOut();
					});
				});
				</script>
				
				<?php
			}
			$groups = $db->prepare("SELECT * FROM private_groups WHERE com_id = :com_id");
			$groups->execute(array("com_id"=>$view_com_id));
			
			?>
			
			
				<?php
					
					if($own==true&&!user_in_group($_SESSION['user_id'],"", "true")){
				?>		
					<div id = "c_group" class="create-group">
						<span id = 'c_group_text'>
							+ Create Group
						</span>
						<span id = 'c_group_form' style = 'display:none;float:right;position:absolute;'>
							<form action = "" method = "POST">
								<input type = "text" name = "group_name" placeholder = "Group Name" class = "group-fields">
								<input id = 'desired_g_mems' type = "text" name = "group_members" placeholder = "e.g user1, user2, user2" class = "group-fields">
								<input type = "submit" class = "group-fields" style = "width:70px;"><br>
								<div style = 'margin-left: 200px'id = "pred_results">
									(desired members)
								</div>	
							</form>
						</span>	
					</div>
				<?php
					}
				?>
				<script>
					$(document).ready(function(){
						clicked = 0;
						$("#c_group").click(function(){	
							$(this).css("text-align", "left").css("height", "70px");
							setTimeout(function(){
								if(clicked==0){
									$("#c_group_text").html($("#c_group_text").html()+": &ensp;&ensp;");
									$("#c_group_form").fadeIn(500);
								}
								clicked = 1;
							}, 500);
						});
						
						
						$(".a-user-opt").click(function(){
							var id = $(this).attr("id").substring(2);
							$("#a-user-form-"+id).slideDown();
						});
					});
				</script>
				<br><br><br>
				<div id = "group-plates-container">
				
				<?php
				if($groups->rowCount()==0){
					echo "<div id = 'no-threads-message'>There are no groups in this community yet.</div>"; 
				}else{	
					while($row = $groups->fetch(PDO::FETCH_ASSOC)){
						if(isset($_GET['highlight_g'])==true&&$_GET['highlight_g']==$row['group_id']){
							$border= "border: 5px dashed salmon;";
						}else{
							$border = "";
						}
						echo "<div class = 'pg-container' style = '".$border."'>";
						echo "<b class = 'group-title'>".$row['group_name']."</b><br>";
						if($own==true){	
							if((user_in_group($_SESSION['user_id'], $row['group_id'], "")==true)&&(user_in_group($_SESSION['user_id'], $row['group_id'], "true")==false)){
								$join_link = "<a href = 'index.php?page=private_groups&join=".$row['group_id']."' class = 'group_link' style = 'color:#a0db8e;font-size:60%;'>Accept invitation to join</a><br>
								<a href = 'index.php?page=private_groups&dec=".$row['group_id']."' class = 'group_link' style = 'color:#fb998e;font-size:60%;'>Decline invitation to join</a>";	
							}else if(user_in_group($_SESSION['user_id'], $row['group_id'], "true")){
								$join_link = "<a href = 'index.php?page=private_groups&leave=".$row['group_id']."' class = 'group_link'>Leave Group </a>";	
								if(group_leader($_SESSION['user_id'])){
									$join_link.="&middot;<span class = 'group_link a-user-opt' id = 'a-".$row['group_id']."'>Add member</span>";
								}
							}else{
								$join_link = "";
							}	
							echo $join_link."<br>
							<form action = '' method = 'POST' id = 'a-user-form-".$row['group_id']."' style = 'display: none;'>
								<input type = 'text' name = 'a_user' style = 'border: none;' placeholder=  'username...'>
								<input type = 'hidden' name = 'group_id' value = '".$row['group_id']."'>
								<input type = 'submit' value = 'Add' style = 'border:none;'>
							</form>
							";
						}
						$users = $db->prepare("SELECT * FROM group_members WHERE group_id = :group_id AND active = 1");
						$users->execute(array("group_id"=>$row['group_id']));
						echo "<b style ='font-size:80%;color:dimgrey;'><u>Members</u></b><span style = 'font-size: 70%;'><br>";
						
						while($row = $users->fetch(PDO::FETCH_ASSOC)){
							if($row['leader']=="1"){
								$leader = "(leader)";	
							}else{
								$leader = "";	
							}
							echo add_profile_link(get_user_field($row['user_id'], "user_username"), 0, "color: grey").$leader."<br>";	
						}	
						echo "</span></div>";
					}
				}
				?>
				</div>
			
			<?php
			if($own==true){	
				if(isset($_POST['a_user'], $_POST['group_id'])){
					$addu = htmlentities($_POST['a_user']);
					$gid = htmlentities($_POST['group_id']);
					if(user_in_group($_SESSION['user_id'], $gid, "true")&&group_leader($_SESSION['user_id'])){
						$uid = $db->query("SELECT user_id FROM users WHERE user_username = ".$db->quote($addu))->fetchColumn();
						if(!empty($uid)){
							group_action($uid, $gid, "addu");
							setcookie("success", "1Successfully sent an invite to ".$addu." to join your group!", time()+10);
						}else{
							setcookie("success", "0That user does not exist.", time()+10);
						}	
					}else{
						setcookie("success", "0There was an error.", time()+10);
					}	

					header("Location: index.php?page=private_groups&com=".$view_com_id);
				}
				if(isset($_GET['join'])){
					$group_id = htmlentities($_GET['join']);
					if(group_action($_SESSION['user_id'], $group_id, "join")==true){
						setcookie("success", "1Successfully joined group.", time()+10);	
						header("Location: index.php?page=private_groups&com=".$view_com_id); 
					}else{
						setcookie("success", "0There was an error. Please check you are not already in a group.", time()+10);	
						header("Location: index.php?page=private_groups&com=".$view_com_id);
					}	
				}else if(isset($_GET['dec'])){
					$group_id = htmlentities($_GET['dec']);
					$check_leader = $db->query("SELECT user_id FROM group_members WHERE user_id = ".$_SESSION['user_id']." AND leader = 1")->fetchColumn();
					if(empty($check_leader)){
						if(group_action($_SESSION['user_id'], $group_id, "dec")==true){
							setcookie("success", "1Successfully declined invite.", time()+10);	
							header("Location: index.php?page=private_groups&com=".$view_com_id);
						}else{
							setcookie("success", "0There was an error.", time()+10);	
							header("Location: index.php?page=private_groups&com=".$view_com_id);
						}
					}
				}else if(isset($_GET['leave'])){
					$group_id = htmlentities($_GET['leave']);
					$leave_act = group_action($_SESSION['user_id'], $group_id, "leave");
					if($leave_act===true){
						setcookie("success", "1Successfully left group.", time()+10);	
					}else if($leave_act==="compe"){
						setcookie("success", "0You cannot leave a group while the group is involved in competitions. Please wait untill any competitions are over.", time()+10);	
					}else{
						setcookie("success", "0There was an error.", time()+10);	
					}
					header("Location: index.php?page=private_groups&com=".$view_com_id);	
				
				}else if(isset($_POST['group_name'], $_POST['group_members'])){
					$name = htmlentities($_POST['group_name']);
					if(strlen($name)>3){
						$members = htmlentities($_POST['group_members']);
						$members = strlist_to_array($members);
						if(end($members)!=="ERROR"){
							$user_id = $_SESSION['user_id'];
							if(create_p_group($user_id, $name, $members)){
								foreach($members as $member){
									
									$text = "You have been invited to join the group '".$name."', to accept or decline please click here.";
									$link = "index.php?page=private_groups";
									
									add_note($db->query("SELECT user_id FROM users WHERE user_username=".$db->quote($member))->fetchColumn(), $text, $link);
								}
								$text = "Your group '".$name."' has successfully been created. Your desired members will recieve their invitations to join shortly.";
								$link = "";
								add_note($_SESSION['user_id'], $text, $link);
								
								
								setcookie("success", "1Successfully created group.", time()+10);
							}else{
							
								setcookie("success", "0There was an error.", time()+10);
							}
						}else{
							$count = 0;
							$text = "The following users don't exist:<br><b>";
							foreach($members as $user){
								if($count!=count($members)-1){
									$text = $text.$user."<br>";
								}
								$count++;	
							}
							$text = "0".trim_commas($text)."</b>";
							setcookie("success", $text, time()+10);	
						}	
					}else{
						setcookie("success", "0Your group name must be longer.", time()+10);
					}		
					header("Location: index.php?page=private_groups&com=".$view_com_id);
					
				}
			}
			echo "<br><hr size = '1'>";

			if($own == false){
				$latest_global_debates = $db->query("SELECT thread_title,thread_id FROM debating_threads WHERE com_id = 0 AND user_com_id = ".$db->quote($view_com_id)." ORDER BY time_created DESC LIMIT 5");
				echo "<div class = 'profile-info-container' style = 'float: left;white-space:normal;margin-top: 5px;width:46%;min-height: 300px;text-align:center;padding: 0px' >
				Latest Global Debates By ".$com_profile['com_name']."<br>";
				if($latest_global_debates->rowCount()>0){
					foreach($latest_global_debates as $row){
						echo "<hr size = '1'><a href = 'index.php?page=view_private_thread&thread_id=".$row['thread_id']."' style = 'color: white;font-size:80%;'>".$row['thread_title']."</a>";
					}
				}else{
					echo "<br><br><br>No latest debates found.";
				}
				echo "</div>";
				$latest_global_debates = $db->query("SELECT comp_title,comp_id FROM competitions WHERE comp_type = 1 AND comp_com_id = ".$db->quote($view_com_id)." ORDER BY created DESC LIMIT 5");
				echo "<div class = 'profile-info-container' style = 'white-space:normal;width:46%;min-height: 300px;text-align:center;float:right;margin-top:5px;padding: 0px' >
				Current Global Competitions By ".$com_profile['com_name']."<br>";
				if($latest_global_debates->rowCount()>0){
					foreach($latest_global_debates as $row){
						echo "<hr size = '1'><a href = 'index.php?page=view_private_thread&thread_id=".$row['thread_id']."' style = 'color: white;font-size:80%;'>".$row['thread_title']."</a>";
					}
				}else{
					echo "<br><br><br>No current competitions found.";
				}
				echo "</div>";
			}	
	}else{
		header("Location: index.php?page=home");
	}		
}else{
	header("Location: index.php?page=home");
}
	
?>