<?php
ob_start();
session_start();
//if($_SERVER['PHP_SELF']!="/buzzzap/index.php"){ 
//	header("Location: index.php?page=home");
//}
$check_valid = "true";
function valid_page($page_name){
	$valid_pages_ = scandir("pages");
	$valid_pages = array();
	foreach($valid_pages_ as $page){
		$pos_prefix = strpos($page, ".php");
		$new_name = substr($page ,0, $pos_prefix);
		if(!empty($new_name)){
			$valid_pages[] = trim($new_name);
		}
	}
	if(in_array($page_name, $valid_pages)){
		return true;
	}else{
		return false;
	}	
}
function get_user_field($user_id, $field){
	global $db;
	$result = $db->query("SELECT ".$field." FROM users WHERE user_id = ".$db->quote($user_id))->fetchColumn();
	return $result;
}
function on_cm($user_id){
	if(get_user_field($user_id, "close_mod")==1){
		return true;
	}else{
		return false;
	}
}
function user_moderation_status($user_id){
	global $db;
	$state = 1;
	if((get_user_field($_SESSION['user_id'], "user_rep")<15)&&(user_rank($user_id, "1", "just")==true)){
		$state = 2;
	}
	
	if(get_user_field($_SESSION['user_id'],"close_mod")=="1"){
		$state = 3;
	}
	
	// 1- completely valid user
	// 2- rep too low, needs basic moderation.
	// 3 - CLOSE MODERATION.
	return $state;
}
function get_feature_status($feature, $return_type="binary"){
	global $db;

	$get_state = $db->query("SELECT activation FROM feature_activation WHERE feature=".$db->quote($feature))->fetchColumn();
	if($return_type == "binary"){
		return $get_state;
	}else if($return_type == "e_d"){
		return ($get_state==1)? "Disabled" : "Enabled";
	}else if($return_type == "bool"){
		return ($get_state==1)? "False" : "True";
	}
}
function encrypt($string){
	$stringlen = strlen($string);
	$string1 = substr($string,0, $stringlen/2);
	$string2 = substr($string, $stringlen/2, $stringlen);

	$string1 = hash("whirlpool", $string1);
	$string2 = hash("sha512", $string2);
	$salts= array("uv68YVocRIXO","HCOxcio76hatxk","YCKCR54xjaj74ka");
	$new_string = $salts[0].$string2.$salts[2].$string1.$salts[1];
	$new_string = hash("sha512", $new_string)."VY4xcYRY5Z3ww";
	return $new_string;
}
function get_user_community($user_id, $field){
	global $db;
	$id = $db->query("SELECT user_com FROM users WHERE user_id = ".$db->quote($user_id)."")->fetchColumn();
	if($field=="id"){
		return $id;
	}else{
		$result = $db->query("SELECT ".$field." FROM communities WHERE com_id = ".$db->quote($id))->fetchColumn();
		return $result;
	}
}

function register_user($username, $password, $vpassword, $firstname, $lastname, $com_pass, $com_name, $email, $check_com_pass="true"){
	global $db;
	$username = htmlentities($username);
	$password = encrypt(htmlentities($password));
	$vpassword= encrypt(htmlentities($vpassword));
	$firstame = htmlentities($firstname);
	$lastname = htmlentities($lastname);
	$com_pass = encrypt(htmlentities($com_pass));
	$com_name = htmlentities($com_name);
	$email    = htmlentities($email);
	$errors = array();
	if($check_com_pass=="true"){
		$check_com = $db->query("SELECT com_id FROM communities WHERE com_name = ".$db->quote($com_name)." AND com_password = ".$db->quote($com_pass)."")->fetchColumn();
		$com_id = $check_com;
		if(empty($com_id)){
			$errors[] = "You supplied the wrong password for the <br>community you want to join.";
		}
	}else{
		$com_id = $check_com_pass;
	}
	if(!filter_var($email, FILTER_VALIDATE_EMAIL)){ 
		$errors[] = "Your email is invalid.";
	}

	if($password != $vpassword){
		$errors[] = "Passwords do not match.";
	}

	$check_username = $db->query("SELECT user_username FROM users WHERE user_username = ".$db->quote($username));
	if($check_username->rowCount()>0){
		$errors[] = "There is already a user with '".$username."' <br>as their username.";
	}
	$check_email = $db->query("SELECT user_email FROM users WHERE user_email = ".$db->quote($email));
	if($check_email->rowCount()>0){
		$errors[] = "There is already a user with the email '".$email."'.";
	}

	if(strlen($username)<=3){
		$errors[]="Your username must be over 3 characters long.";
	}

	if(strlen($password)<=3){
		$errors[]="Your password must be over 3 characters long.";
	}
	$empty = array();
	$required = array("Username"=>$username, "Passwords"=>$password, "Passwords"=>$vpassword, "Firstname"=>$firstname,
	"Lastname"=>$lastname,"Email"=>$email);
	if($check_com_pass=="true"){
		 $required["Community Password"]= $com_pass;
		 $required["Community Name"]=$com_name;
	}
	foreach ($required as $field=>$value){
		if(strlen($value)===0){
			$empty[] = $field;
		}
	}
	if(count($empty)>0){
		$error = "The following fields must be entered";
		foreach($empty as $field){
			$error = $error.",<br> ".$field;
		}

		$errors[] = $error;
	}
	if(count($errors)==0){
		$register = $db->prepare("INSERT INTO users VALUES('',:username, :password, :firstname, :lastname, '', :com, 1, :email, 0,'', 0)");
		$register->execute(array("username"=>$username, "password"=>$password, "firstname"=>$firstname, "lastname"=>$lastname, "com"=>$com_id, "email"=>$email));
		$uid = $db->lastInsertId();
		$register1 = $db->prepare("INSERT INTO about_user VALUES(:user_id, '','','')");
		$register1->execute(array("user_id"=>$uid));
		$first_time = $db->prepare("INSERT INTO first_login VALUES(:user_id)");
		$first_time->execute(array("user_id"=>$uid));
		return "true";
	}else{
		return $errors;
	}

}

function update_user_field($user_id, $value, $field){
	global $db;

	$rank_binds = array("leader"=>3, "member"=>1);

	$value = htmlentities($value);
	$user_id = (int) $user_id;

	if($field=="user_rank"){
		$value = $rank_binds[$value];
	}

	$update = $db->prepare("UPDATE users SET ".$field." = :value WHERE user_id = :id");
	$update->execute(array("value"=>$value, "id"=>$user_id));
	return true;

}
function user_rank($id, $rank, $ext = "just"){
	global $db;
	if($ext == "just"){
		$operator = "=";
	}else if($ext == "up"){
		$operator = ">=";
	}else if($ext =="down"){
		$operator = "<=";
	}	

	$check_rank = $db->query("SELECT * FROM users WHERE user_id = ".$db->quote($id)." AND user_rank ".$operator." ".$db->quote($rank))->fetchColumn();
	if(!empty($check_rank)){
		return true;
	}else{
		return false;
	}
	
}
function login_user($username, $password, $com_pass){
	global $db;
	$login = false;
	$username = htmlentities($username);
	$password = encrypt(htmlentities($password));
	$com_pass_or = htmlentities($com_pass);
	$com_pass = encrypt(htmlentities($com_pass));
	
	$check = $db->query("SELECT user_id FROM users WHERE user_username = ".$db->quote($username)." AND user_password = ".$db->quote($password)."")->fetchColumn();
	
	if(!empty($check)){
		$get_com_id = $db->query("SELECT user_com FROM users WHERE user_username = ".$db->quote($username)."")->fetchColumn();
		$check_com = $db->query("SELECT com_name FROM communities WHERE com_id = '$get_com_id' AND com_password = ".$db->quote($com_pass)."")->fetchColumn();
		
		
		if(user_rank($check, "4", "just")){
		
			$admin_code=$db->query("SELECT user_code FROM users WHERE user_id=".$db->quote($check))->fetchColumn();
		
			if($admin_code === $com_pass_or){
				$login=true;
				$_SESSION['admin_key']=$admin_code;
			}else{
				return false;
			}
		}else if( (get_feature_status("login")=="1") && ($_SESSION['pass_dl']!="true") ){
			return "disabled";
		}else{
			$check_com_act = $db->query("SELECT act FROM com_act WHERE com_id = '$get_com_id'")->fetchColumn();
			if(!empty($check_com)){
				if($check_com_act!=0){
					if(user_rank($check, "0", "just")){
						return "banned";
					}else{
						$login=true;
					}
				}else{
					return "discom";
				}
			}else{
				return false;
			}
		}
	
		if($login===true){
			$user_id = $check;
			$_SESSION['user_id'] = $user_id;
			return true;
		}

	}else{
		return false;
	}
}

