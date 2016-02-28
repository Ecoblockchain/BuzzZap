<?php
if($check_valid!="true"){
	header("Location:../index.php?page=home");
	exit();
}
if(loggedin()){
	if(first_login($_SESSION['user_id'])){
		add_badge("Becoming a member on BuzzZap", $_SESSION['user_id'], " you have logged in for the first time!");
		$quant_msg = $db->query("SELECT * FROM static_content WHERE cont_name LIKE 'first_login_greet%'")->rowCount();
		$greets = array();
		for($i = 1;$i<=$quant_msg;$i++){
			if($i!=1){
				$greets[] = get_static_content('first_login_greet'.$i);
			}else{
				$parse_vars = array("firstname"=>get_user_field($_SESSION['user_id'], 'user_firstname'));
				$greets[] = static_cont_rec_vars(get_static_content('first_login_greet'.$i), $parse_vars);
			}
		}
		$greets = json_encode($greets);
		?>
			<script>
			$(document).ready(function(){
				var greets = eval(<?php echo $greets; ?>);
				$("#fl-intro-box").typed({
					strings: greets,
					typeSpeed: 10,
					callback: function() {
						$("#fl-tour-opt").fadeIn();
					}
				});
					
			});
			</script>
			<div id = "fl-intro-box"></div>
			<div id = "fl-tour-opt"><a class = 'fl-tour-opt' href = 'index.php?page=home&tour=true'>Yes</a><span style = 'color: grey;'>/</span><a href = 'index.php?page=home' class = 'fl-tour-opt'>No</a></div>
		<?php
	}else{
	?>	
		<script>
		$(document).ready(function(){
			
			<?php
			if(!isset($_SESSION['hib-ani'])){
			?>
				$(".hib-c-content").hide();
				$("#hib1").css("width", "0px");
				$("#hib2").css("height", "0px");
				$("#hib3").css("width", "0px");
				$("#hib4").css("height", "0px");
				$("#hib5").css("height", "0px");
				$("#hib6").css("width", "0px");
			
			
				$("#hib1").animate({width:"66.6%"}, 500);
				setTimeout(function(){$("#hib2").animate({height:"300px"}, 500);}, 500);
				setTimeout(function(){$("#hib3").animate({width:"33.3%"}, 500);}, 1000);
				setTimeout(function(){$("#hib4").animate({height:"300px"}, 500);}, 1500);
				setTimeout(function(){$("#hib5").animate({height:"600px"}, 500);}, 2000);
				setTimeout(function(){$("#hib6").animate({width:"66.6%"}, 500);}, 2500);
				setTimeout(function(){

					$(".hib-c-content").fadeIn(3000);
				
				}, 3000);
			<?php
				$_SESSION['hib-ani'] = true;
			}
			?>
			var t1_clicks = 0;
			$("#p-g-toggle1").click(function(){
				t1_clicks = t1_clicks + 1;
				if(t1_clicks % 2==0){
					$("#p-g-toggle1").html("GLOBAL");
					$("#hibc-t1-2").fadeIn();
					$("#hibc-t1-1").fadeOut();
				}else{
					$("#p-g-toggle1").html("PRIVATE");
					$("#hibc-t1-2").fadeOut();
					$("#hibc-t1-1").fadeIn();
				}
			});
			var t2_clicks = 0;
			$("#p-g-toggle2").click(function(){
				t2_clicks = t2_clicks + 1;
				if(t2_clicks % 2==0){
					$("#p-g-toggle2").html("PRIVATE");
					$("#hibc-t2-1").fadeIn();
					$("#hibc-t2-2").fadeOut();
				}else{
					$("#p-g-toggle2").html("GLOBAL");
					$("#hibc-t2-1").fadeOut();
					$("#hibc-t2-2").fadeIn();
				}
			});
			var t3_clicks = 0;
			$("#p-g-toggle3").click(function(){
				t3_clicks = t3_clicks + 1;
				if(t3_clicks % 2==0){
					$("#p-g-toggle3").html("GLOBAL");
					$("#hibc-t3-1").fadeIn();
					$("#hibc-t3-2").fadeOut();
				}else{
					$("#p-g-toggle3").html("PRIVATE");
					$("#hibc-t3-1").fadeOut();
					$("#hibc-t3-2").fadeIn();
				}
			});
		});
		</script>
		
		<div class = "home-in-box" id = 'hib2'>
			<span class = "hib-c-content">
				<div class = "hib-title">
					<?php echo strtoupper(get_user_community($_SESSION['user_id'], "com_name")); ?> LEADER BOARD
				</div>
				<div class = "hib-content" style = "margin-top:-20px;">
				<?php
					$get = $db->prepare("SELECT user_username, user_rep,user_id FROM users WHERE user_com = :com_id ORDER BY user_rep DESC LIMIT 10");
					$get->execute(array("com_id"=>get_user_community($_SESSION['user_id'], "com_id")));
					$count = 1;
					echo "<span style = 'font-size: 80%;color: #0e6eb8;float: right;'>(user rep)</span>";
					echo "<br>";
					while($row = $get->fetch(PDO::FETCH_ASSOC)){
						echo "<div class = 'hib-c-row'>".$count.".".add_profile_link($row['user_username'], 0, 'color:white')."<span style = 'float: right;font-size: 80%;color: #9ec5e2;'>".$row['user_rep']."</span></div>";
						$count++;
					}
				?>
				</div>
			</span>
		</div>
		<div class = "home-in-box" id = 'hib1'>
			<span class = "hib-c-content">
				<div class = "hib-title">
					COMMUNITY NEWS
				</div>
				<div class = "hib-content" style = "overflow: scroll;">
					<?php
						$get = $db->prepare("SELECT * FROM com_news WHERE com_id = :com_id ORDER BY time DESC");
						$get->execute(array("com_id"=>get_user_community($_SESSION['user_id'], "com_id")));
						if($get->rowCount()>0){
							while($row = $get->fetch(PDO::FETCH_ASSOC)){
								echo "<div class = 'hib-c-row'>".$row['feed_text']."<br><br><span style = 'font-size: 80%;color: #9ec5e2;'>-".date("d/M/Y H:i", $row['time'])."</span></div>";
							}
						}else{
							echo "No results found.";
						}
					?>
				</div>
			</span>	
		</div>
		
		<div class = "home-in-box" id = 'hib4'>
			<span class = "hib-c-content">
				<div class = "hib-title">
					LATEST <span id = 'p-g-toggle1' class = "p-g-toggle">GLOBAL</span> DEBATES
				</div>
				<div class = "hib-content" id = "hibc-t1-2">
				<?php
					$get = $db->prepare("SELECT * FROM debating_threads WHERE com_id = 0 AND visible = 1 ORDER BY time_created DESC LIMIT 5");
					$get->execute();
					if($get->rowCount()>0){
						while($row = $get->fetch(PDO::FETCH_ASSOC)){
							echo "<a href = 'index.php?page=view_private_thread&thread_id=".$row['thread_id']."'><div class = 'hib-c-row'>".$row['thread_title']."<br><br><span style = 'font-size: 80%;color: lightgrey;'>-".date("d/M/Y H:i", $row['time_created'])."</span></div></a>";
						}
					}else{
						echo "No Results found.";
					}	
				?>
				</div>
				<div class = "hib-content" id = "hibc-t1-1" style = "display: none;">
				<?php
					$get = $db->prepare("SELECT * FROM debating_threads WHERE com_id = :com_id AND visible = 1 ORDER BY time_created DESC LIMIT 5");
					$get->execute(array("com_id"=>get_user_community($_SESSION['user_id'], "com_id")));
					if($get->rowCount()>0){
						while($row = $get->fetch(PDO::FETCH_ASSOC)){
							echo "<a href = 'index.php?page=view_private_thread&thread_id=".$row['thread_id']."'><div class = 'hib-c-row'>".$row['thread_title']."<br><br><span style = 'font-size: 80%;color: lightgrey;'>-".date("d/M/Y H:i", $row['time_created'])."</span></div></a>";
						}
					}else{
						echo "No Results found.";
					}
				?>
				</div>
			</span>	
		</div>
		<div class = "home-in-box" id = 'hib3'>
			<span class = "hib-c-content">
				<div class = "hib-title">
					LATEST <span id = 'p-g-toggle2' class = "p-g-toggle">PRIVATE</span> COMPETITIONS
				</div>
				<div class = "hib-content" id = "hibc-t2-1">
				<?php
					$get = $db->prepare("SELECT * FROM competitions WHERE comp_type = 0 AND comp_com_id = :com_id AND end != 'true' AND SUBSTRING(end, 0,1) != '.' ORDER BY created DESC LIMIT 5");
					$get->execute(array("com_id"=>get_user_community($_SESSION['user_id'], "com_id")));
					if($get->rowCount()>0){
						while($row = $get->fetch(PDO::FETCH_ASSOC)){
							echo "<a href = 'index.php?page=view_comp&comp=0".$row['comp_id']."'><div class = 'hib-c-row'>".$row['comp_title']."<br><br><span style = 'font-size: 80%;color: lightgrey;'>-".date("d/M/Y H:i", $row['created'])."</span></div></a>";
						}
					}else{
						echo "No Results found.";
					}
				?>
				</div>
				<div class = "hib-content" id = "hibc-t2-2" style = "display: none;">
				<?php
					$get = $db->prepare("SELECT * FROM competitions WHERE comp_type =1 AND end != 'true' AND SUBSTRING(end, 0,1) != '.' ORDER BY created DESC LIMIT 5");
					$get->execute();
					if($get->rowCount()>0){
						while($row = $get->fetch(PDO::FETCH_ASSOC)){
							echo "<a href = 'index.php?page=view_comp&comp=1".$row['comp_id']."'><div class = 'hib-c-row'>".$row['comp_title']."<br><br><span style = 'font-size: 80%;color: lightgrey;'>-".date("d/M/Y H:i", $row['created'])."</span></div></a>";
						}
					}else{
						echo "No Results found.";
					}
				?>
				</div>
			</span>	
		</div>
		<div class = "home-in-box" id = 'hib5'>
			<span class = "hib-c-content">
				<div class = "hib-title">
					POPULAR <span id = 'p-g-toggle3' class = "p-g-toggle">GLOBAL</span> DEBATES
				</div>
				<div class = "hib-content" id = "hibc-t3-2" style = "display: none;height: 510px;">
				<?php
					$get = $db->prepare("SELECT * FROM debating_threads WHERE com_id = :com_id AND visible = 1 ORDER BY thread_likes DESC LIMIT 10");
					$get->execute(array("com_id"=>get_user_community($_SESSION['user_id'], "com_id")));
					if($get->rowCount()>0){
						while($row = $get->fetch(PDO::FETCH_ASSOC)){
							echo "<a href = 'index.php?page=view_private_thread&thread_id=".$row['thread_id']."'><div class = 'hib-c-row'>".$row['thread_title']."<br><br><span style = 'font-size: 80%;color: lightgrey;'>-".date("d/M/Y H:i", $row['time_created'])."</span></div></a>";
						}
					}else{
						echo "No Results found.";
					}
				?>
				</div>
				<div class = "hib-content" id = "hibc-t3-1" style = "height: 510px;">
				<?php
					$get = $db->prepare("SELECT * FROM debating_threads WHERE com_id = 0 AND visible = 1 ORDER BY thread_likes DESC LIMIT 10");
					$get->execute();
					if($get->rowCount()>0){
						while($row = $get->fetch(PDO::FETCH_ASSOC)){
							echo "<a href = 'index.php?page=view_private_thread&thread_id=".$row['thread_id']."'><div class = 'hib-c-row'>".$row['thread_title']."<br><br><span style = 'font-size: 80%;color: lightgrey;'>-".date("d/M/Y H:i", $row['time_created'])."</span></div></a>";
						}
					}else{
						echo "No Results found.";
					}
				?>
				</div>
			</span>	
		</div>
		<div class = "home-in-box" id = 'hib6'>
			<span class = "hib-c-content">
				<div class = "hib-title">
					BUZZZAP NEWS
				</div>
				<div class = "hib-content">
				<?php
					$get = $db->prepare("SELECT * FROM site_news ORDER BY time DESC LIMIT 10");
					$get->execute();
					if($get->rowCount()>0){
						while($row = $get->fetch(PDO::FETCH_ASSOC)){
							echo "<div class = 'hib-c-row'>".$row['feed_text']."<br><br><span style = 'font-size: 80%;color: lightgrey;'>-".date("d/M/Y H:i", $row['time'])."</span></div>";
						}
					}else{
						echo "No Results found.";
					}
				?>
				</div>
			</span>	
		</div>
	<?php
	}
}
?>