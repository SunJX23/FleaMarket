<?php
	require_once('config.php');
	header('Content-type','application/json');
	$con = mysqli_connect(HOST,USERNAME,PASSWORD,DB);
	$con -> set_charset("utf8");
	if(isset($_POST['id']) && isset($_POST['type'])){
		$gid = $_POST['id'];
		switch ($_POST['type']) {
			case 'show':
				echo showData($con);
				break;
			
			case 'revise':
				echo reviseData($con);
				break;
		}
	}

	function reviseData($con){
		$response = "修改失败，请重试";
		$gID = isset($_POST['id']) ? test_input($_POST['id']) : null; //物品id
		$gName = isset($_POST['name']) ? test_input($_POST['name']) : null; //物品名
		$cardn = isset($_POST['cardn']) ? test_input($_POST['cardn']) : null; //卡名
		$cardc = isset($_POST['cardc']) ? test_input($_POST['cardc']) : null; //卡号
		$dy = isset($_POST['datey']) ? test_input($_POST['datey']) : null; //年
		$dm = isset($_POST['datem']) ? test_input($_POST['datem']) : null; //月
		$dd = isset($_POST['dated']) ? test_input($_POST['dated']) : null; //日
		$gDate = $dy."-".$dm."-".$dd; //时间
		$gPlace = isset($_POST['place']) ? test_input($_POST['place']) : null;//丢失地点
		$gDePlace = isset($_POST['deplace']) ? test_input($_POST['deplace']) : null;//具体地点
		$gDetail = isset($_POST['detail']) ? test_input($_POST['detail']) : null;//物品详情
		$gContact = isset($_POST['contact']) ? test_input($_POST['contact']) : null;//联系方式

		$cno_error = 1;//饭卡信息是否填写正确
		$gCard = 1;
		$detail = '';
		if($gName == '饭卡'){
			if($cardn != null || $cardc != null){
				if($cardc != null) {
					$cno_error = 0;
					if(strlen($cardc) == 15){
						$cno_error = 1;
						$detail = 'CC'.$cardc;
					}
				}
				if($cardn != null) $detail = $detail.'CN'.$cardn;
				$gDetail = $detail;
			}else{
				$gCard = 0;
			}
		}

		file_put_contents('demo/xx', $gDetail);

		if($gPlace == null){
			$gPlace = "未知";
		}

		if($gName && $gContact && $gCard && $cno_error){

			$sql = "update goods set gName = '$gName',gDate = '$gDate',gPlace = '$gPlace',gDePlace = '$gDePlace',gDetail = '$gDetail',gContact = '$gContact' where gID = '$gID' ";
			// file_put_contents('demo/a', $sql);
			$query = mysqli_query($con, $sql);
			if($query){
				$response =  "修改成功";
			}

		}
		else{
			if($gContact == null)
				$response = "请留下联系方式";
			if($cno_error == 0)
				$response = "请输入正确的卡号";
			if($gCard == 0)
				$response = "请填写饭卡信息";
			if($gName == null)
				$response = "请选择物品名";
		}

		
		return json_encode($response);
	}

	function showData($con){

		$sql = "select gID,gName,gDate,gPlace,gDePlace,gDetail,gContact,gImage from goods where gID = ".$_POST['id'];

		// file_put_contents('demo/a', "haoqio ".$sql);
		
		$query = mysqli_query($con, $sql);
		if($query){
			$result = mysqli_fetch_array($query);
		}

		$data = array();

		if(isset($result)){
			$data['name'] = $result['gName'];
			$data['place'] = $result['gPlace'];
			$data['deplace'] = $result['gDePlace'];
			$data['detail'] = $result['gDetail'];
			$data['contact'] = $result['gContact'];
			$data['image'] = $result['gImage'];
			$data['date'] = $result['gDate'];
			if($data['name'] == "饭卡" && $result['gDetail']){
				$card = $result['gDetail'];
				switch (substr($card,0,2)){
					case 'CC':
						$data['cno'] = substr($card,2,15);
						if(substr($card,17,2)=="CN"){
							$data['cname'] = substr($card,19);
						}
						else{
							$data['cname'] = '';
						}
						break;
					case 'CN':
						$data['cno'] = '';
						$data['cname'] = substr($card,2);
						break;
					default:
						$data['cno'] = '';
						$data['cname'] = '';
				}
				$data['detail'] = '';
			}
		}
		return json_encode($data);
	}

	function test_input($data) {
		$data = trim($data);// 去除用户输入数据中不必要的字符（多余的空格、制表符、换行）
		$data = stripslashes($data);// 删除用户输入数据中的反斜杠（\）
		$data = htmlspecialchars($data);// 特殊字符转换为 HTML 实体
		return $data;
	}
?>