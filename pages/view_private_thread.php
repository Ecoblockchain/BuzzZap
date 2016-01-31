<?php
if($check_valid!="true"){
	header("Location: index.php?page=home");
	exit();
}
if(loggedin()){

	$get_thread_ids = $db->query("SELECT thread_id FROM debating_threads");
	$valid_ids = array();
	foreach($get_thread_ids as $id){
		$valid_ids[] = $id['thread_id'];
	}

	if(in_array($_GET['thread_id'], $valid_ids)){
		$thread_id = $_GET['thread_id'];
		$header_info = array("thread_title"=>"", "thread_starter"=>"","topic_id"=>"", "time_created"=>"", "vote_yes"=>"", "vote_maybe"=>"", "vote_no"=>"", "thread_likes"=>"", "visible"=>"");
		foreach($header_info as $column => &$value){
			$value = $db->query("SELECT ".$column." FROM debating_threads WHERE thread_id = ".$thread_id."")->fetchColumn();
		}
		if(valid_view_thread($thread_id, $_SESSION['user_id'])){
			?>
				<?php
					if($header_info["visible"]==0){
						echo "<span style = 'color:salmon;'><i><u>*This debate is visible to only you untill it is approved by a leader.</u></i></span><br><br>";
					}
				
					if(valid_debate_like($thread_id, $_SESSION['user_id'])){
						$like_status = "Like";
					}else{
						$like_status = "Unlike";	
					}
				?>
				<a href = "index.php?page=view_private_thread&thread_id=<?php echo $_GET['thread_id']; ?>&d_like=<?php echo $thread_id; ?>" style = "color:lightblue;"><?php echo $like_status; ?> debate</a>
				<?php
					$user_idp = $db->query("SELECT user_id FROM users WHERE user_username = ".$db->quote($header_info['thread_starter']))->fetchColumn();
					$perm_to_delete = false;
					if((user_rank($_SESSION['user_id'], "3")==true)&&(get_user_community($_SESSION['user_id'], "com_id")==get_user_community($user_idp, "com_id"))){
						$perm_to_delete = true;
					}else if($_SESSION['user_id']==$user_idp){
						$perm_to_delete = true;
					}
					if($perm_to_delete==true){
						echo "&middot; <a style = 'font-size: 100%;color:salmon;' href = 'index.php?page=view_private_thread&deld=true&thread_id=".$thread_id."'>Delete Debate</a><br>";
					}
					if(isset($_GET['deld'])&&$perm_to_delete==true){
						$db->query("DELETE FROM debating_threads WHERE thread_id = ".$thread_id);
						$db->query("DELETE FROM thread_replies WHERE thread_id = ".$thread_id);
						setcookie("success", "1Successfully deleted debate.", time()+10);
						$dtrue = (isset($_GET['d']))? "&d=g" : "";
						header("Location: index.php?page=private_debating_topic&topic_id=".$header_info['topic_id'].$dtrue);
					}
					if($perm_to_delete){
					?>
						<script>
						$(document).ready(function(){
							$("#edit-title-opt").click(function(){
								$(this).hide();
								var cur_title = $(".thread-title-header").html();
								$(".thread-title-header").html("");
								$(".thread-title-header").html("<input type = 'text' id = 'edit-d-title-field' value = '"+cur_title+"' >");				
								$("#edit-d-title-field").blur(function(){
									var new_val = $(this).val();
									if(new_val==cur_title){
										$(".thread-title-header").html(cur_title);
										$("#edit-title-opt").show();
									}else{
										window.location = "index.php?page=view_private_thread&thread_id=<?php echo $thread_id; ?>&etitle="+new_val;
									}
								});
							});
						});
						</script>
					<?php
						if(isset($_GET['etitle'])){
							$val = htmlentities($_GET['etitle']);
							if(strlen($val)>=10){
								$db->query("UPDATE debating_threads SET thread_title = ".$db->quote($val)." WHERE thread_id = ".$db->quote($thread_id));
							}else{
								setcookie("success", "0The title is too short.", time()+10);
							}
							header("Location: index.php?page=view_private_thread&thread_id=".$thread_id);
						}
					}
					?>	
				<div class = "thread-title-header"><?php echo $header_info['thread_title']; ?></div>
				<div class = "sub-info-thread" style = 'text-align: left'>
					<?php
						if($perm_to_delete){
							?>
								<span id = 'edit-title-opt' style = 'color:lightblue;cursor:pointer;'>Edit Title</span><br>
							<?php
						}
					?>
					Started By <?php echo $header_info['thread_starter']; ?> &middot; <?php echo $header_info['thread_likes']; ?> Debate Like(s)
				</div>


				
				<br>
			<?php

			if(isset($_GET['scr_to'])){
				$scr_pos = htmlentities($_GET['scr_to']);
				?>
				<script>
				$(document).ready(function(){
					
						$('html, body').animate({scrollTop: "<?php echo $scr_pos; ?>"}, 0);
				
				});	
				</script>
				<?php
			}
			
			$get_replies = $db->prepare("SELECT * FROM thread_replies WHERE thread_id= :thread_id AND size = '' ORDER BY time_replied ASC");
			$get_replies->execute(array("thread_id"=>$thread_id));
			while($row = $get_replies->fetch(PDO::FETCH_ASSOC)){
				if($row['visible']==1){
					$dis = "norm";
				}else if($row['user_replied']==get_user_field($_SESSION['user_id'], "user_username")){
					$dis = "red";
					//unapproved but user owner can see.
				}else{
					$dis = false;
				}
				
				if($dis!=false){
					$agrees = $row['reply_agrees'];
					$disagrees = $row['reply_disagrees'];
					$total = $agrees + $disagrees;
					if($total > 0){
						$agrees_p = round(($agrees/$total)*100);
						$disagrees_p = round(($disagrees/$total)*100);
						if($total==1){
							$plur = "";	
						}else{
							$plur = "s";	
						}
					}else{
						$agrees_p = 0;
						$disagrees_p = 0;
						$plur = "s";
					}
					
					if($dis=="red"){
						$reply_red_style = "border: 3px solid salmon;";
						$red_text = "<span style = 'color: salmon;'>*NOTE: Untill your community leader has approved this post, only you can see it.</span><br><br>";
					}else{
						$reply_red_style = "";
						$red_text = "";
					}
					?>
					<div class = 'thread-reply-container' style = "<?php echo $reply_red_style; ?>">
						<?php
						echo $red_text;
						if($row['reply_status']!=''){ 
							echo "<span style = 'color:grey;'>Voted : ".$row['reply_status']."</span><br>";
						}else{
							echo "<span style = 'color:grey;'>Hasn't Voted</span><br>";
						}
						if((isset($_GET['editp']))&&($_GET['editp']==$row['reply_id'])&&(user_own_reply($row['reply_id'], $_SESSION['user_id'])||(user_rank($_SESSION['user_id'], 2, "up")))){
							?>
							<form method = "POST">
								<textarea name = "editp" class = "textarea-type1"><?php echo $row['reply_text']; ?></textarea>
								<input type= "submit" class = "mreply-submit">
							</form>	
							<?php
						}else{
							echo $row['reply_text'];
						}
						?>
						<br><br>
						<span style = "color:dimgrey;">
							Argued By <?php echo "<a style = 'color:grey;' href = 'index.php?page=profile&user=".$db->query("SELECT user_id FROM users WHERE user_username = ".$db->quote($row['user_replied']))->fetchColumn()."'>".$row['user_replied']."</a>"; ?> on <?php echo date("d/M/Y H:i", $row['time_replied']);?>
							<br><span style = "color:#8FBC8F;"><?php echo $agrees_p; ?>% Agreed</span> and <span style = "color:#CD9B9B;"><?php echo $disagrees_p; ?>% Disagreed</span> with this argument (<?php echo $total;?> voter<?php echo $plur; ?>)
						</span>
		
						<div class = "thread-reply-info">
							<?php
								if(valid_reply_voter($_SESSION['user_id'], $row['reply_id'])){
							?>
									<div class = "reply-option1-container">
										<a style = "color:#8FBC8F;" href = "index.php?page=view_private_thread&thread_id=<?php echo $_GET['thread_id']; ?>&vote=a<?php echo $row['reply_id']; ?>">
											Agree
										</a>
									</div>	
									<div class = "reply-option1-container">
										<a style = "color:#CD9B9B;" href = "index.php?page=view_private_thread&thread_id=<?php echo $_GET['thread_id']; ?>&vote=d<?php echo $row['reply_id']; ?>">
											Disagree
										</a>	
									</div>
							<?php
								}
								?>
								<div class = "reply-option1-container">
						
									<a id = "show-mreply-form<?php echo $row['reply_id']; ?>">
										Reply
									</a>	
								
								</div>
								<?php
								if((user_own_reply($row['reply_id'], $_SESSION['user_id']))||(user_rank($_SESSION['user_id'], 2, "up"))){
								?>
				
								<div class = "reply-option1-container">
						
									<a href = "index.php?page=view_private_thread&thread_id=<?php echo $_GET['thread_id']; ?>&editp=<?php echo $row['reply_id']; ?>">
										Edit
									</a>
								</div>	
								<div class = "reply-option1-container">
									<a href = "index.php?page=view_private_thread&thread_id=<?php echo $_GET['thread_id']; ?>&deletep=<?php echo $row['reply_id']; ?>">
										Delete
									</a>
								</div>	
							

								<?php	
							
								}
								if(!user_own_reply($row['reply_id'], $_SESSION['user_id'])&&!check_c_reported($row['reply_id'], "reply_id", "thread_replies")){
								?>
									<div class = "reply-option1-container">
									<a style = "color: salmon;" href = "index.php?page=view_private_thread&thread_id=<?php echo $thread_id; ?>&repo-c=<?php echo $row['reply_id']; ?>">
										Report Abuse
									</a>
									</div>	
								<?php		
								}else if(check_c_reported($row['reply_id'], "reply_id", "thread_replies")){
								
							?>
								<div class = "reply-option1-container">
									<span style = "color: salmon;">
										*NOTE: This content has been reported
									</span>
								</div>	
							<?php
								}
							?>
						</div>
					</div>
					
					<div id = "mreply_form<?php echo $row['reply_id']; ?>" class = "mreply-form-container">
						<form action = "" method = "POST">
							<textarea placeholder= "Reply" name = "mreply_text<?php echo $row['reply_id']; ?>" class = "textarea-type2"></textarea>
							<input type = "submit" class = "mreply-submit" value = "Submit">
						</form>
					</div>
		
			
					<script>
						$("#show-mreply-form<?php echo $row['reply_id']; ?>").click(function(){
							$("#mreply_form<?php echo $row['reply_id']; ?>").slideDown(300);
						});				
					</script>
					<?php
					$get_mreplies = $db->prepare("SELECT * FROM thread_replies WHERE thread_id = ".$row['reply_id']." AND size = 'mini' ORDER BY time_replied ASC");
					$get_mreplies->execute();
					while($mrow = $get_mreplies->fetch(PDO::FETCH_ASSOC)){
						if($mrow['visible']==1){
							$mdis = "norm";
						}else if($mrow['user_replied']==get_user_field($_SESSION['user_id'], "user_username")){
							$mdis = "red";
							//unapproved but user owner can see.
						}else{
							$mdis = false;
						}
						if($mdis!=false){
							if($mdis=="red"){
								$mreply_red_style = "border: 3px solid salmon;";
								$mred_text = "<span style = 'color: salmon;'>*NOTE: Untill your community leader has approved this post, only you can see it.</span><br><br>";
							}else{
								$mreply_red_style = "";
								$mred_text = "";
							}
							echo "<div class = 'mreply-container' style = '".$mreply_red_style."'>";
								echo $mred_text;
								if((isset($_GET['editp']))&&($_GET['editp']==$mrow['reply_id'])&&(user_own_reply($mrow['reply_id'], $_SESSION['user_id'])||(user_rank($_SESSION['user_id'], 2, "up")))){
								?>
								<form method = "POST">
									<textarea name = "editp" class = "textarea-type1"><?php echo $mrow['reply_text']; ?></textarea>
									<input type= "submit" class = "mreply-submit">
								</form>	
								<?php
								}else{
									echo $mrow['reply_text'];
								}
								?>
								<br><span style = "color:dimgrey;">
									Replied By <?php echo "<a style = 'color:grey;' href = 'index.php?page=profile&user=".$db->query("SELECT user_id FROM users WHERE user_username = ".$db->quote($mrow['user_replied']))->fetchColumn()."'>".$mrow['user_replied']."</a>"; ?> on <?php echo date("d/M/Y H:i", $mrow['time_replied']);
									?>
									<div class = "thread-reply-info">
									<?php
									if((user_own_reply($mrow['reply_id'], $_SESSION['user_id']))||(user_rank($_SESSION['user_id'], 2, "up"))){
										?>
										
											<div class = "reply-option1-container">
							
												<a href = "index.php?page=view_private_thread&thread_id=<?php echo $_GET['thread_id']; ?>&editp=<?php echo $mrow['reply_id']; ?>">
													Edit
												</a>
											</div>
											<div class = "reply-option1-container">	
												<a href = "index.php?page=view_private_thread&thread_id=<?php echo $_GET['thread_id']; ?>&deletep=<?php echo $mrow['reply_id']; ?>">
													Delete
												</a>
												
											</div>	
											<?php
										}	
										if(!user_own_reply($mrow['reply_id'], $_SESSION['user_id'])&&!check_c_reported($mrow['reply_id'], "reply_id", "thread_replies")){
												?>
													<div class = "reply-option1-container">
													<a style = "color: salmon;" href = "index.php?page=view_private_thread&thread_id=<?php echo $thread_id; ?>&repo-c=<?php echo $mrow['reply_id']; ?>">
														Report Abuse
													</a>
													</div>	
												<?php		
											}else if(check_c_reported($mrow['reply_id'], "reply_id", "thread_replies")){
								
											?>
												<div class = "reply-option1-container">
													<span style = "color: salmon;">
														*NOTE: This content has been reported
													</span>
												</div>	
											<?php
										}
											?>	
									</div>
										<?php 
									
									?>
								</span>
								<?php
							echo"</div>";
						}	
					}
					echo "<br><br>";

					if(isset($_POST['mreply_text'.$row['reply_id']])){
						$reply_text = $_POST['mreply_text'.$row['reply_id']];
						$user_replied = get_user_field($_SESSION['user_id'], "user_username");
						$reply_id = $row['reply_id'];
						$size = "mini";
						$reply_status="na";
						$report_header = "";
						$check_abuse = contains_blocked_word($reply_text);
						if($check_abuse[0]==true){
							$reply_text = $check_abuse[1];
							$report_header = true;
						}
			
						if(strlen($reply_text)>2){
							$reply_to  = $row['user_replied'];
							$link = "index.php?page=view_private_thread.php?thread_id=".$thread_id;
							$active = (user_moderation_status($_SESSION['user_id'])>1)? 0:1;

							$reply = reply_debate($reply_text, $user_replied, $reply_id, $size, $reply_status);
							$msge=$reply[1];
							$rid = $reply[0];
							if($report_header==true){
								$report_header = "&repo-c=".$rid."-";
							}
							setcookie("success", "1".$msge, time()+10);
							header("Location: index.php?page=view_private_thread&thread_id=".$_GET['thread_id'].$report_header);
						}else{
							setcookie("success", "0".$msge, time()+10);
							header("Location: index.php?page=view_private_thread&thread_id=".$_GET['thread_id']);
						}
					}
				}	
			}
			
			?>
				<hr size = "1"><br>
				<div class = "thread-title-repeat"><?php echo $header_info['thread_title']; ?></div>
				<br>

				<?php
					$vote_opts = get_question_type($header_info['thread_title'], 2);
				?>
				<form action = "" method = "POST">
					<span style = 'color:grey;'>Vote:</span> <select name = "reply_status" class = "reply-status-select">
						<option value = "na">(optional)</option>
						<?php
							foreach($vote_opts as $opt){
								echo "<option value = '".$opt."'>".$opt."</option>";
							}
						?>
					</select>
					<br><br>
					<textarea placeholder = "Explanation/Argument..."class = "textarea-type1" style = "width:84%;" name = "reply_text"></textarea><br>
					<input type = "submit" class = "mreply-submit">
				</form>
			<?php
				
			if(isset($_POST['reply_status'], $_POST['reply_text'])){
				$reply_status = htmlentities($_POST['reply_status']);
				$reply_text = htmlentities($_POST['reply_text']);
				$user_replied = get_user_field($_SESSION['user_id'], "user_username");
				$thread_id = $_GET['thread_id'];
				$size="";
				$report_header = "";
				$vote_opts[] = "na";
				$check_abuse = contains_blocked_word($reply_text);
				if($check_abuse[0]==true){
					$reply_text = $check_abuse[1];
					$report_header = true;
				}
				if(in_array($reply_status, $vote_opts)){
					if(strlen($reply_text)>50){
						
						$reply = reply_debate($reply_text, $user_replied, $thread_id, $size, $reply_status);
						$msg = $reply[1];
						$rid = $reply[0];
						if($report_header==true){
							$report_header = "&repo-c=".$rid."-";
						}
						$starter = $db->query("SELECT user_id FROM users WHERE user_username = ".$db->quote($header_info['thread_starter']))->fetchColumn();
						if($starter!=$_SESSION['user_id']){
							add_note($starter, $user_replied." has replied to your debate '".$header_info['thread_title']."'.", "index.php?page=view_private_thread&thread_id=".$thread_id);
						}
						setcookie("success", "1".$msg, time()+10);
						
						header("Location: index.php?page=view_private_thread&thread_id=".$_GET['thread_id'].$report_header);
					}else{
						setcookie("success", "0Your post must be longer.", time()+10);
						
					}
				}else{
					setcookie("success", "0Invalid vote.", time()+10);
				}	
				header("Location: index.php?page=view_private_thread&thread_id=".$_GET['thread_id']);
			}
			if(isset($_GET['vote'])){
				
				$code = $_GET['vote'];
				$status = substr($code, 0, 1);
				if($status =="a"){
					$vote_id = 1;	
				}else{
					$vote_id=0;
				}
				$reply_id = substr($code, 1);
				vote_reply($vote_id, $_SESSION['user_id'], $reply_id);
				setcookie("success", "1Successfully voted!", time()+10);
				header("Location: index.php?page=view_private_thread&thread_id=".$_GET['thread_id']);
			}
			
			if(isset($_POST['editp'])){
				$reply_id = $_GET['editp'];
				$new_text = htmlentities($_POST['editp']);
				$check_abuse = contains_blocked_word($new_text);
				$report_header = "";
				if($check_abuse[0]==true){
					$new_text = $check_abuse[1];
					$report_header = "&repo-c=".$reply_id."-";
				}
			
				if(post_action($_SESSION['user_id'], $reply_id, "edit", $new_text)){
					setcookie("success", "1Successfully edited post!", time()+10);
				}else{
					setcookie("success", "0There was an error.", time()+10);
				}

				header("Location: index.php?page=view_private_thread&thread_id=".$_GET['thread_id'].$report_header);
			}
			if(isset($_GET['d_like'])){
				$thread_id = (int) htmlentities($_GET['d_like']);	
				$user_id = $_SESSION['user_id'];
				if(valid_debate_like($thread_id, $user_id)){
					$type = "like";	
				}else{
					$type = "unlike";
				}
				
				if(valid_debate_like($thread_id, $_SESSION['user_id'])){
					setcookie("success", "1Successfully liked debate!", time()+10);
					add_rep(3, $db->query("SELECT user_id FROM users WHERE user_username= ".$db->quote($header_info["thread_starter"]))->fetchColumn());
				}else{
					setcookie("success", "1Successfully unliked debate!", time()+10);
					add_rep(-3, $db->query("SELECT user_id FROM users WHERE user_username= ".$db->quote($header_info["thread_starter"]))->fetchColumn());
				}
				$like_quant_to_check = array(10,50, 100, 200, 500, 1000, 1500, 2000);
				foreach($like_quant_to_check as $x){
					if($x-1 == $header_info['thread_likes']){
						add_badge("Has reached ".$x." likes on one of his/her debates", $db->query("SELECT user_id FROM users WHERE user_username= ".$db->quote($header_info["thread_starter"]))->fetchColumn(), "you have just recieved your ".$x."th like on a debate created by you.");
					}
				}
				
				like_debate($thread_id, $user_id, $type);
				header("Location: index.php?page=view_private_thread&thread_id=".$_GET['thread_id']);
			}
			if(isset($_GET['deletep'])){
				$reply_id = $_GET['deletep'];
				if(post_action($_SESSION['user_id'], $reply_id, "delete", "")){
					setcookie("success", "1Successfully deleted post!", time()+10);
				}else{
					setcookie("success", "0There was an error.", time()+10);
				}
				header("Location: index.php?page=view_private_thread&thread_id=".$_GET['thread_id']);
			}
			
			if(isset($_GET['repo-c'])){
				$reply_id = htmlentities($_GET['repo-c']);
				$reported_by = get_user_field($_SESSION['user_id'], "user_username");
				if(substr($reply_id, strlen($reply_id)-1, strlen($reply_id))=="-"){
					$reply_id = substr($reply_id, 0, strlen($reply_id)-1);
					$reported_by = "BuzzZap Filtering";
				}
				$reported_user = $db->query("SELECT user_replied FROM thread_replies WHERE reply_id = ".$db->quote($reply_id))->fetchColumn();
				$reason = "--This content posted by ".$reported_user." is abusive: ". $db->query("SELECT reply_text FROM thread_replies WHERE reply_id = ".$db->quote($reply_id))->fetchColumn();
				if(!check_c_reported($reply_id, "reply_id", "thread_replies")){
					report_user($reported_by,$reported_user, $reason, array(true,$reply_id,"reply_id", "thread_replies"));
					setcookie("success", "1Successfully reported content.", time()+10);
				}else{
					setcookie("success", "1This post has already been reported.", time()+10);
				}
				header("Location: index.php?page=view_private_thread&thread_id=".$_GET['thread_id']);
			}	
		
		}else{
			//header("Location: index.php?page=home");
		}	
	}else{
		//header("Location: index.php?page=home");
	}
}else{
	//header("Location: index.php?page=home");
}
?>