<?php
if($check_valid!="true"){
	header("Location: index.php?page=home");
	exit();
}
if(isset($_GET['out_judge_key'])){
	$out_judge_key = "out:".htmlentities($_GET['out_judge_key']);
	if(isset($_SESSION['user_id'])){
		header("Location: index.php?page=home");
	}
}
if(loggedin()||!empty($out_judge_key)){
	if(isset($_GET['comp'])){
		$comp_id = substr(htmlentities($_GET['comp']), 1);
		$type = substr(htmlentities($_GET['comp']), 0,1);
		$type_match_comp = $db->query("SELECT comp_id FROM competitions WHERE comp_id = ".$db->quote($comp_id)." AND comp_type = ".$db->quote($type))->fetchColumn();
		$comp_info = get_comp_info($comp_id);
		$comp_com_id = $comp_info['comp_com_id'];
		$judges = $comp_info['judges'];
		$judges = ($judges=="norm")? "norm": get_judge_list($comp_id);
		$valid_to_view = false;
		$user_id = (isset($_SESSION['user_id']))? $_SESSION['user_id']: $out_judge_key;

		
		//if in correct com (includes public where com = 0)
		if(empty($out_judge_key)){
			$com_id = ($type=="0")? get_user_community($_SESSION['user_id'], "com_id"): "0";
			if($com_id==$comp_com_id){
				$valid_to_view = true;
			}else{
				$valid_to_view = false;
			}
		}	

		//if judge
		if($judges!="norm"){
			if(in_array($user_id, $judges)){
				$valid_to_view = true;
			}
		}

		if((!empty($comp_info["comp_title"]))&&($valid_to_view==true)&&(in_array($type, array("0","1"))&&(!empty($type_match_comp)))){

			?>
			<script>
			$(document).ready(function(){
				
				$("#sa-show-form").click(function(){
					$("#add-arg-comp-form").fadeIn();
				});
				
				$(".add-comment-arg-opt").click(function(){
				
					var cand_id = $(this).attr("cand_id");
					var arg_id = $(this).attr("arg_id");
					
					$("#com_cand_id").val(cand_id);
					$("#com_arg_id").val(arg_id);
					$("#add-com-comp-form").fadeIn();
				
				});
				
				$(".vote-opt").click(function(){
					var info = $(this).attr("arg_id");
					var vote = info.substring(0,1);
					if(vote=="0"){
						vote = -1;
					}else{
						vote = 1;
					}	
					var table = "";
					var c_prefix = "";
					if(info.substring(1,2)=="b"){
						table = "comp_arguments";
						c_prefix = "b";
					}else{
						table = "comp_arg_replies";
						c_prefix = "m";
					}	
					
					var arg_id = info.substring(3);
					var comp_id = "<?php echo $comp_id; ?>";
					var parse_judges = "<?php echo ($judges=='norm')? 'norm' : implode(',',$judges); ?>";
					var post_data = {vote:vote, table:table, arg_id:arg_id, comp_id:comp_id, ctype:"<?php echo $type; ?>", judges:parse_judges, user_id:"<?php echo $user_id; ?>"};
					console.log(post_data);
					$.post("<?php echo $ajax_script_loc; ?>", post_data, function(result,err){
						console.log(err+result);
						$("."+c_prefix+"cid"+arg_id).fadeOut(100);
						setTimeout(function(){
							$("."+c_prefix+"success-msg"+arg_id).html(result);
							$("."+c_prefix+"success-msg"+arg_id).fadeIn();					
							setTimeout(function(){
								$("."+c_prefix+"success-msg"+arg_id).fadeOut();
							}, 1000);
						}, 100);

					});
				});
				
				secs_left = parseInt($("#secs_end").html());
				mins_left = parseInt($("#mins_end").html());
				var comp_t = setInterval(function(){
					
					secs_left = secs_left-1;
					$("#secs_end").html(secs_left.toString());
					if(secs_left==1){
						
						if(mins_left!=0){
							secs_left = 60;
							mins_left = mins_left -1;
						}
						$("#mins_end").html(mins_left.toString());
						
					}
					if(mins_left===0&&secs_left===0){
						$("#time_info").html("<span style = 'color:red;'>ENDED</span>");
					}
						
				}, 1000);
			});
			</script>

			<?php

				if($type=="0"){
					$ctype = "Private";
					$comp_home_path_link = "index.php?page=comp_home&type=0";
				}else{
					$ctype = "Global";
					$comp_home_path_link = "index.php?page=comp_home&type=1";
				}
				echo "<div class = 'page-path'>Debating > <a style = 'color: #40e0d0' href= '".$comp_home_path_link."'>".$ctype." Competitions </a> > ".$comp_info['comp_title']."</div><br>";
				$perm_to_delete = false;
				if(($type=="0")&&(get_user_community($user_id, "com_id")==$comp_info["comp_com_id"])&&(user_rank($user_id, "3")&&empty($out_judge_key))){
					$perm_to_delete = true;
					echo "<a style = 'font-size: 100%;color:salmon;' href = 'index.php?page=view_comp&delcomp=true&comp=".$type.$comp_id."'>Delete Competition</a><br>";
				}
				if(isset($_GET['delcomp'])&&$perm_to_delete == true){
					if($type=="0"){
						$ids = get_all_users_in_p_comp($comp_id);
						foreach($ids as $id){
							add_note($id, get_user_field($_SESSION['user_id'],"user_username")." cancelled the competition '".$comp_info['comp_title']."' that you were involved in. ","");
						}
					}else{
						$cids = array_keys(get_comp_acceptance_info($comp_id, $type));
						foreach($cids as $id){
							add_com_feed($id, get_user_field($_SESSION['user_id'],"user_username")." cancelled the competition '".$comp_info['comp_title']." that we were involved in.");
						}
					}
					$db->query("DELETE FROM competitions WHERE comp_id = ".$comp_id);
					$db->query("DELETE FROM comp_arguments WHERE comp_id = ".$comp_id);
					$db->query("DELETE FROM comp_arg_replies WHERE comp_id = ".$comp_id);
					setcookie("success", "1Successfully deleted competition.", time()+10);
					header("Location: index.php?page=comp_home&type=".$type);
				}
				
				if(comp_started($comp_id)){
					$seconds_left = $comp_info["end"]-time();
					$days_left = (int)($seconds_left / 86400);
					$hours_left = (int)($seconds_left / 3600)-($days_left*24);
					$minutes_left = (int)($seconds_left / 60)-($days_left*60*24)-($hours_left*60);
					$seconds_left_ = (int)($seconds_left)-($days_left*86400)-($hours_left*3600)-($minutes_left*60);
				
					$time_left_str = "Time left: ".$days_left." days  ".$hours_left." hrs  ".$minutes_left." mins ";
				
					if($days_left==0 && $hours_left == "0"){
						$time_left_str = "Time left: <span id = 'mins_end'>".$minutes_left."</span> mins  <span id = 'secs_end'>".$seconds_left_."</span> secs";
					}
				}else if(comp_ended($comp_id)){
					$time_left_str = "";
				}else{
					$time_left_str = "";
				}
			?>
			<div class = "thread-title-header" style = 'text-align: center;'><?php echo $comp_info["comp_title"]; ?></div>
			<div class = "sub-info-thread">
				Started By <?php 
				echo get_comp_starter_by_type($comp_id, $type); 
				$judges_by_name = array();
				if($judges!="norm"){
					
						
						foreach($judges as $judgeid){
							if(substr($judgeid, 0,4)!="out:"){
								$judges_by_name[] = get_user_field($judgeid,"user_username");
							}else{
								$judges_by_name[] = get_special_judge_disname($judgeid);
							}
						}
				}
				$judge_dis = ($judges=="norm")? "Anyone not participating":implode(",",$judges_by_name);
				echo "<br>Judges: ".$judge_dis;
				echo "<br><span id = 'time_info'>".$time_left_str."</span><br>";
				?>
				NOTE: All teams are colour coded. Their content is displayed in their colour.<br>
				<?php
				if(comp_started($comp_id)){
					if($judges!="norm"&&in_array($user_id, $judges)){
						echo "As a judge, you must read through the different arguments and comments, and simply vote up or down to<br> which comments you are or aren't persuaded by. It is important you vote as many comments as possible.";
					}else if(!user_in_comp($user_id, $comp_id, $type)){
						echo "As a reader, you can read through the different arguments and comments, and simply vote up or down to<br> which comments you are or aren't persuaded by. This will help towards your reputation!";
					}else{
						echo "As you are involved in this competition, make sure to submit your main argument in your section, and argue against arguments in other sections.";
					}
					if(empty($out_judge_key)){
						if((user_rank($user_id, "3")==true)&&(user_in_comp($user_id, $comp_id, $type))){
							echo "<br>NOTE: As a leader, only delete comments if they are abusive or spam.";
						}
					}
				}

				echo (strlen($comp_info['comp_note'])>0) ? "<br><span style = 'color: #59c3d8;'>NOTE from competition starter: <span style = 'color: #4d95f2;'>".$comp_info['comp_note']."<br></span></span>":"";
				?>

			</div>	
			
			
			<hr size = "1">
			<?php
					$jacceptance = get_judge_acceptance($comp_id);
					if(($judges!="norm")&&(in_array($user_id, $judges))&&($jacceptance[$user_id]!="1")){
						echo "<div id = 'judge-invite-box' style = ''>Do you 
						<a href = 'index.php?page=view_comp&comp=".$_GET['comp']."&res_j_in=1".$user_id."' style = 'color:#66CDAA;'>accept</a>
						  or <a href = 'index.php?page=view_comp&comp=".$_GET['comp']."&res_j_in=0".$user_id."' style = 'color:salmon;'>decline</a>
						  your invitation to judge this competition?
						  </div>";
						  
						if(isset($_GET['res_j_in'])){
							$data = htmlentities($_GET['res_j_in']);
							$res = substr($data, 0,1);
							$jid = substr($data, 1);
							if(judge_respond_invite($comp_id, $jid, $res)){
								setcookie("success", "1Successfully responded to your invitation to judge this competition. ", time()+10);
								header("Location: index.php?page=view_comp&comp=".$_GET['comp']);
							}
						}
					}
				
					if(end_comp($comp_id)||comp_ended($comp_id)){
						$winner_ids = get_comp_winner($comp_id, $type);
						if(count($winner_ids)==1){
							$winner = ($type=="0")?$db->query("SELECT group_name FROM private_groups WHERE group_id=".$db->quote($winner_ids[0]))->fetchColumn():$db->query("SELECT com_name FROM communities WHERE com_id=".$db->quote($winner_ids[0]))->fetchColumn();
							echo "<div id = 'page-disabled'>This competition has ended.
							<br><br>
							Winner: ".$winner."<br>
							<img src = 'pages/trophy.png' style = 'width: 400px'>
						
						
							</div>";
						}else{
							$dt_str=  "";
							foreach($winner_ids as $id){
								$name = ($type=="0")?$db->query("SELECT group_name FROM private_groups WHERE group_id=".$db->quote($id))->fetchColumn():$db->query("SELECT com_name FROM communities WHERE com_id=".$db->quote($id))->fetchColumn();
								$dt_str = $dt_str.$name.",";
							}
							echo "<div id = 'page-disabled'>This competition has ended. The competiton was a draw/tie between the following candidates:
							<br><br>
							".trim_commas($dt_str)."<br>
						
							</div>";
						}
					}
					if((comp_started($comp_id)==false)&&(!comp_ended($comp_id))){
						echo "<div id = 'page-disabled'>This competition will not start untill all candidates have responded to their invitation to participate.</div>";
					}
			?>


				<script>
				$(function(){
					var rec_enabled = true;
					var fileName = "<?php echo $_SESSION['user_id'].',1,'.substr(encrypt(time()),0,8); ?>.wav";
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
							$(this).html("<span style = 'margin-left:-20px;margin-top:-12px;position: absolute;font-size: 300%;color:dimgrey;'>&#9724;</span>");
							$("#recording-status").html(" Recording...");
							recAudio(mediaTypes,accRecord,mediaError);
						}else{
							$(this).css("background-image", "url('<?php echo $spec_judge_email_link; ?>/ext/images/mic.png')");
							$(this).html("");
							mediaRecorder.stop();
							$("#recording-status").html("");
							rec_enabled = false;
        					$("#save-audio, #try-again-audio").fadeIn();
     
						}
						clicks ++;
					});
					$("#try-again-audio").click(function(){
						rec_enabled = true;
						$("#save-audio, #try-again-audio").fadeOut();
					});
					$("#save-audio").click(function(){
						$("#recording-status").html("Processing...");
						var request = new XMLHttpRequest();
			            request.onreadystatechange = function () {
			                if (request.readyState == 4 && request.status == 200) {
			                    $("#add-arg-comp-submit").show();
			                    $("#recording-status").html("");
			                }
			            };
			            $("#save-audio, #try-again-audio").fadeOut();
			            request.open('POST', "<?php echo $ajax_script_loc; ?>");
			            request.send(formData);
					});


					$("#choose-arg-type").change(function(){
						$(".ans-type-container, #add-arg-comp-submit").hide();
						$("#"+$("#choose-arg-type").val()+"-ans-container").show();
						if($("#choose-arg-type").val()=="txt"){
							$("#add-arg-comp-submit").show();
						}	
						$("#space-breaks").show();
					});
				});	
				</script>
				<form method = "POST" class = "add-arg-comp-form" id = "add-arg-comp-form">
					How would you like to argue?<br><br>
					<select id = "choose-arg-type" class = "reply-status-select">
						<option value = "na">---</option>
						<option value = "txt">Text</option>
						<option value = "rec">Speech</option>
					</select><br><br>

					<div id = "txt-ans-container" class = "ans-type-container" style = "display:none;">	
						Text argument:<br>
						<textarea name = "add_arg_text" id = "add-arg-comp-tarea" placeholder = "My argument..."></textarea>
					</div>

					<div id = "rec-ans-container" class = "ans-type-container" style = "display:none;">	
						Record speech for argument:
						<br><br>
						<div class = "rec-audio" id = "rec-audio" style = "border:1px solid grey;margin-left: 155px;"></div><br>
						<br><div id = "recording-status"></div><br>
						<div id = "save-audio" style = "border:1px solid grey;margin-left:5px;float: left;margin-top:-28px;">Use</div><div id = "try-again-audio" style = "border:1px solid grey;margin-top:-32px;float: left;">Re-do</div><br><br>
						<span style = 'font-size:70%'>Warning: the record feature may not<br> work properly in certain browsers. If so, use text instead.</span>
					</div>

					<input type = "submit" value = "Post" id = "add-arg-comp-submit" style = "display: none">
				</form>	
				<form method = "POST" class = "add-arg-comp-form" id = "add-com-comp-form" style = "height: 200px;">
					<input type = "hidden" name = "com_cand_id" value = "" id = "com_cand_id">
					<input type = "hidden" name = "com_arg_id" value = "" id = "com_arg_id">
					<textarea name = "com_text" id = "add-arg-comp-tarea" placeholder = "Comment..." style ="height:80%;"></textarea>
					<input type = "submit" value = "Submit" id = "add-arg-comp-submit">
				</form>	




			<?php
				$winner_id = get_comp_winner($comp_id, $type);
				
				$cand_ids = array();
				$all_cands = get_comp_acceptance_info($comp_id, $type);
				foreach($all_cands as $key=>$value){
					if($value==1){
						$cand_ids[]=$key;
					}
				}
				$cand_ids[] = $comp_info["starter_id"];
				$colors = array("blue"=>"#80b0fb", "green"=>"#8ed48e", "red"=>"salmon", "orange"=>"#ffc04d");
				$sec_uid = array("a", "b", "c", "d");
				$linked_colors = array();
				$jnamecolors = array_keys($colors);
				$count = 0;
				foreach($cand_ids as $cand_id){
					$linked_colors[$cand_id] = $jnamecolors[$count];
					$count++;
				}
				
				function cand_color($cand_id){
					global $linked_colors;
					global $colors;
					return $colors[$linked_colors[$cand_id]];
				}
				$count = 0;
				$rcounts = array();
				foreach($cand_ids as $cand_id){
					array_push($rcounts,$sec_uid[$count]."0");
					$name = ($type=="0")? $db->query("SELECT group_name FROM private_groups WHERE group_id = ".$db->quote($cand_id))->fetchColumn():$db->query("SELECT com_name FROM communities WHERE com_id = ".$db->quote($cand_id))->fetchColumn();
					if($name!=""){
						$users_host_id = ($type=="0")? get_user_group($user_id, "group_id"):get_user_community($user_id, "com_id");
						$your_host_str = ($users_host_id==$cand_id)? "(your section)": "";
						
					
						/*
						rel_to_sec possible values (relation to section)
			
						User viewing
						1: my host - inv
						2: other host - inv
						3: any host - not inv
						4: judge - not inv
						5: not judge - not inv
						*/
						if(comp_started($comp_id)==false){
							$rel_to_sec = 3;
						}else{
							if($users_host_id==$cand_id){
								$rel_to_sec = 1;
							}else if(in_array($users_host_id, $cand_ids)){
								$rel_to_sec = 2;
							}else{
								$rel_to_sec = 3;
							}
						}
		
						$options = array(
							1=>array(
								"sub_arg"=>"<div id = 'sa-show-form' style = 'cursor: pointer;'>Submit My Argument</div>"
							),
							2=>array(
							
							),
							3=>array(
							
							)
						);
					
						$options_str = "";
						foreach($options[$rel_to_sec] as $key=>$value){
							$options_str.=$value." ";		
						}
						switch($rel_to_sec){
							case 1:
						
								if(isset($_POST['add_arg_text'])){
									$msg = "";

									$text = htmlentities($_POST['add_arg_text']);
									if(strlen($text)<100&&!isset($_COOKIE['temp_audio_ret_aid'])){
										$msg = "0Your argument is too short.";
									}else if (strlen($text)>5000&&!isset($_COOKIE['temp_audio_ret_aid'])){
										$msg = "0Your argument is too long.";
									}else{
										$msg = "1Successfully added your argument.";
										$insert = $db->prepare("INSERT INTO comp_arguments VALUES('',:comp_id, :cand_id, :user_id, :arg_text, UNIX_TIMESTAMP(), 0, 0)");
										
										$insert->execute(array(
											"comp_id"=>$comp_id,
											"cand_id"=>$users_host_id, 
											"user_id"=>$user_id,
											"arg_text"=>$text,
										));

										$aid = $db->lastInsertId();
										
										if(isset($_COOKIE['temp_audio_ret_aid'])){
											echo $aid . "fffffffff";
											$f_code = $_COOKIE['temp_audio_ret_aid'];
											$db->query("UPDATE audio SET owner_id = ".$db->quote($aid)." WHERE audio_flocation LIKE '%$f_code'");
											setcookie("temp_audio_ret_aid", "", time()-1000000000);
										}

										if($judges!="norm"){
											foreach($judges as $jid){
												add_note($jid, "There is new activity in a competition you are judging. Click here to get judging!", "index.php?page=view_comp&comp=".$_GET['comp']);
											}
										}
									}
									setcookie("success", $msg, time()+10);									
									header("Location: index.php?page=view_comp&comp=".$_GET['comp']);
								}	
							
								break;
						
							case 2:
						
								break;
						
							case 3:
						
								break;
						}
					

						if(get_question_type($comp_info['comp_title'], 1)=="state"){
							$side = (get_cand_side($comp_id, $cand_id)=="1")? "FOR" : "AGAINST";
							$argue_side_txt = "Must argue <u>".$side."</u> notion";
						}else{
							$side = (get_cand_side($comp_id, $cand_id)=="1")? "YES" : "NO";
							$argue_side_txt = "Must argue <u>".$side."</u> to question";
						}

						echo "
						<div class = 'comp-view-cand-box'>
							<div id = 'cvcb-sec1'>
								<span style = 'color:".cand_color($cand_id).";'><b>".add_profile_link($name, 1, $style="color:".cand_color($cand_id))."'s</b></span>
								Section ".$your_host_str." <span id = 'comp-side-dis'>".$argue_side_txt."</span>
							</div>
							<div id = 'cvcb-sec2'>
								".$options_str."
							</div>
							<div id = 'cvcb-sec3'>
								";
					
						$get_m_args = $db->prepare("SELECT * FROM comp_arguments WHERE comp_id = :comp_id AND cand_id = :cand_id");
						$get_m_args->execute(array("comp_id"=>$comp_id, "cand_id"=>$cand_id));
						while($row = $get_m_args->fetch(PDO::FETCH_ASSOC)){
							$rcount = $rcounts[$count];
							$distext = $row['arg_text'];
							$check_audio = $db->query("SELECT audio_flocation FROM audio WHERE owner_id = ".$db->quote($row['arg_id'])." AND owner_table = 'comp_arguments'")->fetchColumn();
							if($check_audio){
								$bcolor = "";
								if(cand_color($cand_id)=="salmon"){
									$bcolor = "border: 1px solid grey";
								}
								$check_audio=$spec_judge_email_link."/audio/".$check_audio;
								$play_button= "<div class = 'rec-audio play-audio' id = 'play-audio-".$rcount."' style = 'background-image: none;padding: 10px;".$bcolor."'>
											 <span id = 'play-button-cont".$rcount."' style = 'margin-left:1px;margin-top:-7px;font-size: 300%;color:dimgrey;'>&#9658;</span>
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
							$distext = $play_button.$distext;
							echo "<div id = 'arg-text-body' style = 'margin-top: 10px;background-color:".cand_color($cand_id).";'>".$distext."<br>
								<span style = 'color:#ffffff;'>By <a style = 'color:grey;' href = 'index.php?page=profile&user=".$row['user_id']."'>".get_user_field($row['user_id'], "user_username")."</a></span>";
							if($rel_to_sec!=3){	
								echo "<span style = 'float:right;color:grey;cursor:pointer;' arg_id = '".$row['arg_id']."' class = 'add-comment-arg-opt' cand_id = '".$cand_id."'>Add Comment</span>";
							}else if((user_already_voted_comp_arg("comp_arguments", $user_id, $row['arg_id'])==false)&&(($judges=="norm")||($judges!="norm"&&in_array($user_id, $judges)))){
								echo "<span style = 'float:right;color:grey;cursor:pointer;' arg_id = '1b-".$row['arg_id']."' class = 'vote-opt bcid".$row['arg_id']." bsuccess-msg".$row['arg_id']."'> Vote Up</span>
							
								<span style = 'float:right;color:grey;cursor:pointer;' arg_id = '0b-".$row['arg_id']."' class = 'vote-opt bcid".$row['arg_id']."'>Vote Down &middot; </span>";
							}
							if(empty($out_judge_key)){
								if((user_rank($user_id, "3")==true)&&(get_user_community($user_id, "com_id")==get_user_community($row['user_id'], "com_id"))){
								
									echo "<a style = 'float:right;color:black;text-decoration:none;cursor:pointer;' href = 'index.php?page=view_comp&comp=".$_GET['comp']."&delc=1".$row['arg_id']."&uid=".$row['user_id']."' >&ensp;&ensp;Delete&ensp;&ensp;&ensp;&ensp;</a>";

								}
							}		
							echo "</div>";
						
							$get_replies = $db->prepare("SELECT * FROM comp_arg_replies WHERE arg_id = :arg_id AND cand_id = :cand_id");
							$get_replies->execute(array("arg_id"=>$row['arg_id'], "cand_id"=>$cand_id));
						
							while($row_= $get_replies->fetch(PDO::FETCH_ASSOC)){
								echo "<div id = 'arg-text-body' style = 'margin-left: 28%;width:70%;margin-top: 2px;background-color:".cand_color($row_['user_cand_id']).";'>".$row_['reply_text']."<br>
								<span style = 'color:#ffffff;'>By <a style = 'color:grey;' href = 'index.php?page=profile&user=".$row_['user_id']."'>".get_user_field($row_['user_id'], "user_username")."</a>";
							
								if(($rel_to_sec==3)&&(user_already_voted_comp_arg("comp_arg_replies", $user_id, $row_['reply_id'])==false)&&(($judges=="norm")||($judges!="norm"&&in_array($user_id, $judges)))){
									echo "<span style = 'float:right;color:grey;cursor:pointer;' arg_id = '1m-".$row_['reply_id']."' class = 'vote-opt mcid".$row_['reply_id']." msuccess-msg".$row_['reply_id']."'> Vote Up</span>
									<span style = 'float:right;color:grey;cursor:pointer;' arg_id = '0m-".$row_['reply_id']."' class = 'vote-opt mcid".$row_['reply_id']."'>Vote Down &middot; </span>";
								}
								if(empty($out_judge_key)){
									if((user_rank($user_id, "3")==true)&&(get_user_community($user_id, "com_id")==get_user_community($row_['user_id'], "com_id"))){
									
										echo "<a style = 'float:right;color:black;text-decoration:none;cursor:pointer;' href = 'index.php?page=view_comp&comp=".$_GET['comp']."&delc=0".$row_['reply_id']."&uid=".$row_['user_id']."' >&ensp;&ensp;Delete&ensp;&ensp;&ensp;&ensp; </a>";

									}
								}
								echo "</div>";
							}
							$newval = substr($rcounts[$count], 1);
							$newval++;

							$rcounts[$count] = substr($rcounts[$count], 0,1).$newval;	
						}	
							
						echo"
							</div>
							<div id = 'cvcb-sec4'>
								
							</div>	
						
						</div>";
					}
					$count++;
				}
				
				if(isset($_GET['delc'], $_GET['uid'])){
					$del_c = htmlentities($_GET['delc']);
					$ctype = substr($del_c, 0,1);
					$uid = htmlentities($_GET['uid']);
					$cid = substr($del_c, 1);
					$table = ($ctype == "1") ? "comp_arguments" : "comp_arg_replies";
					$col = array("comp_arguments"=>"arg_id", "comp_arg_replies"=>"reply_id")[$table];
					$check = $db->query("SELECT user_id FROM `".$table."` WHERE `".$col."` = ".$db->quote($cid)." AND user_id = ".$db->quote($uid))->fetchColumn();
					$error = false;
					if(!empty($check)){
						if(empty($out_judge_key)){
							if((user_rank($user_id, "3")==true)&&(get_user_community($user_id, "com_id")==get_user_community($uid, "com_id"))){
								$db->query("DELETE FROM `".$table."` WHERE `".$col."` = ".$db->quote($cid));
							}else{
								$error = true;
							}
						}else{
							$error = true;
						}
					}else{
						$error = true;
					}
					if($error == true){
						$msg = "0Unknown Error";
					}else{
						$msg = "1Successfully deleted.";
					}
					setcookie("success", $msg, time()+10);	
					header("Location: index.php?page=view_comp&comp=".$_GET['comp']);
				}

				if($rel_to_sec!=3){	
					if(isset($_POST['com_cand_id'],$_POST['com_arg_id'],$_POST['com_text'])&&(!comp_ended($comp_id))){
						$arg_id = htmlentities($_POST['com_arg_id']);
						echo $cand_id = htmlentities($_POST['com_cand_id']);
						 
						$text = htmlentities($_POST['com_text']);
						$user_cand_id = $users_host_id;
				
						if(strlen($text)<2){
								$msg = "0Your comment is too short.";
							}else if (strlen($text)>5000){
								$msg = "0Your comment is too long.";
							}else{
								$msg = "1Successfully posted.";
								$insert = $db->prepare("INSERT INTO comp_arg_replies VALUES('',:arg_id, :user_id, :cand_id, :reply_text, UNIX_TIMESTAMP(), 0, :uci, 0,:comp_id)");
				
								$insert->execute(array(
									"arg_id"=>$arg_id,
									"user_id"=>$user_id, 
									"cand_id"=>$cand_id,
									"reply_text"=>$text,
									"uci"=>$user_cand_id,
									"comp_id"=>$comp_id
								));
							}
						setcookie("success", $msg, time()+10);									
						header("Location: index.php?page=view_comp&comp=".$_GET['comp']);
					
					}
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