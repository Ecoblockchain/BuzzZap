<?php
if($check_valid!="true"){
	header("Location: index.php?page=home");
	exit();
}
if(loggedin()){
	
	?>
		<div class = 'page-path'><?php echo get_user_field($_SESSION['user_id'], "user_username"); ?> > My Notifications
		<div class = "loggedin-headers">Your Notifications</div>
		<div id = "note-menu"><a class = "note-menu" href = "index.php?page=notifications&cleara=true">Clear All</a><span class ="note-menu" id = "ds-n">  &middot; Delete Selected</span></div>
		<br><br>
	<?php
		$counter = 0;
		$act_id= array();
		$get_notes = $db->prepare("SELECT * FROM notifications  WHERE `to` = :user_id OR `to` = :all ORDER BY time DESC");
		$get_notes->execute(array("user_id"=>$_SESSION['user_id'], "all"=>"--all"));
		$quant = $get_notes->rowCount();
		$dids = array();
		$check_all_count_show = array(0,0);
		
		echo "<div class = 'profile-info-container' style = 'font-size: 90%;width:99%;padding:10px'>";
		while($row = $get_notes->fetch(PDO::FETCH_ASSOC)){
			$dids[] = $row['note_id'];
			$act_id[$counter]=$row['note_id'];
			$as_arr = explode(",",trim_commas($row['seen']));
			$not_seen_an_all = ($row['to']=="--all")? !in_array($_SESSION['user_id'], $as_arr)&&!in_array("-".$_SESSION['user_id'], $as_arr): false;
			if($row['to']=="--all"&&$not_seen_an_all){
				$db->query("UPDATE notifications SET `seen` = CONCAT(`seen`,".$db->quote(",".$_SESSION['user_id']).") WHERE note_id = ".$db->quote($row['note_id']));
			}
			if($row['to']=="--all"){
				$check_all_count_show[0]++;
			}
			if($row['seen']=="0"||$not_seen_an_all){
				$b = "<b>";
				$b_ = "</b>";
			}else{
				$b = "";
				$b_ = "";
			}
			if(!in_array("-".$_SESSION['user_id'], $as_arr)){
				echo  $b."<span style = 'color:grey;'>".date("d/M/Y H:i", $row['time']).
				"</span><a href = '".$row['link']."'><div id = 'note-line' style = 'white-space:normal;'>".$row['text']."</div></a>".$b_."
				<div class = 'note-sel-box' id = 'sel-box-".$counter."'></div>
				<hr size = '1'>";
			}else{
				$check_all_count_show[1]++;
			}
			$counter++;	
		}
		echo "</div>";
		if(count(array_unique($check_all_count_show))==1){
			echo "<div id = 'no-threads-message'>You have no notifications.</div> ";
		}else if($quant==0){
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
			clear_notes($_SESSION['user_id'], false, $dids);
			header("Location: index.php?page=notifications");
		}
		mark_all_notes_read($_SESSION['user_id']);
}else{
	header("Location: index.php?page=home");
}

?>