<?php
if(!loggedin()){
?>
		<script type="text/javascript">
		$(document).ready(function(){
			$("#r1").rotate(315);
			$("#r6").rotate(65);
			$("#r2").rotate(210);
			$("#r5").rotate(190);
			$("#r3").rotate(240);
			$("#r4").rotate(120);
			$("#r7").rotate(230);
			var dot_pos = [];
			function check_dot_pos(){
				if($( window ).width()<=1250){
					dot_pos = ["-30", "10", "104", "199", "320", "415"];
				}else{
					dot_pos = ["", "12", "108", "205", "332", "430"];
				}
			}
			check_dot_pos();
			$( window ).resize(function() {
				check_dot_pos();
			});
				
			$(".loggedout-main-container").fadeIn(2000);
			
			$('.section').windows({
				snapping: true,
				snapSpeed: 500,
				snapInterval: 200,
				onScroll: function(scrollPos){
					// scrollPos:Number
				},
				onSnapComplete: function($el){	

					$("#nav-bar-out").fadeIn();
					var key = $el.attr("id").substring(7);
					
					if(key!=="1"){
						
						$("#opt-sel").animate({marginTop:dot_pos[key-1]+"px"}, 1000);
					
					}else{
						$("#nav-bar-out").fadeOut();	
					}
					
					
				},
				onWindowEnter: function($el){
					// when new window ($el) enters viewport
				}
				
            });
            var old_pos = 0;
            
            
				
			$("#loggedout-paragraph-container1").click(function(){
				$("#form2, #form3").fadeOut(300);
				$("#loggedout-form-container").slideDown(1000);
				setTimeout(function(){
					$("#forloggedout-paragraph-container1").fadeIn(1400);
				}, 1000); 
				$("#loggedout-form-container").animate({height:'410px'});


			});
			$("#loggedout-paragraph-container3").click(function(){
				$("#loggedout-form-container").slideDown(1400);
				$("#loggedout-form-container").animate({height:'670px'});
				$("#forloggedout-paragraph-container1, #form3").fadeOut(1000);
				setTimeout(function(){
					$("#form2").fadeIn(1400);
				}, 1000);

				
			
			});
			$("#close-loggedout-form-container").click(function(){
				$("#loggedout-form-container").slideUp(1400);
				$("#forloggedout-paragraph-container1, #form2, #form3").fadeOut(600);
			});
		
			
			<?php
				
				for ($counter = 0; $counter <= 7; $counter++) {
					?>
					var colors = ["#836FFF", "#98F5FF", "#98FB98", "#FF7256", "#BDB76B", "#EEA9B8"];
					var rarray = ["1", "3", "5", "5", "2", "1", "2"];
					$("#loggedout-paragraph-container<?php echo $counter; ?>").hover(function(){
						$("#r<?php echo $counter; ?>").fadeIn(300);
	
					});

					$("#loggedout-paragraph-container<?php echo $counter; ?>").mouseleave(function(){
						$("#r<?php echo $counter; ?>").fadeOut(300);
					});
					<?php
				}
				
			
			$counter=0;
			
			while($counter<=5){
				?>	
				var link_to_pos1 =["5","7","4","2","6"];
				var link_to_pos2 = ["2","3", "4","5","6"];
				//var dot_pos = ["", "12", "114", "216", "350", "453"];
				
				$("#loggedout-paragraph-container"+link_to_pos1[<?php echo $counter; ?>]).click(function(){
					
					$('html, body').animate({
						scrollTop: $("#section"+link_to_pos2[<?php echo $counter; ?>]).offset().top
					}, 2000);
					$("#opt-sel").animate({marginTop:dot_pos[<?php echo $counter+1; ?>]}, 3000)
					
					
					setTimeout(function(){
						$("#nav-bar-out").fadeIn();			
					 }, 500);
					 
				});
			
			
			<?php 
			$counter++;
		}
			
		
		
		$i = 1;
		while($i<=5){
	
			?>
			
		//	var dot_pos = ["", "12", "114", "216", "350", "453"];
			$("#nbo<?php echo $i; ?>").click(function(){
				$('html, body').animate({
					scrollTop: $("#section<?php echo $i+1; ?>").offset().top
				}, 2000);
				
				$("#opt-sel").animate({marginTop:dot_pos[<?php echo $i; ?>]}, 2000);
				
				
				
			});
			
			<?php
			$i++;
		}
		
		?>
			$("#home-link-out").click(function(){
				
				$('html, body').animate({
					scrollTop: $("#section1").offset().top
				}, 2000);
				$("#nav-bar-out").fadeOut();				
			});
			
			//--EXPLORE
			
			
			
			<?php
				$debates_explore = json_encode(get_rand_debates(10));
				
			?>
			var debates_explore = <?php echo $debates_explore; ?>;	
			
				//--
				
				
			<?php
			
			if(isset($_GET["go_to"])&&$_GET["go_to"]<7){
			
					?>
					$("#nav-bar-out").fadeIn();
					$('html, body').animate({
						scrollTop: $("#section<?php echo $_GET['go_to']; ?>").offset().top
					}, 0);
				
			<?php		
			}
			?>
			
			$("#forgot-pass-link").click(function(){
				$("#forloggedout-paragraph-container1, #form2").fadeOut(600);
				setTimeout(function(){
					$("#form3").fadeIn();
				}, 700);
				
				
			});
		});
	</script>
	<div id = "section1" class = "section">
		<center>
			<div class = "loggedout-main-container"  style = "display: none;">
			
				<div class  = 'loggedout-form-container' id  = 'loggedout-form-container'>
					<br><br>
					<span class = "close-loggedout-form-container" id = "close-loggedout-form-container">Close</span><br>
					<div id = "forloggedout-paragraph-container1" style = "display:none;">
						<form method = "POST">
							<span id = 'forml1'><span id = 'login-label-1'>Username:</span></span><br><br>
							<input type= "text" autocomplete="off" spellcheck="false" name = "username"  id = "t1" class = "loggedout-form-fields"><br><br>
							
							<span id = 'forml1'><span id = 'login-label-2'>Your Password:</span></span><br><br>
							<input type= "password" autocomplete="off" spellcheck="false" name = "password" id = "t2" class = "loggedout-form-fields"><br><br>	

							<span id = 'forml1'><span id = 'login-label-3'>Community Code:</span></span><br><br>
							<input type= "password" autocomplete="off" spellcheck="false" name = "com_pass"  id = "t3" class = "loggedout-form-fields"><br><br>	

							<input type = "submit" value = "Login" class = "loggedout-form-submit" id = 'login-submit'><span id = 'forgot-pass-link'>Forgot Password?</span>

							<?php

							if(isset($_GET['login_error'])){
								$loggin_error_text = "Error logging in, check your details and try again.";
								if($_GET['login_error']=="banned"){
									$loggin_error_text = "Your account has been suspended.";
								}else if($_GET['login_error']=="discom"){
									$loggin_error_text = "The community you are trying to login to is <br>deactivated. Please contact your community <br>leader or BuzzZap administration.";
								}else if($_GET['login_error']=="disabled"){
									
									$message = $db->query("SELECT message FROM feature_activation WHERE feature='login'")->fetchColumn();
									$loggin_error_text = $message."<br> To bypass this, type in the passcode:<br>
									<form action = '' method = 'POST'>
										<input type = 'text' name = 'pass_dl' style = 'border:none;'>
										<input type = 'submit' style = 'border:none;'>
									</form>
									";
								
								if(isset($_POST['pass_dl'])){
									$pass = htmlentities($_POST['pass_dl']);
									$get_true_pass = $db->query("SELECT pass FROM feature_activation WHERE feature='login'")->fetchColumn();
									if($pass===$get_true_pass){
										$_SESSION['pass_dl']="true";
										header("Location: index.php?page=home&login_error=disabled");
									}else{
										echo "error";
									}
								}	
							
								if(isset($_SESSION['pass_dl'])){
									$loggin_error_text = "Correct code. Try to login again.";
								}
							}
								
							?>
								<script type="text/javascript">
									$(document).ready(function(){
										$("#loggedout-form-container").css("display", "block");
										$("#forloggedout-paragraph-container1").css("display", "block");
										$("#loggedout-form-container").css("height", "470px");		
									});
								</script>
								<div style = "height:10px;"></div>
								<div id = "loggedout-error" class = "loggedout-error">
									<span class = "loggedout-error-content">
										<center><?php echo $loggin_error_text; ?></center>
									</span>
								</div>
							<?php
							}
							?>	
						</form>
					</div>
					<div id = "form3" style = "display: none;">
						<form action = "" method = "POST">
							<br><br><br>
							<span id = 'forml1' style = "text-align: left;">
									Please enter your username or email<br>
									and we will send you a new password<br>
									which you can then change later.
							</span><br><br>
							<input type= "text" autocomplete="off" spellcheck="false" placeholder = "username/email" name = "forgot_pass"  class = "loggedout-form-fields"><br><br>
							<input type = "submit" value = "Go" class = "loggedout-form-submit">
						</form>
						
						<?php
							if(isset($_POST['forgot_pass'])){
								?>
								<script>
									$("#loggedout-form-container").show();
									$("#forloggedout-paragraph-container1, #form2").hide();
									$("#form3").fadeIn();
								</script>
								<?php
								$ident = htmlentities($_POST['forgot_pass']);
								$get_email = $db->query("SELECT user_email FROM users WHERE user_username = ".$db->quote($ident)." OR user_email = ".$db->quote($ident))->fetchColumn();
								if(!empty($get_email)){
									$get_name = $db->query("SELECT user_firstname FROM users WHERE user_email = ".$db->quote($get_email))->fetchColumn();
									$get_uname = $db->query("SELECT user_username FROM users WHERE user_email = ".$db->quote($get_email))->fetchColumn();
									
									$reset_id = md5(encrypt(time()));
									$db->query("INSERT INTO password_resets VALUES('',".$db->quote($get_uname).", ".$db->quote($reset_id).", UNIX_TIMESTAMP()+7200)");
									$link = "www.buzzzap.com?page=home&resetp=".$reset_id;
									$headers  = 'MIME-Version: 1.0' . "\r\n";
									$headers .= 'Content-type: text/html; charset=iso-8859-1' . "\r\n";
									$headers .= "From: Administration@buzzzap.com" . "\r\n";
									$body = "Dear ".$get_name.", <br> BuzzZap has recieved your request to reset your password. Please follow this link below:<br> ".$link."<br> Thank you!";
					
									mail($get_email,"BuzzZap Password Reset",$body,$headers);
									echo "<div class = 'loggedout-error' style = 'background-color: #66cdaa;'>Successfully sent confirmation email.</div>";
									?>
									<script>
										setTimeout(function(){$("#loggedout-form-container").slideUp(2000);}, 2000);
									</script>
									<?php
								}else{
									echo "<div class = 'loggedout-error'>There is no account with that email or username</div>";
								}
							}
							
							
							
							if(isset($_GET['resetp'])){
								$reset_id = htmlentities($_GET['resetp']);
								$user = $db->query("SELECT username FROM password_resets WHERE reset_id = ".$db->quote($reset_id))->fetchColumn();
								if($user){
									$new_pass = substr(md5(encrypt(time())), 0,8);	
									$db->query("UPDATE users SET user_password = ".$db->quote(encrypt($new_pass))." WHERE user_username = ".$db->quote($user));
									$db->query("DELETE FROM password_resets WHERE reset_id = ".$db->quote($reset_id)." AND username = ".$db->quote($user));
									?>
									<script>
										$("#loggedout-form-container").show();
										$("#forloggedout-paragraph-container1, #form2").hide();
										$("#loggedout-form-container").html("<a style = 'float:left;color:grey;cursor:pointer;' href = 'index.php?page=home'>Close</a><div style = 'margin-top: 100px; font-size: 130%; color: grey;letter-spacing:2px;width:400px;white-space:normal;'>Your new password is:<br><b><?php echo $new_pass; ?></b><br>You can now login and change your password to what you want.</div>");
									</script>
									<?php
								}else{
									header("Location: index.php?page=home");
								}
							}
							
							refresh_password_resets();
						?>
					</div>
					<div id = "form2" style = "display:none;">
					<?php
						if((get_feature_status("new_users")=="0")||(isset($_SESSION['pass_dnu']))){
					?>
							<form action = "" method = "POST" id = "form2_">
								
								<span id = 'forml1'>Username:</span><br>
								<input type= "text" maxlength = "11" autocomplete="off" spellcheck="false" name = "username_"  class = "loggedout-form-fields"><br><br>

								<span id = 'forml1'>Fullname:</span><br>															
								<input type= "text" autocomplete="off" spellcheck="false" name = "firstname_"  style  = "width:140px;" class = "loggedout-form-fields">
								<input type= "text" autocomplete="off" spellcheck="false" name = "lastname_"  style  = "width:140px;" class = "loggedout-form-fields"><br><br>

								<span id = 'forml1'>Email:</span><br>															
								<input type= "text" autocomplete="off" spellcheck="false" name = "email_"  class = "loggedout-form-fields"><br><br>

								<span id = 'forml1'>Password:</span><br>															
								<input type= "password" autocomplete="off" spellcheck="false" name = "password_"  class = "loggedout-form-fields"><br><br>

								<span id = 'forml1'>Verify Password:</span><br>															
								<input type= "password" autocomplete="off" spellcheck="false" name = "vpassword_"  class = "loggedout-form-fields"><br><br>


								<span id = 'forml1'>Community Name:</span><br>
								<select name = "com_name_" class = "loggedout-form-fields">
									<?php
										$get_coms = $db->query("SELECT com_name FROM communities ORDER BY com_name");
										echo "<option value = ''></option>";
										foreach($get_coms as $com){
											echo "<option value = '".$com["com_name"]."'>".$com["com_name"]."</option>";
										}
									?>
								</select>

								<br><br>

								<span id = 'forml1'>Community Pass:</span><br>															
								<input type= "password" autocomplete="off" spellcheck="false" name = "com_pass_" class = "loggedout-form-fields"><br><br>

								<input type = "submit" value = "Join" class = "loggedout-form-submit"><br><br>
								<span class = "join-a">By signing up you understand <br>and accept the legal agreements.</span>
							</form>
							
						<?php
						}else{
							$message = $db->query("SELECT message FROM feature_activation WHERE feature=".$db->quote("new_users"))->fetchColumn();
						?>
								<div class = "loggedout-error" style = "margin-top:200px;height:100px;">
									<span class = "loggedout-error-content">
										<center>
										<?php echo $message; ?><br><br>
										To bypass this please enter the passcode.
										<form action = '' method = 'POST'>
											<input type = 'text' name = 'pass_dnu' style = 'border:none;'>
											<input type = 'submit' style = 'border:none;'>
										</form>
										</center>
									</span>
								</div>
								
								
							<?php	
								
								if(isset($_POST['pass_dnu'])){
									$pass = htmlentities($_POST['pass_dnu']);
									$get_true_pass = $db->query("SELECT pass FROM feature_activation WHERE feature='new_users'")->fetchColumn();
									if($pass===$get_true_pass){
										$_SESSION['pass_dnu']="true";
										header("Location: index.php?page=home");
									}
								}
						
						}
						
					
						if((isset($_GET['reg_error']))&&($_GET['reg_error']=="true")&&(isset($_COOKIE['reg_errors']))&&($_COOKIE['reg_errors']!=="no_errors")){
							$reg_errors = unserialize($_COOKIE['reg_errors']);

							?>
								
							<div id = "errors2" style  = "display:none;">
							<?php

							foreach($reg_errors as $error){
								?>
								<div class = "loggedout-error">
									<span class = "loggedout-error-content">
										<center><?php echo $error ?></center>
									</span>
								</div><br>
								<?php
							}

							?>
							<span id = "try-again-reg" class = "try-again-reg">< < Try Again</span>
							</div>

							<script type="text/javascript">
									$(document).ready(function(){
										$("#loggedout-form-container").css("display", "block");
										$("#form2").css("display", "block");
										$("#form2_").css("display", "none");
										$("#errors2").css("display", "block");
										$("#loggedout-form-container").css("height", "770px");
										$("#try-again-reg").click(function(){
											$("#errors2").fadeOut(1000);
											setTimeout(function(){
												$("#form2_").fadeIn(1000);
											}, 1000);
										});
									});
								</script>
							<?php
							setcookie("reg_errors", "", time()-60);
						}else if((isset($_GET['reg_error']))&&($_GET['reg_error']=="false")&&(isset($_COOKIE['reg_errors']))&&($_COOKIE['reg_errors']=="no_errors")){
							?>
								<div id = "success2" style  = "display:none;">
									<img src= "http://i73.servimg.com/u/f73/17/40/86/18/tick10.png" height = "240" width = "280"><br>
									<span class = "contact-form-container">
										<span style = "font-size:108%;"><b>Your account has successfully been created!</b></span><br><br>

										<p>Login now, start debating, <br>
										and participate with your <br>
										commmunity in all sorts of events<br> 
										from competitions to general discussion. <br>
										Enjoy!</p><br>

										<b>Any Questions?</b><br><br>

										<a href="mailto:hello@buzzzap.com" class ="contact-email">administration@buzzzap.com</a><br> 
										<span class = "contact-sub-info">Want to know something? have a <br>question? Need help?</span><br>
										<a href="mailto:founder@buzzzap.com" class ="contact-email">technical@buzzzap.com</a><br> 
										<span class = "contact-sub-info">Have you noticed any faults/bugs in the<br>site that we should know about?</span><br>
									</span><br>

								</div>
								<script type="text/javascript">
									$(document).ready(function(){
										$("#loggedout-form-container").css("display", "block");
										$("#form2").css("display", "block");
										$("#form2_").css("display", "none");
										$("#success2").css("display", "block");
										$("#loggedout-form-container").css("height", "770px");
										
									});
								</script>
							<?php
							
							setcookie("reg_errors", "", time()-60);
						}
							?>

							
					</div>	
				</div>
			
			<div id = "c1" style = "display:none;position:absolute;background-color:red;height:300px;width:300px;border-radius:300px;opacity:0.2;margin-top:-100px;margin-left:10px;"></div>
			<div class = "loggedout-paragraph-container" id = "loggedout-paragraph-container1">
				<span style = "color:#8E388E;" id = 'lpc-title1' class = 'lpctitle'>Login</span><br>
				<div class = "loggedout-paragraph">
					<?php echo get_static_content("lo_para1"); ?>
				</div>
			</div>

			<div id = "r1" class = "rotate"></div>
			<div id = "c2" style = "display:none;position:absolute;background-color:blue;height:300px;width:300px;border-radius:300px;opacity:0.2;margin-top:-20px;margin-left:430px;"></div>
			<div class = "loggedout-paragraph-container" id = "loggedout-paragraph-container2">
				<span style = "color:#79CDCD;" id = 'lpc-title2' class = 'lpctitle'>Contact Us</span><br>
				<div class = "loggedout-paragraph">
					<?php echo get_static_content("lo_para2"); ?>
				</div>
			</div>
				
			<div id = "r2" class = "rotate"></div>
			
			<div id = "c3" style = "display:none;position:absolute;background-color:pink;height:300px;width:300px;border-radius:300px;opacity:0.2;margin-top:-50px;margin-left:835px;padding:20px;"></div>
			<div class = "loggedout-paragraph-container" id = "loggedout-paragraph-container3">
				<span style = "color:#71C671;" id = 'lpc-title3' class = 'lpctitle'>Join A Community</span><br>
				<div class = "loggedout-paragraph">
				<?php echo get_static_content("lo_para3"); ?>
				</div>
			</div>
			<div class = "rotate" id = "r3"></div>
			<div class = "rotate" id = "r4"></div>
			<div class = "rotate" id = "r5"></div>
			<div class = "rotate" id = "r6"></div>
			<div class = "rotate" id = "r7"></div>
			<div class=  "loggedout-header" id = "loggedout-header">
				<span id = "tl1">B</span><span id = "tl2">u</span><span id = "tl3">z</span><span id = "tl4">z</span><span style = "color:grey;display:inline;" id = 'b-header-2'><span id = "tl5">Z</span><span id = "tl6">a</span><span id = "tl7">p</span></span>
			</div>
			<?php
				if(user_browser()!="supported"){
					?>
					<script>
						$("#loggedout-paragraph-container1").css("margin-top", "-140px");
						$("#loggedout-paragraph-container2").css("margin-top", "-140px");
						$("#loggedout-paragraph-container3").css("margin-top", "-140px");
						$("#loggedout-header").css("margin-top", "250px");
						$(".rotate").remove();

					</script>
					<?php
				}
			?>
			<div id = "c4" style = "display:none;position:absolute;background-color:orange;height:300px;width:300px;border-radius:300px;opacity:0.2;margin-top:-50px;margin-left:690px;"></div>
			<div class = "loggedout-paragraph-container" id = "loggedout-paragraph-container4">
				<span style = "color:#FF82AB;" id = 'lpc-title4' class = 'lpctitle'>Start A Community</span><br>
				<div class = "loggedout-paragraph">
					<?php echo get_static_content("lo_para4"); ?>
				</div>
			</div>
			<div id = "c5" style = "display:none;position:absolute;background-color:pink;height:300px;width:300px;border-radius:300px;opacity:0.2;margin-top:100px;margin-left:260px;"></div>
			<div class = "loggedout-paragraph-container" id = "loggedout-paragraph-container5">
				<span style = "color:#F4A460;" id = 'lpc-title5' class = 'lpctitle'>Happening</span><br>
				<div class = "loggedout-paragraph">
					<?php echo get_static_content("lo_para5"); ?>
				</div>
			</div>

			<div id = "c7" style = "display:none;position:absolute;background-color:yellow;height:300px;width:300px;border-radius:300px;opacity:0.2;margin-top:150px;margin-left:890px;"></div>
			<div class = "loggedout-paragraph-container" id = "loggedout-paragraph-container7">
				<span style = "color:#63B8FF;" id = 'lpc-title7' class = 'lpctitle'>Learn More</span><br>
				<div class = "loggedout-paragraph">
					<?php echo get_static_content("lo_para6"); ?>
				</div>
			</div>
			
		</div>	
		</center>
		
	</div>
	
	<div id = "nav-bar-out">
		<div id = 'home-link-out'>Home</div>
		<div id = "opt-sel-route"></div>
		<div id = "opt-sel"></div>
		<div class = 'nav-bar-out-opt' id = 'nbo1'>Happening</div>
		<div class = 'nav-bar-out-opt' id = 'nbo2'>Learn More</div>
		<div class = 'nav-bar-out-opt' id = 'nbo3'>Start A Community</div>
		<div class = 'nav-bar-out-opt' id = 'nbo4'>Contact Us</div>
		<div class = 'nav-bar-out-opt' id = 'nbo5'>Legal Agreements</div>
		
	</div>
	<div id = 'copy-text' style  = 'margin-top: -20px;position: fixed;color: grey;font-size: 80%;'>Copyright &copy; 2016 BuzzZap</div>
	<div id = "section2" class = "section">
		<div class = "sec-title">HAPPENING</div>
		<div class = "loggedout-main-container1" style = "">
			<?php
			$get = $db->prepare("SELECT * FROM site_news ORDER BY feed_id DESC");
			$get->execute();
			if($get->rowCount()>0){
				foreach($get as $value){
					echo "<div class = 'bn-f'>
							<div class = 'bn-f-title'>".$value['title']."<br><span class = 'bn-f-time'>".date("d/M/Y H:i", $value['time'])."</span></div>
							<div class = 'bn-f-text' style = ''>".$value['feed_text']."</div>
						</div><br>";
				}
			}else{
				echo "<br>There are no feeds";
			}	
			?>
		</div>		
	</div>
	
	<div id = "section3" class = "section">
		<div class = "sec-title">LEARN MORE</div>
		<div class = "loggedout-main-container1" style = "overflow: hidden;">
				<br>
				<div id = 'learn-more-p' style = 'font-size:110%;'>
					
					<?php
						echo nl2br(get_static_content("learn_more")); 
					?>	
					<br><br>
					<iframe width="400" height="175" src="<?php echo get_static_content('video_link'); ?>" frameborder="0" allowfullscreen></iframe>
				</div>
		</div>		
	</div>
	
	<div id = "section4" class = "section">
		<div class = "sec-title">START A COMMUNITY</div>
		<div class = "loggedout-main-container1" id = 'lmc4'>
				
			<?php
			if( (get_feature_status('new_coms')=="0")||(isset($_SESSION['pass_snc'])) ){
				if(isset($_POST['snc_com_name'],$_POST['snc_com_pass'],$_POST['snc_leader_username'],
				$_POST['snc_leader_pass'],$_POST['snc_leader_vpass'],$_POST['snc_leader_firstname'],
				$_POST['snc_leader_lastname'],$_POST['snc_leader_email'])){
			
					foreach($_POST as &$value){
						htmlentities($value);
					}
					$snc = snc($_POST['snc_com_name'],$_POST['snc_com_pass'],$_POST['snc_leader_username'],
					$_POST['snc_leader_pass'],$_POST['snc_leader_vpass'],$_POST['snc_leader_firstname'],
					$_POST['snc_leader_lastname'],$_POST['snc_leader_email']);
					if($snc[0]=="true"){
						$com_ipn_ident = $snc[1];
						setcookie("snc_made_suc", $com_ipn_ident, time()+10000);
						if(get_feature_status("payments")=="0"){
							header("Location: index.php?page=home&go_to=4&pay=true&com_ident=".$com_ipn_ident);
						}else{
							header("Location: index.php?page=home&go_to=4&snc_free=".$com_ipn_ident);
						}
					}else{
				
						?>
						<script>
						$(document).ready(function(){
							$(".snc-form").hide();
							$(".snc-error-container").css("display", "block");
							var errors = [];
							<?php 
								foreach($snc as $error){
									?>
									errors.push("<?php echo $error; ?>");
									<?php
								}
							?>
							var error_str = "";
							var count = 0;
							while(count<=errors.length-1){
								error_str = error_str + errors[count] + "<br><br>";
								count++;
							}
							var html = "<div id = 'loggedout-error' class = 'loggedout-error'><span class = 'loggedout-error-content'><center>"+error_str+"</center></span></div><br><br>";
							$(".snc-error-container").html(html+"<span class = 'try-again-reg' style = 'margin-left:50px;' id = 'try-again-snc'>< < Try Again</span>");
					
							$("#try-again-snc").click(function(){
								$(".snc-error-container").fadeOut();
								$(".snc-form").fadeIn();
							});
						});
						</script>
						<?php
				
					}
				}
			
				if(isset($_GET['snc_suc'])){
					if(isset($_COOKIE['snc_made_suc'])){
						$com_id = $db->query("SELECT com_id FROM com_act WHERE act = 1 AND ipn = ".$db->quote($_COOKIE['snc_made_suc']))->fetchColumn();
						if($com_id){
							$leadername = $db->query("SELECT user_firstname FROM users WHERE user_com = ".$db->quote($com_id)." AND user_rank = 3 LIMIT 1")->fetchColumn();
							$email = $db->query("SELECT user_email FROM users WHERE user_com = ".$db->quote($com_id)." AND user_rank = 3 LIMIT 1")->fetchColumn();
							$headers  = 'MIME-Version: 1.0' . "\r\n";
							$headers .= 'Content-type: text/html; charset=iso-8859-1' . "\r\n";
							$headers .= "From: Administration@buzzzap.com" . "\r\n";
							$com_name = $db->query("SELECT com_name FROM communities WHERE com_id = ".$db->quote($com_id))->fetchColumn();
							$parse_vars = array("leadername"=>$leadername, "com_name"=>$com_name);
							$body = nl2br(static_cont_rec_vars(get_static_content("snc_suc_email"), $parse_vars));
							mail($email,"BuzzZap Community Activation",$body,$headers);
							send_admin_note("A new community has successfully registered: ".$com_name);
							?>
							<div style = "color: #62c9b2;font-size: 240%;" class = "contact-result-msg">
								<?php
									if($_GET['snc_suc']!="no_pay"){
										echo get_static_content("snc_suc_msg");
									}else{
										echo get_static_content("snc_suc_msg_no_pay");
									}
								?>
							</div>
							<?php
							setcookie("snc_made_suc", "", time()-10000);

						}else{
							header("Location: index.php?page=home");
						}
					}else{
						header("Location: index.php?page=home");
					}

				}else if(isset($_GET['pay'], $_GET['com_ident'])){
					$suc_msg = ($_GET['pay']=="true")? "Successfully registered your<br> community. " : "";
					$com_ident = htmlentities($_GET['com_ident']);
					if(isset($_GET['revisit'])&&$_GET['revisit']==substr($com_ident, 0,8)){
						setcookie("snc_made_suc", $com_ident, time()+10000);
					}
					?>
					
					
					<?php echo static_cont_rec_vars(get_static_content("paypal_button_form"), array("com_ident"=>$com_ident)); ?>

					<div style = "color: #62c9b2;" class = "contact-result-msg">
					<?php echo $suc_msg; ?>
					We are redirecting<br> you to the payment form in<br>
					<span id = 'p-form-counter'>5</span> seconds
					</div>
					
					<script>
						count = 4;
						setInterval(function(){
							$("#p-form-counter").html(count);
							
							if(count!=1){
								count = count - 1;
							}
						}, 1000);
						setTimeout(function(){
							$("#subscribe-form").submit();
						}, 5000);
				
					</script>
					<?php
				}else if(isset($_GET['snc_free'])&&get_feature_status("payments")=="1"){
					$com_ident = htmlentities($_GET['snc_free']);
					$db->query("UPDATE com_act SET act = 1 WHERE act = 0 AND ipn = ".$db->quote($com_ident));
					header("Location: index.php?page=home&go_to=4&snc_suc=no_pay");
				}else{
				
			?>	
				<div id = "more-pay-info-container">
					<span style = 'color: #6082B6;font-size: 140%;'>Subscription Fee</span><span id = 'mpic-close' style = "cursor:pointer;float:right;">X</span>
					<br><br><?php echo get_static_content("subscription_fee_info"); ?>
				</div>
				
				<div class = "snc-error-container"></div>
				<div class = "snc-form" style = "">
					<form action = "index.php?page=home&go_to=4" method = "POST" id = "snc-form-in">
						<span style = 'line-height: 20px;letter-spacing: 1px;'><?php echo (get_feature_status("payments")=="0")? get_static_content("snc_header"): get_disabled_message("payments"); ?></span><br><br>
						<input placeholder = "Community Name" type = "text" name = "snc_com_name" class = "loggedout-form-fields-snc" style = "width:500px;" id = "sf1"><div class = "snc-field-note" id = "sfn1">This is the name of your community.</div>
						<input placeholder = "Community Pass Code" type = "password" name = "snc_com_pass" class = "loggedout-form-fields-snc" style = "width:400px;" id = "sf2"><div class = "snc-field-note" id = "sfn2">This is the passcode used to access the community by all members.</div>
						<input placeholder = "Your Username" type = "text" name = "snc_leader_username" class = "loggedout-form-fields-snc" style = "width:500px;" id = "sf3"><div class = "snc-field-note" id = "sfn3">This is the community leaders username, you.</div>
						<input placeholder = "Your Password" type = "password" name = "snc_leader_pass" class = "loggedout-form-fields-snc" style = "width:400px;" id = "sf4"><div class = "snc-field-note" id = "sfn4">Your personal password.</div>
						<input placeholder = "Verify Your Password" type = "password" name = "snc_leader_vpass" class = "loggedout-form-fields-snc" style = "width:400px;" id = "sf5"><div class = "snc-field-note" id = "sfn5">Verify your personal password.</div>
						<input placeholder = "Your Firstname" type = "text" name = "snc_leader_firstname" class = "loggedout-form-fields-snc" style = "width:250px;" id = "sf6"><input placeholder = "Your Lastname"  type = "text" name = "snc_leader_lastname" class = "loggedout-form-fields-snc" style = "width:250px;" id = "sf6"><div class = "snc-field-note" id = "sfn6">Enter your fullname.</div>
						<input placeholder = "Your Email"  type = "text" name = "snc_leader_email" class = "loggedout-form-fields-snc" style = "width:500px;" id = "sf7"><div class = "snc-field-note" id = "sfn7">Enter your email.</div>
						<input type = "submit" value = "Submit" class = "loggedout-form-submit" style = "margin-top:10px;border:none;box-shadow:none;">
					</form>
				</div>
				
				<script>
						
						$("#mpic-show").click(function(){
							$("#more-pay-info-container").slideDown(1500);
						});
						$("#mpic-close").click(function(){
							$("#more-pay-info-container").slideUp(1500);
						});
				
				</script>
			<?php
				}
			}else{
			?>

				<div class = "loggedout-error" style = "margin-top:200px;margin-left:450px;position:absolute;">
					<span class = "loggedout-error-content">
						<center><?php echo  get_disabled_message('new_coms'); ?>
								<br><br>To bypass this please enter the passcode.
											<form action = '' method = 'POST'>
												<input type = 'text' name = 'pass_snc' style = 'border:none;'>
												<input type = 'submit' style = 'border:none;'>
											</form>
						</center>
					</span>
				</div><br>

			
			<?php	
		
			if(isset($_POST['pass_snc'])){
				$pass = htmlentities($_POST['pass_snc']);
				$get_true_pass = $db->query("SELECT pass FROM feature_activation WHERE feature='new_coms'")->fetchColumn();
				if($pass===$get_true_pass){
					$_SESSION['pass_snc']="true";
					header("Location: index.php?page=home");
				}else{
					echo "error";
				}
			}
								
			}
			?>	
		</div>	
	</div>
	
	<div id = "section5" class = "section">
		<div class = "sec-title">CONTACT US</div>
		<div class = "loggedout-main-container1" style = "">
			<?php
			if(isset($_GET['cu'])){
				if($_GET['cu']!="success"){
						?>
						
						<div style = "color: salmon;" class = "contact-result-msg"><?php echo $_GET['cu']; ?></div>
						<a style = 'margin-left:300px;color:grey;' href = "index.php?page=home&go_to=5">Go Back</a>
						
					<?php
				}else{
					?>
						
						<div style = "color: lightgrey;" class = "contact-result-msg">Successfully Sent :-)</div>
						
					<?php
				}
			}else{
			?>	
			
			<form method = "POST" id = "contact-form" style = "">
				<select id = "contact-opt-sel" name = "cu_temail">
					<option>--category--</option>
					<option value = "admin@buzzzap.com">Administration</option>
					<option value = "technical@buzzzap.com">Technical</option>
					<option value = "billing@buzzzap.com">Billing</option>
				</select><br><br>
				<input type = "text" class = "loggedout-form-fields-c" placeholder="Your Email..." name = "cu_femail"><br>
				<input type = "text" class = "loggedout-form-fields-c" placeholder="Subject..." name = "cu_subject"><br>
				<textarea id = "contact-textarea" style = "border:none;box-shadow:none;" placeholder="Message..." name = "cu_body"></textarea><br>
				<input type = "submit" value = "Submit" class = "loggedout-form-submit" style = "border:none;box-shadow:none;margin-top:10px;">
			</form>
			<div style = 'line-height: 20px;letter-spacing: 1px;color:grey;text-align:center;'>Please contact us with<br> any questions or queries.</div>
			<?php
			}
				if(isset($_POST['cu_temail'], $_POST['cu_femail'], $_POST['cu_subject'], $_POST['cu_body'])){
					$to = htmlentities($_POST['cu_temail']);
					$from = htmlentities($_POST['cu_femail']);
					$subject = htmlentities($_POST['cu_subject']);
					$body = htmlentities($_POST['cu_body']);
					$error = "";
					$valid_b_emails = array("admin@buzzzap.com", "technical@buzzzap.com", "billing@buzzzap.com");
					if(!empty($to)&&!empty($from)&&!empty($subject)&&!empty($body)){
						if(in_array($to, $valid_b_emails)){
							if(filter_var($from, FILTER_VALIDATE_EMAIL)){ 
								mail($to, $subject, $body, "From: ".$from);
								header("Location: index.php?page=home&go_to=5&cu=success");
							}else{
								$error = "Your email is invalid.";
							}
						}else{
							$error = "Unknown error occured. Try again later.";
						}
					}else{
						$error = "All fields are required.";
					}
					
					if($error!=""){
						header("Location: index.php?page=home&go_to=5&cu=".$error);
					}	
				}
				
				
			?>
			
		</div>	
	</div>
	
	<div id = "section6" class = "section">
		<div class = "sec-title">LEGAL AGREEMENTS</div>
		<div class = "loggedout-main-container1" style = 'text-align: center;white-space:normal;color: #3b3b3b;'>
			<?php echo get_static_content("legal"); ?> 
		</div>
	</div>


<?php
}
?>