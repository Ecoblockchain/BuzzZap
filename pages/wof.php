<?php
if($check_valid!="true"){
	header("Location: index.php?page=home");
	exit();
}
if(loggedin()){
	?>
	<div class = "wof-container" style = 'float: left;'>
		<span class = 'wof-cont-title'>BuzzZap Community Leader Board</span><br>
		<br>
		<?php
			$get1 = $db->query("SELECT com_name FROM com_profile ORDER BY com_rep DESC LIMIT 20");
			$count = 1;
			$color = "#59c3d8";
			foreach($get1 as $com){
				if($count==1){
					$color = "gold";
				}else if($count==2){
					$color = "#59c3d8";
				}
				echo "<span style = 'color:".$color."'>".$count.".".add_profile_link($com['com_name'], 1, 'color: '.$color)."</span><hr size = '1'>";
				$count++;
			}
		?>
	</div>
	<div class = "wof-container" style = 'float: right;'>
		<span class = 'wof-cont-title'>BuzzZap User Leader Board</span><br>
		<br>
		<?php
			$get2 = $db->query("SELECT user_username FROM users ORDER BY user_rep DESC LIMIT 20");
			$count = 1;
			$color = "#59c3d8";
			foreach($get2 as $user){
				if($count==1){
					$color = "gold";
				}else if($count==2){
					$color = "#59c3d8";
				}
				echo "<span style = 'color:".$color."'>".$count.".".add_profile_link($user['user_username'], 0, 'color: '.$color)."</span><hr size = '1'>";
				$count++;
			}
		?>
	</div>
	<?php
}else{
	header("Location: index.php?page=home");
}

?>