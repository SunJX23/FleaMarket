<?php
	session_save_path(dirname(__FILE__)."/cache/");
	session_start();
	require_once('config.php');
	header('Content-type','application/json');
	$con = mysqli_connect(HOST,USERNAME,PASSWORD,DB);
	$con -> set_charset("utf8");
	if(isset($_POST['card']) && isset($_POST['phone'])){
		$card = "CC".$_POST['card'];
		$uID = isset($_SESSION['openid']) ? $_SESSION['openid'] : "olKuAwHPm_1vwuv1dh6GD20bRlMM";
		$response = array();
		$response['pickup'] = '0';
		$query = mysqli_query($con, "select gID,gContact from goods where gDetail like '{$card}%' and isLose = 0");
		//检索到该饭卡被捡拾的信息
		$result = mysqli_fetch_row($query);
		$gID = $result[0];
		$gContact = $result[1];
		if($gID && $gContact){
			$query = mysqli_query($con, "select cID from claim where gID = '$gID' and uID = '$uID' ");
			$result = mysqli_fetch_row($query);
			if($result){
				$response['isfirst'] = '0';
				$response['pickup'] = '1';
				$response['contact'] = $gContact;
			}
			else{
				//上传申请认领信息
				$cID = time();
				for($i = 0; $i < 3; $i++){
					$cID .= chr(mt_rand(48,57));
				}
				$cTime = date('Y-m-d');
				$cContact = $_POST['phone'];
				$query = mysqli_query($con, "insert into claim (cID,gID,uID,cContact,cTime) values ('$cID','$gID','$uID','$cContact','$cTime')");
				if($query){
					$response['isfirst'] = '1';
					$response['pickup'] = '1';
					$response['contact'] = $gContact;
				}
			}
		}
		else{
			//未检索到饭卡信息
			$query = mysqli_query($con, "select gID from goods where gDetail like '{$card}%' and uID = '$uID' and isLose = 1");
			$result = mysqli_fetch_row($query);
			if($result){
				$response['isfirst'] = '0';
				$response['success'] = "1";
			}
			else{
				//上传丢失物品信息
				$response['isfirst'] = '1';
				$num = mysqli_query($con,"select count(*) from goods");
				list($gID) = mysqli_fetch_row($num);
				$gID += 1;
				$gName = "饭卡";
				$gDate = date('Y-m-d');
				$isBack = '0';
				$uID = isset($_SESSION['openid']) ? $_SESSION['openid'] : "olKuAwHPm_1vwuv1dh6GD20bRlMM";
				$gPlace = "未知";
				$gDePlace = "";
				$gDetail = "CC".$_POST['card'];
				$gContact = $_POST['phone'];
				$gImage = "";
				$isLose = '1';
				$imghash = "";
				$query = mysqli_query($con, "insert into goods (gID,gName,gDate,isBack,uID,gPlace,gDePlace,gDetail,gContact,gImage,isLose,imghash) values ('$gID','$gName','$gDate','$isBack','$uID','$gPlace','$gDePlace','$gDetail','$gContact','$gImage','$isLose','$imghash')");
				if($query){
					$response['success'] = "1";
				}
				else{
					$response['success'] = "0";
				}
			}
		}
		echo json_encode($response);
	}
?>