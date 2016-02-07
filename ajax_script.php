<?php
require("connect_db.php");
require("functions.php");

if(isset($_POST['ajax_search'], $_POST['table'], $_POST['column'], $_POST['str'], $_POST['ex_query'], $_POST['res_limit'])){
	$str = htmlentities($_POST['str']);
	$col = htmlentities($_POST['column']);
	$table = htmlentities($_POST['table']);
	$ex_query = htmlentities($_POST['ex_query']);
	$limit = htmlentities($_POST['res_limit']);
	
	if(substr($ex_query, 0,5)!="c:AND"){
		$ex_query = "";
	}else{
		$ex_query = substr($ex_query, 2);
	}

	$ele_array =explode(",",$str);
	$preds = array();
	foreach($ele_array as $value){
		$value = trim($value);
		if(strlen($value)>0){

			$get_pred = $db->prepare("SELECT `".$col."` FROM `".$table."` WHERE `".$col."` LIKE :v ".$ex_query." LIMIT ".$limit);
			$get_pred->execute(array("v"=>"%".$value."%"));
			$pred_str = "";
			$count = 0;
			while($row = $get_pred->fetch(PDO::FETCH_ASSOC)){
				$pred_str = $pred_str."<span class = '".$table."-".$col."' id = 'res".$count."' style = 'font-size:80%;cursor: pointer;'>".$row[$col]."</span><br>";
				$count++;
			}
			$preds[$value] = $pred_str;
			echo implode(",",$preds);
		}
	}
}
	


if(isset($_POST['vote'], $_POST['table'], $_POST['arg_id'], $_POST['comp_id'], $_POST['ctype'], $_POST['judges'], $_POST['user_id'])){

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


if(isset($_POST['get_q_type'])){
	$q = htmlentities($_POST['get_q_type']);
	echo $res = get_question_type($q, 1);
}

$type = "audio";
if(isset($_FILES["${type}-blob"])) {
    $fileName = $_POST["${type}-filename"];
    $hdata = explode(",",$fileName);
    //0 = userid
    //1 = 0|1 (where 0 is general debating and 1 is comp)
    //2 = file code
    if($hdata[1]=="0"){
    	$table = "thread_replies";
    	$col = "reply_id";
    	$cookie_bit = "r";
    }else{
    	$table = "comp_arguments";
    	$col = "arg_id";
    	$cookie_bit = "a";
    }
    setcookie("temp_audio_ret_".$cookie_bit."id", $hdata[2], time()+10000000);
    $uploadDirectory = "audio/$fileName";

    if (!move_uploaded_file($_FILES["${type}-blob"]["tmp_name"], $uploadDirectory)) {
        echo("problem moving uploaded file");
    }else{
    	$db->query("INSERT INTO audio VALUES('', '0', ".$db->quote($table).", ".$db->quote($col).", ".$db->quote($fileName).")");
    }
}

?>