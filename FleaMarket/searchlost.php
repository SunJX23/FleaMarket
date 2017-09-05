<?php

	// class goodData{
	// 	public $name;
	// 	public $cname;
	// 	public $cno;
	// 	public $date;
	// 	public $place;
	// 	public $contact;
	// 	public $image;
	// }
	
	header('Content-type','application/json');
	require_once('config.php');
	$con = mysqli_connect(HOST,USERNAME,PASSWORD,DB);
	$con -> set_charset("utf8");
	$time = isset($_POST['time']) ? $_POST['time'] : null;
	$place =  isset($_POST['place']) ? $_POST['place'] : null;
	$name = isset($_POST['name']) ? $_POST['name'] : null;
	$isLose = isset($_POST['isLose']) ? $_POST['isLose'] : "1";

	$enddate = strtotime("now");
	switch ($time) {
		case 'threedays':
			$startdate=strtotime("-3 days");
			break;
		case 'aweek':
			$startdate=strtotime("-1 week");
			break;
		case 'amonth':
			$startdate=strtotime("-1 month");
			break;
		case 'halfyear':
			$startdate=strtotime("-6 months");
			break;
	}
	$sql="select gID,gName,gDate,gPlace,gDePlace,gDetail,gContact,gImage,isBack from goods where ";
	$isand=0;
	if($time==null&&$place==null&&$name==null){
		$sql=$sql."isLose='$isLose'";
	}
	else{
		if($time!=null){
			$isand=1;
			$sql=$sql."UNIX_TIMESTAMP(gDate) >= $startdate and UNIX_TIMESTAMP(gDate) <= $enddate ";
		}
		if($place!=null){
			if($isand){
				$sql=$sql."and ";
			}
			else $isand=1;
			$sql=$sql."gPlace='$place' ";
		}
		if($name!=null){
			if($isand){
				$sql=$sql."and ";
			}
			else $isand=1;
			$sql=$sql."gName='$name' ";
		}
		$sql=$sql."and isLose='$isLose'";
	}
	$sql=$sql." order by gDate desc";
	$query = mysqli_query($con,$sql);
	if($query){
		while($row=mysqli_fetch_array($query)){
			$result[]=$row;
		}
	}

	// $size = sizeof($result);
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
				$splace = $xinxi['gDePlace'] ? $xinxi['gDePlace'] : $splace;

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
			$data['isBack'] = $isBack;

			$datas[] = $data;
		}
		echo json_encode($datas);
	}
	// else{
	// 	echo "对不起，无此类物品";
	// }
	mysqli_close($con);
?> 