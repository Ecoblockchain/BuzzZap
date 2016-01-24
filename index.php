<?php ob_start(); 
if(substr($_SERVER['PHP_SELF'], 0,3)=="/pr"){
	//DEV 
	$ajax_script_loc = "/projects/buzzzap/ajax_script.php";
	$spec_judge_email_link = "http://localhost/projects/buzzzap/";
}else{
	//PROD
	$ajax_script_loc = "../ajax_script.php";
	ini_set('display_errors', 'Off');
	ini_set("log_errors", 1);
	ini_set("error_log", "php-error.log");
	$spec_judge_email_link= "http://buzzzap.com/";
}
?>
<DOCTYPE html>
	<html>
		<head>
			<title> BuzzZap Online Debating</title>
			<meta name="viewport" content="width=1300, initial-scale=1">
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
				if(get_static_content("last_errorf_size")<filesize("php-error.log")){
					send_admin_note("There are new errors in the error log file");
					change_static_content("last_errorf_size", filesize("php-error.log"));
				}
				$page = "pages/".htmlentities($_GET['page']).".php";
				
					if((loggedin())&&(loggedin_as_admin()==false)){
						
						$user_id = $_SESSION['user_id'];
						?>
		
							<script type="text/javascript">
							$(document).ready(function(){
								$("#debating-li").hover(function(){
									$("#nav-sub1").css("display", "block");
								});
			
								$("#item1").mouseover(function(){
									$("#note-bubble").hide();
									$("#note-bubble1,#note-bubble2,#note-bubble3").css("display", "inline");
								}).mouseleave(function(){
									$("#note-bubble").css("display", "inline");
								});
								$("#mbox1").mouseover(function(){
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
						<nav class = "nav-container">
				
							<ul id="menu" class = "menu">
						
							  <li id = 'lilist1'>
							  <a href="index.php?page=home" id = 'item1'>
							  <?php echo get_user_field($user_id, "user_username"); ?>
							  <?php 
					  
							 $total =get_unread_pm_quant($user_id) + count(get_pending_friends(get_user_field($user_id, "user_username")))+get_unread_notes($user_id, $quant=true);
							 if($total>0){
								 echo " <div class= 'note-bubble' id = 'note-bubble'>".$total."</div>";
							 }
							  ?></a>
								<ul id = 'mbox1'>
									<li>
										<a href="index.php?page=profile&user=<?php echo $_SESSION['user_id']; ?>">My Profile
											<div class= 'note-bubble' id = 'note-bubble3' style = ''>
													<?php
													 $friend_p_q = count(get_pending_friends(get_user_field($user_id, "user_username")));
													 if($friend_p_q>0){
														echo $friend_p_q;
													 }
													 ?>
											</div>
										</a>
									</li>
								
									<li>
										<a href="index.php?page=inbox">Inbox
											<div class= 'note-bubble' id = 'note-bubble1'>
												<?php if(get_unread_pm_quant($user_id)>0){echo get_unread_pm_quant($user_id);}?>
											</div>
										</a>
									</li>
							
									<li>
										<a href="index.php?page=notifications">
											Notifications
											<div class= 'note-bubble' id = 'note-bubble2'>
												<?php
											
													$note_count = get_unread_notes($user_id, $quant=true);
													 if($note_count>0){
														echo $note_count;
													 }
												?>
											</div>	
										</a>
									</li>
							

								</ul>
					  
							  </li>
					 
							  <li><a href="" id = 'item2'>Debating</a>
								<ul>
									<li><a href="index.php?page=private_debating">Private Debating</a></li>
									<li><a href="index.php?page=comp_home&type=0">Private Competitions</a></li>
									<li><a href="index.php?page=private_debating&d=g">Global Debating</a></li>
									<li><a href="index.php?page=comp_home&type=1">Global Competitions</a></li>
								</ul>
							  </li>
							  <li><a href="index.php?page=private_groups" id = 'item3'><?php echo get_user_community($user_id, "com_name"); ?> Groups</a></li>
							   <li><a href="index.php?page=iwonder" id = 'item4'>I Wonder...</a>
							  <li><a href="index.php?page=logout" id = 'item5'>Logout</a></li>
							  <?php
								if(user_rank($_SESSION['user_id'], 3,"just")){
									?>
										<div class = "admin-links">
											<a href = "index.php?page=leader_cp">Community Manager</a>
										</div>
									<?php	
								}
								?>
							</ul>
					
						</nav>	
				
			
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
							$text = $db->query("SELECT text FROM tour_content WHERE page_name=".$db->quote($page_name))->fetchColumn();
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
					<div id = "footer">
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