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
		$header_info = array("thread_title"=>"", "thread_starter"=>"","topic_id"=>"", "com_id"=>"","time_created"=>"", "vote_yes"=>"", "vote_maybe"=>"", "vote_no"=>"", "thread_likes"=>"", "visible"=>"");
		foreach($header_info as $column => &$value){
			$value = $db->query("SELECT ".$column." FROM debating_threads WHERE thread_id = ".$thread_id."")->fetchColumn();
		}
		$dtype = ($header_info['com_id']>0)? "Private" : "Global";
		$path_link1 = ($dtype=="Private")? "index.php?page=private_debating":"index.php?page=private_debating&d=g";
		$path_link2 = ($dtype=="Private")? "index.php?page=private_debating_topic&topic_id=".$header_info['topic_id']:"index.php?page=private_debating_topic&topic_id=".$header_info['topic_id']."&d=g";
		$topic_name = $db->query("SELECT topic_name FROM debating_topics WHERE topic_id = ".$db->quote($header_info['topic_id']))->fetchColumn();
		if(valid_view_thread($thread_id, $_SESSION['user_id'])){
			?>
			<div class = 'page-path'>Debating > <?php echo "<a style = 'color: #40e0d0;' href = '".$path_link1."'>".$dtype; ?> Debating </a> > <?php echo "<a style = 'color: #40e0d0;' href = '".$path_link2."'>".$topic_name." </a> > ".$header_info['thread_title']; ?></div><br>
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
				<?php
					$user_idp = $db->query("SELECT user_id FROM users WHERE user_username = ".$db->quote($header_info['thread_starter']))->fetchColumn();
					$perm_to_delete = false;
					if((user_rank($_SESSION['user_id'], "3")==true)&&(get_user_community($_SESSION['user_id'], "com_id")==get_user_community($user_idp, "com_id"))){
						$perm_to_delete = true;
					}else if($_SESSION['user_id']==$user_idp){
						$perm_to_delete = true;
					}
					if($perm_to_delete==true){
						echo "<a style = 'font-size: 100%;color:salmon;' href = 'index.php?page=view_private_thread&deld=true&thread_id=".$thread_id."'>Delete Debate</a><br>";
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

							$("#add-arg-div-link").click(function(){

								$("#give-arg-text,#thread-title-repeat").css({"color":"#15a9ce", "font-weight":"bold"});
								var flash_txt_border = setInterval(function(){
									$("#give-arg-text,#thread-title-repeat").css({"color":"#15a9ce", "font-weight":"bold"});
									setTimeout(function(){
										$("#give-arg-text,#thread-title-repeat").css({"color":"grey", "font-weight":"normal"});
									}, 200);
								}, 400);
								
								setTimeout(function(){
									clearInterval(flash_txt_border);
								}, 5000);
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
				<div class = "thread-title-header" id = "thread-title-header"><?php echo $header_info['thread_title']; ?></div>
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
				<a href = "index.php?page=view_private_thread&thread_id=<?php echo $_GET['thread_id']; ?>&d_like=<?php echo $thread_id; ?>" class = "view-thread-opts-link"><?php echo $like_status; ?> debate</a>
				<a href = "#thread-title-repeat" class= "view-thread-opts-link" id = "add-arg-div-link">Add Argument</a>
				<hr size = '1'>
				<br>
				<?php
				$qtype = get_question_type($header_info['thread_title'], 1);
				$dis_vote_opts = get_question_type($header_info['thread_title'], 2, $thread_id);
				if(count($dis_vote_opts)!=0){
					$dis_votes = "";
					$sec_width = (100/count($dis_vote_opts))-1;
					if($qtype=="open"){
						$colors = array("#5a9999", "#5a9999", "#5a9999", "#5a9999", "#5a9999");
						$vote_vals = merge_cus_vote_vals($thread_id);
					}else{
						$colors = array("#7fdd99","salmon", "#5a9999");
						$vote_vals = array($header_info['vote_yes'],$header_info['vote_no'],$header_info['vote_maybe']);
					}

					$total = array_sum(array_values($vote_vals));

					$count = 0;
					foreach($dis_vote_opts as $opt){
						$fsize = 110 - 1.5*(strlen($opt));
						if($count==count($dis_vote_opts)-1){
							$borderright = "border-right: 1px solid #b2b2b2;";
						}else{
							$borderright = "";
						}
						$vote_val = ($qtype=="open")? $vote_vals[$opt]: $vote_vals[$count];
						$vote_val = get_vote_perc($vote_val, $total);
						$dis_votes.="
							<div class = 'thread-end-sec' style = 'width:".$sec_width."%;font-size:".$fsize."%;border-left:1px solid #b2b2b2;height:40px;".$borderright."color:grey;height:70px;'>
								<span style = 'color:".$colors[$count].";'>".$opt."</span><br>
								".$vote_val."%
							</div>
						";
						$count++;
					}
					echo "<div id = 'vote-dis-box' style = ''>
						<div class = 'voter-amount' style = 'font-size:120%'>".$total." vote(s):</div>

							".$dis_votes."
						
						</div><br>";
				}
				

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
			$rcount = 0;
			if($get_replies->rowCount()>0){
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
						$check_audio = $db->query("SELECT audio_flocation FROM audio WHERE owner_id = ".$db->quote($row['reply_id'])." AND owner_table = 'thread_replies'")->fetchColumn();
						if($check_audio){
							$check_audio=$spec_judge_email_link."/audio/".$check_audio;
							$play_button= "<div class = 'rec-audio play-audio' id = 'play-audio-".$rcount."' style = 'background-image: none;'>
										 <span id = 'play-button-cont".$rcount."' style = 'margin-left:-5px;margin-top:-12px;position: absolute;font-size: 300%;color:dimgrey;'>&#9658;</span>
									</div><audio src  = '".$check_audio."' id = 'audio-tag".$rcount."'></audio><br><br><br>
									
									<script>
									var pclicks = 0;
									$('#play-audio-".$rcount."').click(function(){
										
										var audio = document.getElementById('audio-tag".$rcount."');
										if(pclicks%2==0){
											audio.play();
											$('#play-button-cont".$rcount."').html('&#10074;&#10074;');
										}else{
											audio.pause();	
											$('#play-button-cont".$rcount."').html('&#9658;');
										}

										audio.addEventListener('ended', function(){
											$('#play-button-cont".$rcount."').html('&#9658;');
										});	
										pclicks++;
									});

									</script>
									<br>

									";
						}else{
							$play_button = "";
						}
						?>
						<div class = 'thread-reply-container' style = "<?php echo $reply_red_style; ?>" id = "treply-<?php echo $rcount; ?>">
							<?php
							echo $red_text;
							if($row['reply_status']!=''){ 
								echo "<span style = 'color:grey;'>Voted: ".$row['reply_status']."</span><br>";
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
								echo $play_button.$row['reply_text'];
							}
							?>
							<br><br>
							<span style = "color:dimgrey;">
								Argued By <?php echo add_profile_link($row['user_replied'], 0, $style="color:grey"); ?> on <?php echo date("d/M/Y H:i", $row['time_replied']);?>
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
							
										<a href = "index.php?page=view_private_thread&thread_id=<?php echo $_GET['thread_id']; ?>&editp=<?php echo $row['reply_id']; ?>#treply-<?php echo $rcount;?>">
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
						$mrcount = 0;
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
								echo "<div class = 'mreply-container' style = '".$mreply_red_style."' id = 'mreply-".$mrcount."'>";
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
										Replied By <?php echo add_profile_link($mrow['user_replied'], 0, $style="color:grey"); ?> on <?php echo date("d/M/Y H:i", $mrow['time_replied']);
										?>
										<div class = "thread-reply-info">
										<?php
										if((user_own_reply($mrow['reply_id'], $_SESSION['user_id']))||(user_rank($_SESSION['user_id'], 2, "up"))){
											?>
											
												<div class = "reply-option1-container">
								
													<a href = "index.php?page=view_private_thread&thread_id=<?php echo $_GET['thread_id']; ?>&editp=<?php echo $mrow['reply_id']; ?>#mreply-<?php echo $mrcount; ?>">
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
								echo "</div>";
							}	
							$mrcount ++;
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

					$rcount ++;	
				}
			}else{
				echo "<div id = 'no-threads-message' style = 'margin-top:0px;'>No arguments posted yet.</div>";
			}	
			
			?>
				<script>
				$(function(){
					var rec_enabled = true;
					var fileName = "<?php echo $_SESSION['user_id'].',0,'.substr(encrypt(time()),0,8); ?>.wav";
					var mediaTypes = {audio:true};
					function recAudio(mediaTypes, mediaSuccess, mediaError){
						navigator.mediaDevices.getUserMedia(mediaTypes).then(mediaSuccess).catch(mediaError);
					}
					function mediaError(e) {
        				console.error('media error', e);
    				}
					
					var mediaRecorder; 
					var index = 1;

					function bytesToSize(bytes) {
				        var k = 1000;
				        var sizes = ['Bytes', 'KB', 'MB', 'GB', 'TB'];
				        if (bytes === 0) return '0 Bytes';
				        var i = parseInt(Math.floor(Math.log(bytes) / Math.log(k)), 10);
				        return (bytes / Math.pow(k, i)).toPrecision(3) + ' ' + sizes[i];
				    }
				    function getTimeLength(milliseconds) {
				        var data = new Date(milliseconds);
				        return data.getUTCHours() + " hours, " + data.getUTCMinutes() + " minutes and " + data.getUTCSeconds() + " second(s)";
				    }
				    var formData = new FormData();
					function accRecord(stream){
				        
       					mediaRecorder = new MediaStreamRecorder(stream);
				        mediaRecorder.mimeType = 'audio/wav';
				        mediaRecorder.type = StereoAudioRecorder;
				        mediaRecorder.audioChannels = 2;
				        mediaRecorder.ondataavailable = function(blob) {
				            var fileType = "audio";
				            formData.append(fileType + '-filename', fileName);
        					formData.append(fileType + '-blob', blob);
			
				        };
				        var timeInterval = 100000000;
				        mediaRecorder.start(timeInterval);

					}

					var clicks = 0;
					$("#rec-audio").click(function(){
						if(clicks%2==0&&rec_enabled ==true){
							$(this).css("background-image", "none");
							$(this).html("<span style = 'margin-left:-7px;margin-top:-12px;position: absolute;font-size: 300%;color:dimgrey;'>&#9724;</span>");
							$("#recording-status").html(" Recording...");
							recAudio(mediaTypes,accRecord,mediaError);
						}else{
							$(this).css("background-image", "url('<?php echo $spec_judge_email_link; ?>/ext/images/mic.png')");
							$(this).html("");
							mediaRecorder.stop();
							$("#recording-status").html("");
        					$("#save-audio, #try-again-audio").fadeIn();
     
						}
						clicks ++;
					});
					$("#try-again-audio").click(function(){
						rec_enabled = true;
						$("#save-audio, #try-again-audio").fadeOut();
					});
					$("#save-audio").click(function(){
						$("#argsubmit").animate({letterSpacing:"2px"}, 100);
						setInterval(function(){
							$("#argsubmit").animate({letterSpacing:"1px"}, 100);
							setTimeout(function(){
								$("#argsubmit").animate({letterSpacing:"2px"}, 100);
							}, 100);
						}, 200);
						var request = new XMLHttpRequest();
			            request.onreadystatechange = function (err) {
			                if (request.readyState == 4 && request.status == 200) {
			                    console.log(location.href + request.responseText);
			                }
			                console.log(err);
			            };
			            $("#save-audio, #try-again-audio").fadeOut();
			            request.open('POST', "<?php echo $ajax_script_loc; ?>");
			            request.send(formData);
					});


					$("#choose-arg-type").change(function(){
						$(".ans-type-container").hide();
						$("#"+$("#choose-arg-type").val()+"-ans-container").show();
						$("#argsubmit").show();
						$("#space-breaks").show();
					});
				});
				</script>
				<hr size = "1"><br>
				<div class = "thread-title-repeat" id = "thread-title-repeat"><?php echo $header_info['thread_title']; ?></div>
				<br>

				<?php
					$vote_opts = get_question_type($header_info['thread_title'], 2, $thread_id);
				?>
				<form action = "" method = "POST">


					<span style = 'color:grey;' id = "give-arg-text">
						Vote answer:<br>
						<?php if(!empty($vote_opts)){ ?>
						<select name = "reply_status" class = "reply-status-select">
							<option value = "na">(optional)</option>
							<?php
								foreach($vote_opts as $opt){
									echo "<option value = '".$opt."'>".$opt."</option>";
								}
							?>
						</select>
						<?php }else{ echo "No voting options available."; } ?>
						<br><br>
						
						How would you like to argue?<br>
						<select id = "choose-arg-type" class = "reply-status-select">
							<option value = "na">---</option>
							<option value = "txt">Text</option>
							<option value = "rec">Speech</option>
						</select><br><br>
						<div id = "----ans-container" class = "ans-type-container">
							<br><br><br><br><br><br><br><br><br><br><br><br>
						</div>
						<div id = "rec-ans-container" class = "ans-type-container" style = "display:none;">	
							Record speech for argument:
							<br><br>
							<div class = "rec-audio" id = "rec-audio"></div><br>
							<br><div id = "recording-status"></div><br>
							<div id = "save-audio">Use</div><div id = "try-again-audio">Re-do</div><br><br>
							<span style = 'font-size:70%'>Warning: the record feature may not<br> work properly in certain browsers. If so, use text instead.</span>
						</div>
						<div id = "txt-ans-container" class = "ans-type-container" style = "display:none;">	
							Text argument:<br>
							<textarea placeholder = "Explanation/Argument..."class = "textarea-type1" id = 'new-arg-textarea' style = "width:84%;" name = "reply_text"></textarea><br>
							
						</div>
						<br><input type = "submit" class = "loggedout-form-submit" id = "argsubmit" style = "display:none;" value = "Post">
						<div id = "space-breaks" style = "display: none">
							<br><br><br><br><br><br><br><br><br>
						</div>	
					</span>
				</form>
			<?php

				
			if(isset($_POST['reply_text'])){
				$reply_status = (isset($_POST['reply_status'])) ? htmlentities($_POST['reply_status']) : "";
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
					if(strlen($reply_text)>50||isset($_COOKIE['temp_audio_ret_rid'])){
						
						$reply = reply_debate($reply_text, $user_replied, $thread_id, $size, $reply_status);
						$msg = $reply[1];
						$rid = $reply[0];
						
						if(isset($_COOKIE['temp_audio_ret_rid'])){

							$f_code = $_COOKIE['temp_audio_ret_rid'];
							$db->query("UPDATE audio SET owner_id = ".$db->quote($rid)." WHERE audio_flocation LIKE '%$f_code'");
							setcookie("temp_audio_ret_rid", "", time()-1000000000);
						}
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
				header("Location: index.php?page=view_private_thread&thread_id=".$_GET['thread_id']."#thread-title-repeat");
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
				//developement
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
			header("Location: index.php?page=home");
		}	
	}else{
		header("Location: index.php?page=home");
	}
}else{
	header("Location: index.php?page=home");
}
?>