function loggedin(){
	if((isset($_SESSION['user_id']))&&(!empty($_SESSION['user_id']))){
		if(user_rank($_SESSION['user_id'], "0", "just")){
			return false;
		}else{
			return true;
		}
	}else{
		return false;
	}
}
function loggedin_as_admin(){
	global $db;
	if(loggedin()){
		if(user_rank($_SESSION['user_id'], "4", "just")){
			if(isset($_SESSION['admin_key'])){
				$admin_code=$db->query("SELECT user_code FROM users WHERE user_id=".$db->quote($_SESSION['user_id']))->fetchColumn();
				if($_SESSION['admin_key']==$admin_code){
					return true;
				}else{
					return false;
				}
			}else{
				return false;
			}
		}else{
			return false;
		}	
	}else{
		return false;
	}
}
function valid_thread_status($status){
	$valid = array("yes", "no", "it_depends", "not_sure");
	if(in_array($status, $valid)){
		return true;
	}else{
		return false;
	}
}
function create_thread($title, $text, $com_id, $topic_id){
	global $db;
	$title = htmlentities($title);
	$text = nl2br(htmlentities($text));
	$com_id = (int) $com_id;
	$topic_id = $topic_id;
	$username = get_user_field($_SESSION['user_id'], "user_username");
	$visible = (user_moderation_status($_SESSION['user_id'])==1)? "1":"0";	
	$uci = (int) get_user_community($_SESSION['user_id'], "com_id");
	$time = time();
	
	if(user_not_posted($username)){
		add_badge("Posting for the first time", $_SESSION['user_id'], "you posted for the first time!");
	}	
	re_for_p_count_on_post($username);
	$insert_data = $db->prepare("INSERT INTO debating_threads VALUES('', :title, :starter, :topic_id, :com_id, :time, 0, :time, :visible,0,0,0, :uci)");
	$insert_data->execute(array("title"=>$title, "starter"=>$username, "topic_id"=>$topic_id, "com_id"=>$com_id,"time"=>$time, "visible"=>$visible, "uci"=>$uci));
	$thread_id = $db->lastInsertId();

	$insert_data = $db->prepare("INSERT INTO thread_replies VALUES('', :thread_id, :reply_text, :time, :user_replied, 0, 0, '', :visible, 1, '')");
	$insert_data->execute(array("thread_id"=>$thread_id, "reply_text"=>$text, "time"=>$time,"user_replied"=>$username, "visible"=>$visible));
	add_rep(5, $_SESSION['user_id']);
	return $thread_id;
}
function valid_reply_voter($user_id, $reply_id){
	global $db;
			
	$user_id = (int) htmlentities($user_id);
	$reply_id = (int) htmlentities($reply_id);
	$check = $db->query("SELECT user_id FROM thread_reply_votes WHERE user_id = '$user_id' AND reply_id = '$reply_id'")->fetchColumn();	
	if($check==$user_id){
		return false;	
	}else{
		return true;	
	}
}

function add_com_feed($com_id, $text){
	global $db;
	$add = $db->prepare("INSERT INTO com_news VALUES('', :com_id, :text, UNIX_TIMESTAMP())");
	$add->execute(array("com_id"=>$com_id, "text"=>$text));
}

function vote_reply($vote_id, $user_id, $reply_id){
	global $db;
	if(valid_reply_voter($user_id, $reply_id)){
		if($vote_id==1){
			//agreed
			$status = "agrees";	
			$username = $db->query("SELECT user_replied FROM thread_replies WHERE reply_id = ".$db->quote($reply_id))->fetchColumn();
			add_rep(5, $db->query("SELECT user_id FROM users WHERE user_username = ".$db->quote($username))->fetchColumn());	
		}else{
			//disagreed
			$status = "disagrees";
		}
		$column = "reply_".$status;
		$update = $db->prepare("UPDATE thread_replies SET ".$column."=(".$column."+1) WHERE reply_id = :reply_id");	
		$update->execute(array("reply_id"=>$reply_id));
			
		$insert = $db->prepare("INSERT INTO thread_reply_votes VALUES(:reply_id, :user_id, :vote_id)");
		$insert->execute(array("reply_id"=>$reply_id, "user_id"=>$user_id, "vote_id"=>$vote_id));
	}
}

function user_own_reply($reply_id, $user_id){
	global $db;
	$username = get_user_field($user_id, "user_username");
	$check = $db->query("SELECT reply_id FROM thread_replies WHERE user_replied ='$username' AND reply_id = '$reply_id'")->fetchColumn();	
	if(!empty($check)){
		return true;
	}else{
		return false;
	}
}

function post_action($user_id, $reply_id, $action, $editval){
	global $db;
	//action = "edit" || "delete"
	if($action!=="report"){
		if((user_own_reply($reply_id, $user_id))||(user_rank($user_id, 2, "up"))){
			if($action == "edit"){
				$update = $db->prepare("UPDATE thread_replies SET reply_text = :new_text WHERE reply_id = :reply_id");
				$update->execute(array("new_text"=>$editval, "reply_id"=>$reply_id));
				return true;
			}else if($action=="delete"){
				$update = $db->prepare("DELETE FROM thread_replies WHERE reply_id = :reply_id");
				$update->execute(array("reply_id"=>$reply_id));
				return true;
			}
		}else{
			return false;	
		}
	}	
}

function vote_debate($vote, $d_id){
	global $db;
	$vote = strtolower($vote);
	$assoc_vtypes = array("yes"=>"vote_yes", "no"=>"vote_no", "maybe"=>"vote_maybe", "agree"=>"vote_yes", "disagree"=>"vote_no");
	$update = $db->prepare("UPDATE debating_threads SET `".$assoc_vtypes[$vote]."` = `".$assoc_vtypes[$vote]."` + 1 WHERE thread_id = ".$db->quote($d_id));
	$update->execute();
}

function re_for_p_count_on_post($username){
	$p_quant_to_check = array(10,50, 100, 200, 500, 1000, 1500, 2000);
	foreach($p_quant_to_check as $x){
		if(is_users_x_post($x-1, $username)){
			add_badge("Has reached ".$x." posts!", $_SESSION['user_id'], "you have just posted your ".$x."th post!");
		}
	}	
}
function reply_debate($reply_text, $user_replied, $thread_id, $size, $reply_status){
	global $db;
	//$thread_id = parent content id
	$reply_text = nl2br(htmlentities($reply_text));
	$active = (user_moderation_status($_SESSION['user_id'])>1)? 0:1;
	if($active==0){
		$msg = "Your comment will not be visible untill it has been approved by your community leader.";	
	}else{
		$visibility = 1;
		$msg = "Posted Successfully!";	
	}
	if($reply_status=="na"){
		$reply_status="";
	}else{
		vote_debate($reply_status, $thread_id);
	}	
	$b_cont = "";
	if(user_not_posted($user_replied)){
		add_badge("Posting for the first time", $_SESSION['user_id'], "you posted for the first time!");
	}

	
	if($size=="mini"){

		$starter = $db->query("SELECT user_replied FROM thread_replies WHERE reply_id = ".$db->quote($thread_id))->fetchColumn();
		$deb_id = $db->query("SELECT thread_id FROM thread_replies WHERE reply_id = ".$db->quote($thread_id))->fetchColumn();
		$starter = $db->query("SELECT user_id FROM users WHERE user_username=".$db->quote($starter))->fetchColumn();
		$deb_title = $db->query("SELECT thread_title FROM debating_threads WHERE thread_id = ".$db->quote($deb_id))->fetchColumn();
		add_note($starter, $user_replied." has replied to your argument in the debate '".$deb_title."'.", "index.php?page=view_private_thread&thread_id=".$deb_id);
	}
	
	re_for_p_count_on_post($user_replied);	
	$insert = $db->prepare("INSERT INTO thread_replies VALUES('', :thread_id, :reply_text, :time, :user_replied, 0, 0, :reply_status, :visible, 0, :size )");
	$insert->execute(array("thread_id"=>$thread_id, "reply_text"=>$reply_text, "time"=>time(), "user_replied"=>$user_replied, 
							"visible"=>$active, "size"=>$size, "reply_status"=>$reply_status));	
		
	$rid = $db->lastInsertId();
	if($size==""){
		add_rep(3, $_SESSION['user_id']);
	}else{
		add_rep(1, $_SESSION['user_id']);
	}																	
	return array($rid,$msg);
}
function valid_debate_like($thread_id, $user_id){
	global $db;
	$check = $db->query("SELECT user_id FROM thread_likes WHERE thread_id = '$thread_id' AND user_id = '$user_id'")->fetchColumn();
	if(!empty($check)){
		return false;	
	}else{
		return true;	
	}	
}
function like_debate($thread_id, $user_id, $type){
	global $db;
	if (valid_debate_like($thread_id, $user_id)){
		if($type=="like"){
			$insert = $db->prepare("INSERT INTO thread_likes VALUES(:thread_id, :user_id)");
			$insert->execute(array("thread_id"=>$thread_id, "user_id"=>$user_id));	
			$update = $db->prepare("UPDATE debating_threads SET thread_likes = (thread_likes+1) WHERE thread_id = :thread_id");
			$update->execute(array("thread_id"=>$thread_id));		
		}
	}
	if($type=="unlike"){	
		$delete = $db->prepare("DELETE FROM thread_likes WHERE thread_id = :thread_id  AND user_id = :user_id");
		$delete->execute(array("thread_id"=>$thread_id, "user_id"=>$user_id));	
		$update = $db->prepare("UPDATE debating_threads SET thread_likes = (thread_likes-1) WHERE thread_id = :thread_id");
		$update->execute(array("thread_id"=>$thread_id));	
	}
	
}

