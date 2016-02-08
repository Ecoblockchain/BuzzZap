<?php ob_start(); 
if(substr($_SERVER['PHP_SELF'], 0,3)=="/pr"){
	//DEV 
	$ajax_script_loc = "/projects/buzzzap/ajax_script.php";
	$spec_judge_email_link = "http://localhost/projects/buzzzap/";
}else{
	//PROD
	$ajax_script_loc = "../ajax_script.php";
	//ini_set('display_errors', 'Off');
	ini_set("log_errors", 1);
	ini_set("error_log", "php-error.log");
	$spec_judge_email_link= "https://buzzzap.com/";

}
?>
<DOCTYPE html>
	<html>
		<head>
			<title> BuzzZap Online Debating</title>
			<meta name="viewport" content="width=device-width, initial-scale=1">
			<meta name="description" content="Online debating">
			<meta name="keywords" content="BuzzZap, debating, debate, schools, community, forum, online">
			<link rel="shortcut icon" type="image/png" href="/favicon.ico"/>
		</head>
		<body>
			<?php
			require("requires.php");

			if( (get_feature_status("site")=="1") && (!isset($_SESSION['pass_site_d'])) && (isset($_SESSION['admin_key'])==false) ){
				header("Location: site_disabled.php");
				exit();
			}
			if((isset($_GET['page']))&&(valid_page($_GET['page']))){
				if(substr($_SERVER['PHP_SELF'], 0,3)!="/pr"){
					if(get_static_content("last_errorf_size")<filesize("php-error.log")){
						send_admin_note("There are new errors in the error log file");
						change_static_content("last_errorf_size", filesize("php-error.log"));
					}
				}
				$page = "pages/".htmlentities($_GET['page']).".php";
				
					if((loggedin())&&(loggedin_as_admin()==false)){
						if(isset($_GET['login_error'])){
							
							if(substr($_GET['login_error'], 0, 8)=='lheader-'){
								$h = str_replace("~", "&", $_GET['login_error']);
								header("Location: ".substr($h,8));
							}	
						}

						$user_id = $_SESSION['user_id'];
						$com_id = get_user_field($user_id, "user_com");
						if($db->query("SELECT com_id FROM com_profile WHERE com_id = ".$com_id)->rowCount()==0){
							$leaders = $db->query("SELECT user_username FROM users WHERE user_com = ".$db->quote($com_id)." AND user_rank = 3");
							$leaders_str = "";
							foreach($leaders as $row){
								$leaders_str.=",".$row['user_username'];
							}
							$insert = $db->prepare("INSERT INTO com_profile VALUES('',:com_id, :name, '','','','0,0',:leader, '', '')");
							$insert->execute(array("com_id"=>$com_id, "name"=>get_user_community($user_id, "com_name"),"leader"=>trim_commas($leaders_str)));
						}

						$pos_rep = $db->query("SELECT com_rep FROM com_profile WHERE com_id = ".$db->quote($com_id))->fetchColumn();
						$acc_rep = get_com_rep($com_id);
						if($acc_rep!=$pos_rep){
							update_com_profile($com_id, "com_rep",$acc_rep );
						}
						?>
		
							<script type="text/javascript">
							$(document).ready(function(){
								$("#debating-li").hover(function(){
									$("#nav-sub1").css("display", "block");
								});
			
								$("#mitem1").mouseover(function(){
									$("#note-bubble").hide();
									$("#note-bubble1,#note-bubble2,#note-bubble3").css("display", "inline");
								}).mouseleave(function(){
									$("#note-bubble").css("display", "inline");
								});
								
								$("#report-problem-link").click(function(){
									$("#report-problem-form").fadeIn();
								});
								$("#close-report-p").click(function(){
									$("#report-problem-form").fadeOut();
								});
							});
							</script>
					<div class = 'loggedin-body'>
						<div class = "menu">
							<div class = "mitem-container">
								<div class = "mitem" id = "mitem1">

									<a href="index.php?page=home">
										<?php
										 	$total =get_unread_pm_quant($user_id) + count(get_pending_friends(get_user_field($user_id, "user_username")))+get_unread_notes($user_id, $quant=true);
										 	if($total>0){
												echo " <div class= 'note-bubble' id = 'note-bubble' style = 'margin-top:4px;margin-left:30px;'>".$total."</div>";
											}
										?>
										<div id "mitem1sub1" class = "subitem" style = "padding-top:20px;" hoverc = "#bc5a9b"><?php echo get_user_field($user_id, "user_username"); ?></div>
									</a>

									<a href="index.php?page=profile&user=<?php echo $_SESSION['user_id']; ?>">
										<div class= 'note-bubble' id = 'note-bubble3' style = 'margin-top:5px;margin-left:50px;'>
											<?php
											
											$friend_p_q = count(get_pending_friends(get_user_field($user_id, "user_username")));
											if($friend_p_q>0){
												echo $friend_p_q;
											}
											?>
										</div>
										<div id "mitem1sub2" style = "padding-top:25px;" class = "subitem usubitem">My Profile</div>
									</a>

									<a href="index.php?page=inbox">
										<div class= 'note-bubble' id = 'note-bubble1' style = "margin-left: 30px">
											<?php if(get_unread_pm_quant($user_id)>0){echo get_unread_pm_quant($user_id);}?>
										</div>
										<div id "mitem1sub3" class = "subitem usubitem">Inbox</div>
									</a>

									<a href="index.php?page=notifications">
										<div class= 'note-bubble' id = 'note-bubble2'>
											<?php
												$note_count = get_unread_notes($user_id, $quant=true);
												 if($note_count>0){
													echo $note_count;
												 }
											?>
										</div>
										<div id "mitem1sub4" class = "subitem usubitem">Notifications</div>
									</a>

								</div>
							</div>
							<div class = "mitem-container">
								<div class = "mitem" id = "mitem2">
									<a href="index.php?page=home">
										<div id "mitem1sub1" class = "subitem" style = "padding-top:20px;"  hoverc = "#049a77">Debating</div>
									</a>
									<a href="index.php?page=private_debating">
										<div id "mitem2sub2" class = "subitem usubitem" style = "">Private Debating</div>
									</a>
									<a href="index.php?page=private_debating&d=g">
										<div id "mitem2sub3" class = "subitem usubitem">Global Debating</div>
									</a>
									<a href="index.php?page=comp_home&type=0">
										<div id "mitem2sub4" class = "subitem usubitem">Private Competitions</div>
									</a>
									<a href="index.php?page=comp_home&type=1">
										<div id "mitem2sub4" class = "subitem usubitem">Global Competitions</div>
									</a>
									<a href="index.php?page=wof">
										<div id "mitem2sub4" class = "subitem usubitem">Wall Of Fame</div>
									</a>
									<a href="index.php?page=iwonder">
										<div id "mitem4sub1" class = "subitem usubitem">I Wonder...</div>
									</a>
								</div>
							</div>
							<div class = "mitem-container">	
								<div class = "mitem" id = "mitem3">
									<a href="index.php?page=private_groups&com=<?php echo get_user_field($_SESSION['user_id'],'user_com'); ?>">
										<div id "mitem3sub1" class = "subitem"  style = "padding-top:20px;" hoverc = "#e94f42"><?php echo get_user_community($user_id, "com_name"); ?></div>
									</a>
								</div>
							</div>
							
							<div class = "mitem-container">
								<div class = "mitem" id = "mitem4">
									<a href="index.php?page=logout">
										<div id "mitem4sub1" class = "subitem" style = "padding-top:20px;" hoverc="#045ca2">Logout</div>
									</a>
								</div>
							</div>

							<?php
								if(user_rank($_SESSION['user_id'], 3,"just")){
									?>
										<div class = "mitem-container" style = 'float:right;width:120px;font-size:80%;'>
											<div class = "mitem admin-links" id = "mitem5">
												<a href="index.php?page=leader_cp">
													<div id "mitem5sub1" class = "subitem" mouseeffect = "false" style = "padding-top:10px;">Community Manager</div>
												</a>
											</div>
										</div>
									<?php	
								}
							?>
					
						</div>
						<br>

						<script>
						$(function(){
							$("#mitem1").mouseover(function(){
								$(this).animate({height:"230px"}, 200);

							}).mouseleave(function(){
								$(this).animate({height:"58px"}, 200);
							});
							$("#mitem2").mouseover(function(){
								$(this).animate({height:"390px"}, 200);

							}).mouseleave(function(){
								$(this).animate({height:"58px"}, 200);
							});

							$(".subitem").mouseover(function(){
								if($(this).attr("mouseeffect") != "false"){
									var hoverc = $(this).attr("hoverc");
									if(hoverc==undefined){
										hoverc = "pink";
									}
									$(this).css("background-color", hoverc).css("color", "white");
								}
							}).mouseleave(function(){
								if($(this).attr("mouseeffect") != "false"){
									$(this).css("background-color", "#d1ecf0").css("color", "#3a8187");
								}	
							});
						});
						</script>	
				
			
					<div class = "loggedin-inner-container">

						<form id = "report-problem-form" method = 'POST'>
							<span id = 'close-report-p' style = 'float:right;color: salmon;cursor: pointer;'>x</span> 
							<textarea placeholder = "Explain problem in as much detail as possible..." name = "problem" id = "about-me-textarea" style = 'width:100%;height:100%;'></textarea>
							<input type = "submit" value="Report"  class = "leader-cp-submit">
						</form>

						<?php 
							if(isset($_POST['problem'])){
								$txt = htmlentities($_POST['problem']);
								if(strlen($txt)>5){
									$errorfile = fopen("php-error.log", "a+");
									fwrite($errorfile, date("Y-m-d", time()).": Manual Report: ".$txt."\n\n");
									fclose($errorfile);
									setcookie("success", "1Successfully reported problem. Thank you for your feedback.",time()+10);
								}else{
									setcookie("success", "0You have not supplied enough detail.", time()+10);
								}
								header("Location: index.php?page=".$_GET['page']);
							}
						?>

				<?php





				}else if(loggedin_as_admin()==true){
					?>
						<div class = "admin-body">
							<?php include("admin_panel.php"); ?>
						</div>
					<?php
				}
		
		
		
		
				function success_message($message, $icon, $dur = 7500, $click_close = true){
					if(preg_match("/<form/", $message)){
						$dur = 10000000;
						$click_close= false;	
					}
					if($icon =="1"){
						$icon = "<img src= 'http://i73.servimg.com/u/f73/17/40/86/18/tick10.png' height = '190' width = '215'><br>";
					}else if($icon=="0"){
						$icon = "<img src= 'http://i73.servimg.com/u/f73/17/40/86/18/error10.png' height = '120' width = '120'><br><br>";
					}else{
						$icon = "";	
					}
					?>
					<div id = "quick-msg" class = "quick-success-msg" style= "">
						<center>
							<?php echo $icon; ?>
							<?php echo $message; ?>
						</center>
					</div>
					<script>
						setTimeout(function(){
							$("#quick-msg").fadeOut(300);
						}, <?php echo $dur; ?>);
						<?php
						if($click_close==true){
						?>
				
							$(document).click(function(){
								$("#quick-msg").fadeOut(300);
							});
				
						<?php
						}
						?>
					</script>
					<?php
				}
				if(loggedin()){
					if(isset($_COOKIE['success'])){
		
						$icon = substr($_COOKIE['success'], 0, 1);
						$text = substr($_COOKIE['success'], 1);
						success_message($text, $icon);
						setcookie("success", "", time()-100);
					}
				}
				if(loggedin_as_admin()&&($_GET['page']!="home"&&$_GET['page']!="logout")){
					header("Location: index.php?page=home");
				}
				if($_GET['page']!="ajax_script"){
					if(isset($_GET['tour'])){
						//--custom 
						$page_name = $_GET['page'];
						if($page_name=="private_debating"&&isset($_GET['d'])){
							$page_name = $page_name."&d=g";
						}
						if($page_name=="comp_home"&&isset($_GET['type'])){
							$page_name = $page_name."&type=".$_GET['type'];
						}
						//--
						$next_page = $db->query("SELECT next_page FROM tour_content WHERE page_name=".$db->quote($page_name))->fetchColumn();
						$np_corrections = array("profile"=>"profile&user=".$_SESSION['user_id']);
				
						if(array_key_exists($next_page, $np_corrections)){
							$next_page = $np_corrections[$next_page];
						}
						if(!preg_match('/&tour=end/', $next_page)){
							$next_page = $next_page."&tour=true";
						}
						if($_GET['tour']=="true"){
							$text = get_static_content($db->query("SELECT text FROM tour_content WHERE page_name=".$db->quote($page_name))->fetchColumn());
							$link = "<a href = 'index.php?page=".$next_page."'>Next >> </a>";
						}else if($_GET['tour']=="end"){
							$text = "That is the end of the tour. For more information you can read the site manual, which contains great detail into every feature.";
							$link = "<a href = 'index.php?page=home'>Close </a>";
						}
					
					?>
						<div class = "tour-text-box">
							<?php echo $text;?>
							<br><?php echo $link; ?>
						</div>
					<?php	
					}
					if(!loggedin_as_admin()||$_GET['page']=="logout"){
						include($page);
						echo "<br>";
					}
				}else{
					header("Location: index.php?page=home");
				}
		
				?>

				<?php
				if(loggedin()){
					?>
					</div>
					<div id = "footer" style = 'border: 1px solid lightblue;'>
						<span class = "footer-links" id = "report-problem-link" style = 'cursor: pointer;'>Report Problem</span>
						&middot;
						<a href = 'index.php?page=home&tour=true' class = "footer-links">Take Site Tour </a>
						&middot;
						<a href = 'ext/buzzzap_site_manual.pdf' class = "footer-links">Read Site Manual</a>
						&middot;
						<a href = 'index.php?page=logout&sub_p=5' class = "footer-links">Contact Us</a>
						&middot;
						<a href = 'index.php?page=logout&sub_p=3' class = "footer-links">About Us</a>
						&middot;
						<a href = 'index.php?page=logout&sub_p=6' class = "footer-links">Terms of Use</a>
						&middot;
						<a href = 'index.php?page=logout&sub_p=1' class = "footer-links">BuzzZap News</a>
					</div>
					<br>
			
				</div>
			</body>
		<?php
	}
	
}else{
	header("Location: index.php?page=home");
}

?>
</html>