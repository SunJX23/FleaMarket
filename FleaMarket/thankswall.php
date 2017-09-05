<?php

	session_save_path(dirname(__FILE__)."/cache/");
	session_start();
	header('Content-type','application/json');
	require_once('config.php');
	$con = mysqli_connect(HOST,USERNAME,PASSWORD,DB);
	$con -> set_charset("utf8");
	if(isset($_POST['type'])){
		switch ($_POST['type']) {
			case 'show':
				echo show($con);
				break;
			case 'update':
				echo update($con);
				break;
			case 'delete':
				echo delet($con);
				break;
			default:
				break;
		}
	}
	show($con);

	//删除留言
	function delet($con){
		if(isset($_POST['tID'])){
			// $query = mysqli_query($con, "select count(*) from thankswall");
			// $num = mysqli_fetch_row($query)[0];
			$tID = $_POST['tID'];
			// $query = mysqli_query($con, "select tdate from thankswall where tdate = '$tdate' ");
			// $tID = mysqli_fetch_row($query)[0];
			$delete = mysqli_query($con, "delete from thankswall where tID = '$tID' ");
			if($delete){
				// $update = true;
				// if($num != $tID){
					// $update = false;
					// $update = mysqli_query($con, "update thankswall set tID = '$tID' where tID = '$num' ");
				// }
				// if($update){
				return json_encode("删除成功");
			}
			else{
				return json_encode("删除失败");
			}
		}else{
			return json_encode("");
		}
	}

	//显示留言
	function show($con){
		$query = mysqli_query($con, "select thankswall.uID,unick,tID,tcontent,tdate from thankswall,users where thankswall.uID=users.uID order by tdate");
		if($query){
			while($row = mysqli_fetch_array($query))
				$result[] = $row;
		}
		if(isset($result)){
			$session_id = isset($_SESSION['openid']) ? $_SESSION['openid'] : "olKuAwHPm_1vwuv1dh6GD20bRlMM";
			$datas = array();
			$data = array();
			$data['nick'] = isset($_SESSION['nickname']) ? $_SESSION['nickname'] : "九夏三冬";
			$datas[] = $data;
			foreach ($result as $value) {
				$data['id'] = $value['tID'];
				$data['nick'] = $value['unick'];
				$data['content'] = $value['tcontent'];
				$data['date'] = $value['tdate'];
				$data['isdelete'] = ($value['uID'] == $session_id) ? "true" : "false";
				$datas[] = $data;
			}
		}
		else{
			$datas = "";
		}
		return json_encode($datas);
	}

	//上传留言
	function update($con){
		// $query = mysqli_query($con, "select count(*) from thankswall");
		// $num = mysqli_fetch_array($query);
		// $tID = $num[0]+1;
		$tID = time();
		$tcontent = isset($_POST['content']) ? $_POST['content'] : null;
		$uID = isset($_SESSION['openid']) ? $_SESSION['openid'] : "olKuAwHPm_1vwuv1dh6GD20bRlMM";
		$tdate = date('Y-m-d H:i:s');
		// file_put_contents('demo/a', "insert into thankswall (tID,tcontent,tdate,uID) values ('$tID','$tcontent','$tdate','$uID')");
		if($tcontent){
			$query = mysqli_query($con, "insert into thankswall (tID,tcontent,tdate,uID) values ('$tID','$tcontent','$tdate','$uID')");
			if($query){
				$datas = array();
				$data = array();
				$data['nick'] = isset($_SESSION['nickname']) ? $_SESSION['nickname'] : "九夏三冬";
				$data['id'] = $tID;
				$data['content'] = $tcontent;
				$data['date'] = $tdate;
				$data['isdelete'] = "true";
				$datas[] = $data;
				return json_encode($datas);
			}
			else{
				return json_encode('');
			}
		}
		else{
			return json_encode('');
		}
		
	}
?>