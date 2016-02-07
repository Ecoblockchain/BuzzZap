<?php
if($check_valid!="true"){
	header("Location: index.php?page=home");
	exit();
}
if(loggedin()){
	?>
	<div class = 'page-path'>Debating > Wall Of Fame </div><br>
	<div class = "wof-container">
		<div style = 'padding:10px;' class = 'wof-cont-title'>BuzzZap Community Leaderboard</div>
		<div class = 'wof-info'>This board shows the best communities on the whole of BuzzZap based on reputation.</div><br>
		<?php
			$hidden_coms = explode(",",get_static_content("hide_coms"));
			$get1 = $db->query("SELECT com_name,com_id FROM com_profile ORDER BY com_rep DESC LIMIT 20");
			$count = 1;
			$color = "#59c3d8";
			foreach($get1 as $com){
				if(!in_array($com['com_id'], $hidden_coms)){
					if($count==1){
						$color = "gold";
					}else if($count==2){
						$color = "#59c3d8";
					}
					echo "<span style = 'color:".$color."'>".$count.".".add_profile_link($com['com_name'], 1, 'color: '.$color)."</span><hr size = '1'>";
					$count++;
				}
			}
		?>
	</div>
	<div class = "wof-container">
		<div style = 'padding:10px;' class = 'wof-cont-title'>BuzzZap User Leaderboard</div>
		<div class = 'wof-info'>This board shows the best users on the whole of BuzzZap based on reputation.</div><br>
		<?php
			$get2 = $db->query("SELECT user_username,user_com FROM users ORDER BY user_rep DESC LIMIT 20");
			$count = 1;
			$color = "#59c3d8";
			foreach($get2 as $user){
				if(!in_array($user['user_com'], $hidden_coms)){
					if($count==1){
						$color = "gold";
					}else if($count==2){
						$color = "#59c3d8";
					}
					echo "<span style = 'color:".$color."'>".$count.".".add_profile_link($user['user_username'], 0, 'color: '.$color)."</span><hr size = '1'>";
					$count++;
				}
			}
		?>
	</div>
	<div class = "wof-container">
		<div style = 'padding:10px;' class = 'wof-cont-title'><?php echo get_user_community($_SESSION['user_id'], "com_name"); ?> Group Leaderboard</div>
		<div class = 'wof-info'>This board shows the best groups in your community based on reputation.</div><br>
		<?php
			$get3 = $db->query("SELECT * FROM private_groups WHERE com_id = ".$db->quote(get_user_field($_SESSION['user_id'], "user_com")));
			$count = 1;
			$color = "#59c3d8";
			//gid => rep
			$reps = array();
			foreach($get3 as $g){
				$reps[$g["group_name"]] = get_group_rep($g['group_id']);
			}
			foreach($reps as $gname=>$rep){
				if($count==1){
					$color = "gold";
				}else if($count==2){
					$color = "#59c3d8";
				}
				echo "<span style = 'color:".$color."'>".$count.".".$gname."</span><hr size = '1'>";
				$count++;
			}
		?>
	</div>
	<?php
}else{
	header("Location: index.php?page=home");
}

?>