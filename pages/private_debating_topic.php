<?php
if($check_valid!="true"){
	header("Location: index.php?page=home");
	exit();
}
if(loggedin()){
	$user_id = $_SESSION['user_id'];
	$com_id = get_user_field($user_id, "user_com");
	$topic_id = htmlentities($_GET['topic_id']);
	$valid_topics = array();
	
	if(isset($_GET['d'])){
		if($_GET['d']=="g"){
			$com_id = 0;
		}	
	}
	$get_valid_topics = $db->query("SELECT topic_id FROM debating_topics");
	foreach($get_valid_topics as $topicid){
		$valid_topics[] = $topicid['topic_id'];
	}
	if(in_array($topic_id, $valid_topics)){
		?>
		<script>
		$(document).ready(function(){
			var opened = 0;
			
				$(".start-debate-option").click(function(){
					if(opened===0){
						opened = 1;
						$(this).animate({backgroundColor:'#FFFFFF'}, 500).animate({color:'#757575'}, 500)
						.animate({width:'520px'}, 500).animate({height:'340px'}, 500);
						setTimeout(function(){
							$("#start-debate-form").fadeIn();
							$(".start-debate-option").css("box-shadow", "0px 0px 200px #030303");
						;}, 2000);
						$(this).animate({marginLeft:'20%'}, 500);
					}	
				});
			
			
				$("#close-sd").click(function(){
					window.location="index.php?page=private_debating_topic&topic_id=<?php echo $topic_id; ?>";
					
				});
				
				
		
			
		});
		</script>
		<div class = 'start-debate-option'>Start Debate

			<form method = "POST" id = "start-debate-form">
			<span style = "position:absolute;margin-left:250px;margin-top:-20px;z-index:10000;" id = "close-sd">X</span>
				<input type = "text" placeholder="Debate Question" maxlength = "120" autocomplete="off" 
				spellcheck="false" name = "debate_title" class = "loggedin-form-field1" style = "">
				<br>
				<textarea name = "debate_text" class = "loggedin-form-field2" placeholder = "Debate Description..."></textarea>
				<br>
				<div class = "loggedin-form-info1"><p>If your reputation is higher than 20, 
					this question will not require any approvel from
					staff/leaders.</p>
				</div>
				<input type = "submit" value = "Submit" class = "loggedin-form-submit1">
			</form>
		</div>
		<br><br><br>
		<?php
		if(isset($_POST['debate_title'],$_POST['debate_text'])){
			$title = htmlentities($_POST['debate_title']);
			$text = htmlentities($_POST['debate_text']);
			$errors = false;
			if($com_id==0){
				$extra_get="&d=g";
			}else{
				$extra_get="";
			}
			if(strlen($text)<20){
				$errors = true;
				setcookie("success", "0Your description must be longer.",time()+10);
				header("Location: index.php?page=private_debating_topic&topic_id=".$topic_id.$extra_get);
			}else if(strlen($title)<10){
				$errors = true;
				setcookie("success", "0Your question must be longer.",time()+10);
				header("Location: index.php?page=private_debating_topic&topic_id=".$topic_id.$extra_get);	
			}
			if($errors==false){
				$thread_id = create_thread($title, $text, $com_id, $topic_id);
				if(!empty($thread_id)){
					header("Location: index.php?page=view_private_thread&thread_id=".$thread_id);
					setcookie("success", "1Debate created successfully!",time()+10);
				}else{
					setcookie("success", "0Unknown error.",time()+10);
					header("Location: index.php?page=private_debating_topic&topic_id=".$topic_id);
				}
			}
		}
		$get_threads = $db->prepare("SELECT * FROM debating_threads WHERE topic_id = :id AND com_id = :com_id ORDER BY latest_action DESC");
		$get_threads->execute(array("id"=>$topic_id, "com_id"=>$com_id));
		if($get_threads->rowCount()!=0){
		
			while($row = $get_threads->fetch(PDO::FETCH_ASSOC)){

				if((get_user_field($_SESSION['user_id'], "user_username")==$row['thread_starter'])&&($row['visible']=='0')){
					$color = "#FF6A6A";
					$dis = True;
				}else if ($row['visible']=='1'){
					$color = "#457EA4";
					$dis = True;
				}else if(($row['visible']=='0')&&(get_user_field($_SESSION['user_id'], "user_username")!==$row['thread_starter'])){
					$color = "#457EA4";
					$dis = False;
				}
				if($dis==True){
				
					$description = $db->query("SELECT reply_text FROM thread_replies WHERE
											 thread_id = ".$db->quote($row['thread_id'])." AND first_post = '1'")->fetchColumn();
					$votes = array("yes"=>$row['vote_yes'], "maybe"=>$row['vote_maybe'], "no"=>$row['vote_no']);
					$total = $votes['yes'] + $votes['maybe'] + $votes['no'];

					foreach ($votes as $key=>&$value){
						if($total !== 0){
							$value = (100 / $total) * ($value);
							$value = round($value);
						}
					}
					$dis_vote_opts = get_question_type($row['thread_title'], 2);
					echo "<a href = 'index.php?page=view_private_thread&thread_id=".$row['thread_id']."'><div class = 'thread-container' style= 'padding-top:10px;'>";
					echo "<div class = 'thread-title' style = 'color:".$color.";'>".$row['thread_title']."<br>";
					if($color=="#FF6A6A"){
						echo "<span class = 'non-act-warning'>*untill this debate has been
						 activated/approved<br> by a leader, only you can see it.</span>";
					}	
					echo "</div><br><br>";
					if(count($dis_vote_opts)!=0){
					echo "<div class = 'thread-end'>
							<div class = 'voter-amount'>".$total." vote(s)</div>

							<div class = 'thread-end-sec'>
								<span style = 'color:#8FBC8F;'>".$dis_vote_opts[0]."</span><br>
								".$votes['yes']."%
							</div>
							<div class = 'thread-end-sec'>
								<span style = 'color:#CD9B9B'>".$dis_vote_opts[1]."</span><br>
								".$votes['no']."%
							</div>
							<div  class = 'thread-end-sec'>
							<span style = 'color:dimgrey;'>".$dis_vote_opts[2]."</span><br>
							".$votes['maybe']."%

							
							</div>
						</div>";
					}else{
						echo "<div class = 'thread-end'>
							<br>This question is not votable.
						</div>";
					}	
				
					echo "<div class = 'thread-des'>".$description."</div>";
					echo "</div></a><br>";
				}
			}
		}else{
			?>
				<div id = "no-threads-message">There are no debates in this section yet.</div>
			<?php	
		}
	}else{
		header("location:index.php?page=home");
	}	
}else{
	header("location:index.php?page=home");
}
?>