function valid_view_thread($thread_id, $user_id){
	global $db;
	$real_com_id= get_user_field($user_id, "user_com");
	$check = $db->query("SELECT com_id FROM debating_threads WHERE thread_id = '$thread_id'")->fetchColumn();
	$visible = $db->query("SELECT visible FROM debating_threads WHERE thread_id = '$thread_id'")->fetchColumn();
	$starter = $db->query("SELECT thread_starter FROM debating_threads WHERE thread_id = '$thread_id'")->fetchColumn();
	$username = get_user_field($user_id,"user_username");
	if($real_com_id==$check||$check==0){
		if($starter==$username){
			return true;
		}else if($visible==1){
			return true;
		}else{
			return false;	
		}
	}else{
		return false;	
	}
}
function action_user($user_id, $action){
	global $db;
	if(user_rank($_SESSION['user_id'], 3, "up")){
		
		$info_array = array("user_id"=>$user_id);
		
		if($action=="del_user"){
			$delete = $db->prepare("DELETE FROM users WHERE user_id = :user_id");
			$delete->execute($info_array);
		}else if($action=="ban_user"){
			$ban = $db->prepare("UPDATE users SET user_rank = 0 WHERE user_id = :user_id");
			$ban->execute($info_array);
		}else if($action=="unban_user"){
			$unban = $db->prepare("UPDATE users SET user_rank = 1 WHERE user_id = :user_id");
			$unban->execute($info_array);
		}else if($action=="reset_user"){
			//0 = asks for username
			$username = get_user_field($user_id, "user_username");
			//1 = asks for user_id
			$clear_tables = array("debating_threads"=>"0thread_starter", "thread_likes"=>"1user_id", "thread_replies"=>"0user_replied", "thread_reply_votes"=>"1user_id");
			foreach($clear_tables as $table=>$user_ident){
				if(substr($user_ident, 0, 1)=="1"){
					$user_ident_value = $user_id;
				}else{
					$user_ident_value = $username;
				}
				$user_ident = substr($user_ident, 1);
				$reset = $db->prepare("DELETE FROM ".$table." WHERE ".$user_ident." = :user_ident_value");
				$reset->execute(array("user_ident_value"=>$user_ident_value));
			}
		}else if($action=="cm_user"||$action=="tcm_user"){
			if($action == "cm_user"){
				$close_mod = "1";
			}else{
				$close_mod = "0";
			}
			$update = $db->prepare("UPDATE users SET close_mod = :close_mod WHERE user_id = :user_id");
			$update->execute(array("close_mod"=>$close_mod, "user_id"=>$user_id));
				
		}
		
		return true;
	}
}

function user_in_group($user_id, $spec = "", $check_act=""){
	global $db;
	if($spec!=""){
		$query_auth = " AND group_id =".$spec;
	}else{
		$query_auth = "";	
	}
	
	if($check_act==""){
		$query_act= "";
	}else{
		$query_act= " AND active=1";
	}

	$check = $db->query("SELECT group_id FROM group_members WHERE user_id = '$user_id' ".$query_act." ".$query_auth." LIMIT 1")->fetchColumn();
	if($check==""){
		
		return false;
	}else{
		return true;
	}
}	

function user_in_community($user_id, $com_id){
	global $db;
	$check = $db->query("SELECT user_id FROM users WHERE user_com = '$com_id' AND user_id = '$user_id' LIMIT 1")->fetchColumn();
	if($check==""){
		return false;
	}else{
		return true;
	}
}	

function get_user_group($user_id, $field){
	global $db;
	
	if(user_in_group($user_id)){
		$get_group_id = $db->query("SELECT group_id FROM group_members WHERE user_id = ".$db->quote($user_id))->fetchColumn();
		$get_group = $db->query("SELECT ".$field." FROM private_groups WHERE group_id= ".$db->quote($get_group_id))->fetchColumn();
		return $get_group;
	}else{
		return false;	
	}
}

function get_group_leader_id($group_id){
	global $db;
	return $db->query("SELECT user_id FROM group_members WHERE leader = 1 AND group_id=".$db->quote($group_id))->fetchColumn();
}	

