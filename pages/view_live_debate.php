<?php
if($check_valid!="true"){
	header("Location: index.php?page=home");
	exit();
}

if(loggedin()||isset($_GET['judge_key'])){
	$judge_key = (isset($_GET['judge_key'])) ? htmlentities($_GET['judge_key']) : "";
	//judge_key e.g = test@test.com44io6ybv
	//db judge val e.g = -out:test@test.com44io6ybv
	$involvement = 0; //0 not involved, 1 involved, 2 creator, -1 judge
	if(isset($_GET['did'])){
		$did = htmlentities($_GET['did']);
		$judge = get_ldeb_val($did, "judge");
		if(substr($judge, 0,4)=="out:"){

			$judge = array("out", substr($judge, 4));
			if($judge[1]==$judge_key){
				$involvement = -1;
			}else if($judge_key!=""){
				header("Location: index.php?page=home");
			}

		}else{
			$judge = array("in", $judge);
			if($judge[1]==get_user_field($_SESSION['user_id'], "user_username")){
				$involvement =-1;
			}
		}

		$involved_users = get_ldeb_involved($did);
		$sid = get_ldeb_val($did, "starter_id");
		if($involvement == 0){
			if(in_array($_SESSION['user_id'], array_keys($involved_users))){
				if(get_group_leader_id($sid)==$user_id){
					$involvement = 2; //starter user
				}else{
					$involvement = 1; // involved norm
				}
			}else if($involvement!=-1){
				$involvement = 0; //viewer
			}
		}

		

		$rounds = get_ldeb_val($did, "rounds");
		if(empty($rounds)){
			header("Location: index.php?page=home");
		}
		$dur_min = get_ldeb_val($did, "duration");
		$interrupt_start = 5;
		$sname = $db->query("SELECT group_name FROM private_groups WHERE group_id = ".$db->quote($sid))->fetchColumn(); 
		$start_time = get_ldeb_val($did, "start_time");
		$oid = get_ldeb_val($did, "opp_id");
		$deb_note = get_ldeb_val($did, "note");
		$deb_note = (!empty($deb_note))? "<span class  = 'ldeb-gen-detail-row'><b>Message To All: </b>".$deb_note."</span><br>" : "";
		$oname = $db->query("SELECT group_name FROM private_groups WHERE group_id = ".$db->quote($oid))->fetchColumn(); 
		$phase = 0; //0 = waiting to start, 1= start, 2=end
		$question = get_ldeb_val($did, "question");
		$user_id = ($involvement == -1 && $judge[0]=="out")? $judge[1] : $_SESSION['user_id'];
		
		if($involvement > 0){
			$gid = get_user_group($user_id, "group_id");
			$ooname = ($sid==$gid)? $oname: $sname;
			$gname = get_user_group($user_id, "group_name");
		}else{
			$gid = 0;
			$ooname = "";
			$gname = "";
		}

		$rnd_timeline_width = 600/$rounds;
		$scolor = "#D09458"; //light
		$ocolor = "#9E643F"; //dark
		$for = get_ldeb_val($did, "arguing_for");
		$color_dis = ($gid==$sid)? "light" : "dark";
		$oppcolor_dis = ($gid==$sid)? "dark" : "light";
		$opp_pod_img = "url('../ext/images/podium-".$oppcolor_dis.".png')";
		$own_pod_color  = ($oid==$gid)? $ocolor : $scolor;
		
		$dur_sec = $dur_min*60;

		$timeline_cues = calc_ldeb_timeline($dur_min, $rounds);

		$json_timeline_cues = json_encode($timeline_cues);
		
		if($involvement==-1){
			$gen_detail_grp_color = "<span class  = 'ldeb-gen-detail-row'><b>Your involvement:</b> The judge</span><br>";
		}else{
			$gen_detail_grp_color = "<span class  = 'ldeb-gen-detail-row'><b>Your Group's Colour: </b>".$color_dis." brown</span><br>";
		}
		

		?>

			<script>
				$(function(){
					var json_timeline_cues = <?php echo $json_timeline_cues; ?>;
				
					// time control

					$("#dis-deb-phase").html("Waiting To Start");

					<?php
						
		
					//involved user, streaming setup
					?>
					//js ldeb data
					var phase = <?php echo $phase; ?>;
					var duration = <?php echo $dur_sec; ?>;
					var start_time = <?php echo $start_time; ?>;
					//socket & peerjs init
					var socket = io.connect("https://buzzzap.com:9001");
					var getUserMedia = navigator.getUserMedia || navigator.webkitGetUserMedia || navigator.mozGetUserMedia;
					var upid = "<?php echo ($involvement>0)?$involved_users[$user_id].','.get_user_field($user_id, 'user_username').','.$did : '0,'.$judge[1].','.$did; ?>";
					var peer = new Peer({host: 'www.buzzzap.com', port:9000, path:''});
					var peer_data;
					var timer_interval;
					peer.on('open', function(id) {

						var ownStream;
						var own_id = id;
						var count_down= 15;

						function timer(offset, stage){

							var new_pos = offset *(600/duration);
							var secs_in = offset;
							var secs_left = <?php echo $dur_sec; ?> - secs_in;
							var milisecs_left = secs_left*1000;
							var milisecs_in = secs_in*1000;
							var fstage = stage[0];

							if(fstage == 0){
								$("#note-white").html("The debate has started!");
							}
						
							$("#ldeb-timeline-mrk").css("margin-left", new_pos+"px");
						
							timer_interval = setInterval(function(){
								
								secs_in++;
								var round_secs_in = Math.floor(secs_in).toString();
				                var nxt_cue = json_timeline_cues[fstage+1][0];
				               	var nxt_turn = json_timeline_cues[fstage+1][1];
				               	<?php if ($involvement>0){ ?>
				               		var turn_name = (nxt_turn==3)? "break" : (nxt_turn==1)?"<?php echo ($involved_users[$user_id] == $sid)? 'your group': $sname; ?>" : "<?php echo ($involved_users[$user_id] == $oid)? 'Your group': $oname; ?>";
				              	<?php } else { ?>
				              			var turn_name = (nxt_turn==3)? "break" : (nxt_turn==1)?"<?php echo $sname; ?>" : "<?php echo $oname; ?>";
				              	<?php } ?>	

				              	if(json_timeline_cues[fstage][1]==1){
				                	$("#judge-vote-container").fadeOut();
				                	if("<?php echo ($gid == $oid)? 'true' : 'false'; ?>" == 'true'){
				                		$("#interrupt-opt").fadeIn();
				                	}else{
				                		$("#interrupt-opt").fadeOut();
				                	}
				                }else if(json_timeline_cues[fstage][1]==2){
				                	if("<?php echo ($gid == $sid)? 'true' : 'false'; ?>" == 'true'){
				                		$("#interrupt-opt").fadeIn();
				                	}else{
				                		$("#interrupt-opt").fadeOut();
				                	}
				                }


				                if (round_secs_in > (nxt_cue-15)){
				                	var count_text;
				                	var result_text;
				                	if(turn_name == "break"){
				                		count_text = " seconds until break time";
				                		result_text = "BREAK TIME";

				                	}else{
				                		count_text = " seconds until "+turn_name+"'s turn to speak...";
				                		result_text = turn_name + "'s turn to speak...";
				                	}	

				                	count_down = nxt_cue - round_secs_in;		

				                	if(count_down==0){
				                		if(turn_name=="break"){
				                			$("#judge-vote-container").fadeIn();
				                		}
				                		$("#note-white").html(result_text);
				                	}else{
				                		$("#note-white").html(count_down + count_text);
				                	}

				                	
				                }

				                if(round_secs_in == nxt_cue){

				                	fstage++;
				                    socket.emit("check_deb_stage", {did:"<?php echo $did; ?>", man:true});
				                  
				                }

								mins_in = Math.floor(secs_in/60).toString();
								secs =Math.floor(secs_in%60).toString();

								if(mins_in.length==1){
									mins_in = "0"+mins_in;
								}
								if(secs.length==1){
									secs = "0"+secs;
								}
								$("#ldeb-time-left").html(mins_in + ":" + secs);
							}, 1000);
						
								$("#ldeb-timeline-mrk").animate({marginLeft:"590px"}, milisecs_left, "linear");
	
						}
						var audio_id_c = 0;
						function add_audio_stream(stream, gnum){
							var audio = $("<audio class = 'a-g"+gnum+"' id = 'a-"+Math.random(audio_id_c)+gnum+"' autoplay />").appendTo('body');
					   		audio[0].src = window.URL.createObjectURL(stream);
					    	audio.onloadedmetadata = function(e){
					        	console.log('now playing the audio');
					        	audio.play();
					   		}
					   		audio_id_c++;
						}

						function call_and_recieve(dest_pid, dest_gid){
							getUserMedia({video: false, audio: true}, function(stream) {
								var call = peer.call(dest_pid, stream);
								call.on('stream', function(stream) {

									add_audio_stream(stream,dest_gid);
									
								});

							}, function(err) {
							  console.log('Failed to get local stream' ,err);
							});
						}

						function mute_stream(gnum){
							if(gnum==0){
								$("#jpod-speaking-icon1, #jpod-speaking-icon2").fadeOut();
								$("audio").each(function(){
									var id = $(this).attr("id");
									document.getElementById(id).muted = true;
								});
							}else{
								$(".a-g"+gnum).each(function(){
									var id = $(this).attr("id");
									document.getElementById(id).muted = true;
								});
								var gnum_ = (gnum==1)? 2: 1;
								$("#jpod-speaking-icon"+gnum_).fadeIn();
								$("#jpod-speaking-icon"+gnum).fadeOut();
								$(".a-g"+gnum_).each(function(){
									var id = $(this).attr("id");
									document.getElementById(id).muted = false;
								});
							}
						}

						//answer and recieve
						peer.on('connection', function(conn){
							
							peer.on('call', function(call) {
								getUserMedia({video: false, audio: true}, function(stream) {
							   		call.answer(stream);
							    	call.on('stream', function(stream) {
							    		
										add_audio_stream(stream, -1);
							     	
							    	});
							  	}, function(err) {
							    	console.log('Failed to get local stream' ,err);
							  	});
							});
						});
						
						function render_online_peers(pdata){
							$("#online-sec-<?php echo $sid; ?>,#online-sec-<?php echo $oid; ?>, #online-sec-3").html("");
							for(var i in pdata){
								var p_gid = pdata[i][0];
								var pname = pdata[i][1];
								
								if(p_gid=="<?php echo $sid; ?>"){
									secToAppend = $("#online-sec-<?php echo $sid; ?>");
								}else if(p_gid=="<?php echo $oid; ?>"){
									secToAppend = $("#online-sec-<?php echo $oid; ?>");
								}else{
									secToAppend = $("#online-sec-3");
									pname = pname + " (the judge)";
								}
								secToAppend.append("<span id = 'ponline-"+pname+"'>"+pname+"</span><br>");
								
							}
						}


						
						//init peer
						socket.emit('add_peer',  
							{did:"<?php echo $did; ?>",
							pid:own_id, 
							involvement: "<?php echo $involvement; ?>",
							uident:upid, 
							ugid: "<?php echo $gid; ?>",
							deb_data: { phase:phase,
										question: "<?php echo $question; ?>",
										start_time: start_time,
										timeline:json_timeline_cues,
										deb_dur: duration,
										g1_points:0,
										g2_points:0,
										g1_interrupts: {i_left:"<?php echo $interrupt_start; ?>", t_end:0},
										g2_interrupts: {i_left:"<?php echo $interrupt_start; ?>", t_end:0},
							 	}
							}
						);

						//get scores
						if (<?php echo $involvement; ?> == -1){
							socket.emit('get-scores', "<?php echo $did; ?>");
						}
						

						//update users
						socket.on('new_peer_data', function(data){
							peer_data = data.rel_peers; //pid[data], pid[data], ...
							pid_call = data.pid_call;
							var gcount1 = 0;
							var gcount2 = 0;
							var jcount = 0;
							if(pid_call!=false){ // call peers
								for(var i in peer_data){
									
									peer.connect(i);
									if(peer_data[i][0]=="<?php echo $sid; ?>"){
										g_num = 1;
										gcount1++;
									}else if(peer_data[i][0]=="<?php echo $oid; ?>"){
										g_num = 2;
										gcount2++;
									}else{
										g_num = 3;
										jcount++;

									}
									if(i!=own_id){
										call_and_recieve(i, g_num);
									}
									
								}
							}
							
							if(gcount1>0&&gcount2>0&&jcount>0){
								$("#start-ldeb-pretext").fadeOut();
								$("#start-ldeb-opt").fadeIn();
							}
							
							render_online_peers(peer_data);
						});

						
						//start timer/debate
						<?php if ($involvement == 2){ ?>
							$("#start-ldeb-opt").click(function(){
								$(this).fadeOut();
								socket.emit("start_deb", {did:"<?php echo $did; ?>"});
				
							});
						<?php } ?>

						//interruption

						function init_interruption(data){
							<?php if ($sid ==$gid) { ?>
								var g_num = 1;
							<?php } else if($oid==$gid){ ?>
								var g_num = 2;
							<?php }else { ?>
								var g_num = 3;
							<?php } ?>	
							if(g_num != data.target && g_num != 3){
								$("#i-left-dis").html(data.ints_left);
							}
							
							var tname = (data.target == 1)? "<?php echo $sname; ?>" : "<?php echo $oname; ?>";
							var iname = (data.target == 2)? "<?php echo $sname; ?>" : "<?php echo $oname; ?>";
							var c = Math.floor(data.time_left);
							
							$("#note-white").hide();
							$("#note-red").show();
							var cdown_int = setInterval(function(){
								var text = (g_num == data.target)? "Your group is being interrupted for " + c + " seconds..." : "<span id = 'note-red' style = 'color:red;'>Your group has " + c + " seconds left of interruption...";
								if(g_num==3){
									text = iname +  " is interrupting " + tname + " for " + c + " seconds..."; 
								}
								$("#note-red").html(text);
								c--;
							}, 1000);

							mute_stream(data.target);
							
							setTimeout(function(){
								$("#note-white").show();
								$("#note-red").hide();
								var gnum = (data.target==1)? 2: 1;
								mute_stream(gnum);
								clearInterval(cdown_int);
								socket.emit('end-interrupt', {interrupter: gnum, did:"<?php echo $did; ?>"});
							}, parseInt(data.time_left * 1000));
						}

						$("#interrupt-opt").click(function(){
							<?php if ($sid ==$gid) { ?>
								var g_num = 2;
							<?php } else { ?>
								var g_num = 1;
							<?php } ?>	

							var data = {target_g:g_num, did:"<?php echo $did; ?>"};
							socket.emit("interrupt", data);
						});

						socket.on('interrupting', function(data){
							if(data.success==true){
								init_interruption(data);
							}
						});

						//recieve stages from server
						socket.on('checked_deb_stage', function(data){
							if(data.phase==1){
								var stage = data.stage;
								if(data.man==false){
									timer(data.time_in, stage);
								}
								$("#dis-deb-phase").html("Started");
								$("#dis-round").html(stage[2]);
								var gturn_name;
								var stream_to_mute;
								if(stage[1]==1){
									stream_to_mute = 2;
									gturn_name = "<?php echo $sname; ?>";
								}else if(stage[1]==2){
									stream_to_mute = 1;
									gturn_name = "<?php echo $oname; ?>";
								}else{
									stream_to_mute = 0;
									gturn_name = " No one (break time)";
								}

								
								mute_stream(stream_to_mute);

								<?php if ($sid ==$gid) { ?>
									$("#i-left-dis").html(data.interrupt_l1);
								<?php } else { ?>
									$("#i-left-dis").html(data.interrupt_l2);
								<?php } ?>	

								$("#dis-turn").html(gturn_name);
							}else if(data.phase == 2){
								clearInterval(timer_interval);
								$('#ldeb-end-container').fadeOut(function(){
									<?php if($involvement == -1){ ?>
										$("#ldeb-end-header").html("Please make your final vote: ");
										$("#judge-vote-container").fadeIn();
									<?php }else{ ?>
										$("#ldeb-end-header").html("The debate has ended. Please wait while the judge makes his/her final decision... ");
									<?php  } ?>
									$("#ldeb-end-header").fadeIn();
									
								});
							}
						});

						// private message
						<?php
						 if($involvement>0){
						?>

							var open_count = 0;
							var cur_header_html = "Private <?php echo $gname; ?> Chat";
							var new_msg_flash;

							$("#ldeb-chat-inner").css("background-color", "<?php echo $own_pod_color; ?>");
							$("#ldeb-chat-header").click(function(){
								if(open_count%2==0){
									$("#ldeb-chat-header").html("<span style = 'float:left;'>&darr;</span>"+cur_header_html + "<span style = 'color:salmon;float:right;'>X</span>");
									$("#ldeb-chat-container").animate({height:"400px"}, 500);
								}else{
									$("#ldeb-chat-header").html("<span style = 'float:left;'>&uarr;</span>"+cur_header_html);
									$("#ldeb-chat-container").animate({height:"20px"}, 500);
								}	
								open_count++;
							});

						
							$("#pm-msg-submit").click(function(){
								var msg_txt = $("#pm-msg-txt").val();
								var name = "<?php echo get_user_field($user_id, 'user_username'); ?>";
								if(msg_txt.length > 1){
									var data = {text:msg_txt, name:name, gid: "<?php echo $gid; ?>"};
									socket.emit('send-pm', data);
									$("#pm-msg-txt").val("");
								}
							});

							$("#ldeb-chat-container").click(function(){
								clearInterval(new_msg_flash);
								$("#ldeb-chat-header").css('color', '#fff');
							});

							function dis_msg(message){
								if(message.name!="<?php echo get_user_field($user_id, 'user_username'); ?>"){
									new_msg_flash = setInterval(function(){
										$("#ldeb-chat-header").css('color', 'salmon');
										setTimeout(function(){
											$("#ldeb-chat-header").css('color', '#fff');
										}, 500);
									}, 1000);
								}
								$("#ldeb-chat-inner").append("<hr size = '1'><b>"+message.name+"</b>: &ensp;"+message.text);
							}
							
							socket.on('new-pm', function(message){
								dis_msg(message);
							});

						<?php
						}
						?>
						//judge voting
						$("#judge-vote-sel").change(function(){
							var grp = $(this).val();
							$("#judge-vote-sel").prop('selectedIndex',0);
							socket.emit("judge-vote", {group:grp, amount:1,did:"<?php echo $did; ?>"});
							$("#judge-vote-container").fadeOut();
						});

						socket.on('group_scores', function(data){
							var score1 = data.g1;
							var score2 = data.g2;
							$("#jpod-score1").html(score1 + " Point(s)");
							$("#jpod-score2").html(score2 + " Point(s)");
							console.log("emitted corrtl2y");
							if(data.phase==3){
								var lost;
								var lost_span_id;
								var won_span_id;
								if(data.won!=0){
									lost = (data.won==1)? 2 : 1;
									lost_span_id = "#title-g"+lost.toString();
									won_span_id = "#title-g"+data.won.toString();
								}else{
									lost = 0;
								}
								$("#ldeb-end-header").html("And the winner is...");
								$('#main-title').animate({marginTop: "30%"}, 4000);
								setTimeout(function(){
									$('#title-vs, '+lost_span_id).fadeOut();
									$(won_span_id).animate({fontSize: "300%"}, 2000);
								}, 4000);
							}
						});
					});
				});
			</script>
		<div class = 'page-path'>Debating > <a style = 'color: #40e0d0;' href = 'index.php?page=live_debating'>Live Debating</a> > <?php echo $question; ?></div>
		<div id = 'ldeb-end-header' class = "loggedin-headers" style = 'color: grey;display: none;'>And the winner is...</div> 
		<div class = "loggedin-headers" style = 'color: grey;' id = 'main-title'>
			<?php echo "<span id = 'title-g1' style = 'color: ".$scolor.";'>".$sname; ?></span>
			<span id = "title-vs">Vs</span>
			<?php echo "<span id = 'title-g2' style = 'color: ".$ocolor.";'>".$oname; ?></span>
		</div>
		<?php if ($involvement == -1){ ?>
			<div id = "judge-vote-container">
				Who won this round?
				<select id = 'judge-vote-sel'>
					<option value = "0">---</option>
					<option value = "1"><?php echo $sname; ?></option>
					<option value = "2"><?php echo $oname; ?></option>
				</select><br><span style = 'font-size:70%;'>(If draw/tie, leave blank)</span>
			</div>
		<?php } ?>
		<div id = 'ldeb-end-container'>
			<div id = "ldeb-timeline">
				<div id = "ldeb-timeline-mrk"><div id = 'ldeb-time-left'></div></div>
				<?php
					$mleft = 0;
					$submleft = 0;
					for($i = 1;$i<=$rounds;$i++){
						if($i>1){
							$mleft =  $mleft + $rnd_timeline_width;
						}
						echo "<div class = 'ldeb-timeline-rnd-mrk' style = 'margin-left:".$mleft."px;width:".$rnd_timeline_width."px;'>
							<div class = 'ldeb-timeline-rnd-mrk' style = 'position:relative;float:left;background: grey;width:".strval($rnd_timeline_width/3)."px;'></div>
							<div class = 'ldeb-timeline-rnd-mrk' style = 'position:relative;float:left;background: ".$scolor.";width:".strval($rnd_timeline_width/3)."px;'></div>
							<div class = 'ldeb-timeline-rnd-mrk' style = 'position:relative;float:left;background: ".$ocolor.";width:".strval($rnd_timeline_width/3)."px;'></div>
						</div>";
					}
				?>
			</div>
			

			<div id = "ldeb-general-container"  style = "background: <?php echo $own_pod_color; ?>;<?php echo ($involvement==-1)? 'float:none;position: absolute;margin-top:300px;height: 250px;margin-left:calc(50% - 9vw)':''; ?>">
				<div class = "ldeb-general-header">General Details</div>
				<div class = "ldeb-general-inner" style = "font-size: 75%;letter-spacing: -1px;padding: 5px;">
					<?php echo $gen_detail_grp_color; ?>
					<?php echo $deb_note; ?>
					<span class  = "ldeb-gen-detail-row"><b>Debate Phase: </b><span id = 'dis-deb-phase'></span></span><br>
					<span class  = "ldeb-gen-detail-row"><b>Round: </b><span id = 'dis-round'></span></span><br>
					<span class  = "ldeb-gen-detail-row"><b>Turn To Speak: </b><span id = 'dis-turn'></span></span><br>
					<?php if ($involvement>0){ ?>
					<span class  = "ldeb-gen-detail-row"><b>Interruptions left: </b><span id = 'i-left-dis'></span></span><br>
					<?php } ?>
					<span class  = "ldeb-gen-detail-row"><b>test data: </b><span id = 'testh-data'></span></span><br>
					<span class  = "ldeb-gen-detail-row"><b>Arguing <u>FOR</u> Notion: </b><?php echo ($for==$sid)? $sname : $oname; ?></span><br>
					<span class  = "ldeb-gen-detail-row"><b>Arguing <u>AGAINST</u> Notion: </b><?php echo ($for==$oid)? $sname : $oname; ?></span><br>
				</div>
			</div>
			<?php if($involvement>0){ ?>
				<div id = "ldeb-online-container">
					<div class = "ldeb-general-header">User's Online</div>
					<div class = "ldeb-general-inner">
						<div class = "ldeb-online-sec" id = "online-sec-<?php echo $sid; ?>" style = "background-color: <?php echo $scolor; ?>;"></div>
						<div id = "online-sec-3" style = "background-color: grey;width:100%;padding:5px;"></div>
						<div class = "ldeb-online-sec" id = "online-sec-<?php echo $oid; ?>" style = "background-color: <?php echo $ocolor; ?>;"></div>
					</div>
				</div>

			<?php }else{ ?>
				<div id = "ldeb-side-podium-left" class = "ldeb-side-podium">
					<div class = 'judge-podiums-score' id = "jpod-score1">0 Point(s)</div>
					<div class = 'judge-pods-audio-icon' id = "jpod-speaking-icon1"></div>
				</div>
				<div id = "ldeb-side-podium-right" class = "ldeb-side-podium">
					<div class = 'judge-podiums-score' id = "jpod-score2">0 Point(s)</div>
					<div class = 'judge-pods-audio-icon' id = "jpod-speaking-icon2"></div>
				</div>
			<?php } ?>
				
			<div id = "ldeb-central-container">
				<div id = "ldeb-question-header"><?php echo $question; ?></div>
				<?php if($involvement == 2){ ?>
					<div id = 'start-ldeb-area'>
						<div id = 'start-ldeb-pretext'>
							You can start the debate when the judge and atleast one user from each side has joined. Waiting...
						</div>
						<br>
						<div id = 'start-ldeb-opt' class= 'view-thread-opts-link' style = 'float: none;width: 200px;margin: 0 auto;'>
							Start Live Debate
						</div>
					</div>	
				<?php }else if($involvement==-1){ ?>
					<div id = "ldeb-note-container" style = 'color: grey;'>
						<span id = 'note-white'>Waiting To Start...</span>
						<span id = 'note-red' style = 'color: red;'></span>
					</div>	
	
				<?php } ?>
			</div>

			<?php if($involvement>0){ ?>
				<div id = "ldeb-chat-container">
					<div id = "ldeb-chat-header"><span style = 'float:left;'>&uarr;</span>Private <?php echo $gname; ?> Chat</div>
					<div id = "ldeb-chat-inner">

					</div>
					<div id = "ldeb-chat-form">
						<input type = "text" id = "pm-msg-txt" placeholder = "Message..."><input type = "button" id = 'pm-msg-submit' value = "Send">
					</div>
				</div>
				
				<div id = 'ldeb-opp-pod-container'>
					<div class = 'podium-container' style = "background-image: <?php echo $opp_pod_img; ?>">
						<div id = "interrupt-opt" class = "view-thread-opts-link">Interrupt <?php echo $ooname; ?></div>
					</div>
				</div>
			

				<div id = 'ldeb-own-pod-container'>
					<div class = 'own-mics-container'></div>
					<div class = 'pod-own-top-container' style = "background: <?php echo $own_pod_color; ?>;">
						<div id = "ldeb-note-container">
							<span id = 'note-white'>Waiting To Start...</span>
							<span id = 'note-red' style = 'color: red;'></span>
						</div>
					</div>
				</div>
			<?php } ?>
		</div>
		<?php
	}else{
		header("Location: index.php?page=home");
	}
}else{
	header("Location: index.php?page=home");
}
?>		