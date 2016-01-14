<?php
if($check_valid!="true"){
	header("Location: index.php?page=home");
	exit();
}
if(loggedin()){
	$type = htmlentities($_GET['type']);
	//0 = groups
	//1 = coms
	if($type=="0" || $type == "1"){
		
		?>
			<script>
			$(document).ready(function(){
				var start_comp_opened=0;
				
					$("#start_comp").click(function(){
						if(start_comp_opened==0){
							start_comp_opened=1;
							
							$(this).animate({height:"600px"}, 500).animate({marginLeft:"28%"})
							.animate({width:"40%"}, 500).css("color", "#ffffff")
							.css("box-shadow", "0px 0px 40px dimgrey").css("border", "1px solid grey");
					
							setTimeout(function(){
								$("#start-comp-form").fadeIn();
							}, 1000);
						}
					});
				
				
								
					$("#close-comp-form").click(function(){
						if(start_comp_opened==1){	
							
							$("#start-comp-form").fadeOut();
							$("#start_comp").animate({height:"40px"}, 500).animate({marginLeft:"0%"})
							.animate({width:"200px"}, 500).css("color", "#ffffff")
							.css("box-shadow", "none").css("border", "none");
							setTimeout(function(){
								start_comp_opened = 0;
							}, 1000);
							
						}
					});
					
					$("#comp_filter").change(function(){
						if($(this).val()=="2"){
							$("#false").fadeOut();
						}else{
							$("#false").fadeIn();
						}
					});
			});
			
			</script>
			<?php
			if($type=="0"){
				$p_title = "Private Competitions";
				$des_opp_placeholder = "e.g group1, group2, group3";
				$gc_string = "group";
				$gp_string = "private";
				$table_search = "private_groups";
				$col_name = "group_name";
					
			}else{
				$p_title = "Global Competitions";
				$des_opp_placeholder = "e.g community1, community2, community3";
				$gc_string = "community";
				$gp_string = "global";
				$table_search = "communities";
				$col_name = "com_name";
			}
			echo "<div class = 'title-private-debate'>".$p_title."</div><br><br><br>";
			if($type == "0" || $type == "1"){
				if( (group_leader($_SESSION['user_id'])&&$type=="0")||($type=="1"&&user_rank($_SESSION['user_id'],3)) ){
				?>
				<script>
				$(document).ready(function(){
					
					$(".comp-judge-sel").change(function(){
						var val = $(this).val();
						if(val=="jnorm"){
							$(".tjudge-info").html("This means any user who is not involved in the competition can view the debate, and vote each argument up or down.<br>");
						}else if(val=="jspec"){
							$(".tjudge-info").html("This means you can choose any specific and trustworthy user(s) to judge the competition, however they cannot be in any of the involved <?php echo $gc_string.'\'s'; ?>: <input type = 'text' placeholder = 'usernames... e.g user1,user2,user3' class = 'tjudge-spec-users-field' name = 'jspec_users'><br>");
						}else if(val=="jout"){
							$(".tjudge-info").html("This means you would like someone who is not on BuzzZap at all to judge the competition, a link will be sent to them. Please supply their email(s) here: <input type = 'text' placeholder = 'emails... e.g email1,email2,email3' id = 'tjudge-out-email-field' name = 'jout_emails' class = 'tjudge-spec-users-field'>");
						}
					});	

					$("#deb_question").focus(function(){
						$("#c-deb-sub-sec").hide();
					}).blur(function(){
						if($(this).val().length==0){
							$("#c-deb-sub-sec").show();
						}
					});
				});
				</script>
				
				<div class = "start_comp_link" id = "start_comp">
					Start New Competition
					<div class = "start-comp-form" id = "start-comp-form">
						<form action = "" method = "POST">
						<span style = "color:grey;float:right;margin-top:-20px;" id = 'close-comp-form'>x</span>
							<br>

								<span style = 'font-size:80%;'>Debate Notion: </span><br>
								<input type = "text" id = "deb_question" class = "loggedout-form-fields" placeholder = "e.g Education is a human right." style = "height:30px;outline-width:0px;font-size:60%;box-shadow:none;" name = "deb_question"><br>
								<span id = 'comp_field_labels'>
									Leave this blank if you would like a randomly picked debate notion.
								</span>	<br><br>

								<span style = 'font-size:80%;'>Desired Opponents: </span><br>
								<input type = "text" id = "des_opponents" class = "loggedout-form-fields" placeholder = "<?php echo $des_opp_placeholder; ?>" style = "height:30px;outline-width:0px;font-size:60%;box-shadow:none;" name = "des_opponents">
							 
								 <div id = "pred_results"></div>
							 
								<span id = 'comp_field_labels'>
									Enter the <?php echo $gc_string; ?>(s) you want to compete agaisnt in this debate.
									<?php if($type=="0"){ ?> As it is a private competition, the groups must be within this community.<?php } ?>
								</span>	<br>
								<span id = 'c-deb-sub-sec'>
									<br><span style = 'font-size:80%;'>Debate subject:</span><br>
									<select name = "comp_topic" id = "contact-opt-sel" style = "margin-left:25px;width:305px;color:grey;">
										<option value = "">---</option>
										<option value = "0">Any</option>
										<?php
											$get_topics = $db->prepare("SELECT topic_name, topic_id FROM debating_topics");
											$get_topics->execute();
											while($row = $get_topics->fetch(PDO::FETCH_ASSOC)){
												echo "<option value = '".$row['topic_id']."'>".$row['topic_name']."</option>";
											}
										?>
									</select>
									<br>
								
									<span id = 'comp_field_labels'>
										If you have not supplied your own notion, pick the subject of what you want to debate about in this competition. A random debate question will then be picked within that subject.
									</span>	<br>
								</span>
								<br><span style = 'font-size:80%;'>Competition Duration:</span><br>
								<select name = "comp_dur" id = "contact-opt-sel" style = "margin-left:25px;width:305px;color:grey;">
									<option value = "24">24 Hours</option>
									<option value = "48">48 Hours</option>
									<option value = "72">3 days</option>
									<option value = "168">1 week (7 days)</option>
								</select>
								<br>
								<span id = 'comp_field_labels'>
									Pick the duration of the competition (starting once all opposition have decided to participate or not).
								</span>	<br><br>
								<span style = 'font-size:80%;'>How would you like this competition to be judged?</span><br>
								<select name = "comp_judge" id = "contact-opt-sel" class = "comp-judge-sel" style = "margin-left:25px;width:305px;color:grey;">
									<option value = "">---</option>
									<option value = "jnorm">Any user not involved can judge</option>
									<option value = "jspec">Choose specific judge(s)</option>
									<option value = "jout">Invite special judge (outside BuzzZap)</option>
								</select><br><span style = "" id = "comp_field_labels" class = 'tjudge-info'></span>
								<span id = 'comp_field_labels'>
									<hr size = '1'>
									Each <?php echo $gc_string; ?> will be randomly chosen to be either FOR or AGAISNT (argue yes or no to) the debate question/notion. Groups are judged on how well they have argued their point, baring in mind it may not be their actual opinion.
								</span>	<br><br>
								<input type = "submit" class = "loggedout-form-submit" style = "font-size:80%;box-shadow:none;width:200px;padding:10px;" value = "Start Competition">
							</span>
						</form>	
					
						<?php
							if(isset($_POST['des_opponents'], $_POST['comp_topic'], $_POST['comp_dur'], $_POST['comp_judge'], $_POST['deb_question'])){
								if($type=="0"){
									$user_host_name = array(get_user_group($_SESSION['user_id'], "group_name"));
								}else{
									$user_host_name = array(get_user_community($_SESSION['user_id'], "com_name"));	
								}
								$opps_w_starter = strlist_to_array(htmlentities(trim_commas(trim($_POST['des_opponents']))), false);
								$opps = array_diff($opps_w_starter, $user_host_name);
								
								$comp_topic_id = htmlentities($_POST['comp_topic']);
								$comp_dur = htmlentities($_POST['comp_dur']);
								$deb_question = htmlentities($_POST['deb_question']);
								
								$errors = array();
								
								$jtype = htmlentities($_POST['comp_judge']);
								if(empty($jtype)){
									$errors[] = "You must choose a way your competition is going to be judged.";
								}
								
								$jtype_spec_valid = false;

								if(($jtype=="jspec")&&(isset($_POST['jspec_users']))&&(!empty($_POST['jspec_users']))){
									$judges = htmlentities($_POST['jspec_users']);
									$judges = strlist_to_array($judges, true);
									if(end($judges)=="ERROR"){
										unset($judges[count($judges)-1]);
										$errors[] = "The following desired judges were invalid users: ".trim_commas(implode(",", $judges));
									}else{
										foreach($judges as &$judge){
											$judge = $db->query("SELECT user_id FROM users WHERE user_username = ".$db->quote($judge))->fetchColumn();
										}
										$jtype_spec_valid = true;
									}
								}else if($jtype=="jnorm"){
									$judges = "norm";
								}else if($jtype=="jout"){
									$jemails = htmlentities($_POST['jout_emails']);
									$judges = strlist_to_array($jemails, false);
									$judges_emails = $judges;
									foreach($judges as &$judge){
										if(!filter_var($judge, FILTER_VALIDATE_EMAIL)){ 
											$errors[]= "One of your desired special judges has an invalid email address.";
											break;
										}
										$judge = "-out:".$judge.substr(encrypt($judge), 0,8);
									}
								}else{
									$errors[]= "You must supply valid judges.";
								}
								
								// comp_topic_id  = 0 = any topic
								$valid_comp_durs = array("24", "48", "72", "168");
								if(!in_array($comp_dur, $valid_comp_durs)){
									$errors[] = "You supplied an invalid competition duration.";
								}
								if(count($opps)>4){
									$errors[] = "You can have a maximum of four opponents.";
								}
								$invalid_hosts = "";
								
								$com_id = get_user_community($_SESSION['user_id'], "com_id");
								
								if($type=="0"){
									$table = "private_groups";
									$col_name = "group_name";
									$e_validation = "AND com_id = ".$db->quote($com_id);
									$id_col = "group_id";
								}else{
									$col_name = "com_name";
									$table = "communities";
									$e_validation = "";
									$id_col = "com_id";
								}
								foreach($opps as &$opp){
									
									$check_host = $db->query("SELECT `".$col_name."` FROM `".$table."` WHERE `".$col_name."` = ".$db->quote($opp).$e_validation)->fetchColumn();
									if($check_host==""){
										$invalid_hosts = $invalid_hosts." ".$opp.",";
									}else{
										$opp = $db->query("SELECT `".$id_col."` FROM `".$table."` WHERE `".$col_name."`=".$db->quote($opp))->fetchColumn();
									}
									
								}	
								if(count($opps)!=0){
									if(strlen($invalid_hosts)>0){
										$errors[] = "The following ".$gc_string."(s) do not exist, or are not part of your community: ".trim_commas($invalid_hosts)."<br>";
									}
								}else{
									$errors[] = "You must have atleast one valid opponent.";
								}


								$end_time = ".".$comp_dur;
								// once started, end time is time()+($comp_dur*3600);
								
								
								$invjudges = "";
								if($type=="0"){
									$user_host_id = get_user_group($_SESSION['user_id'], "group_id");
								}else{
									$user_host_id = get_user_community($_SESSION['user_id'], "com_id");	
								}
								
								$opps_ws = $opps;
								$opps_ws[] = $user_host_id;

								if($jtype_spec_valid == true){
									
									foreach($opps_ws as $opph){
										foreach($judges as $judgeid){
											if($type=="0"){
												if(user_in_group($judgeid, $opph)){
													$invjudges = $invjudges.",".get_user_field($judgeid, "user_username");
												}
											}else{
												if(user_in_community($judgeid, $opph)){

													$invjudges = $invjudges.",".get_user_field($judgeid, "user_username");
												}
											}
										}
									}
									
									if(!empty($invjudges)){
										$errors[] = "The following desired judges are too involved with the competition to judge it: ".trim_commas($invjudges);
									}
								}	
								
								if( (group_leader($_SESSION['user_id'])&&$type=="0")||($type=="1"&&user_rank($_SESSION['user_id'],3)) ){
									$starter_id= ($type=="0")? get_user_group($_SESSION['user_id'], "group_id") : get_user_community($_SESSION['user_id'], "com_id");
								}else{
									$errors[] = ($type=="0")? "You must be in a group and the group leader to start a private competition.<br>" : "You must be your community's leader to start a global competition.<br>";
								}

								if(empty($deb_question)){
									$check_valid_topic_id= $db->query("SELECT topic_id FROM debating_topics WHERE topic_id=".$db->quote($comp_topic_id))->fetchColumn();
									if((!empty($comp_topic_id)&&!empty($check_valid_topic_id))||$comp_topic_id=="0"){
										$deb_question = get_rand_debate($comp_topic_id);
										if(!$deb_question){
											$errors[] = "We were unable to choose a random debate notion. Please choose another subject or enter your own.";
										}
									}else{
										$errors[] = "You must supply a debate subject if you do not want to supply your own debate notion.";
									}
								}else if(strlen($deb_question)<8){
									$errors[]= "Your debate notion must be longer.";
								}

								if(count($errors)==0){
									setcookie("success", "1Successfully started competition", time()+10);
									$comp_id = start_comp($type, $opps, $end_time, $judges, $comp_topic_id, $starter_id, $deb_question);
									add_note($_SESSION['user_id'], "You have successfully started a competition. Please wait while your opponents accept or decline to take part.", "");
									
									if($jtype=="jspec"){
										foreach($judges as $judgeid){
											add_note($judgeid, "You have been invited to judge a competition. Please click here to view the competition and respond to the invitation.", "index.php?page=view_comp&comp=".$type.$comp_id);
										}
									}else if($jtype=="jout"){
										$headers  = 'MIME-Version: 1.0' . "\r\n";
										$headers .= 'Content-type: text/html; charset=iso-8859-1' . "\r\n";
										$headers .= "From: Administration@buzzzap.com" . "\r\n";

										foreach($judges_emails as $key=>$email){
											$link = $spec_judge_email_link."index.php?page=view_comp&comp=".$type.$comp_id."&out_judge_key=".substr($judges[$key],4);
											$body = "Dear ".$email.", <br> The user ".get_user_field("user_username", $_SESSION['user_id'])." at BuzzZap Online
											Debating would like you to judge a competition. Please view it <a href = '".$link."'>here</a>. Simply vote up/down on the comments and arguments that are or are not particularly persuasive and agreeable.<br>Thank you!";
											mail($email,"BuzzZap Judge request",$body,$headers);
										}
									}
									
									if($type=="0"){
										$users_in_group = array_diff(get_users_in_group($starter_id), array($_SESSION['user_id']));
										foreach($users_in_group as $user_id){
											add_note($user_id, "Your group leader has started a new private competition. Please wait while other groups accept or decline to take part.", "");
										}
									}else{
										add_com_feed(get_user_community($_SESSION['user_id'], "com_id"), "Your community leader has started a new global competition. Please wait while other groups accept or decline to take part.");
									}	
									
									$host_name = ($type=="0")? get_user_group($_SESSION['user_id'], "group_name"): get_user_community($_SESSION['user_id'], "com_name");
									$host_id = ($type=="0")? get_user_group($_SESSION['user_id'], "group_id"): get_user_community($_SESSION['user_id'], "com_id");
									$opts = array(0,1,0,1);
									
									foreach($opps as $opp_){
									
										$leader_id = ($type=="0")? get_group_leader_id($opp_): get_com_leader_id($opp_);
										add_note($leader_id, "The ".$gc_string." ".$host_name." has started a new ".$gp_string." competition, and has invited your ".$gc_string." to take part. Visit the ".$gp_string." competition page to accept or decline.", "index.php?page=comp_home&type=".$type);
									
										if($type=="0"){
											$users_in_g = array_diff(get_users_in_group($opp_), array($leader_id));
											foreach($users_in_g as $user_id){
												add_note($user_id, "The group ".get_user_group($_SESSION['user_id'], "group_name")." has started a new private competition, and has invited your group to take part. Please wait for your group leader to accept or decline the invite.", "index.php?page=comp_home&type=0");
											}
										}else{
											add_com_feed($opp_, "The community ".get_user_community($_SESSION['user_id'], "com_name")." has started a new global competition, and has invited your community to take part. Please wait for your community leader to accept or decline the invite.");
										}
									}
									$opps[] = $db->query("SELECT `".$id_col."` FROM `".$table."` WHERE `".$col_name."`=".$db->quote($user_host_name[0]))->fetchColumn();	
									$c = 0;
									shuffle($opps);
									foreach($opps as $opp_){
										$insert = $db->prepare("INSERT INTO comp_sides VALUES(:comp_id, :cand_id, :side)");
										$insert->execute(array("comp_id"=> $comp_id, "cand_id"=>$opp_, "side"=>$opts[$c]));
										$c++;
									}	
									
								}else{
									setcookie("success", "0".implode("<br>",$errors), time()+10);
								}	
							
								header("Location: index.php?page=comp_home&type=".$type);
							}
						?>
					</div>
				</div>	
				<?php
				}
				?>
				<hr size = "1">
				<span style = 'color:grey;'>Show</span>
				<select id = 'comp_filter' class = "leader-cp-fields" style = "width:250px;">
					<option value = '1'>All <?php echo ($type=="0")?get_user_community($_SESSION['user_id'], "com_name"): "Global"; ?> Competitions</option>
					<option value = '2'>Competitions I Am In</option>
				</select><br><br>
				
				<?php
				$invitations = array(); // contains comp_ids that await response.
				$host_id = ($type=="0")? get_user_group($_SESSION['user_id'], "group_id"): get_user_community($_SESSION['user_id'], "com_id");
				$com_id_parse = ($type=="0")?get_user_field($_SESSION['user_id'], "user_com"): "0";
				$get_relevant_comps= $db->prepare("SELECT * FROM competitions WHERE comp_type=:ctype AND comp_com_id = :com_id AND end != 'true' ORDER by created DESC");
				$get_relevant_comps->execute(array("com_id"=>$com_id_parse, "ctype"=>$type));
				if($get_relevant_comps->rowCount()>0){
					while($row = $get_relevant_comps->fetch(PDO::FETCH_ASSOC)){

						if(comp_started($row['comp_id'])){
							$seconds_left = $row['end']-time();
							$days_left = (int)($seconds_left / 86400);
							$hours_left = (int)($seconds_left / 3600)-($days_left*24);
							$minutes_left = (int)($seconds_left / 60)-($days_left*60*24)-($hours_left*60);
							$seconds_left_ = (int)($seconds_left)-($days_left*86400)-($hours_left*3600)-($minutes_left*60);
						
							$time_left_str = "Time left: ".$days_left." days  ".$hours_left." hrs  ".$minutes_left." mins ";
						
							if($days_left==0 && $hours_left == "0"){
								$time_left_str = "Time left: <span id = 'mins_end'>".$minutes_left."</span> mins  <span id = 'secs_end'>".$seconds_left_."</span> secs";
							}
						}else{
							$time_left_str = "*NOTE: Waiting for all candidates to accept/decline invitation to participate.";
						}

						if(end_comp($row['comp_id'])||comp_ended($row['comp_id'])){
							$time_left_str = "*NOTE: This competition has ended";
						}
						$user_involved = "false";
						$cand_names = get_comp_acceptance_info($row['comp_id'], $type);
						$get_starter = $db->query("SELECT starter_id FROM competitions WHERE comp_id = ".$db->quote($row['comp_id']))->fetchColumn();
						$cand_names[$get_starter] = "1";	
						$refined_names = array();
						foreach ($cand_names as $id=>$value){
							$label_own_group="false";
							
							if($id==$host_id){//if involved
								$label_own_group="true";
								$user_involved = "true";
							}
							$name = ($type=="0")? $db->query("SELECT group_name FROM private_groups WHERE group_id = ".$db->quote($id))->fetchColumn():$db->query("SELECT com_name FROM communities WHERE com_id = ".$db->quote($id))->fetchColumn();
							
							if($label_own_group=="true"){
								$name = "*".$name."*";
							}
							if($value=="1"){
								$refined_names[]= $name;
							}
						}
						$cand_names = implode(",",$refined_names);
						
						if(!comp_started($row['comp_id'])){
							$cand_ex_str = "so far";
						}else{
							$cand_ex_str = "";
						}	
						
						if( (group_leader($_SESSION['user_id'])&&$type=="0")||($type=="1"&&user_rank($_SESSION['user_id'],3)) ){ 
							if(waiting_for_comp_response($row['comp_id'], $type, $host_id)){
								$invitations[]  = $row['comp_id'];
							}
						}
					
						
						echo "<a href = 'index.php?page=view_comp&comp=".$type.$row['comp_id']."'><div id = '".$user_involved."' class = 'thread-container' style='color:#457EA4;padding:10px;font-size:150%;margin-top:5px;'>".
							$row['comp_title']."
							<hr size = '1' color = 'lightgrey'>
							<div style = 'font-size:60%;color:#40e0d0;letter-spacing:2px;'>
								Candidates ".$cand_ex_str.": ".$cand_names."&ensp;&ensp;&ensp;&middot;&ensp;&ensp;&ensp;<span id = 'time_info'>".$time_left_str."</span>
							</div>
						</div></a>";
					}
					
					if(!empty($invitations)){
						?>
							<div id = '' class = 'respond-comp-invite-body'>
								<span style = 'font-size:120%;'>
									New Competition Invitations<br><br>
								</span>
								<span style = 'color:lightgrey;'>
								<?php
									foreach($invitations as $comp_id){
										echo "<hr size = '1'>";
										$gci = get_comp_info($comp_id);
										if($type=="0"){
											$invited_by = $db->query("SELECT group_name FROM private_groups WHERE group_id = ".$db->quote($gci["starter_id"]))->fetchColumn();
										}else{
											$invited_by = $db->query("SELECT com_name FROM communities WHERE com_id = ".$db->quote($gci["starter_id"]))->fetchColumn();
										}
										echo $gci["comp_title"];
										echo "<br><span style = 'font-size:90%;'><i>-Invited by ".$invited_by."</i></span><br>";
										echo "<a href = 'index.php?page=comp_home&type=".$type."&rci=".$comp_id.",1' style = 'color:#66cdaa;'>Accept</a> or ";
										echo "<a href = 'index.php?page=comp_home&type=".$type."&rci=".$comp_id.",2' style = 'color:#fa8072;'>Decline</a>";
										
									}
								?>
								</span>
							</div>
						
						<?php
						//respond comp invite = COMP_ID,RESPONSE
						if(isset($_GET['rci'])){
							$rci = explode(",",htmlentities($_GET['rci']));
							//print_r($rci);
							$response = $rci[1];
							$comp_id = $rci[0];
							if(($response=="1" || $response=="2")&&(count($rci)==2)){
								respond_comp_invite($comp_id, $response, $type, $host_id);
								check_comp_ready($comp_id, $type);
								setcookie("success", "1Successfully responded to invitation.", time()+10);
							}else{
								setcookie("success", "0There was a problem.", time()+10);
							}
							
							header("Location: index.php?page=comp_home&type=".$type);
						}	
					}
					
				}else{
					?>
						<div id = "no-threads-message">There are no private competitions yet.</div>
					<?php
				}
				
			}	
			?>
		<?php
		
	}else{
		header("Location: index.php?page=home");
	}
}else{
	header("Location: index.php?page=home");
}
?>