function group_in_a_comp($group_id){
	global $db;
	$like1 = ",".$group_id;
	$like2 = $group_id.",";
	$like3 = ",".$group_id.",";
	$like4 = $group_id;
	
	$get_opp_ids = $db->query("SELECT * FROM competitions WHERE comp_type = 0 AND
	 opp_id LIKE '%$like1' OR 
	 opp_id LIKE '$like2%' OR
	 opp_id LIKE '%$like3%' OR
	 opp_id LIKE '$like4'");
	 
	$get_as_starter = $db->query("SELECT starter_id FROM competitions WHERE starter_id = '$group_id' AND comp_type = 0")->fetchColumn();
	if($get_opp_ids->rowCount()>0){
		return true;
	}else if(empty($get_as_starter)){
		return false;
	}else{
		return true;
	}
	
}
function group_action($user_id, $group_id, $action){
	global $db;
	$g_com_id = $db->query("SELECT com_id FROM private_groups WHERE group_id = '$group_id' LIMIT 1")->fetchColumn();
	$gname = $db->query("SELECT group_name FROM private_groups WHERE group_id = '$group_id' LIMIT 1")->fetchColumn();
	if(get_user_community($user_id, "com_id")==$g_com_id){
		if($action == "join" || $action == "addu"){
			if(!user_in_group($user_id, $spec="", "true")){

				if($action=="join"){
					$update = $db->prepare("UPDATE group_members SET active=1 WHERE user_id=:user_id AND group_id = :group_id");
					$update->execute(array("user_id"=>$user_id, "group_id"=>$group_id));
					add_badge("Has been in a group", $user_id, "you are now in a group!");
					$text = get_user_field($user_id, "user_username")." is now a member of your group.";
					add_note(get_group_leader_id($group_id), $text,"");
				}else{
					add_note($user_id,"You have been invited to join the group '".$gname."', to accept or decline please click here.","index.php?page=private_groups");
					$add = $db->prepare("INSERT INTO group_members VALUES(:group_id,:user_id, 0,0)");
					$add->execute(array("user_id"=>$user_id, "group_id"=>$group_id));
				}
				add_rep(10, $user_id);
				
				return true;
			}else{
				return false;	
			}
		}else if(($action == "leave"&&user_in_group($user_id, $group_id)) || ($action == "dec")){
			if($action=="leave"){
				add_rep(-10, $user_id);
			}
			$check_in_comp = group_in_a_comp($group_id);
			if(($action == "leave"&&!$check_in_comp)||($action=="dec")){
				$delete = $db->prepare("DELETE FROM group_members WHERE user_id = :user_id AND group_id = :group_id");
				$delete->execute(array("user_id"=>$user_id, "group_id"=>$group_id));
				$check_group_count = $db->query("SELECT user_id FROM group_members WHERE active = 1 AND group_id = ".$db->quote($group_id));
				if($check_group_count->rowCount()==0){
					delete_group($group_id);

				}
			}else{
				echo "compe";
				return "compe";
			}
			return true;
		}else{
			return false;
		}	
	}	
}

function strlist_to_array($list, $valid_as_user = true){
	global $db;
	$list = explode(",", trim_commas(trim($list)));
	if($valid_as_user==true){
		$invalid_users = array();	
	}
	foreach($list as &$value){
		$value = trim($value);
		if($valid_as_user==true){
			$user = $db->query("SELECT user_id FROM users WHERE user_username = ".$db->quote($value))->fetchColumn();
			if(empty($user)){
				$invalid_users[] = $value;
			}
		}
	}
	if($valid_as_user!=true){
		return $list;	
	}else if(count($invalid_users)>0){
		$invalid_users[] = "ERROR";
		return $invalid_users;
	}else{
		return $list;	
	}
}
function group_leader($user_id){
	global $db;
	$check = $db->query("SELECT user_id FROM group_members WHERE leader = 1 AND user_id = ".$db->quote($user_id))->fetchColumn();
	if(!empty($check)){
		return true;	
	}else{
		return false;	
	}
}
function create_p_group($user_id, $name, $members){
	global $db;
	if(!user_in_group($user_id,"", "true")){
		$leader_com = get_user_community($user_id, "com_id");
		$com_id = get_user_field($user_id, "user_com");
		$insert = $db->prepare("INSERT INTO private_groups VALUES('', :name, :com_id)");
		$insert->execute(array("name"=>$name, "com_id"=>$com_id));
		$error = false;
		$group_id = $db->lastInsertId();
		$insert = $db->prepare("INSERT INTO group_members VALUES(:group_id, :user_id, 1, 1)");
		$insert->execute(array("group_id"=>$group_id, "user_id"=>$user_id));
		foreach($members as $member){
			$uid = $db->query("SELECT user_id FROM users WHERE user_username = ".$db->quote($member))->fetchColumn();
			if(!empty($uid)){
				$user_com = get_user_community($uid, "com_id");
				if($leader_com==$user_com){
					$insert = $db->prepare("INSERT INTO group_members VALUES(:group_id, :user_id, 0, 0)");
					$insert->execute(array("group_id"=>$group_id, "user_id"=>$uid));
				}else{
					$error = true;	
				}
			}
		}
		if($error==true){
			return false;
		}else{
			add_rep(10, $user_id);
			add_badge("Has been a group leader", $user_id, " you are a group leader!");
			add_badge("Has been in a group", $user_id, " you are a now in a group!");
			return true;
		}
	}else{
		return false;	
	}	
}
function get_users_in_group($group_id){
	global $db;
	$users = array();
	$get_users = $db->prepare("SELECT user_id FROM group_members WHERE group_id = :group_id");
	$get_users->execute(array("group_id"=>$group_id));
	while($row = $get_users->fetch(PDO::FETCH_ASSOC)){
		$users[] = $row['user_id'];
	}	
	
	return $users;
}
function delete_group($group_id){
	global $db;
	$delete = $db->prepare("DELETE FROM private_groups WHERE group_id = :group_id");
	$delete->execute(array("group_id"=>$group_id));
	$delete = $db->prepare("DELETE FROM group_members WHERE group_id = :group_id");
	$delete->execute(array("group_id"=>$group_id));
}

function make_visible($id, $table){
	global $db;
	$types= array("thread_replies"=>"reply_id", "debating_threads"=>"thread_id", "iwonder_replies"=>"reply_id");
	$change = $db->prepare("UPDATE ".$table." SET visible = 1 WHERE ".$types[$table]." = :id");
	$change->execute(array("id"=>$id));
}
function get_user_com_by_name($username){
	global $db;
	$owner_id = $db->query("SELECT user_id FROM users WHERE user_username = ".$db->quote($username))->fetchColumn();
	return $com_id = get_user_community($owner_id, "com_id");
}
function send_pm($valid_ids, $body, $subject){
	global $db;
	$new_pm = $db->prepare("INSERT INTO private_messages VALUES('', :subject)");
	$user_replied= get_user_field($_SESSION['user_id'], "user_username");
	$new_pm->execute(array("subject"=>$subject));
	$pm_id = $db->lastInsertId();
	$new_reply = $db->prepare("INSERT INTO pm_replies VALUES('', :pm_id, UNIX_TIMESTAMP(), 1, :user_replied, :body)");
	$new_reply->execute(array("pm_id"=>$pm_id, "user_replied"=>$user_replied, "body"=>$body));
	foreach($valid_ids as $id){	
		$new_mem = $db->prepare("INSERT INTO pm_members VALUES(:pm_id, :user_id, 0, 1)");
		$new_mem->execute(array("pm_id"=>$pm_id, "user_id"=>$id));	
	}
	
	return $pm_id;
}

function delete_pm($pm_id){
	global $db;
	$db->query("DELETE * FROM private_messages WHERE pm_id = ".$db->quote($pm_id));
	$db->query("DELETE * FROM pm_members WHERE pm_id = ".$db->quote($pm_id));
	$db->query("DELETE * FROM pm_replies WHERE pm_id = ".$db->quote($pm_id));
	return true;
}
function mark_pms_as_seen($user_id, $pm_ids){
	global $db;
	foreach($pm_ids as $pm_id){
		$update = $db->prepare("UPDATE pm_members SET last_seen = UNIX_TIMESTAMP() WHERE user_id = :user_id AND pm_id = :pm_id");
		$update->execute(array("pm_id"=>$pm_id, "user_id"=>$user_id));
	}
}
function user_not_seen_latest_pm($user_id, $pm_id){
	global $db;
	$get_last_seen = $db->query("SELECT last_seen FROM pm_members WHERE user_id = ".$db->quote($user_id)." AND pm_id = ".$db->quote($pm_id))->fetchColumn();
	$get_last_reply = $db->prepare("SELECT time_sent FROM pm_replies WHERE pm_id = :pm_id ORDER BY time_sent DESC");
	$get_last_reply->execute(array("pm_id"=>$pm_id));
	$last_reply_array = array();
	while($row = $get_last_reply->fetch(PDO::FETCH_ASSOC)){
		$last_reply_array[]=$row['time_sent'];
	}
	$last_reply = $last_reply_array[0];
	if($get_last_seen<$last_reply){
		return true;	
	}else{
		return false;	
	}
}

function valid_pm_id($pm_id, $user_id){
	global $db;
	$check = $db->query("SELECT pm_id FROM pm_members WHERE pm_id = ".$db->quote($pm_id)." AND user_id = ".$db->quote($user_id). " AND visible = 1")->fetchColumn();
	if(empty($check)){
		return false;	
	}else{
		return true;	
	}
}

function users_seen_pm_reply($reply_id, $pm_id){
	global $db;
	$time_sent = $db->query("SELECT time_sent FROM pm_replies WHERE reply_id = ".$db->quote($reply_id))->fetchColumn();
	$get_users = $db->prepare("SELECT * FROM pm_members WHERE last_seen >= :time_sent AND pm_id = :pm_id");
	$get_users->execute(array("time_sent"=>$time_sent, "pm_id"=>$pm_id));
	$users = array();
	while($row = $get_users->fetch(PDO::FETCH_ASSOC)){
		$users[] = get_user_field($row['user_id'], "user_username");	
	}
	
	return $users;
}
function get_unread_pm_quant($user_id){
	global $db;
	$quant = 0;
	$get_active_pms = $db->prepare("SELECT pm_id FROM pm_members WHERE user_id = :user_id AND visible = 1");
	$get_active_pms->execute(array("user_id"=>$user_id));
	while($row = $get_active_pms->fetch(PDO::FETCH_ASSOC)){
		if(user_not_seen_latest_pm($user_id, $row['pm_id'])){
			$quant++;
		}
	}
	
	return $quant++;
}
function pm_reply($pm_id, $reply_text){
	global $db;
	$username = get_user_field($_SESSION['user_id'], "user_username");	
	$insert = $db->prepare("INSERT INTO pm_replies VALUES('', :pm_id, UNIX_TIMESTAMP(), 0, :username, :text)");
	$insert->execute(array("pm_id"=>$pm_id, "username"=>$username, "text"=>$reply_text));
}

function get_pending_friends($username){
	global $db;
	$get_pending = $db->prepare("SELECT * FROM friends WHERE accepted = 0 AND accepter = :username");
	$get_pending->execute(array("username"=>$username));
	$pending_list = array();
	while($row = $get_pending->fetch(PDO::FETCH_ASSOC)){
		$pending_list[] = $row["requester"];
	}
	return $pending_list;
}
function get_friend_status($requester, $accepter){
	global $db;
	$status = array("friends", "pending", "none");
	$check_friends = $db->prepare("SELECT * FROM friends
					WHERE (accepter = :accepter AND requester = :requester)
					OR(accepter = :requester AND requester = :accepter)");
	$check_friends->execute(array("accepter"=>$accepter, "requester"=>$requester));
	$row = $check_friends->fetch(PDO::FETCH_ASSOC);				
	if(!empty($row)&&$row['accepted']==1){
		return $status[0];
	}else if(($row['requester']==$requester)&&($row['accepted']==0)){
		return $status[1];		
	}else if(($row['accepter']==$requester)&&($row['accepted']==0)){
		return $status[1]."v";		
	}else{
		return $status[2];
	}				
}
function add_friend($requester, $accepter){
	global $db;
	if(get_friend_status($requester, $accepter)=="none"){
		$add = $db->prepare("INSERT INTO friends VALUES(:requester, :accepter, 0)");
		$add->execute(array("requester"=>$requester, "accepter"=>$accepter));
		return true;
	}else{
		return false;
	}
}

function accept_f_req($accepter, $requester){
	global $db;
	if(get_friend_status($accepter, $requester)=="pendingv"){
		$accept = $db->prepare("UPDATE friends SET accepted = 1 WHERE accepter = :accepter AND requester = :requester");
		$accept->execute(array("accepter"=>$accepter, "requester"=>$requester));
		$note_m = $accepter." has accepted your friend request.";
		
		$accepter_id = $db->query("SELECT user_id FROM users WHERE user_username=".$db->quote($accepter))->fetchColumn();
		$req_id = $db->query("SELECT user_id FROM users WHERE user_username=".$db->quote($requester))->fetchColumn();
		add_note($req_id, $note_m, "index.php?page=profile&user=".$accepter_id);
		add_rep(3, $accepter_id);
		add_rep(3, $req_id);
		return true;
	}else{
		return false;
	}	
}

function get_rand_debates($quant){
	global $db;
	$get_debates = $db->prepare("SELECT thread_title FROM debating_threads WHERE visible = 1 LIMIT ".$quant);
	$get_debates->execute();
	$debates = array();
	while($row = $get_debates->fetch(PDO::FETCH_ASSOC)){
		$debates[]=$row['thread_title'];
	}
	
	return $debates;
}

function add_note($to, $text, $link){
	global $db;
	
	$insert = $db->prepare("INSERT INTO notifications VALUES('', :to, :text, UNIX_TIMESTAMP(), :link, 0)");
	$insert->execute(array("to"=>$to, "text"=>$text, "link"=>$link));
	
}

function mark_all_notes_read($user_id){
	global $db;
	$update = $db->prepare("UPDATE notifications SET seen = 1 WHERE `to` = :user_id");
	$update->execute(array("user_id"=>$user_id));
}

function get_unread_notes($user_id, $quant=false){
	global $db;
	$select = $db->prepare("SELECT text FROM notifications WHERE `to` = :user_id AND seen = :seen");
	$select->execute(array("user_id"=>$user_id, "seen"=>"0"));
	
	if($quant==true){
		return $select->rowCount();
	}else{
		$notes= array();
		while($row = $select->fetch(PDO::FETCH_ASSOC)){
			$notes[] = $row['text'];
		}
		return $notes;
	}
}

function clear_notes($user_id, $all=false, $to_del){
	global $db;
	if($all==false){
		foreach($to_del as $id){
			$delete = $db->prepare("DELETE FROM notifications WHERE `to` = :user_id AND note_id = :id");
			$delete->execute(array("user_id"=>$user_id, "id"=>$id));
		}	
	}else{
		$delete = $db->prepare("DELETE FROM notifications WHERE `to` = :user_id");
		$delete->execute(array("user_id"=>$user_id));
	}
}


function snc($com_name, $com_pass, $l_username, $l_password, $l_vpassword, $l_firstname, $l_lastname, $l_email){
	global $db;
	$true = 0;
	$errors_com = array();	
	$all_errors = "";
	if(strlen($com_pass)<4){
		$errors_com[] = "Your community passcode is too short.";
	}else if(strlen($com_pass)>20){
		$errors_com[] = "Your community passcode is too long.";
	}
	if(strlen($com_name)<4){
		$errors_com[] = "Your community name is too short.";
	}else if(strlen($com_name)>30){
		$errors_com[] = "Your community name is too long.";
	}
	if(count($errors_com)==0){
		$reg_user = register_user($l_username, $l_password, $l_vpassword, $l_firstname, $l_lastname, $com_pass, $com_name, $l_email, "");
		if($reg_user=="true"){
			$insert = $db->prepare("INSERT INTO communities VALUES('', :com_name, :com_pass)");
			$insert->execute(array("com_name"=>$com_name, "com_pass"=>encrypt($com_pass)));
			$com_id = $db->lastInsertId();
			$com_ipn_ident = encrypt($_POST['snc_leader_email'].$_POST['snc_com_name']);
			$insert1 = $db->prepare("INSERT INTO com_act VALUES(:com_id, :act, :ipn)");
			$insert1->execute(array("com_id"=>$com_id, "act"=>0, "ipn"=>$com_ipn_ident));
			
			$update_com_id = $db->prepare("UPDATE users SET user_com = :com_id, user_rank = 3 WHERE user_username = :username");
			$update_com_id->execute(array("com_id"=>$com_id, "username"=>$l_username));
			$true = 1;

			return array("true",$com_ipn_ident);
			
		}else{
			$all_errors = $reg_user;
		}	
	}else{
		$all_errors = $errors_com;
	}
	
	if($true==0){
		return $all_errors;
	}
	
}

function report_user($by, $reported, $reason, $for_c = array(false)){
	global $db;
	$time = time();
	$fc = 0;
	if($for_c[0]==true){
		//for_c = if for content, cid ,id_c_name, c_table
		$fc = 1;
		$db->query("INSERT INTO reported_content VALUES(".$db->quote($for_c[1]).",".$db->quote($for_c[2]).", ".$db->quote($for_c[3]).", ".$db->quote($by).", ".$db->quote($reported).", ".$time.")");
	}
	$com_id = get_user_community($db->query("SELECT user_id FROM users WHERE user_username = ".$db->quote($reported))->fetchColumn(), "com_id");
	$insert = $db->prepare("INSERT INTO reported_users VALUES(:reported_user, :reason, :reported_by, :com_id, :time, :fc)");
	$insert->execute(array("reported_user"=>$reported, "reason"=>$reason, "reported_by"=>$by, "com_id"=>$com_id, "time"=>$time, "fc"=>$fc));
	return true;
}

function e_d_feature($sf){
	global $db;
	$get_state = $db->query("SELECT activation FROM feature_activation WHERE feature=".$db->quote($sf))->fetchColumn();
	if($get_state=="0"){
		$new_act = 1;
	}else{
		$new_act = 0;
	}
	
	$update = $db->prepare("UPDATE feature_activation SET activation = :new_act WHERE feature=:sf");
	$update->execute(array("new_act"=>$new_act, "sf"=>$sf));
	return $new_act;

}


function get_disabled_message($f){
	global $db;
	return $db->query("SELECT message FROM feature_activation WHERE feature = ".$db->quote($f))->fetchColumn();
}	

function run_admin_query($query){
	global $db;
	$substr_len = 10+strlen(get_user_field($_SESSION['user_id'], "user_code"));
	$valid_pass = "runquery-".get_user_field($_SESSION['user_id'], "user_code").":";


	if(substr($query, 0, $substr_len)===$valid_pass){
		$q = $db->prepare(substr($query, $substr_len));
		$q->execute();
		if(substr($query, $substr_len, 6)==="SELECT"){
			return $q;
			
		}else{
			return "true";
		}
		
	}else{
		return "false";
	}
}

function get_rand_debate($topic = ""){
	global $db;
	if($topic=="0"){
		$ext1 = "";
	}else{
		$get_topic_id = $topic;
		$ext1 = " WHERE topic_id = ".$get_topic_id;
	}
	$count = 0;
	$get_title = $db->prepare("SELECT thread_title FROM debating_threads".$ext1);
	$get_title->execute();
	$title_list = array();
	while($row = $get_title->fetch(PDO::FETCH_ASSOC)){
		$title_list[] = $row["thread_title"];
	}
	if(count($title_list)>0){
		$rand_title = $title_list[rand(0, count($title_list)-1)];
		return $rand_title;
	}else{
		return false;
	}
	
}

function trim_commas($string){
		
		if(substr($string , 0 ,1)==","){
			$string = substr($string,1);
		}
		if(substr($string, strlen($string)-1, strlen($string))==","){
			$string = substr($string, 0, strlen($string)-1);
		}
	return $string;
	
}
function start_comp($type, $opps_array, $end_time, $judges, $topic, $starter_id, $deb_question, $info){
	global $db;

	$acceptance_str = "";
	for($i=0;$i<count($opps_array);$i++){
		$acceptance_str = $acceptance_str."0,";
	}

	$acceptance_str = trim_commas($acceptance_str);

	$comp_com_id = ($type=="0")? get_user_field($_SESSION['user_id'], "user_com"): "0";

	if($judges != "norm"){
		$judges = implode(",",$judges);
	}

	
	$opps = implode(",",$opps_array);
	$insert = $db->prepare("INSERT INTO competitions VALUES('', :title, :type, :starter, :opps, UNIX_TIMESTAMP(), :end, :judges, :com, :acceptance, :info)");
	$insert->execute(array(
			"title"=>$deb_question,
			"type"=>$type,
			"starter"=>$starter_id,
			"opps"=>$opps,
			"end"=>$end_time,
			"judges"=>$judges,
			"com"=>$comp_com_id,
			"acceptance"=>$acceptance_str,
			"info"=>$info
			
	));
	return $db->lastInsertId();
}

function get_comp_acceptance_info($comp_id, $type){
	global $db;
	/*
	acceptance 1 = accepted.
	acceptance 0 = waiting for decision.
	acceptance 2 = rejected.
	*/
	
	$opps = $db->query("SELECT opp_id FROM competitions WHERE comp_id = ".$db->quote($comp_id))->fetchColumn();
	$acceptance = $db->query("SELECT opp_acceptance FROM competitions WHERE comp_id = ".$db->quote($comp_id))->fetchColumn();

	$opps = explode(",", $opps);
	$acceptance = explode(",", $acceptance);
	$count = 0;
	$final_result = array();
	foreach($opps as $opp){
		//OPP_ID=>ACCEPTANCE_STATE
		$final_result[$opp] = $acceptance[$count];
		$count++;
	} 
	
	return $final_result;
	
}

function user_in_comp($user_id, $comp_id, $type){
	global $db;
	
	$host_ids = array();
	$all_cands = get_comp_acceptance_info($comp_id, $type);
	foreach($all_cands as $key=>$value){
		if($value==1){
			$host_ids[]=$key;
		}
	}
	$gci = get_comp_info($comp_id);
	$host_ids[] = $gci["starter_id"];

	foreach($host_ids as $id){
		if( (($type=="0")&&(user_in_group($user_id, $id, $check_act="true")))||(($type=="1")&&(get_user_community($user_id, "com_id")==$id))){
			return true;
			break;
		}		
	}
	
}

function comp_started($comp_id){
	//if started, end will be end time. Otherwise, it will be the comp duration.
	global $db;
	$end = $db->query("SELECT end FROM competitions WHERE comp_id = ".$db->quote($comp_id))->fetchColumn();
	$check = substr($end, 0 ,1);
	if($check==="."||$end==="true"){
		return false;
		
	}else{
		return true;
	}
}

function get_comp_user_starter_id($comp_id, $type){
	global $db;
	$get_starter = $db->query("SELECT starter_id FROM competitions WHERE comp_id = ".$db->quote($comp_id))->fetchColumn();	
	if($type=="0"){
		return $user_id = get_group_leader_id($get_starter);	
	}else{
		return $user_id = $db->query("SELECT  user_id FROM users WHERE user_com = ".$db->quote($get_starter). "AND user_rank = 3")->fetchColumn();
	}
}	

function respond_comp_invite($comp_id, $response, $type, $opp_id){
	global $db;
	
	$acceptance_info = get_comp_acceptance_info($comp_id, $type);
	if(array_key_exists($opp_id, $acceptance_info)){
		$acceptance_info[$opp_id]=$response;
	}	
	$acc_str = implode(",",$acceptance_info);
	$update = $db->prepare("UPDATE competitions SET opp_acceptance = :new_acc WHERE comp_id = :comp_id");
	$update->execute(array("new_acc"=>$acc_str, "comp_id"=>$comp_id));

}	

function waiting_for_comp_response($comp_id, $type, $opp_id){
	global $db;
	$acc_info = get_comp_acceptance_info($comp_id, $type);
	if((array_key_exists($opp_id, $acc_info))&&($acc_info[$opp_id]=="0")){
		return true;
	}else{
		return false;
	}	
}	

function get_comp_info($comp_id){
	global $db;
	$info = $db->prepare("SELECT * FROM competitions WHERE comp_id = :comp_id");
	$info->execute(array("comp_id"=>$comp_id));
	
	return $info->fetch(PDO::FETCH_ASSOC);
	
	//get_comp_info($comp_id)["field"]
}

function get_all_users_in_p_comp($comp_id){
	global $db;
	$opp_id_as_key = get_comp_acceptance_info($comp_id, "0");
	$opp_ids = array();
	
	foreach($opp_id_as_key as $opp_id=>$element){
		$opp_ids[] = $opp_id;
	}	
	$gci = get_comp_info($comp_id);
	$opp_ids[] = $gci["starter_id"];
	$user_ids= array();
	
	foreach($opp_ids as $group_id){
		foreach(get_users_in_group($group_id) as $uid){
			$user_ids[] = $uid;
		}
	}	
	
	return $user_ids;
}


function judge_respond_invite($comp_id, $judge_id, $res){
	global $db;
	
	$judges_str = $db->query("SELECT judges FROM competitions WHERE comp_id =".$db->quote($comp_id))->fetchColumn();
	$judges_array = explode(",", $judges_str);
	
	if(in_array($judge_id, $judges_array)){
		$key_unset = array_search($judge_id,$judges_array);
		unset($judges_array[$key_unset]);
		if($res=="1"){
			$judges_array[] = "-".$judge_id;
		}
		$judges_str = implode(",",$judges_array);	
		$db->query("UPDATE competitions SET judges = ".$db->quote($judges_str)." WHERE comp_id = ".$db->quote($comp_id));
		return true;
	}else{
		return false;
	}
	
}

function get_judge_list($comp_id){
	global $db;
	$judges_str = $db->query("SELECT judges FROM competitions WHERE comp_id =".$db->quote($comp_id))->fetchColumn();
	$judges_array = explode(",", $judges_str);
	$clean_arr = array();
	foreach($judges_array as $id){
		
		if(substr($id,0,1)=="-"){
			$clean_arr[]=substr($id,1);
		}else{
			$clean_arr[]=$id;
		}
	}	
	return $clean_arr;	
}	

function get_special_judge_disname($judge_key){
	$disname = substr($judge_key,4);
	return $disname = substr($disname, 0, -8);
}

function get_judge_acceptance($comp_id){
	global $db;
	$judges_str = $db->query("SELECT judges FROM competitions WHERE comp_id =".$db->quote($comp_id))->fetchColumn();
	if($judges_str!="norm"){
		$acceptance = array();
		$judges_array = explode(",", $judges_str);
		if(!empty($judges_str)){
			foreach($judges_array as $id){
				if(substr($id,0,1)=="-"){
					$acceptance[substr($id,1)]="1";
				}else{
					$acceptance[$id]="0";
				}
			}
		
			return $acceptance;
		}else{
			return array("empty");
		}
	}else{
		return "norm";
	}
}

function check_comp_ready($comp_id, $type){
	global $db;
	$acceptance = get_comp_acceptance_info($comp_id, $type);
	if(comp_started($comp_id)==false){
		
		if(in_array("0", $acceptance)){
			return false;
			//Still waiting for all opps 
		}else if(implode(",",array_unique($acceptance))=="2"){
			//all opps declined. Delete comp.
			add_note(get_comp_user_starter_id($comp_id, $type), "A competition you previously created has been cancelled as all your requested opponents declined the invitation to participate.", "");
			$delete = $db->prepare("DELETE FROM competitions WHERE comp_id = :comp_id");
			$delete->execute(array("comp_id"=>$comp_id));
		}else{
			// start comp.
			$jacceptance=get_judge_acceptance($comp_id);
			if(($jacceptance!="norm")&&(!in_array("1",$jacceptance)||$jacceptance==array("empty"))){
				$db->query("UPDATE competitions SET judges = 'norm' WHERE comp_id = ".$db->quote($comp_id));
				add_note(get_comp_user_starter_id($comp_id, $type), "All the judges you requested to judge your newly started competition either declined or have not responded to the invite in time. This means anyone who is not participating can judge the competiton.","");
				if($jacceptance!=array("empty")&&$jacceptance!="norm"){
					foreach($jacceptance as $jid=>$acc){
						add_note($jid, "A competition you were previously invited to judge has now started and you are now too late to accept the invitation.","");
					}
				}
				
			}else{
				foreach(get_judge_list($comp_id) as $jid){
					add_note($jid,"A competition you are meant to be judging has just started. Click here to view it!", "index.php?page=view_comp&comp=".$type.$comp_id);
				}
			}
			$comp_dur = substr($db->query("SELECT end FROM competitions WHERE comp_id = ".$db->quote($comp_id))->fetchColumn(), 1);
			$end = time()+($comp_dur*3600);
			$update = $db->prepare("UPDATE competitions SET end = :end WHERE comp_id = :id");
			$update->execute(array("id"=>$comp_id, "end"=>$end));
			
			if($type=="0"){
				$all_users_to_note = get_all_users_in_p_comp($comp_id);
				foreach($all_users_to_note as $user_id){
					echo $user_id;
					add_note($user_id, "A competition you are involved in has just started. Click here to start debating!", "index.php?page=view_comp&comp=".$type.$comp_id);
				}
				$jval = $db->query("SELECT judges FROM competitions WHERE comp_id = ".$db->quote($comp_id)." LIMIT 1")->fetchColumn();
				$comp_c_id = $db->query("SELECT comp_com_id FROM competitions WHERE comp_id = ".$db->quote($comp_id)." LIMIT 1")->fetchColumn();
				if($jval=="norm"){
					add_com_feed($comp_c_id, "A new private competition has started and everyone not involved is welcome to judge it which will add to your reputation! <a href = 'index.php?page=view_comp&comp=0".$comp_id."' >Click here</a> to see!");
				}
			}else{
				
				$opp_id_as_key = get_comp_acceptance_info($comp_id, "1");
				$opp_ids = array();
				foreach($opp_id_as_key as $opp_id=>$element){
					if($element=="1"){
						$opp_ids[] = $opp_id;
					}
				}	
				$gci = get_comp_info($comp_id);
				$opp_ids[] = $gci["starter_id"];
				foreach($opp_ids as $cid){
					
					add_com_feed($cid, "A global competition we are all involved in has just started! <a href = 'index.php?page=view_comp&comp=1".$comp_id."' >Click here</a> to get involved.");
				
				}
				
			}
			return true;
		}
	}
	
}

function get_comp_starter_by_type($comp_id, $type){
	global $db;
	//returns group name or com name.
	$gci = get_comp_info($comp_id);
	$starter_id = $gci["starter_id"];
	if($type=="0"){
		return $db->query("SELECT group_name FROM private_groups WHERE group_id = ".$db->quote($starter_id))->fetchColumn();
	}else{
		return $db->query("SELECT com_name FROM communities WHERE com_id = ".$db->quote($starter_id))->fetchColumn();
	}
}

function get_cand_side($comp_id, $cand_id){
	global $db;
	$side = $db->query("SELECT side FROM comp_sides WHERE comp_id = ".$db->quote($comp_id)." AND cand_id = ".$db->quote($cand_id))->fetchColumn();
	return $side;
}

function user_already_voted_comp_arg($table, $user_id, $arg_id){
	global $db;
	$table_columns = array("comp_arguments"=>"arg_id", "comp_arg_replies"=>"reply_id");
	$cur_votes= $db->query("SELECT voters FROM `".$table."` WHERE `".$table_columns[$table]."` = ".$db->quote($arg_id))->fetchColumn();
	$cur_votes_arr = explode(",",$cur_votes);
	if(in_array($user_id, $cur_votes_arr)){
		return true;
	}else{
		return false;
	}
}

function add_voter_comp_arg($table, $user_id, $arg_id){
	global $db;
	if(!user_already_voted_comp_arg($table, $user_id, $arg_id)){
		$table_columns = array("comp_arguments"=>"arg_id", "comp_arg_replies"=>"reply_id");
		$cur_votes= $db->query("SELECT voters FROM `".$table."` WHERE `".$table_columns[$table]."` = ".$db->quote($arg_id))->fetchColumn();
		$cur_votes = $cur_votes.",".$user_id;
		$cur_votes = trim_commas($cur_votes);
		$update = $db->prepare("UPDATE `".$table."` SET voters = :new_str WHERE `".$table_columns[$table]."` = :arg_id");
		$update->execute(array("new_str"=>$cur_votes, "arg_id"=>$arg_id));
		return true;
	}else{
		return false;
	}		
}

function get_comp_winner($comp_id, $type){
	global $db;
	$scores = get_comp_acceptance_info($comp_id,$type);
	$gci = get_comp_info($comp_id);
	$scores[$gci["starter_id"]] = "0";
	foreach($scores as $key=>&$value){
		$value = 0;
		$comp_args_count = $db->prepare("SELECT points FROM comp_arguments WHERE comp_id = :comp_id AND cand_id = :cand");
		$comp_args_count->execute(array("comp_id"=>$comp_id, "cand"=>$key));
	
		while($row = $comp_args_count->fetch(PDO::FETCH_ASSOC)){
			$ex = intval($row['points']);
			$value = $value + $ex;
		}
		
		$arg_replies_count = $db->prepare("SELECT points FROM comp_arg_replies WHERE comp_id = :comp_id AND user_cand_id= :cand");
		$arg_replies_count->execute(array("comp_id"=>$comp_id, "cand"=>$key));
		
		while($row_ = $arg_replies_count->fetch(PDO::FETCH_ASSOC)){
			$ex_ = intval($row_['points']);
			$value = $value + $ex_;
		}
		
	}
	//scores: cand_id => points
	//winner either is cand_id or an array of joint winners
	$winner = array_search(max($scores), $scores);
	$highest_score = max($scores);
	$joint = array();
	$draw = false;
	foreach($scores as $cid=>$score){
		if($score==$highest_score){
			$joint[]=$cid;
			if(count($joint)==2){
				$draw = true;
			}
		}
	}
	
	if($draw==true){
		return $joint;
	}else{
		return array($winner);
	}
}

function comp_ended($comp_id){
	global $db;
	$end = $db->query("SELECT end FROM competitions WHERE comp_id = ".$db->quote($comp_id))->fetchColumn();
	if($end=="true"){
		return true;
	}else{
		return false;
	}	
}
function add_rep($quant, $user_id, $reason = ""){
	global $db;
	if($reason!=""){
		add_note($user_id, "You just recieved ".$quant." reputation points because ".$reason, "");
	}
	
	$update= $db->prepare("UPDATE users SET user_rep = user_rep + :quant WHERE user_id = :id");
	$update->execute(array("quant"=>$quant, "id"=>$user_id));
}

function get_users_contributed_comp($comp_id, $cand_id){
	global $db;
	$user_ids = array();
	$get = $db->prepare("SELECT user_id FROM comp_arguments WHERE cand_id = :cand_id AND comp_id = :comp_id");
	$get->execute(array("cand_id"=>$cand_id, "comp_id"=>$comp_id));
	while($row = $get->fetch(PDO::FETCH_ASSOC)){
		$user_ids[] = $row['user_id'];
	}
	$get_ = $db->prepare("SELECT user_id FROM comp_arg_replies WHERE user_cand_id = :cand_id AND comp_id = :comp_id");
	$get_->execute(array("cand_id"=>$cand_id, "comp_id"=>$comp_id));
	while($row = $get_->fetch(PDO::FETCH_ASSOC)){
		$user_ids[] = $row['user_id'];
	}
	
	return $user_ids = array_unique($user_ids);
}
function end_comp($comp_id){
	global $db;
	$comp_info = get_comp_info($comp_id);
	if((substr($comp_info['end'],0,1)!=".")&&(!comp_ended($comp_id))){
		if(time()>intval($comp_info['end'])){
			if($comp_info['comp_type']=="0"){
				$all_users = get_all_users_in_p_comp($comp_id);
				foreach($all_users as $uid){
					add_note($uid, "A competition you are involved in ('".$comp_info['comp_title']."') has just ended. Click here to see the results.", "index.php?page=view_comp&comp=".$comp_info['comp_type'].$comp_id);
				}
			}else{
				$opp_id_as_key = get_comp_acceptance_info($comp_id, "1");
				$opp_ids = array();
				foreach($opp_id_as_key as $opp_id=>$element){
					if($element=="1"){
						$opp_ids[] = $opp_id;
					}
				}	
				
				$opp_ids[] = $comp_info["starter_id"];
				foreach($opp_ids as $cid){
				
					add_com_feed($cid, "A global competition we are all involved in has just ended! <a href = 'index.php?page=view_comp&comp=1".$comp_id."' >Click here</a> to see the results.");
			
				}
			}
	
			$end = $db->prepare("UPDATE competitions SET end = 'true' WHERE comp_id = :comp_id");
			$end->execute(array("comp_id"=>$comp_id));
			
			$winner_ids = get_comp_winner($comp_id, $comp_info['comp_type']);
			if(count($winner_ids)==1){
				$winner = $winner_ids[0];
				$gc_string = ($comp_info['comp_type']=="0")? "group":"community";
				$rew_users = get_users_contributed_comp($comp_id, $winner);
				foreach($rew_users as $uid){
					add_rep(10, $uid, " you contributed to your ".$gc_string."'s victory in the competition '".$comp_info['comp_title']."'");
					add_badge("Being on the winning side of a competition.", $uid, "you contributed to your ".$gc_string."'s victory in the competition '".$comp_info['comp_title']."'");
				}
				
			}
			return true;
		}else{
			return false;
		}
	}
}

function get_com_leader_id($com_id){
	global $db;
	return $db->query("SELECT user_id FROM users WHERE user_com = ".$db->quote($com_id)." AND user_rank = 3")->fetchColumn();
}

function add_badge($text, $user_id, $reason = ""){
	global $db;
	
	$add = $db->prepare("INSERT INTO badges VALUES('',:text, :uid)");
	$add->execute(array("text"=>$text, "uid"=>$user_id));
	add_note($user_id, "You just recieved a new badge because ".$reason, "index.php?page=profile&user=".$user_id);
}

function first_login($user_id){
	global $db;
	$check = $db->query("SELECT user_id FROM first_login WHERE user_id = ".$db->quote($user_id))->fetchColumn();
	if(!empty($check)){
		$db->query("DELETE FROM first_login WHERE user_id = ".$db->quote($user_id));
		return true;
	}else{
		return false;
	}
}

function add_b_news($text, $title, $edit){
	global $db;
	if(!empty($edit)){
		$db->query("UPDATE site_news SET feed_text = ".$db->quote($text).",title = ".$db->quote($title)." WHERE feed_id = ".$db->quote($edit));
	}else{
		$db->query("INSERT INTO site_news VALUES('',".$db->quote($text).", ".$db->quote($title).", UNIX_TIMESTAMP())");
	}
}
function del_b_news($id){
	global $db;
	$db->query("DELETE FROM site_news WHERE feed_id = ".$db->quote($id));
}

function check_c_reported($id, $icn, $ct){
	global $db;
	$check = $db->query("SELECT cid FROM reported_content WHERE cid = ".$db->quote($id)." AND id_c_name=".$db->quote($icn)." AND c_table=".$db->quote($ct))->fetchColumn();
	if(!empty($check)){
		return true;
	}else{
		return false;
	}
}

function has_full_profile($user_id){
	global $db;
	$get = $db->prepare("SELECT general, reg_beliefs, pol_views FROM about_user WHERE user_id = :uid");
	$get->execute(array("uid"=>$user_id));
	$res = $get->fetchAll();
	if(!empty($res)){
		$res = $res[0];
		$result = true;
		foreach($res as $value){
			if(empty($value)){
				$result = false;
				break;
			}
		}
		return $result;
	}else{
		return false;
	}
}

function check_not_rep_inc($type, $user_id){
	global $db;
	$check = $db->query("SELECT `inc` FROM `only_once_rep_inc` WHERE `for` = ".$db->quote($type)." AND `user_id` = ".$db->quote($user_id))->fetchColumn();
	if(empty($check)){
		return true;
	}else{
		return false;
	}
}

function user_not_posted($username){
	global $db;
	$c1 = $db->query("SELECT user_replied FROM thread_replies WHERE user_replied = ".$db->quote($username)." LIMIT 1")->fetchColumn();
	$c2 = $db->query("SELECT user_replied FROM iwonder_replies WHERE user_replied = ".$db->quote($username)." LIMIT 1")->fetchColumn();
	$c3 = $db->query("SELECT thread_starter FROM debating_threads WHERE thread_starter = ".$db->quote($username)." LIMIT 1")->fetchColumn();
	if(empty($c1)&&empty($c2)&&empty($c3)){
		return true;
	}else{
		return false;
	}
}
function is_users_x_post($x, $username){
	global $db;
	$c1 = $db->query("SELECT user_replied FROM thread_replies WHERE user_replied = ".$db->quote($username))->rowCount();
	$c2 = $db->query("SELECT user_replied FROM iwonder_replies WHERE user_replied = ".$db->quote($username))->rowCount();
	$c3 = $db->query("SELECT thread_starter FROM debating_threads WHERE thread_starter = ".$db->quote($username))->rowCount();
	if(($c1+$c2+$c3)==$x){
		return true;
	}else{
		return false;
	}
}

function get_static_content($cont_name){
	global $db;
	return $db->query("SELECT cont FROM static_content WHERE cont_name = ".$db->quote($cont_name))->fetchColumn();
}

function change_static_content($cont_name, $new_cont){
	global $db;
	$db->query("UPDATE static_content SET cont = ".$db->quote($new_cont)." WHERE cont_name = ".$db->quote($cont_name));
}
function refresh_password_resets(){
	global $db;
	$db->query("DELETE FROM password_resets WHERE expire_time < UNIX_TIMESTAMP()");
}


function send_admin_note($note){
	global $db;
	$emails = $db->query("SELECT cont FROM static_content WHERE cont_name = 'admin_note_emails' LIMIT 1")->fetchColumn();
	$emails = explode(",", $emails);
	foreach($emails as $email){
		if(filter_var($email, FILTER_VALIDATE_EMAIL)){
			mail($email, "BuzzZap Admin Note", $note, "From: admin@buzzzap.com");
		}
	}
}

function static_cont_rec_vars($str, $replacements){
	foreach($replacements as $key=>$value){
		$str = str_replace("--".$key, $value, $str);
	}

	return $str;
}

function contains_blocked_word($txt){
	global $db;
	$blocked_words= explode(",",get_static_content("blocked_words"));
	$result = false;
	foreach($blocked_words as $word){

		if(preg_match("/".$word."/", $txt)){
			$repval = substr($word, 0,1);
			for($i=0;$i<strlen($word)-2;$i++){
				$repval.="*";
			}
			$repval.=substr($word, strlen($word)-1,strlen($word));
			$txt = str_replace($word, $repval, $txt);

			$result = true;
		}
	}
	return array($result, $txt);
}

function get_question_type($q, $return_type){
	global $db;

	/* return type = 1
	func return values = "closed", "open", "state"

		return type = 2

	func returns array of corresponding response types	
	*/

	$possibility = array("closed"=>0, "open"=>0, "state"=>0);
	$q = trim($q);
	$closed_ident_words = explode(",",get_static_content("closed_q_ident_words"));
	
	$first_word_q = explode(" ", $q)[0];
	$all_chars = str_split($q);
	$has_word = false;
	foreach($closed_ident_words as $word){
		if(strtolower($first_word_q)==strtolower($word)){
			$possibility["closed"] ++;
			$has_word = true;
			break;
		}
	}
	if($has_word==false){
		$possibility["state"] ++;
		$possibility["open"] ++;
	}
	if(in_array("?", $all_chars)){
		$possibility["closed"] ++;
		$possibility["open"] ++;
	}else{
		$possibility["state"] ++;
	}

	$qtype = array_keys($possibility,max($possibility))[0];
	if($return_type==1){
		return $qtype;
	}else if($return_type==2){
		switch($qtype){
			case "open":
				return array();
			case "closed":
				return array("Yes", "No", "Maybe");
			case "state":
				return array("Agree", "Disagree", "Maybe");
		}
	}
}

function user_browser(){
	if(strpos($_SERVER['HTTP_USER_AGENT'], 'MSIE') !== FALSE){
 		return "IE";
	}else if(strpos($_SERVER['HTTP_USER_AGENT'], 'Trident') !== FALSE){ //For Supporting IE 11
    	return 'IE';
	}else if(strpos($_SERVER['HTTP_USER_AGENT'], 'Firefox') !== FALSE){
   		return 'firefox';
	}else{
		return "supported";
	}
}	
?>