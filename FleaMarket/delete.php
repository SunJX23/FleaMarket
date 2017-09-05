<?php
	require_once('config.php');
	header('Content-type','application/json');
	$gid = isset($_GET['id']) ? $_GET['id'] : null;//要删除物品的gID
	if($gid){
		$response = array();
		$response['alert'] = "删除失败";
		$con = mysqli_connect(HOST,USERNAME,PASSWORD,DB);
		$con -> set_charset('utf8');
		// $query = mysqli_query($con, "select count(*) from goods");
		// $num = mysqli_fetch_row($query)[0];//要顶替其ID的物品gID
		$query = mysqli_query($con, "select isLose,gImage from goods where gID = ".$gid);
		$result = mysqli_fetch_row($query);
		$isLose = $result[0];
		$gImage = $result[1];
		if(mysqli_query($con, "delete from goods where gID = ".$gid)){
			exec("rm ".$gImage, $res, $status);
			if(mysqli_query($con, "delete from claim where gID = ".$gid)){
				$response['alert'] = "删除成功";
			}
		}
		$response['isLose'] = $isLose;
		echo json_encode($response);
		mysqli_close($con);
	}
?>