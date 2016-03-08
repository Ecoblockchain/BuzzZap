<?php
if($check_valid!="true"){
	header("Location: index.php?page=home");
	exit();
}
if(loggedin()){
	?>	
	<script>
		$(function(){

			$('.live-deb-room-container').jqFloat({
				width: 40,
				height: 40,
				speed: 2000
			});
			var curbg;
			$('.live-deb-room-container').hover(function(){
				curbg = $(this).css("backgroundColor");
				$(this).css({"border":"3px dotted white"});
			}).mouseleave(function(){
				$(this).css({"border":"none"});
			}); 
			var start_ldeb_opened=0;
			$("#start_ldeb").click(function(){
				if(start_ldeb_opened==0){
					start_ldeb_opened=1;
					
					$(this).animate({height:"700px"}, 500).animate({marginLeft:"28%"})
					.animate({width:"42%", marginTop:"-=100px"}, 500).css("color", "#ffffff").css("z-index", "100000000")
					.css("box-shadow", "0px 0px 40px dimgrey");
			
					setTimeout(function(){
						$("#start-ldeb-form").fadeIn();
					}, 1000);
					setTimeout(function(){
						$("#start-ldeb-form").fadeIn();
						$("#start_ldeb").css("min-width", "370px");
						$("#close-ldeb-form").show();
					}, 2000);
				}
			});
			$("#close-ldeb-form").click(function(){
				if(start_ldeb_opened==1){	
					$("#close-ldeb-form").hide();
					$("#start-ldeb-form").fadeOut();
					$("#start_ldeb").css("min-width", "0px");
					$("#start_ldeb").animate({height:"30px", marginTop:"+=100px"}, 500).animate({marginLeft:"0%"})
					.animate({width:"200px"}, 500).css("color", "#ffffff")
					.css("box-shadow", "none");
					setTimeout(function(){
						start_ldeb_opened = 0;
						$("#start_ldeb").css("z-index", "1");
					}, 1000);
					
				}
			});

			$(".ldeb-judge-sel").change(function(){
				var val = $(this).val();
				if(val=="jspec"){
					$(".tjudge-info").html("This means you can choose any specific and trustworthy user to judge the debate, however they cannot be in any of the involved groups: <input type = 'text' placeholder = 'Username...' class = 'tjudge-spec-users-field' name = 'jspec_users'><br>");
				}else if(val=="jout"){
					$(".tjudge-info").html("This means you would like someone who is not on BuzzZap at all to judge the debate (a link will be sent to them). Please supply their email(s) here: <input type = 'text' placeholder = 'Email...' id = 'tjudge-out-email-field' name = 'jout_emails' class = 'tjudge-spec-users-field'><br>");
				}
			});	
		});
	</script>
	<?php print_r(calc_ldeb_struct(3, 3)); ?>
	<div class = 'page-path'>Debating > Live Debating</div><br>
	<div class = 'loggedin-headers'>
		Live Debating<br>
		<span style = 'font-size:40%'>(Showing all current live debating rooms)</span>
	</div>
	<div class = "start_comp_link no-hyphens" id = "start_ldeb" style = "margin-top: -50px;height: 30px;">
		Start Live Debate
		<div class = "start-comp-form" id = "start-ldeb-form">
			<form action = "" method = "POST">
				<span style = "color:grey;float:right;margin-top:-20px;display:none;" id = 'close-ldeb-form'>x</span>
				<br>
				<span style = 'font-size:80%;'>Debate Notion: </span><br>
				<input type = "text" id = "ldeb_question" class = "loggedout-form-fields" placeholder = "e.g Education is a human right." style = "height:30px;outline-width:0px;font-size:60%;box-shadow:none;" name = "ldeb_question"><br>
				<span id = 'comp_field_labels'>
					Leave this blank if you would like a randomly picked debate notion.
				</span>	<br><br>
				<span style = 'font-size:80%;'>Desired Opponent: </span><br>
				<input type = "text" id = "des_opponents" class = "loggedout-form-fields" placeholder = "Group Name..." style = "height:30px;outline-width:0px;font-size:60%;box-shadow:none;" name = "ldeb_opponent">
				<div id = "pred_results"></div>
				<span id = 'comp_field_labels'>
					Enter the groups you want to compete against in this debate. You can only have one opponent in live debating.
				</span>	<br><br>
				<span style = 'font-size:80%;'>Debate Duration: </span><br>
				<input type = "text" id = "" class = "loggedout-form-fields" placeholder = "e.g 30" style = "height:30px;outline-width:0px;font-size:60%;box-shadow:none;" name = "ldeb_duration"><br>
				<span id = 'comp_field_labels'>
					How long the debate will last (in minutes)
				</span>	<br><br>
				<span style = 'font-size:80%;'>Rounds: </span><br>
				<input type = "text" id = "" class = "loggedout-form-fields" placeholder = "e.g 3" style = "height:30px;outline-width:0px;font-size:60%;box-shadow:none;" name = "ldeb_rounds"><br>
				<span id = 'comp_field_labels'>
					How many rounds you would like.
				</span>	<br><br>
				<span style = 'font-size:80%;'>How would you like this competition to be judged?</span><br>
				<select name = "ldeb_judge" id = "contact-opt-sel" class = "ldeb-judge-sel" style = "margin-left:25px;width:305px;color:grey;">
					<option value = "">---</option>
					<option value = "jspec">Choose specific judge on BuzzZap</option>
					<option value = "jout">Invite special judge (outside BuzzZap)</option>
				</select>
				<br><span style = "" id = "comp_field_labels" class = 'tjudge-info'></span>
				<br>
				<span style = 'font-size:80%;'>Display note:</span><br>
				<textarea name = "ldeb_note" id = "sncomp-txtarea" placeholder= "e.g ...Good luck!"></textarea><br>
				<span id = 'comp_field_labels'>
					A note that will be displayed to everyone involved (optional)
				</span>	
				<hr size = '1'>
				<span id = 'comp_field_labels'>
					-browser warnings
					-for/against randomly picked note
					-time to start limit explained
				</span>
				<br><br>
				<input type = "submit" class = "loggedout-form-submit" style = "font-size:80%;box-shadow:none;width:200px;padding:10px;" value = "Start Debate">
			</form>	

			<?php
				if(isset($_POST['ldeb_question'],$_POST['ldeb_note'],$_POST['ldeb_opponent'],$_POST['ldeb_duration'],$_POST['ldeb_rounds'], $_POST['ldeb_judge'])){
					$question  = htmlentities($_POST['ldeb_question']);
					$note = htmlentities($_POST['ldeb_note']);
					$opp = htmlentities($_POST['ldeb_opponent']);
					$dur = htmlentities($_POST['ldeb_duration']);
					$rounds = htmlentities($_POST['ldeb_rounds']);
					$judge_type = htmlentities($_POST['ldeb_judge']);

					
					$errors = "";

					if(strlen($question)<10){
						$errors.="Your debate notion is too short.<br>";
					}

					$opp_id = $db->query("SELECT group_id FROM private_groups WHERE group_name = ".$db->quote($opp))->fetchColumn();
					if(empty($opp_id)){
						$errors.="The opponent you requested does not exist.<br>";
					}
					if(group_leader($_SESSION['user_id'])){
						$starter_id= get_user_group($_SESSION['user_id'], "group_id");
						if($starter_id==$opp_id){
							$errors.= "You can not supply your own group as an opponent.<br>";
						}
					}else{
						$errors.= "You must be in a group and the group leader to start a competition.<br>";
					}

					$struct = calc_ldeb_struct($dur, $rounds);
				}

			?>
		</div>	
	</div>	
	<div id = "room-container">
		<?php
			$get_rooms = $db->query("SELECT * FROM live_debates ORDER BY start_time DESC");
			$count = 0;
			$colors = array("#ff9fad", "#72df92", "#8fc8f0","#f4a05a");
			$ccount = 0;
			
			foreach($get_rooms as $room){
				$randc = $colors[$ccount];
				echo "<div class = 'live-deb-room-container' id = 'roomc-".$count."' style = 'background-color:".$randc.";margin-top:1px;margin-left:2px;'>"
				."<div class = 'room-title-container'>"
				.$room['question'].
				"</div></div>";
				$count++;
				$ccount++;
				if($ccount>count($colors)-1){
					$ccount = 0;
				}
			}
		?>
	</div>

	<?php
}else{
	header("Location: index.php?page=home");
}