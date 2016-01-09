<?php ob_start(); error_reporting(0);?>
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
				$page = "pages/".htmlentities($_GET['page']).".php";
				//IPN
				// STEP 1: read POST data

				// Reading POSTed data directly from $_POST causes serialization issues with array data in the POST.
				// Instead, read raw POST data from the input stream. 
				/*$raw_post_data = file_get_contents('php://input');
				$raw_post_array = explode('&', $raw_post_data);
				$myPost = array();
				foreach ($raw_post_array as $keyval) {
				  $keyval = explode ('=', $keyval);
				  if (count($keyval) == 2)
					 $myPost[$keyval[0]] = urldecode($keyval[1]);
				}
				// read the IPN message sent from PayPal and prepend 'cmd=_notify-validate'
				$req = 'cmd=_notify-validate';
				if(function_exists('get_magic_quotes_gpc')) {
				   $get_magic_quotes_exists = true;
				} 
				foreach ($myPost as $key => $value) {        
				   if($get_magic_quotes_exists == true && get_magic_quotes_gpc() == 1) { 
						$value = urlencode(stripslashes($value)); 
				   } else {
						$value = urlencode($value);
				   }
				   $req .= "&$key=$value";
				}

				$txt = fopen("res.txt", "w");
				fwrite($txt, $req);
				// Step 2: POST IPN data back to PayPal to validate

				$ch = curl_init('https://www.paypal.com/cgi-bin/webscr');
				curl_setopt($ch, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_1);
				curl_setopt($ch, CURLOPT_POST, 1);
				curl_setopt($ch, CURLOPT_RETURNTRANSFER,1);
				curl_setopt($ch, CURLOPT_POSTFIELDS, $req);
				curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 1);
				curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 2);
				curl_setopt($ch, CURLOPT_FORBID_REUSE, 1);
				curl_setopt($ch, CURLOPT_HTTPHEADER, array('Connection: Close'));

				// In wamp-like environments that do not come bundled with root authority certificates,
				// please download 'cacert.pem' from "http://curl.haxx.se/docs/caextract.html" and set 
				// the directory path of the certificate as shown below:
				// curl_setopt($ch, CURLOPT_CAINFO, dirname(__FILE__) . '/cacert.pem');
				if( !($res = curl_exec($ch)) ) {
					// error_log("Got " . curl_error($ch) . " when processing IPN data");
					curl_close($ch);
					exit;
				}
				curl_close($ch);
				*/
				//--
					if((loggedin())&&(loggedin_as_admin()==false)){
						end_comps_rel_to_user();
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
						<a href = 'index.php?page=home&tour=true' class = "footer-links">Take Site Tour </a>
						&middot;
						<a href = 'ext/buzzzap_site_manual.pdf' class = "footer-links">Read Site Manual</a>
						&middot;
						<a href = 'index.php?page=logout&sub_p=5' class = "footer-links">Contact Us</a>
						&middot;
						<a href = 'index.php?page=logout&sub_p=3' class = "footer-links">About Us</a>
						&middot;
						<a href = 'index.php?page=logout&sub_p=7' class = "footer-links">Terms of Use</a>
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