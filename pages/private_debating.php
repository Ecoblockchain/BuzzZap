<?php
if($check_valid!="true"){
	header("Location: index.php?page=home");
	exit();
}
if(loggedin()){
	$user_id = $_SESSION['user_id'];
	$com_id = get_user_field($user_id, "user_com");
	if(isset($_GET['d'])){
		$extra_get = "&d=g";	
		$dtype = "Global";
		$title = "Global Debating Categories <br>For All Communities";
	}else{
		$extra_get = "";
		$dtype = "Private";
		$com_name = $db->query("SELECT com_name FROM communities WHERE com_id = '$com_id'")->fetchColumn();
		$title = $com_name." Private Debating<br> Categories";
	}
	
	$get_topics = $db->query("SELECT * FROM debating_topics");
	?>	
		<script>
		$(document).ready(function(){
			<?php
			for($i=0;$i<=$get_topics->rowCount();$i++){
			?>
				$("#t-<?php echo $i; ?>").mouseover(function(){
					$(this).animate({letterSpacing:"7px"}, 300);
				}).mouseleave(function(){
					$(this).animate({letterSpacing:"2px"}, 300);
				});
			<?php	
			}
			?>
		});
		</script>
		<div class = 'page-path'>Debating > <?php echo $dtype; ?> Debating</div>
		<div class = "title-private-debate"><?php echo $title ;?></div><hr size = '1'><br><br>
		<div class = "topic-container">
	
	<a href = "index.php?page=private_debating_topic&amp;topic_id=0<?php echo $extra_get; ?>" style = "text-decoration:none;">
			<div class = "topic-link" id = "t-0">
				<?php echo "<span style = 'color:orange;'>All Latest</span>"; ?>
			</div>
			
		</a>
	<?php
	$count = 0;	
	foreach($get_topics as $topic){
		?>	
		<a href = "index.php?page=private_debating_topic&amp;topic_id=<?php echo $topic['topic_id'].$extra_get;?>" style = "text-decoration:none;">
			<div class = "topic-link" id = "t-<?php echo $count; ?>">
				<?php echo $topic['topic_name']; ?>
			</div>
			
		</a>
		<?php
		$count++;
	}
	?>
	</div>
	<?php
}else{
	header("Location: index.php?page=home");
}
?>