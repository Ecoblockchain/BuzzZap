<?php
if($check_valid!="true"){
	header("Location:../index.php?page=home");
	exit();
}
if(loggedin()){
	?>
	<div class = 'p-groups-title'>
		<b>
		<?php echo get_user_community($user_id, "com_name"); ?>
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
		$groups->execute(array("com_id"=>get_user_community($user_id, "com_id")));
		
		?>
		
		
			<?php
				if(!user_in_group($_SESSION['user_id'],"", "true")){
			?>		
				<div id = "c_group" class="create-group">
					<span id = 'c_group_text'>
						+ Create Group
					</span>
					<span id = 'c_group_form' style = 'display:none;float:right;position:absolute;'>
						<form action = "" method = "POST">
							<input type = "text" name = "group_name" placeholder = "Group Name" class = "group-fields">
							<input type = "text" name = "group_members" placeholder = "Desired Members" class = "group-fields">
							<input type = "submit" class = "group-fields" style = "width:70px;"><br>
							<span style = 'color:grey;font-size:70%;float:right;'>
								Enter desired members by separating each<br>
								username with commas, e.g user1, user2, user3
							</span>	
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
						$("#a-user-form").slideDown();
					});
				});
			</script>
			<br><br><br>
			<div id = "group-plates-container">
			
			<?php
			if($groups->rowCount()==0){
				echo "There are no groups in this community yet."; 
			}else{	
				while($row = $groups->fetch(PDO::FETCH_ASSOC)){
					
					echo "<div class = 'pg-container'>";

					if((user_in_group($_SESSION['user_id'], $row['group_id'], "")==true)&&(user_in_group($_SESSION['user_id'], $row['group_id'], "true")==false)){
						$join_link = "<br><a href = 'index.php?page=private_groups&join=".$row['group_id']."' id = 'group_link' style = 'color:#a0db8e;font-size:60%;'>Accept invitation to join</a><br>
						<a href = 'index.php?page=private_groups&dec=".$row['group_id']."' id = 'group_link' style = 'color:grey;font-size:60%;'>Decline invitation to join</a>";	
					}else if(user_in_group($_SESSION['user_id'], $row['group_id'], "true")){
						$join_link = "<br><a href = 'index.php?page=private_groups&leave=".$row['group_id']."' id = 'group_link'>Leave Group </a>";	
						if(group_leader($_SESSION['user_id'])){
							$join_link.="&middot;<span id = 'group_link' class = 'a-user-opt'>Add member</span>";
						}
					}else{
						$join_link = "";
					}	
					echo "<b class = 'group-title'>".$row['group_name']."</b>".$join_link."<br>
					<form action = '' method = 'POST' id = 'a-user-form' style = 'display: none;'>
						<input type = 'text' name = 'a_user' style = 'border: none;' placeholder=  'username...'>
						<input type = 'hidden' name = 'group_id' value = '".$row['group_id']."'>
						<input type = 'submit' value = 'Add' style = 'border:none;'>
					</form>
					";
					$users = $db->prepare("SELECT * FROM group_members WHERE group_id = :group_id AND active = 1");
					$users->execute(array("group_id"=>$row['group_id']));
					while($row = $users->fetch(PDO::FETCH_ASSOC)){
						if($row['leader']=="1"){
							$leader = "(leader)";	
						}else{
							$leader = "";	
						}
						echo "<a style = 'color: grey' href = 'index.php?page=profile&user=".$row['user_id']."'>".get_user_field($row['user_id'], "user_username")."</a> ".$leader."<br>";	
					}	
					echo "</div>";
				}
			}
			?>
			</div>
		
		<?php
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

			header("Location: index.php?page=private_groups");
		}
		if(isset($_GET['join'])){
			$group_id = htmlentities($_GET['join']);
			if(group_action($_SESSION['user_id'], $group_id, "join")==true){
				setcookie("success", "1Successfully joined group.", time()+10);	
				header("Location: index.php?page=private_groups"); 
			}else{
				setcookie("success", "0There was an error. Please check you are not already in a group.", time()+10);	
				header("Location: index.php?page=private_groups");
			}	
		}else if(isset($_GET['dec'])){
			$group_id = htmlentities($_GET['dec']);
			$check_leader = $db->query("SELECT user_id FROM group_members WHERE user_id = ".$_SESSION['user_id']." AND leader = 1")->fetchColumn();
			if(empty($check_leader)){
				if(group_action($_SESSION['user_id'], $group_id, "dec")==true){
					setcookie("success", "1Successfully declined invite.", time()+10);	
					header("Location: index.php?page=private_groups");
				}else{
					setcookie("success", "0There was an error.", time()+10);	
					header("Location: index.php?page=private_groups");
				}
			}
		}else if(isset($_GET['leave'])){
			$group_id = htmlentities($_GET['leave']);
			$leave_act = group_action($_SESSION['user_id'], $group_id, "leave");
			if($leave_act==true&&$leave_act!="compe"){
				setcookie("success", "1Successfully left group.", time()+10);	
			}else if($leave_act=="compe"){
				setcookie("success", "0You cannot leave a group while the group is involved in competitions. Please wait untill any competitions are over.", time()+10);	
			}else{
				setcookie("success", "0There was an error.", time()+10);	
			}
			header("Location: index.php?page=private_groups");	
		}else if(isset($_POST['group_name'], $_POST['group_members'])){
			$name = htmlentities($_POST['group_name']);
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
			header("Location: index.php?page=private_groups");
			
		}
		
}else{
	header("Location: index.php?page=home");
}
	
?>