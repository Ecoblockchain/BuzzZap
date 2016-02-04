<?php
if($check_valid!="true"){
	header("Location: index.php?page=home");
	exit();
}
if(loggedin()){
	?>
	<div class = 'page-path'>Debating > Wall Of Fame </div><br>
	<div class = "wof-container" style = 'float: left;'>
		<span class = 'wof-cont-title'>BuzzZap Community Leader Board</span><br>
		<br>
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
	<div class = "wof-container" style = 'float: right;'>
		<span class = 'wof-cont-title'>BuzzZap User Leader Board</span><br>
		<br>
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
	<?php
}else{
	header("Location: index.php?page=home");
}

?>