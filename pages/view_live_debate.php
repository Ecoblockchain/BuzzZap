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
		$oid = get_ldeb_val($did, "opp_id");
		$phase = get_ldeb_val($did, "phase"); //0 = waiting to start, 1= start, 2=end
		$question = get_ldeb_val($did, "question");
		$gid = get_user_group($_SESSION['user_id'], "group_id");
		$rounds = 5;
		$rnd_timeline_width = 600/$rounds;
		$dur_min = 0.5;

		$scolor = "#D09458"; //light
		$ocolor = "#9E643F"; //dark

		$color_dis = ($gid==$sid)? "light" : "dark";
		$oppcolor_dis = ($gid==$sid)? "dark" : "light";
		$opp_pod_img = "url('../ext/images/podium-".$oppcolor_dis.".png')";
		$own_pod_color  = ($oid==$gid)? $ocolor : $scolor;
		
		$dur_sec = $dur_min*60;
		$involvement = 0; //0 not involved, 1 involved, 2 creator
		$involved_users = get_ldeb_involved($did);
		if(in_array($_SESSION['user_id'], array_keys($involved_users))){
			if(get_group_leader_id($sid)==$_SESSION['user_id']){
				$involvement = 2;
			}else{
				$involvement = 1;
			}
		}else{
			$involvement = 0;
		}

		?>

			<script>
				$(function(){
					

					// time control
					var secs = <?php echo $dur_sec; ?>;
					var milisecs = 1000*secs;
					var mins = parseFloat(secs/60);
					var csecs = 60;
					$("#ldeb-time-left").html(mins+":00");
					function start_timer(){
						setInterval(function(){

							if(csecs==0){
								csecs = 60;
								mins--;
							}

							csecs--;

							if(csecs<=9){
								zero = "0";
							}else{
								zero = "";
							}
							$("#ldeb-time-left").html(mins+":"+zero+(csecs).toString());
						}, 1000);

						$("#ldeb-timeline-mrk").animate({marginLeft:"598px"}, milisecs, "linear");
					}

					<?php
						
						if($involvement==1){
							//involved user, streaming setup
							?>
							var socket = io.connect("https://buzzzap.com:9001");
							var getUserMedia = navigator.getUserMedia || navigator.webkitGetUserMedia || navigator.mozGetUserMedia;
							var upid = "<?php echo $involved_users[$_SESSION['user_id']].','.get_user_field($_SESSION['user_id'], 'user_username').','.$did; ?>";
							var peer = new Peer({host: 'www.buzzzap.com', port:9000, path:''});
							peer.on('open', function(id) {
								var own_id = id;
								
								function add_audio_stream(stream){
									var audio = $('<audio autoplay />').appendTo('body');
							   		audio[0].src = window.URL.createObjectURL(stream);
							    	audio.onloadedmetadata = function(e){
							        	console.log('now playing the audio');
							        	audio.play();
							   		}
								}

								function call_and_recieve(dest_pid){
									getUserMedia({video: false, audio: true}, function(stream) {
										var call = peer.call(dest_pid, stream);
										call.on('stream', function(stream) {
											 add_audio_stream(stream);
										});
									}, function(err) {
									  console.log('Failed to get local stream' ,err);
									});
								}

								//answer and recieve
								peer.on('connection', function(conn){
									conn.send('Hello!');
									console.log("rec: "+conn);
									peer.on('call', function(call) {
										getUserMedia({video: false, audio: true}, function(stream) {
									   		call.answer(stream);
									    	call.on('stream', function(stream) {
									     		add_audio_stream(stream);
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

							
								socket.emit('addPeer',  {did:"<?php echo $did; ?>",pid:own_id, uident:upid});
								socket.on('new_peer_data', function(data){
									peer_data = data.rel_peers; //pid[data], pid[data], ...
									pid_call = data.pid_call;
									if(pid_call!=false){ // call peers
										for(var i in peer_data){
											if(i!=own_id){
												peer.connect(i);
												call_and_recieve(i);
											}
										}
									}
									render_online_peers(peer_data);
								});
								

									/*setInterval(function(){
										$.post('https://www.buzzzap.com:9001/getPeers', {did:"<?php echo $did; ?>"}, function(result, err){
											form_pids = [];
											for(var i in result){
												form_pids.push(i);
											}
											if(form_pids!=cur_pids){
												render_online_peers(result);
												cur_pids = form_pids;
											}
										});
									}, 2000);*/
									
							});
							<?php
						}
					?>	
				});
			</script>
		<div class = 'page-path'>Debating > <a style = 'color: #40e0d0;' href = 'index.php?page=live_debating'>Live Debating</a> > <?php echo $question; ?></div>
		<div class = "loggedin-headers" style = 'color: grey;'>
			<?php echo "<span style = 'color: ".$scolor.";'>".$db->query("SELECT group_name FROM private_groups WHERE group_id = ".$db->quote($sid))->fetchColumn(); ?></span>
			 Vs
			 <?php echo "<span style = 'color: ".$ocolor.";'>".$db->query("SELECT group_name FROM private_groups WHERE group_id = ".$db->quote($oid))->fetchColumn(); ?></span>
		</div>
		<div id = "ldeb-timeline">
			<div id = "ldeb-timeline-mrk"><div id = 'ldeb-time-left'>00:00</div></div>
			<?php
				$mleft = 0;
				$submleft = 0;
				for($i = 1;$i<=$rounds;$i++){
					if($i>1){
						$mleft =  $mleft + $rnd_timeline_width;
					}
					echo "<div class = 'ldeb-timeline-rnd-mrk' style = 'background: ".$ocolor.";border-left: 2px solid grey;margin-left:".$mleft."px;width:".$rnd_timeline_width."px;'>
						<div class = 'ldeb-timeline-rnd-mrk' style = 'background: ".$scolor.";margin-left:0px;width:".strval($rnd_timeline_width/2)."px;'>
					</div></div>";
				}
			?>
		</div>
		<div id = "ldeb-general-container"  style = "background: <?php echo $own_pod_color; ?>;">
			<div class = "ldeb-general-header">General Details</div>
			<div class = "ldeb-general-inner" style = "font-size: 75%;letter-spacing: -1px;padding: 5px;">
				<span class  = "ldeb-gen-detail-row"><b>Your Group's Colour: </b><?php echo $color_dis." brown"; ?></span>
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
		</div>
		
		
		<div id = 'ldeb-opp-pod-container'>
			<div class = 'podium-container' style = "background-image: <?php echo $opp_pod_img; ?>"></div>
		</div>

		<div id = 'ldeb-own-pod-container'>
			<div class = 'own-mics-container'></div>
			<div class = 'pod-own-top-container' style = "background: <?php echo $own_pod_color; ?>;"></div>
		</div>

		<?php
	}else{
		header("Location: index.php?page=home");
	}
}else{
	header("Location: index.php?page=home");
}
?>		
