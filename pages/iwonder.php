<?php
if($check_valid!="true"){
	header("Location: index.php?page=home");
	exit();
}
if(loggedin()){
	?>
	<script>
	
	$(document).ready(function(){
		var curr_open = "";

			
		$("#d-sticky").click(function(){
		
			$("#new-sticky-text").val("I wonder...").fadeIn().focus();
			$(".iwonder-s-submit").fadeIn();
			
		});

		$("#new-sticky-text").keyup(function(){
			
			if($(this).val().substring(0, 11)!=="I wonder..."){
				$(this).val("I wonder...");
			}
			
		});
		$(".iwonder-s-submit").css({"margin-top":"75%"});
		$(".iwonder-s-submit").click(function(){
			var text = $("#new-sticky-text").val();
			$.post('<?php echo $ajax_script_loc; ?>', {start_iwonder:text}, function(result){
				if(result.search("success")==true||result.search("-success")==true){
					$("#d-sticky").css("background-image", "none");
					$("#new-sticky-text").remove();
					$(".iwonder-s-submit").html("");
					$(".iwonder-s-submit").animate({marginTop: "-20px", width:"13px", height:"13px"}, 1000, function(){
						$(".iwonder-s-submit").html("<img src = 'ext/images/note-pin.png' class = 'note-pins' style = 'height: 25px;margin-top: -3px;margin-right: -3px;'>");
					});
					var warning = "";
					if(result.search("-")==true){
						warning = "<br><span style = 'color: white;font-size:40%;'>Your question has been saved, however will be invisible to public (appear to you as red) untill approved by a leader.</span>";
						$("#d-sticky").css("background-color", "salmon");
					}
					$("#d-sticky").append("<div style = 'position: absolute;'><span class = 'iwst'>"+text+"</span><span style = 'color:#71C671;font-size:60%;'><br>by <?php echo $username.'<br>'. date('h:ia d M Y ', time()); ?></span>"+warning+"</div>");
					
				}
			});
		});

	});	
	</script>
	<div class = 'page-path'>Debating > I Wonder... </div><br>
	<div id = "iwonder-t" class = "loggedin-headers" style = 'color:#a0784d;'>The I Wonder Sticky Board</div>
	<?php
	$get_threads = $db->prepare("SELECT * FROM iwonder_threads ORDER BY time_created DESC");
	$get_threads->execute();
	$quant = $get_threads->rowCount();
	echo "<div id = 'iwonder-board'>";
	
	?>
	<div id = "d-sticky" class = "iwonder-sticky">
		<div class = "note-pins iwonder-s-submit" style = "height: 15px;">Stick</div>
		<div id ="add-new-sticky-plus"></div>
		<textarea id = "new-sticky-text" maxlength="80"></textarea>
	</div>
	<?php
	$cl = 1;
	$ct = 1;
	$ntop = 0;
	$randl = rand(0,24);
	$randt = rand(-100,-200);
	$note_pos = array();
	if($quant!=0){
		while($row = $get_threads->fetch(PDO::FETCH_ASSOC)){
		
			$username = get_user_field($_SESSION['user_id'], "user_username");
			$user_idp = $db->query("SELECT user_id FROM users WHERE user_username = ".$db->quote($row['thread_starter']))->fetchColumn();
			if($row['thread_starter']==$username||$row['active']=="1"){
				if($row['thread_starter']==$username&&$row['active']=="0"){
					$not_act_msg = true;
					$attr_style_bg = "background-color:#FF6A6A;";
					$sticky_footer_bg = "#FFFFFF";
				}else{
					$sticky_footer_bg = "#71C671";
					$attr_style_bg = "";
				}
				
				//$note_pos[$row['thread_id']]=[$c,$c];
				
			?>
			<?php

				$randl = rand(0,100);
				$randt = -100;

				$cl1 = $cl*24;
				$ct1 = $ct*5;
			
				$cols = 4;
				$nleft = $cl+$cl1+1.5;
			
				if($ct%$cols==0){
					$nleft = 1.5;
					$cl = 0;
					$ntop = $ct+$ct1;
				}

				$note_pos[$row['thread_id']] = [$nleft, $ntop, $cl1];
				
			?>
			<div class = "iwonder-sticky" style = 'position:absolute;margin-left:<?php echo $randl; ?>%;margin-top:<?php echo $randt;?>%;' id = "iws<?php echo $row['thread_id']; ?>" >
				
				<img src = "ext/images/note-pin.png" class = "note-pins">
				<script>
				var deg = Math.floor((Math.random() * 8) + 1).toString();
				if(deg%2==0){
					deg = "-"+deg;
				}
				$("#iws<?php echo $row['thread_id']; ?>").animate({rotate: deg+'deg'}, 100);
				</script>
				<?php
					$perm_to_delete = false;
					if((loggedin_as_admin())||(user_rank($_SESSION['user_id'], "3")==true)&&(get_user_community($_SESSION['user_id'], "com_id")==get_user_community($user_idp, "com_id"))){
						$perm_to_delete = true;
					}else if($_SESSION['user_id']==$user_idp){
						$perm_to_delete = true;
					}
					if($perm_to_delete==true){
						echo "<a style = 'font-size: 50%;color:salmon;' href = 'index.php?page=iwonder&del_q=".$row['thread_id']."'>Delete</a><br>";
					}
					
		
					?>
				<div style = "overflow: hidden;height: 90%;width:95%;<?php echo $attr_style_bg; ?>">
					<span style = "display:none;font-size:60%;float:right;color:salmon;" id = "min-opt<?php echo $row['thread_id']; ?>" class = 'iwonder-min-opt'>X<br></span>
					<span class = 'iwst' id = "iwst<?php echo $row['thread_id']; ?>"><?php echo $row['thread_title']; ?></span>
					<?php echo "<br><span style = 'color:".$sticky_footer_bg.";font-size:60%;'>by  <a style = 'color: #71C671;' href = 'index.php?page=profile&user=".$db->query("SELECT user_id FROM users WHERE user_username = ".$db->quote($row['thread_starter']))->fetchColumn()."'>".$row['thread_starter']."</a><br>
					".date("h:ia d M Y ", $row['time_created'])."</span>";?>
					<br><br>
					<div id = "sticky-content<?php echo $row['thread_id']; ?>" class = "sticky-content">
						<form action = "" method = "POST">
								<textarea placeholder= "I think..." name = "reply_thread" class = "textarea-mreply"></textarea>
								<input type = "hidden" value = "<?php echo $row['thread_id']; ?>" name = "thread_id">
								<input type = "submit" class = "mreply-submit" value = "Submit" >
						</form>
						<div style = 'margin-top:150px;position: relative;'>
						<?php 
							
							$get_replies = $db->prepare("SELECT * FROM iwonder_replies WHERE thread_id = :thread_id ORDER BY time_created ASC");
							$get_replies->execute(array("thread_id"=>$row['thread_id']));
							if($get_replies->rowCount()>0){
								while($row_ = $get_replies->fetch(PDO::FETCH_ASSOC)){
									$muser_idp = $db->query("SELECT user_id FROM users WHERE user_username = ".$db->quote($row_['user_replied']))->fetchColumn();
									$mperm_to_delete = false;
									if((loggedin_as_admin())||(user_rank($_SESSION['user_id'], "3")==true)&&(get_user_community($_SESSION['user_id'], "com_id")==get_user_community($muser_idp, "com_id"))){
										$mperm_to_delete = true;
									}else if($_SESSION['user_id']==$muser_idp){
										$mperm_to_delete = true;
									}
									if(($_SESSION['user_id']!=$muser_idp)&&(!check_c_reported($row_['reply_id'], "reply_id", "iwonder_replies"))){
										?>
											<a style = "color: salmon;font-size:50%;" href = "index.php?page=iwonder&keep_o=<?php echo $row['thread_id']; ?>&repo-c=<?php echo $row_['reply_id']; ?>">
												-Report Abuse
											</a>
										<?php
									}else if(check_c_reported($row_['reply_id'], "reply_id", "iwonder_replies")){
										?>
										<span style = "color: salmon;font-size:50%;" href = "index.php?page=iwonder&keep_o=<?php echo $thread_id; ?>&repo-c=<?php echo $row_['reply_id']; ?>">
												-This comment has been reported.
										</span>
										<?php
									}
						
									if($row_['visible']==1){
										$dis = "norm";
									}else if($row_['user_replied']==get_user_field($_SESSION['user_id'], "user_username")){
										$dis = "red";
										//unapproved but user owner can see.
									}else{
										$dis = false;
									}
									if($dis!=false){
										if($dis=="red"){
											$reply_red_style = "<div style = 'border: 3px solid salmon;'>";
											$red_text = "<span style = 'color: salmon;font-size:11px;'>*NOTE: Untill your community leader has approved this post, only you can see it.</span><br><br>";
										}else{
											$reply_red_style = "<div>";
											$red_text = "";
										}
										echo $reply_red_style;
										echo $red_text;
										if($mperm_to_delete==true){
											echo "<a style = 'font-size: 50%;color:salmon;' href = 'index.php?page=iwonder&del_r=".$row_['reply_id']."&keep_o=".$row_['thread_id']."'>-Delete</a><br>";
										}
										echo $row_['reply_text']."<span style = 'color:#71C671;font-size:70%;'>- by <a style = 'color: #71C671;' href = 'index.php?page=profile&user=".$db->query("SELECT user_id FROM users WHERE user_username = ".$db->quote($row_['user_replied']))->fetchColumn()."'>".$row_['user_replied']."</a> ".date("h:ia d M Y ", $row_['time_created'])."</span></div><hr size = '1'>";
									}
								}
							}else{
								echo "<div style = 'margin-top: 50%;font-size:150%;text-align:center;'>No discussion so far.</div>";
							}
						?>
						</div>
						
					</div>
				</div>
			</div>
			
		<?php
			}
			$cl++;
			$ct++;
		}
		//$board_height = ($ct/4)*50;
		$note_pos = json_encode($note_pos);
	}else{
		echo "<div id = 'no-threads-message'>There are no questions yet.</div>";
	}
	echo "</div>";
	?>
		
	<script>
	$(document).ready(function(){
		var note_is_open = false;
		$('body').css("background-color", "#C89661");
		var note_pos = <?php echo $note_pos; ?>;
		function getRand(min, max) {
    		return Math.random() * (max - min) + min;
		}
		var opened = {};

		function close_note(note_id, temp){
			note_is_open = false;
			if(temp!=true){
				$(".iwonder-sticky").animate({opacity:"1"}, 200);
			}
			$("#min-opt"+note_id).fadeOut();
			opened[note_id] = 0;
			$("#sticky-content"+note_id).hide();
			setTimeout(function(){$("#iws"+note_id).css({"z-index":"100","box-shadow":"none"});}, 500);
			$("#iws"+note_id).animate({width:"20%",height:"21%",marginLeft:note_pos[note_id][0]+"%"}, 400);
		}
		function open_note(note_id ,instant){
			if(!opened[note_id]){
				for(var i in opened){
					$("#iws"+i).css("z-index", "1");
					if(opened[i]==1&&i!=note_id){
						close_note(i, true);
					}
					if(i!=note_id){
						$("#iws"+i).animate({opacity:"0.6"}, 200);
					}else{
						$("#iws"+i).animate({opacity:"1"}, 200);
					}
				}
				note_is_open = true;
				open_time = 400;
				if(instant==true){
					open_time = 0;
				}
				$("#iws"+note_id).animate({rotate: '0deg'}, 0);
				$("#iws"+note_id).css({"z-index":"1000","box-shadow":"0px 0px 100px #2f5653"})
				.animate({width:"50%",height:"100%", marginLeft:'25%'}, open_time);
				$("#min-opt"+note_id).fadeIn();

				$("#sticky-content"+note_id).show();
				var scroll_to = $("#iws"+note_id).offset().top;
				if(instant==true){
					setTimeout(function(){
						scroll_to = $("#iws"+note_id).offset().top;
						$('html, body').scrollTop(scroll_to);
					}, 100);
					
				}else{
					$('html, body').animate({
				        scrollTop: scroll_to
				    }, 400);
				}
				
				opened[note_id] = 1;
			}
		}



		$(".iwonder-sticky").hover(function(){
			var hnote_id = $(this).attr("id").substring(4);
			if(note_is_open == false){
				$(this).animate({rotate: '0deg'}, 100);
			}

		}).mouseleave(function(){
			var hnote_id = $(this).attr("id").substring(4);
			if(note_is_open == false){
				var deg = Math.floor((Math.random() * 8) + 1).toString();
				if(deg%2==0){
					deg = "-"+deg;
				}
				$(this).animate({rotate: deg+'deg'}, 100);
			}
		});
		
		<?php
			$nc = 1;
			$get_thread_ids = $db->query("SELECT thread_id FROM iwonder_threads ORDER BY time_created DESC");
			$jsarray = array();
			foreach($get_thread_ids as $i){
				$jsarray[] = $i[0];
			}
			$jsarray = json_encode($jsarray);	
		?>
		var nids = <?php echo $jsarray; ?>;
		var nspeed;
		var animate = <?php echo (isset($_GET['instant_load'])||isset($_GET['keep_o']))? "false":"true"; ?>;
		
		if(animate==true){
			nspeed = getRand(600,3000);
		}else{
			nspeed = 0;
		}
		for(key in nids){
			var note_id = nids[key];
			if(nspeed>0){
				nspeed = getRand(600,3000);
			}
			opened[note_id] = 0;
			$("#iws"+note_id).animate({marginLeft:note_pos[note_id][0]+"%", marginTop:note_pos[note_id][1]+"%"}, nspeed);
		}
		var dsticky_speed;
		if(animate==true){
			dsticky_speed = 2000;
		}else{
			dsticky_speed = 0;
		}
		setTimeout(function(){$("#d-sticky").fadeIn();},dsticky_speed);

		$(".iwst").click(function(){
			var note_id = $(this).attr("id").substring(4);
			open_note(note_id, false);
		
		});

		$(".iwonder-min-opt").click(function(){
			var note_id = $(this).attr("id").substring(7);
			close_note(note_id, false);
		});	

		<?php
		if(isset($_GET['keep_o'])){
			$id = htmlentities($_GET['keep_o']);
			?>
			

			curr_open = "<?php echo $id; ?>";
			open_note("<?php echo $id; ?>", true);
			


			<?php
		}
		?>
			

	});
	</script>
	
	<?php
	
	$username = get_user_field($_SESSION['user_id'], "user_username");
	
	if(isset($_GET['del_q'])){
		$q_id = htmlentities($_GET['del_q']);
		$user_idp = $db->query("SELECT thread_starter FROM iwonder_threads WHERE thread_id = ".$db->quote($q_id))->fetchColumn();
		$user_idp = $db->query("SELECT user_id FROM users WHERE user_username = ".$db->quote($user_idp))->fetchColumn();
		$perm_to_delete = false;
		if((loggedin_as_admin())||(user_rank($_SESSION['user_id'], "3")==true)&&(get_user_community($_SESSION['user_id'], "com_id")==get_user_community($user_idp, "com_id"))){
			$perm_to_delete = true;
		}else if($_SESSION['user_id']==$user_idp){
			$perm_to_delete = true;
		}
		if($perm_to_delete==true){
			$db->query("DELETE FROM iwonder_threads WHERE thread_id = ".$db->quote($q_id));
			$db->query("DELETE FROM iwonder_replies WHERE thread_id = ".$db->quote($q_id));
			setcookie("success", "1Deleted Successfully!", time()+10);
		}else{
			setcookie("success", "0You do not have permission to delete this question.", time()+10);
		}
		header("Location: index.php?page=iwonder&instant_load=true");
	}
	if(isset($_GET['del_r'], $_GET['keep_o'])){
		$r_id = htmlentities($_GET['del_r']);
		$keep_o = htmlentities($_GET['keep_o']);
		$muser_idp = $db->query("SELECT user_replied FROM iwonder_replies WHERE reply_id = ".$db->quote($r_id))->fetchColumn();
		$muser_idp = $db->query("SELECT user_id FROM users WHERE user_username = ".$db->quote($muser_idp))->fetchColumn();
		$mperm_to_delete = false;
		if((loggedin_as_admin())||(user_rank($_SESSION['user_id'], "3")==true)&&(get_user_community($_SESSION['user_id'], "com_id")==get_user_community($muser_idp, "com_id"))){
			$mperm_to_delete = true;
		}else if($_SESSION['user_id']==$muser_idp){
			$mperm_to_delete = true;
		}
		if($mperm_to_delete==true){
			$db->query("DELETE FROM iwonder_replies WHERE reply_id = ".$db->quote($r_id));
			setcookie("success", "1Deleted Successfully!", time()+10);
		}else{
			setcookie("success", "0You do not have permission to delete this comment.", time()+10);
		}
		header("Location: index.php?page=iwonder&instant_load=true&keep_o=".$keep_o);
	}
	/*if(isset($_GET['start_t'])){
		$text = htmlentities($_GET['start_t']);
		if(strlen($text)>20){
			if(substr($text, 0, 11)=="I wonder..."){
				$active = (user_moderation_status($_SESSION['user_id'])>1)? 0:1;	
				if($active==0){
					$cleaders = get_com_leader_id(get_user_field($_SESSION['user_id'], "user_com"), true);
					foreach($cleaders as $id){
						add_note($id, "There is new content awaiting your approval in the community manager.", "index.php?page=leader_cp&go_to=2");
					}
				}
				$insert = $db->prepare("INSERT INTO iwonder_threads VALUES('', :text, :username, UNIX_TIMESTAMP(), :active)");
				$insert->execute(array("text"=>$text, "username"=>$username, "active"=>$active));
				add_rep(5, $_SESSION['user_id']);
				if(user_moderation_status($_SESSION['user_id'])>1){
					setcookie("success", "1Your question has been sent, however will be invisible to public (appear to you as red) untill approved by a leader.", time()+10);
				}
			}else{
				setcookie("success", "0Your question must start with 'I wonder...'.", time()+10);
			}
			
		}else{
			setcookie("success", "0Your question must be longer.", time()+10);
		}
		
		header("Location: index.php?page=iwonder&instant_load=true");
	}*/
	if(isset($_GET['repo-c'])){
		$reply_id = htmlentities($_GET['repo-c']);
		$reported_by = get_user_field($_SESSION['user_id'], "user_username");
		if(substr($reply_id, strlen($reply_id)-1, strlen($reply_id))=="-"){
			$reply_id = substr($reply_id, 0, strlen($reply_id)-1);
			$reported_by = "BuzzZap Filtering";
		}
		$reported_user = $db->query("SELECT user_replied FROM iwonder_replies WHERE reply_id = ".$db->quote($reply_id))->fetchColumn();
		$reason = "--This content posted by ".$reported_user." is abusive: ". $db->query("SELECT reply_text FROM iwonder_replies WHERE reply_id = ".$db->quote($reply_id))->fetchColumn();
		if(!check_c_reported($reply_id, "reply_id", "iwonder_replies")){
			report_user($reported_by,$reported_user, $reason, array(true,$reply_id,"reply_id", "iwonder_replies"));
			setcookie("success", "1Successfully reported content.", time()+10);
		}else{
			setcookie("success", "1This post has already been reported.", time()+10);
		}
		header("Location: index.php?page=iwonder&instant_load=true&keep_o=".$_GET['keep_o']);
	}	
	if(isset($_POST['reply_thread'])){
		$reply_text = htmlentities($_POST['reply_thread']);
		$thread_id = htmlentities($_POST['thread_id']);
		$report_header = "";
		$check_abuse = contains_blocked_word($reply_text);
		if($check_abuse[0]==true){
			$reply_text = $check_abuse[1];
			$report_header = true;
		}
		if(strlen($reply_text)>10){
			$active = (user_moderation_status($_SESSION['user_id'])==3)? 0:1;	
			if($active==0){
				$cleaders = get_com_leader_id(get_user_field($_SESSION['user_id'], "user_com"), true);
				foreach($cleaders as $id){
					add_note($id, "There is new content awaiting your approval in the community manager.", "index.php?page=leader_cp&go_to=2");
				}
			}
			if(user_not_posted(get_user_field($_SESSION['user_id'], "user_username"))){
				add_badge("Posting for the first time", $_SESSION['user_id'], "you posted for the first time!");
			}
			re_for_p_count_on_post(get_user_field($_SESSION['user_id'], "user_username"));	
			$insert = $db->prepare("INSERT INTO iwonder_replies VALUES('', :thread_id, :reply, :user, UNIX_TIMESTAMP(), :visible)");
			$insert->execute(array("thread_id"=>$thread_id, "reply"=>$reply_text, "user"=>$username, "visible"=>$active));
			add_rep(1, $_SESSION['user_id']);
			if($report_header==true){
				$report_header = "&repo-c=".$db->lastInsertId()."-";
			}
			$thread_starter = $db->query("SELECT thread_starter FROM iwonder_threads WHERE thread_id=".$db->quote($thread_id))->fetchColumn();
			$starter_id =$db->query("SELECT user_id FROM users WHERE user_username=".$db->quote($thread_starter))->fetchColumn();
			$link = "index.php?page=iwonder&keep_o=".$thread_id;
			if($starter_id!=$_SESSION['user_id']){
				$note_m = "The user '".get_user_field($_SESSION['user_id'], "user_username")."' has replied to an I Wonder question you made.";
				add_note($starter_id, $note_m, $link);
			}	
			
			if($active==0){
				$message = "1Your comment will not be visible untill it is approved by your community leader.";

				setcookie("success", $message, time()+10);
			}
		}else{
			$message = "0Your reply must be longer.";

			setcookie("success", $message, time()+10);
		}
		
		header("Location: index.php?page=iwonder&instant_load=true&keep_o=".$thread_id.$report_header);
	}
}else{
	header("Location: index.php?page=home");	
}

?>