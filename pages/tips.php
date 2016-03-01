<?php
if($check_valid!="true"){
	header("Location: index.php?page=home");
	exit();
}
if(loggedin()){
	?>
	<div class = 'page-path'> Debating > BuzzZap Tips</div>
	<div class = "title-private-debate" style = 'font-size:330%'>BuzzZap Usage Tips</div>
	<div id = "tip-container">
		<?php
		$get_tips = $db->query("SELECT * FROM `notifications` WHERE `to` = '--all' AND `text` LIKE 'Tip:%' ORDER BY note_id DESC");

		foreach($get_tips as $tip){
			echo "<div class = 'tip-row'>".substr($tip['text'],4)."</div><hr size = '1'>";
		}
		?>
	</div>
	<?php
}else{
	header("Location: index.php?page=home");
}

?>