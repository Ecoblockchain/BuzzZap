<?php
if($check_valid!="true"){
	header("Location: index.php?page=home");
	exit();
}
if(loggedin()){
	$user_id = $_SESSION['user_id'];
	$com_id = get_user_field($user_id, "user_com");
	$topic_id = htmlentities($_GET['topic_id']);
	if($topic_id!=0){
		$topic_name = $db->query("SELECT topic_name FROM debating_topics WHERE topic_id = ".$db->quote($topic_id))->fetchColumn();
	}else{
		$topic_name = "All Latest Debates";
	}
	$valid_topics = array();
	$dtype = "Private";

	if(isset($_GET['d'])){
		if($_GET['d']=="g"){
			$dtype = "Global";
			$com_id = 0;
		}	
	}

	if(isset($_GET['view_c'])){
		if(loggedin_as_admin()){
			$com_id = htmlentities($_GET['view_c']);
		}else{
			header("Location: index.php?page=home");
		}
	}

	$path_link1 = ($dtype=="Private")? "index.php?page=private_debating":"index.php?page=private_debating&d=g";

	$get_valid_topics = $db->query("SELECT topic_id FROM debating_topics");
	foreach($get_valid_topics as $topicid){
		$valid_topics[] = $topicid['topic_id'];
	}
	$valid_topics[] = "0";
	if(in_array($topic_id, $valid_topics)){
		?>
		<script>
		$(document).ready(function(){
			var opened = 0;
			var offer_own_vote_opts = true;
			$(".start-debate-option").click(function(){
				if(opened===0){
					opened = 1;
					$(this).animate({backgroundColor:'#FFFFFF'}, 500).animate({color:'#757575'}, 500)
					.animate({width:'470px'}, 500).animate({height:'340px'}, 500);
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
			
			$("#start-deb-submit").mouseover(function(){
				var q = $("#start-deb-question").val();
				
				if(q.length>0&&offer_own_vote_opts==true){
					$.post("<?php echo $ajax_script_loc; ?>", {get_q_type:q}, function(result){
						if(result=="open"){
							$("#vote-opt-offer-box").fadeIn(50);
						}
					});

					$("#offer-vote-opt-no").click(function(){
						offer_own_vote_opts = false;
						$("#vote-opt-offer-box").fadeOut(100);
					});
					$("#offer-vote-opt-yes").click(function(){
						$("#voob-dis1").hide();
						$("#voob-dis2").show();
					});

					$("#cus-vote-save").click(function(){
						var strlist = "";
						for(var i=1;i<=5;i++){
							var opt = $("#cus-vote-opt"+i.toString()).val();
							if(opt.length>0){
								strlist+=","+opt;
							}
						}
						$("#cus-vote-opts-post").attr("value", strlist);
						$("#vote-opt-offer-box").fadeOut(100);
						offer_own_vote_opts = false;
					});
				}
			});
			
		});
		</script>
		
		<?php
			if(loggedin_as_admin()&&$dtype=="Private"){
				$get_coms = $db->query("SELECT com_id, com_name FROM communities");
				echo "<div id = 'choose-p-com-viewbox'>";
					foreach($get_coms as $com){
						echo "<a href = 'index.php?page=private_debating_topic&topic_id=".$_GET['topic_id']."&view_c=".$com['com_id']."'>".$com['com_name']."</a><bR>";
					}
				echo "</div>";
			}

			if(isset($_GET['keep_sd'])){
				?>
					<script>
					$(function(){
						$(".start-debate-option").css("background-color","#FFFFFF").css('color','#757575')
						.animate({width:'470px'}, 0).animate({height:'340px'}, 0);
					
						$("#start-debate-form").show();
						$(".start-debate-option").css("box-shadow", "0px 0px 200px #030303");
					
						$(".start-debate-option").animate({marginLeft:'20%'}, 0);
					});
					</script>
				<?php
			}
		?>

		<br>
		<div class = 'page-path'>Debating > <?php echo "<a style = 'color: #40e0d0' href = '".$path_link1."'>".$dtype; ?> Debating </a> > <?php echo $topic_name; ?></div><br>
		<?php if($topic_id!="0"){ ?>
		<div class = 'start-debate-option no-hyphens'><b>Start Debate</b>

			<form method = "POST" id = "start-debate-form">
			<div id = "vote-opt-offer-box">
				<div id = 'voob-dis1'>
					As your question does not seem to have a straight answer (Yes/No/Agree/Disagree)
					would you like to add your own vote options?<br><br><br>
					<span style = 'font-size: 220%;color: white;'><span id = 'offer-vote-opt-yes'>Yes</span> / <span id = 'offer-vote-opt-no'>No</span></span>
				</div>
				<div id = 'voob-dis2' style = 'display:none;'>
					Enter Vote Options:<br><br>
					<input id = 'cus-vote-opt1' type = 'text' placeholder = 'Option 1' class = 'loggedout-form-fields-snc' style = 'margin-top: 2px; width: 80%;' maxlength = "50"><br>
					<input id = 'cus-vote-opt2' type = 'text' placeholder = 'Option 2' class = 'loggedout-form-fields-snc' style = 'margin-top: 2px; width: 80%;' maxlength = "50"><br>
					<input id = 'cus-vote-opt3' type = 'text' placeholder = 'Option 3' class = 'loggedout-form-fields-snc' style = 'margin-top: 2px; width: 80%;' maxlength = "50"><br>
					<input id = 'cus-vote-opt4' type = 'text' placeholder = 'Option 4' class = 'loggedout-form-fields-snc' style = 'margin-top: 2px; width: 80%;' maxlength = "50"><br>
					<input id = 'cus-vote-opt5' type = 'text' placeholder = 'Option 5' class = 'loggedout-form-fields-snc' style = 'margin-top: 2px; width: 80%;' maxlength = "50"><br>
					<span style = 'font-size:80%'>*Maximum 5 options - leave blank if not needed.</span>
					<br><input id = 'cus-vote-save' type = 'button' value = 'Save' class = 'leader-cp-submit' style = 'width: 70px;border: none;'><br>
				</div>
			</div>
			<span style = "position: absolute;margin-left:420px;margin-top:-40px;z-index:10000;" id = "close-sd">X</span>
				<span style = 'letter-spacing: 1px;font-size: 70%;position: absolute;margin-top:-15px;'>*Use correct punctuation and grammar<br>
				<input type = "text" placeholder="Debate Question..." maxlength = "120" autocomplete="off" 
				spellcheck="false" name = "debate_title" class = "loggedin-form-field1" style = "" id = "start-deb-question">
				<br>
				<textarea name = "debate_text" class = "loggedin-form-field2" placeholder = "Debate Description/Your Argument..."></textarea>
				<br>
				<div class = "loggedin-form-info1"><p>If your reputation is higher than 15, 
					this question will not require any approvel from
					staff/leaders.</p>
				</div>

				<input type = "hidden" name = "cus_vote_opts" value = "" id = "cus-vote-opts-post">
				<input type = "submit" value = "Submit" class = "loggedin-form-submit1" id = "start-deb-submit">
			</form>
		</div>
		<?php } ?>
		<br><br><br>
		<?php
		if(isset($_POST['debate_title'],$_POST['debate_text'])&&$topic_id!="0"){
			$title = htmlentities($_POST['debate_title']);
			$text = htmlentities($_POST['debate_text']);
			$cus_vote_opts = trim(trim_commas(htmlentities($_POST['cus_vote_opts'])));
			if(strlen($cus_vote_opts)<=1){
				$cus_vote_opts = false;
			}

			$errors = false;
			if($com_id==0){
				$extra_get="&d=g";
			}else{
				$extra_get="";
			}
			if(strlen($title)<10){
				$errors = true;
				setcookie("success", "0Your question must be longer.",time()+10);
				header("Location: index.php?page=private_debating_topic&keep_sd=true&topic_id=".$topic_id.$extra_get);	
			}
			if($errors==false){
				$thread_id = create_thread($title, $text, $com_id, $topic_id, $cus_vote_opts);
				if(!empty($thread_id)){
					header("Location: index.php?page=view_private_thread&thread_id=".$thread_id);
					setcookie("success", "1Debate created successfully!",time()+10);
				}else{
					setcookie("success", "0Unknown error.",time()+10);
					header("Location: index.php?page=private_debating_topic&keep_sd=true&topic_id=".$topic_id);
				}
			}
		}

		$topic_query_bounds = array("com_id"=>$com_id);
		if($topic_id=="0"){
			$topic_filter = "";
		}else{
			$topic_filter = " topic_id = :id AND ";
			$topic_query_bounds["id"] = $topic_id;
		}
		$get_threads = $db->prepare("SELECT * FROM debating_threads WHERE ".$topic_filter." com_id = :com_id ORDER BY latest_action DESC");
		$get_threads->execute($topic_query_bounds);
		
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
		
					$qtype = get_question_type($row['thread_title'], 1);
					$dis_vote_opts = get_question_type($row['thread_title'], 2, $row['thread_id']);
					echo "<a href = 'index.php?page=view_private_thread&thread_id=".$row['thread_id']."'><div class = 'thread-container' style= 'padding-top:10px;'>";
					echo "<div class = 'thread-title' style = 'color:".$color.";'>".$row['thread_title']."<br>";
					if($color=="#FF6A6A"){
						echo "<span class = 'non-act-warning'>*untill this debate has been
						 activated/approved<br> by a leader, only you can see it.</span>";
					}	
					echo "</div><br><br>";
					if(count($dis_vote_opts)!=0){
						$dis_votes = "";
						$sec_width = (100/count($dis_vote_opts))-1;
						if($qtype=="open"){
							$colors = array("#5a9999", "#5a9999", "#5a9999", "#5a9999", "#5a9999");
							$vote_vals = merge_cus_vote_vals($row['thread_id']);
						}else{
							$colors = array("#7fdd99","salmon", "#5a9999");
							$vote_vals = array($votes['yes'],$votes['no'],$votes['maybe']);
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
								<div class = 'thread-end-sec' style = 'width:".$sec_width."%;font-size:".$fsize."%;border-left:1px solid #b2b2b2;".$borderright."'>
									<span style = 'color:".$colors[$count].";'>".$opt."</span><br>
									".$vote_val."%
								</div>
							";
							$count++;
						}
						echo "<div class = 'thread-end'>
							<div class = 'voter-amount'>".$total." vote(s)</div>

								".$dis_votes."
							
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