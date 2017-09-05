<?php
	require_once('config.php');
	require_once(dirname(__FILE__).'/weixin/http_request.php');
	header('Content-type','application/json');

	$up_image = false;
	$imgpath = "cache/";
	$isLose = isset($_POST['isLose']) ? $_POST['isLose'] : "1";
	if(isset($_POST['id'])){
		file_put_contents('demo/a', $_POST['id']);
		//图片命名
		$x = '00';
		$date = date('YmdHis');
		while (file_exists(dirname(__FILE__).'/cache/'.$date.$x.'.jpg')){
			$x ++;
		}
		$imgpath .= $date.$x.'.jpg';

		//下载图片
		$serverId = $_POST['id'];
		$access_token = getAccess_token();
		$url="http://file.api.weixin.qq.com/cgi-bin/media/get?access_token={$access_token}&media_id={$serverId}";
		$ch = curl_init();
		$fp = fopen($imgpath, 'wb');
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_FILE, $fp);
		curl_setopt($ch, CURLOPT_HEADER, 0);
		$up_image = curl_exec($ch);
		curl_close($ch);
		fclose($fp);
	}

	if($up_image){

		//得到搜索图片的phash值
		$path = './shash '.$imgpath;
		exec($path, $res);
		$imghash = $res[0];

		//删除图片
		exec("rm ".$imgpath);

		//连接数据库拿到所有图片的phash值
		$con = mysqli_connect(HOST,USERNAME,PASSWORD,DB);
		$con->set_charset("utf8");
		$sql = "select gID,imghash from goods where isBack = 0 and isLose = $isLose";
		$query = mysqli_query($con, $sql);
		if($query){
			while($row = mysqli_fetch_array($query))
				$result[] = $row;
		}

		//得到汉明距离
		$difs = array();
		foreach ($result as $val) {
			$id = $val['gID'];
			if($val['imghash']){
				$dif = HamingDistance($imghash, $val['imghash']);
				//if($dif < 10)
				$difs{$id} = $dif;
			}
		}
		asort($difs);
		unset($result);
		// var_dump($difs);
		// file_put_contents('demo/a', var_dump($difs));
		$size = sizeof($difs);
		if($size){

			//根据汉明距离取出数据
			$sql = "select gID,gName,gDate,gPlace,gDePlace,gDetail,gContact,gImage,isBack from goods where ";
			$i = 0;
			foreach ($difs as $key => $value) {
				if($i)
					$sql = $sql." or ";
				$sql = $sql."gID = ".$key." and isLose = ".$isLose;
				$i++;
				// if($i > 5)
				// 	break;
			}
			$query = mysqli_query($con, $sql);
			if($query){
				while($row = mysqli_fetch_array($query))
					$result[] = $row;
			}

			// foreach ($result as $value) {
			// 	echo "<br>".$value['gID'];
			// }
			// echo "<br>";

			//在difs数组中标记取出数据的顺序
			foreach ($result as $value) {
				$key = array_search($value, $result);
				$id = $value['gID'];
				$difs{$id} = $key;
			}

			$null="无";
			$datas = array();
			foreach($difs as $value)
			{
				$xinxi = $result[$value];

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
					$data['cname'] = $cname ? $cname : null;
					$data['cno'] = $cno ? $cno : null;
				}
				else{
					$data['cname'] = null;
					$data['cno'] = null;
				}
				$data['date'] = $sdate;
				$data['place'] = $splace;
				$data['contact'] = $scontact;
				$data['image'] = $simage;
				$data['isBack'] = $isBack;

				$datas[] = $data;
			}
			echo json_encode($datas);
			// var_dump($datas);
		}
		mysqli_close($con);
	}



	function HamingDistance($str1, $str2) {
		$diffNum = 0;
		for($i = 0; $i < 64; $i++)
			if($str1[$i] != $str2[$i])
				$diffNum++;
		return $diffNum;
	}
?>