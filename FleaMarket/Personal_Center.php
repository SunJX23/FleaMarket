<?php
	header('Content-type','application/json');
	require_once('config.php');
	session_save_path(dirname(__FILE__)."/cache/");
	session_start();
	$con = mysqli_connect(HOST,USERNAME,PASSWORD,DB);
	$con -> set_charset('utf8');
	$uID = isset($_SESSION['openid']) ? $_SESSION['openid'] : "olKuAwHPm_1vwuv1dh6GD20bRlMM";
	$isLose = isset($_GET['isLose']) ? $_GET['isLose'] : null;
	$sql = "select gID,gName,gDate,gPlace,gDePlace,gDetail,gContact,gImage,isBack from goods where isLose = {$isLose} and uID = '{$uID}' order by gDate desc";
	$query = mysqli_query($con,$sql);
	if($query){
		while($row=mysqli_fetch_array($query)){
			$result[]=$row;
		}
	}
	// $size = sizeof($result);
	$flag = 0;
	if (isset($result)){
		$null="无";
		$datas = array();
		foreach($result as $xinxi)
		{
			$sid=$xinxi['gID'];
			$sname=$xinxi['gName'];
			$sdate=$xinxi['gDate'];
			$splace=$xinxi['gPlace'];
			$scontact=$xinxi['gContact'];
			$simage=$xinxi['gImage'];
			$isBack=$xinxi['isBack'];

			if(!$splace||$splace=="未知")
				$splace = $xinxi['gDePlace'] ? $xinxi['gDePlace'] : $null;

			else if($xinxi['gDePlace']) 
				$splace=$splace."  ".$xinxi['gDePlace'];

			if(!$scontact) $scontact=$null;

			if($sname=="饭卡"){
				$card=$xinxi['gDetail'];
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
				if(!$xinxi['gDetail']) 
					$detail=$null;
				else 
					$detail=$xinxi['gDetail'];
			}else{
				if($xinxi['gName']=="其他" && $xinxi['gDetail'])
					$sname=$xinxi['gDetail'];
				else
					$sname=$sname." ".$xinxi['gDetail'];
			}

			$data = array();
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
			$data['isback'] = $isBack;

			$datas[] = $data;
		}
		echo json_encode($datas);
	}
	// session_unset();
	// session_destroy();
	mysqli_close($con);

?>