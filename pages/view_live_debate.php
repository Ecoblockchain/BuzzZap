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
		$gid = get_ldeb_val($did, "duration");
		$rounds = 5;
		$rnd_timeline_width = 600/$rounds;
		$dur_min = 0.5;
		$dur_sec = $dur_min*60;

		?>

			<script>
				$(function(){

					// time control
					var secs = <?php echo $dur_sec; ?>;
					var milisecs = 1000*secs;
					var mins = parseFloat(secs/60);
					var csecs = 60;
					$("#ldeb-time-left").html(mins+":00");
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


					//streaming control
					var getUserMedia = navigator.getUserMedia || navigator.webkitGetUserMedia || navigator.mozGetUserMedia;
					var peer = new Peer({host: 'www.buzzzap.com', port:9000, path:''});

					<?php
						$involved_users = get_ldeb_involved($did);

						if(in_array($_SESSION['user_id'], array_keys($involved_users))){
							$uinv = true;
						}else{
							$uinv = false;
						}
						
						if($uinv==true){
							//involved user, streaming setup
							?>
							var pid = "<?php echo $involved_users[$_SESSION['user_id']]."-".$_SESSION['user_id']; ?>";
							$.post('https://www.buzzzap.com:9001/test', {pid:pid}, function(result){
								console.log(result);
							});

							<?php
							
						}
					?>	


				});
			</script>
		<div id = "ldeb-timeline">
			<div id = "ldeb-timeline-mrk"><div id = 'ldeb-time-left'>00:00</div></div>
			<?php
				$mleft = 0;
				$submleft = 0;
				for($i = 1;$i<=$rounds;$i++){
					if($i>1){
						$mleft =  $rnd_timeline_width;
					}
	
					echo "<div class = 'ldeb-timeline-rnd-mrk' style = 'border-left: 2px solid grey;margin-left:".$mleft."px;width:".$rnd_timeline_width."px;'>
						<div class = 'ldeb-timeline-rnd-mrk' style = 'background: lightblue;margin-left:0px;width:".strval($rnd_timeline_width/2)."px;'>
					</div>";
				}
			?>
		</div>

		<div id = 'ldeb-opp-pod-container'>
			<div class = 'podium-container'></div>
		</div>

		<div id = 'ldeb-own-pod-container'>
			<div class = 'own-mics-container'></div>
			<div class = 'pod-own-top-container'></div>
		</div>

		<?php
	}else{
		header("Location: index.php?page=home");
	}
}else{
	header("Location: index.php?page=home");
}
?>		