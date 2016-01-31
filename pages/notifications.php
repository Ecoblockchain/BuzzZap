<?php
if($check_valid!="true"){
	header("Location: index.php?page=home");
	exit();
}
if(loggedin()){
	
	?>

		<div class = "note_title">Your Notifications</div>
		<div id = "note-menu"><a class = "note-menu" href = "index.php?page=notifications&cleara=true">Clear All</a><span class ="note-menu" id = "ds-n">  &middot; Delete Selected</span></div>
		<br><br>
	<?php
		$counter = 0;
		$act_id= array();
		$get_notes = $db->prepare("SELECT * FROM notifications  WHERE `to` = :user_id ORDER BY time DESC");
		$get_notes->execute(array("user_id"=>$_SESSION['user_id']));
		$quant = $get_notes->rowCount();
		if($quant>0){
			while($row = $get_notes->fetch(PDO::FETCH_ASSOC)){
				$act_id[$counter]=$row['note_id'];
				if($row['seen']=="0"){
					$b = "<b>";
					$b_ = "</b>";
				}else{
					$b = "";
					$b_ = "";
				}
				echo  $b."<span style = 'color:grey;'>".date("d/M/Y H:i", $row['time']).
				"</span><a href = '".$row['link']."'><div id = 'note-line'>".$row['text']."</div></a>".$b_."
				<div class = 'note-sel-box' id = 'sel-box-".$counter."'></div>
				<hr size = '1'>";
				$counter++;
			}
		}else{
			echo "<div id = 'no-threads-message'>You have no notifications.</div> ";
		}
		?>
		<script>
		$(document).ready(function(){
			var clicked = [];
			<?php
			$count = 0;
			while($count<=$quant){
				?>
				clicked.push(1);
				<?php
				$count++;
			}
			?>
			var click_val = [];
			$(".note-sel-box").click(function(){
				$("#ds-n").fadeIn();
				clicked[parseInt($(this).attr("id").substring(8))]++;
				if(clicked[parseInt($(this).attr("id").substring(8))]%2==0){
					$("#"+$(this).attr("id")).html("<div class = 'note-sel-content'>&middot;</div>");
				}else{
					$("#"+$(this).attr("id")).html("<div class = 'note-sel-content'></div>");
				}
				
			});
			$("#ds-n").click(function(){
				var count = 0;
				var selected = [];
				while(count<=clicked.length){
					if(clicked[count]%2==0){
						selected.push(count);
					}
					count++;
				}
				window.location="index.php?page=notifications&del_sel="+selected;
			});
			

		});
		</script>
		<?php
		if(isset($_GET['del_sel'])){
			$del_sel = explode(",",htmlentities($_GET['del_sel']));
			$del_ids = array();
			foreach($act_id as $key=>$value){
				if(in_array($key, $del_sel)){
					$del_ids[]=$value;
				}
			}
			clear_notes($_SESSION['user_id'], $all=false, $del_ids);
			header("Location: index.php?page=notifications");
		}
		if((isset($_GET['cleara']))&&($_GET['cleara']=="true")){
			clear_notes($_SESSION['user_id'], true, array());
			header("Location: index.php?page=notifications");
		}
		mark_all_notes_read($_SESSION['user_id']);
}else{
	header("Location: index.php?page=home");
}

?>