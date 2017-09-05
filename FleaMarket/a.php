<?php
	session_save_path(dirname(__FILE__)."/cache/");
	session_start();
	require_once('config.php');
	header('Content-type','application/json');
	$con = mysqli_connect(HOST,USERNAME,PASSWORD,DB);
	$con -> set_charset("utf8");
	if(isset($_GET['id']) && isset($_GET['type'])){
		$gID = $_GET['id'];
		$query = mysqli_query($con, "select isBack from goods where gID = '$gID' ");
		$isBack = mysqli_fetch_row($query)[0];
		$query = mysqli_query($con, "select uID from goods where gID = '$gID' ");
		$guID = mysqli_fetch_row($query)[0];
		switch ($_GET['type']) {
			case 'showdatas'://显示物品信息
				echo showData($con);
				break;
			case 'showclaim'://显示申请认领信息
				echo showClaim($con);
				break;
			case 'claimbutton'://显示我是否认领
				echo claimButton($con);
				break;
			case 'upclaim'://申请认领
				echo upClaim($con);
				break;
			case 'sure'://确认认领
				echo sureClaim($con);
				break;
			case 'cancel'://取消认领
				echo cancelClaim($con);
				break;
			default:
				# code...
				break;
		}
	}
	mysqli_close($con);

	//取消认领
	function cancelClaim($con){
		$gID = $_GET['id'];
		$uID = isset($_SESSION['openid']) ? $_SESSION['openid'] : "olKuAwHPm_1vwuv1dh6GD20bRlMM";
		$alert = "取消失败请重试";
		$response = array();
		$query = mysqli_query($con, "select cID from claim where gID = '$gID' and uID = '$uID' ");
		if($query){
			$cID = mysqli_fetch_row($query)[0];
			$query = mysqli_query($con, "delete from claim where gID = '$gID' and uID = '$uID' ");
			if($query){
				$alert = "取消成功";
			}
			$response['alert'] = $alert;
			$response['cid'] = $cID;
		}
		return json_encode($response);
	}

	//确认认领
	function sureClaim($con){
		$gID = $_GET['id'];
		$cID = $_GET['cid'];
		$alert = "确认失败请重试";
		$query = mysqli_query($con, "update goods set isBack = '1' where gID = '$gID' ");
		if($query){
			$query = mysqli_query($con, "update claim set isSuccess = '1' where cID = '$cID' ");
			if($query){
				$alert = "确认成功";
			}
		}
		$response = array();
		$response['alert'] = $alert;
		$response['cid'] = $cID;
		return json_encode($response);
	}

	//显示申请认领信息
	function showClaim($con){
		$gID = $_GET['id'];
		$claims = array();
		$flag = array();
		//查询是否领取
		global $isBack;
		$flag['back'] = $isBack;
		//查询谁领取了
		if($isBack){
			$query = mysqli_query($con, "select cID from claim where gID = '$gID' and isSuccess = '1' ");
			$isSuccess = mysqli_fetch_row($query)[0];
			$flag['success'] = $isSuccess;
		}
		else{
			$flag['success'] = '0';
		}
		//查询当前页面是不是我
		global $guID;
		$uID = isset($_SESSION['openid']) ? $_SESSION['openid'] : "olKuAwHPm_1vwuv1dh6GD20bRlMM";
		if($guID == $uID){
			$flag['isme'] = '1';
		}
		else{
			$flag['isme'] = '0';
		}
		$claims[] = $flag;

		$query = mysqli_query($con, "select cID,cContact,cTime,unick from claim,users where claim.uID = users.uID and gID = '$gID' order by cID");
		if($query){
			while($row = mysqli_fetch_array($query))
				$result[] = $row;
		}
		if(isset($result)){
			foreach ($result as $value) {
				$claim = array();
				$claim['id'] = $value['cID'];
				$claim['nick'] = $value['unick'];
				$claim['contact'] = $value['cContact'];
				$claim['time'] = $value['cTime'];
				$claims[] = $claim;
			}
		}

		return json_encode($claims);
	}

	//claim button初始
	function claimButton($con){
		$gID = $_GET['id'];
		$uID = isset($_SESSION['openid']) ? $_SESSION['openid'] : "olKuAwHPm_1vwuv1dh6GD20bRlMM";
		global $guID;
		// file_put_contents('demo/a', "select uID from goods where gID = '$gID' ");
		global $isBack;
		if($isBack){
			$response = "已认领";
		}
		else{
			if($guID == $uID){
				$response = "修改";
			}
			else{
				$query = mysqli_query($con, "select cContact from claim where gID = '$gID' and uID = '$uID' ");
				$result = mysqli_fetch_row($query)[0];
				if($result){
					$response = "取消认领";
				}
				else{
					$response = "申请认领";
				}
			}
		}
		return json_encode($response);
	}

	//申请认领
	function upClaim($con){
		$alert = "申请失败请重试";
		$response = array();
		if(isset($_GET['contact'])){
			$cID = time();
			for($i = 0; $i < 3; $i++){
				$cID .= chr(mt_rand(48,57));
			}
			$gID = $_GET['id'];
			$uID = isset($_SESSION['openid']) ? $_SESSION['openid'] : "olKuAwITLfUvhUL5YqNZciLEka5U";
			$cContact = $_GET['contact'];
			$cTime = date('Y-m-d');
			file_put_contents("demo/a", "insert into claim (cID,gID,uID,cContact,cTime) values ('$cID','$gID','$uID','$cContact','$cTime')");
			$query = mysqli_query($con,"insert into claim (cID,gID,uID,cContact,cTime) values ('$cID','$gID','$uID','$cContact','$cTime')");
			if($query){
				$alert = "申请成功";
			}
			else{
				$alert = "申请失败请重试";
			}
			$query = mysqli_query($con, "select unick from users where uID = '$uID' ");
			$unick = mysqli_fetch_row($query)[0];
			$response['nick'] = $unick;
			$response['contact'] = $cContact;
			$response['id'] = $cID;
			$response['time'] = $cTime;
		}
		else{
			$alert =  "请输入正确的联系方式";
		}
		$response['alert'] = $alert;
		return json_encode($response);
	}

	function showData($con){
		$sql = "select gID,gName,gDate,gPlace,gDePlace,gDetail,gContact,gImage from goods where gID = ".$_GET['id'];
		$query = mysqli_query($con, $sql);
		if($query){
			$result = mysqli_fetch_array($query);
		}

		$data = array();

		if(isset($result)){
			$null="无";
			$sid=$result['gID'];
			$sname=$result['gName'];
			$sdate=$result['gDate'];
			$splace=$result['gPlace'];
			$scontact=$result['gContact'];
			$simage=$result['gImage'];

			if(!$splace||$splace=="未知")
				$splace = $result['gDePlace'] ? $result['gDePlace'] : $null;

			else if($result['gDePlace']) 
				$splace=$splace."  ".$result['gDePlace'];

			if(!$scontact) $scontact=$null;

			if($sname=="饭卡"){
				$card=$result['gDetail'];
				switch (substr($card,0,2)){
					case 'CC':
						$cno=substr($card,2,15);
						if(substr($card,17,2)=="CN"){
							$cname=substr($card,19);
						}
						else $cname=$null;
						break;
					case 'CN':
						$cno=$null;
						$cname=substr($card,2);
						break;
					default:
						$cno=$null;
						$cname=$null;
						break;
				}
				if(!$result['gDetail']) 
					$detail=$null;
				else 
					$detail=$result['gDetail'];
			}else{
				if($result['gName']=="其他" && $result['gDetail'])
					$sname=$result['gDetail'];
				else
					$sname=$sname." ".$result['gDetail'];
			}
			$data['id'] = $sid;
			$data['name'] = $sname;

			if($sname == "饭卡"){
				$data['cname'] = $cname;
				$data['cno'] = $cno;
			}
			$data['date'] = $sdate;
			$data['place'] = $splace;
			$data['contact'] = $scontact;
			$data['image'] = $simage;
		}
		return json_encode($data);
	}
?>