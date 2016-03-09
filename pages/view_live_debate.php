<?php
if($check_valid!="true"){
	header("Location: index.php?page=home");
	exit();
}
if(loggedin()){
	if(isset($_GET['did'])){
		$did = htmlentities($_GET['did']);
		?>	
		<script>
			$(function(){

			});
		</script>
		<?php

		$podium_struct = get_ldeb_struct($did);
		$opp_pod_count = $podium_struct[get_ldeb_val($did,"opp_id")];
		$own_pod_count = $podium_struct[get_ldeb_val($did,"starter_id")] + 2;
		echo $opp_cont_width = ($opp_pod_count * 170) + 20; //pcount * (pod_width+mleft) + mleft
		echo $own_cont_width = ($own_pod_count * 280) + 30; //pcount * (pod_width+mleft) + mleft
		echo "<div id = 'ldeb-opp-pod-container' style = 'width:".$opp_cont_width."px;'>";
		for($i=1;$i<=$opp_pod_count;$i++){
			echo "<div class = 'podium-container'></div>";
		}
		echo "</div>";

		echo "<div id = 'ldeb-own-pod-container'  style = 'width:".$own_cont_width."px;'>";
		for($i=1;$i<=$own_pod_count;$i++){
			echo "<div class = 'pod-own-container'>";
				echo "<div class = 'own-mics-container'></div>";
				echo "<div class = 'pod-own-top-container'></div>";
			echo "</div>";
		}
		echo "</div>";
	}else{
		header("Location: index.php?page=home");
	}
}else{
	header("Location: index.php?page=home");
}
?>		