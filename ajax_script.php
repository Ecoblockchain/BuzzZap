<?php
require("connect_db.php");
require("functions.php");
if(loggedin()){
	
	/*if(isset($_POST['ajax_search'], $_POST['table'], $_POST['column'], $_POST['str'])){
		$str = htmlentities($_POST['str']);
		$col = htmlentities($_POST['column']);
		$table = htmlentities($_POST['table']);
		
		$ele_array =explode(",",$str);
		$preds = array();
		foreach($ele_array as $value){
			$value = trim($value);
			if($value>0){
				$get_pred = $db->prepare("SELECT `".$col."` FROM `".$table."` WHERE `".$col."` LIKE :v");
				$get_pred->execute(array("v"=>$value."%"));
				$pred_str = "";
				
				while($row = $get_pred->fetch(PDO::FETCH_ASSOC)){
					$pred_str = $pred_str."<span class = '".$table."-".$col."' id = ".$value.">".$row[$col]."</span>";
				}
				$preds[$value] = $pred_str;
				echo json_encode($preds);
			}
		}
	}*/
	if(isset($_POST['des_opponents'], $_POST['ctype'])){
		$string = htmlentities($_POST['des_opponents']);
		$ctype = htmlentities($_POST['ctype']);
		
		if($ctype=="0"){
			$table_search = "private_groups";
			$col_name = "group_name";
			$extra_validation = "com_id = :user_com AND";
		}else{
			$col_name = "com_name";
			$table_search = "communities";
			$extra_validation = "";
		}
		
		$pred = array();
		$ev = array();
		$quant_names=explode(",",$string);
		
		foreach($quant_names as $name){
			$name = trim($name);
			if(strlen($name)>0){
				$get_pred = $db->prepare("SELECT `".$col_name."` FROM `".$table_search."` WHERE ".$extra_validation."
				  `".$col_name."` LIKE :name");
			 	$exe_vals = array("name"=>$name."%");
			 	if($ctype=="0"){
					$exe_vals["user_com"]= get_user_field($_SESSION['user_id'], "user_com");
				}
		
				$get_pred->execute($exe_vals);
				//$count = 0;
				$pred_group = "";
				while($row = $get_pred->fetch(PDO::FETCH_ASSOC)){
					$pred_group = $pred_group.",".$row[$col_name];
					$ev[]=$name;
				}
				
				$pred[]=$pred_group;
			}
		}	
		echo implode(",",$pred);
	}
	
	if(isset($_POST['des_to'])){
		$string = htmlentities($_POST['des_to']);
		$pred = array();
		$quant_names=explode(",",$string);
		foreach($quant_names as $name){
			$name = trim($name);
			if(strlen($name)>0){
				$get_pred = $db->prepare("SELECT group_name FROM private_groups WHERE
				 com_id = :user_com AND group_name LIKE :name");
			 
				 $get_pred->execute(array(
					"user_com"=>get_user_field($_SESSION['user_id'], "user_com"),
					"name"=>$name."%"
				 ));
				//$count = 0;
				$pred_group = "";
				while($row = $get_pred->fetch(PDO::FETCH_ASSOC)){
					$pred_group = $pred_group.",".$row['group_name'];
					
				}
				
				$pred[]=$pred_group;
			}
		}	
		echo implode(",",$pred);
	}
}else if(isset($_POST['vote'], $_POST['table'], $_POST['arg_id'], $_POST['comp_id'], $_POST['ctype'], $_POST['judges'], $_POST['user_id'])){
	$table_columns = array("comp_arguments"=>"arg_id", "comp_arg_replies"=>"reply_id");
	$table = htmlentities($_POST['table']);
	$vote = $_POST['vote'];
	$ctype = $_POST['ctype'];
	$user_id = $_POST['user_id'];
	$judges = $_POST['judges'];
	if($judges!="norm"){
		$judges = explode(",", $judges);
	}
	$arg_id = $_POST['arg_id'];
	$comp_id = $_POST['comp_id'];
	$error = "Uknown error.";
	if($table=="comp_arguments"||$table=="comp_arg_replies"){
		if( (user_in_comp($user_id, $comp_id, $ctype)!=true) && (user_already_voted_comp_arg($table, $user_id, $arg_id)==false) && (($judges=="norm")||($judges!="norm"&&in_array($user_id, $judges)))){
		
			if($vote==-1||$vote==1){
				$update = $db->prepare("UPDATE `".$table."` SET points = points + :vote WHERE `".$table_columns[$table]."` = :arg_id");
				$update->execute(array("arg_id"=>$arg_id, "vote"=>$vote));
				add_voter_comp_arg($table, $user_id, $arg_id);
				if(substr($user_id, 0,4)!="out:"){
					add_rep(2, $_SESSION['user_id']);
				}
				echo "Successfully voted.";
			}else{
				echo $error;
			}	
		}else{
			echo $error;
		}
	}else{
		echo $error;
	}	
}
?>