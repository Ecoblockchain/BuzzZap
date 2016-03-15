<?php
	$comp_start_table_search = "";
	$comp_start_col_search = "";
	$page = "";
	$username = "";

	$ex_comp_q= "";
	$com_id = "";
	$uid = "";
	if(loggedin()){
		$uid = $_SESSION['user_id'];
		$username = get_user_field($uid , "user_username");
		$com_id = get_user_field($uid , "user_com");
	}
	if(isset($_GET['page'])){
		$page = htmlentities($_GET['page']);
	}
	
	if(isset($_GET['type'])){
		$type = htmlentities($_GET['type']);
		$comp_start_table_search = "private_groups";
		$comp_start_col_search = "group_name";
		if($type=="0"){
			$ex_comp_q = "c:AND com_id = ".$com_id." AND group_id != ".get_user_group($_SESSION['user_id'], "group_id");
		}else{
			$ex_comp_q = "c:AND group_id != ".get_user_group($_SESSION['user_id'], "group_id");
		}
	}

?>
<script>

$(document).ready(function(){
	function pred_text(table, col, fieldID, ex_query, res_limit){
		if (typeof(ex_query)==='undefined')  ex_query = "";
		if (typeof(res_limit)==='undefined')  res_limit = 5;
		$("#"+fieldID).keyup(function(){
	 		var str_field_val = $("#"+fieldID).val();
	 		if(str_field_val.length!=0){
				var str_field = str_field_val.substring(str_field_val.lastIndexOf(","), str_field_val.length);
			}else{
				str_field = str_field_val;
			}
			if(str_field.length>2){
				$.post("<?php echo $ajax_script_loc; ?>", {ajax_search:true,str:str_field,table:table, column:col, ex_query:ex_query, res_limit:res_limit}, function(result,err){
					$("#pred_results").html($(result));
					$("#pred_results").animate({color:"#62949b"}, 500).delay(200).animate({color:"#ffffff"}, 500);
					setInterval(function(){
						$("#pred_results").animate({color:"#62949b"}, 500).delay(200).animate({color:"#ffffff"}, 500);
					}, 1200);
				
					$("#"+fieldID).blur(function(){
						setTimeout(function(){
							if($("#pred_results").html().length>1){
								$("#pred_results").html("");
							}

						}, 300);
					});
					$("."+table+"-"+col).click(function(){
						if($("#"+fieldID).val().length>0){
							first_half = str_field_val.substring(0, str_field_val.lastIndexOf(","));
						}else{
							first_half="";
						
						}
						if(str_field_val.lastIndexOf(",")>0){
							comma = ", ";
						}else{
							comma = "";
						}
						mid = $(this).html();
						
						$("#"+fieldID).val(first_half+comma+mid+comma);
						$("#"+fieldID).focus();
						$("#pred_results").html("");
					});
				});
			}else{

				$("#pred_results").html("");
			}	
				
		});
			
	}


	//ex query = "SELECT `".$col."` FROM `".$table."` WHERE `".$col."` LIKE :v ".$ex_query." LIMIT 5"

	//inbox
	if(<?php echo loggedin(); ?>){
		if("<?php echo $page; ?>" === "inbox"){
			pred_text("users", "user_username", "desto");

		}else if("<?php echo $page; ?>" === "private_groups"){
		//des group members
		pred_text("users", "user_username", "desired_g_mems", "c:AND user_com = '<?php echo $com_id; ?>' AND user_username != '<?php echo $username; ?>' ", 3);
		
		}else if("<?php echo $page; ?>" === "comp_home"){
			//comp start
			if("<?php echo $comp_start_table_search; ?>" != ""){
				pred_text("<?php echo $comp_start_table_search; ?>", "<?php echo $comp_start_col_search; ?>", "des_opponents", "<?php echo $ex_comp_q; ?>");
			}
		}else if("<?php echo $page; ?>" === "leader_cp"){
			pred_text("users", "user_username", "act-on-user", "c:AND user_com = '<?php echo $com_id; ?>' AND user_username != '<?php echo $username; ?>' ", 3);
		
		}else if("<?php echo $page; ?>" === "live_debating"){
			pred_text("private_groups", "group_name", "des_opponents", "c:AND group_id != '<?php echo get_user_group($uid , 'group_id'); ?>'");
		}
	}else{
		if("<?php echo $page; ?>" === "home"){
			pred_text("communities", "com_name", "com_name_search_j");
		}
	}
});
</script>