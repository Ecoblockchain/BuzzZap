<?php
if($check_valid!="true"){
	header("Location: index.php?page=home");
	exit();
}
if(loggedin()){
	if(isset($_GET['did'])){

		$did = htmlentities($_GET['did']);
		$rounds = get_ldeb_val($did, "rounds");
		$dur_min = get_ldeb_val($did, "duration");
		$sid = get_ldeb_val($did, "starter_id");
		$rounds = 4;
		$dur_min = 4;
		$sname = $db->query("SELECT group_name FROM private_groups WHERE group_id = ".$db->quote($sid))->fetchColumn(); 
		$start_time = get_ldeb_val($did, "start_time");
		$oid = get_ldeb_val($did, "opp_id");
		$oname = $db->query("SELECT group_name FROM private_groups WHERE group_id = ".$db->quote($oid))->fetchColumn(); 
		$phase = get_ldeb_val($did, "phase"); //0 = waiting to start, 1= start, 2=end
		$question = get_ldeb_val($did, "question");
		$gid = get_user_group($_SESSION['user_id'], "group_id");
		$gname = get_user_group($_SESSION['user_id'], "group_name");
		$rnd_timeline_width = 600/$rounds;
		$scolor = "#D09458"; //light
		$ocolor = "#9E643F"; //dark

		$color_dis = ($gid==$sid)? "light" : "dark";
		$oppcolor_dis = ($gid==$sid)? "dark" : "light";
		$opp_pod_img = "url('../ext/images/podium-".$oppcolor_dis.".png')";
		$own_pod_color  = ($oid==$gid)? $ocolor : $scolor;
		
		$dur_sec = $dur_min*60;
		$involvement = 0; //0 not involved, 1 involved, 2 creator
		$involved_users = get_ldeb_involved($did);
		$timeline_cues = calc_ldeb_timeline($dur_min, $rounds);

		$json_timeline_cues = json_encode($timeline_cues);
	
		if(in_array($_SESSION['user_id'], array_keys($involved_users))){
			if(get_group_leader_id($sid)==$_SESSION['user_id']){
				$involvement = 2;
			}else{
				$involvement = 1;
			}
		}else{
			$involvement = 0;
		}
		echo $involvement;

		?>

			<script>
				$(function(){
					var json_timeline_cues = <?php echo $json_timeline_cues; ?>;
				
					// time control

					$("#dis-deb-phase").html("Waiting To Start");

					<?php
						
						if($involvement>0){
							//involved user, streaming setup
							?>
							//js ldeb data
							var phase = <?php echo $phase; ?>;
							var duration = <?php echo $dur_sec; ?>;
							var start_time = <?php echo $start_time; ?>;
							//socket & peerjs init
							var socket = io.connect("https://buzzzap.com:9001");
							var getUserMedia = navigator.getUserMedia || navigator.webkitGetUserMedia || navigator.mozGetUserMedia;
							var upid = "<?php echo $involved_users[$_SESSION['user_id']].','.get_user_field($_SESSION['user_id'], 'user_username').','.$did; ?>";
							var peer = new Peer({host: 'www.buzzzap.com', port:9000, path:''});
							
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
									
								
									$("#ldeb-timeline-mrk").css("margin-left", new_pos+"px");
								
									timer_interval = setInterval(function(){
										
										secs_in++;
										var round_secs_in = Math.floor(secs_in).toString();
						                var nxt_cue = json_timeline_cues[fstage+1][0];
						               	var nxt_turn = json_timeline_cues[fstage+1][1];

						               	var turn_name = (nxt_turn==3)? "break" : (nxt_turn==1)?"<?php echo ($involved_users[$_SESSION['user_id']] == $sid)? 'your group': $oname; ?>" : "<?php echo ($involved_users[$_SESSION['user_id']] == $oid)? 'Your group': $oname; ?>";
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
						                		$("#ldeb-note-container").html(result_text);
						                	}else{
						                		$("#ldeb-note-container").html(count_down + count_text);
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
									$("#online-sec-<?php echo $sid; ?>,#online-sec-<?php echo $oid; ?>").html("");
									for(var i in pdata){
										var p_gid = pdata[i][0];
										var pname = pdata[i][1];
										
										if(p_gid=="<?php echo $sid; ?>"){
											secToAppend = $("#online-sec-<?php echo $sid; ?>");
										}else{
											secToAppend = $("#online-sec-<?php echo $oid; ?>");
										}
										secToAppend.append("<span id = 'ponline-"+pname+"'>"+pname+"</span><br>");
										
									}
								}


								
								//init peer
								socket.emit('add_peer',  
									{did:"<?php echo $did; ?>",
									pid:own_id, 
									uident:upid, 
									ugid: "<?php echo $gid; ?>",
									deb_data: { phase:phase,
												start_time: start_time,
												timeline:json_timeline_cues
									 	}
									}
								);

								//update users
								socket.on('new_peer_data', function(data){
									peer_data = data.rel_peers; //pid[data], pid[data], ...
									pid_call = data.pid_call;
									if(pid_call!=false){ // call peers
										for(var i in peer_data){
											if(i!=own_id){
												peer.connect(i);
												if(peer_data[i][0]=="<?php echo $sid; ?>"){
													g_num = 1;
												}else{
													g_num = 2;
												}
												call_and_recieve(i, g_num);
											}
										}
									}
									
									render_online_peers(peer_data);
								});

								
								//start timer/debate
								<?php if ($involvement == 2){ ?>
									$("#start-ldeb-opt").click(function(){

										socket.emit("start_deb", {did:"<?php echo $did; ?>"});
						
									});
								<?php } ?>

			
								//recieve start request from server
								socket.on('checked_deb_stage', function(data){
									if(data.phase==1){
										var stage = data.stage;
										if(data.man==false){
											timer(data.time_in, stage);
										}
										$("#dis-deb-phase").html("Started");
										$("#dis-round").html(stage[2]);
										var gturn_name;



										if(stage[1]==1){
											
											mute_stream(2);
							
											gturn_name = "<?php echo $sname; ?>";
										}else if(stage[1]==2){
											
											mute_stream(1);
											
											gturn_name = "<?php echo $oname; ?>";
										}else{
											gturn_name = " No one (break time)";
											mute_stream(0);
					
										}
										$("#dis-turn").html(gturn_name);
									}
								});

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
									var name = "<?php echo get_user_field($_SESSION['user_id'], 'user_username'); ?>";
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
									if(message.name!="<?php echo get_user_field($_SESSION['user_id'], 'user_username'); ?>"){
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
							});
							<?php
						}
					?>	
				});
			</script>
		<div class = 'page-path'>Debating > <a style = 'color: #40e0d0;' href = 'index.php?page=live_debating'>Live Debating</a> > <?php echo $question; ?></div>
		<div class = "loggedin-headers" style = 'color: grey;'>
			<?php echo "<span style = 'color: ".$scolor.";'>".$sname; ?></span>
			 Vs
			 <?php echo "<span style = 'color: ".$ocolor.";'>".$oname; ?></span>
		</div>
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
		<div id = "ldeb-general-container"  style = "background: <?php echo $own_pod_color; ?>;">
			<div class = "ldeb-general-header">General Details</div>
			<div class = "ldeb-general-inner" style = "font-size: 75%;letter-spacing: -1px;padding: 5px;">
				<span class  = "ldeb-gen-detail-row"><b>Your Group's Colour: </b><?php echo $color_dis." brown"; ?></span><br>
				<span class  = "ldeb-gen-detail-row"><b>Debate Phase: </b><span id = 'dis-deb-phase'></span></span><br>
				<span class  = "ldeb-gen-detail-row"><b>Round: </b><span id = 'dis-round'></span></span><br>
				<span class  = "ldeb-gen-detail-row"><b>Turn To Speak: </b><span id = 'dis-turn'></span></span><br>
			</div>
		</div>
		<div id = "ldeb-online-container">
			<div class = "ldeb-general-header">User's Online</div>
			<div class = "ldeb-general-inner">
				<div class = "ldeb-online-sec" id = "online-sec-<?php echo $sid; ?>" style = "background-color: <?php echo $scolor; ?>;"></div>
				<div class = "ldeb-online-sec" id = "online-sec-<?php echo $oid; ?>" style = "background-color: <?php echo $ocolor; ?>;"></div>
			</div>
		</div>
		<div id = "ldeb-central-container">
			<div id = "ldeb-question-header"><?php echo $question; ?></div>
			<?php if($involvement == 2){ ?>
				<div id = "start-ldeb-opt" class= "view-thread-opts-link" style = "float: none;width: 200px;margin: 0 auto;">Start Live Debate</div>
			<?php } ?>
		</div>
		
		<div id = "ldeb-chat-container">
			<div id = "ldeb-chat-header"><span style = 'float:left;'>&uarr;</span>Private <?php echo $gname; ?> Chat</div>
			<div id = "ldeb-chat-inner">

			</div>
			<div id = "ldeb-chat-form">
				<input type = "text" id = "pm-msg-txt" placeholder = "Message..."><input type = "button" id = 'pm-msg-submit' value = "Send">
			</div>
		</div>
		
		<div id = 'ldeb-opp-pod-container'>
			<div class = 'podium-container' style = "background-image: <?php echo $opp_pod_img; ?>"></div>
		</div>

		<div id = 'ldeb-own-pod-container'>
			<div class = 'own-mics-container'></div>
			<div class = 'pod-own-top-container' style = "background: <?php echo $own_pod_color; ?>;">
				<div id = "ldeb-note-container"></div>
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