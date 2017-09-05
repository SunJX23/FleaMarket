<?php
	error_reporting(0);
	header('Content-type','application/json');
	session_save_path(dirname(__FILE__)."/cache/");
	session_start();
	require_once(dirname(__FILE__).'/class/imgedit.class.php');
	require_once(dirname(__FILE__).'/config.php');
	require_once(dirname(__FILE__).'/weixin/http_request.php');
	$con = mysqli_connect(HOST,USERNAME,PASSWORD,DB);
	$con -> set_charset("utf8");
	// $num = mysqli_query($con,"select count(*) from goods");
	// list($gID) = mysqli_fetch_row($num);
	// $gID += 1;
	$gID = time();
	for($i = 0; $i < 3; $i++){
		$gID .= chr(mt_rand(48,57));
	}
	$gName = isset($_POST['name']) ? test_input($_POST['name']) : null; //物品名
	$cardn = isset($_POST['cardn']) ? test_input($_POST['cardn']) : null; //卡名
	$cardc = isset($_POST['cardc']) ? test_input($_POST['cardc']) : null; //卡号
	$dy = isset($_POST['datey']) ? test_input($_POST['datey']) : null; //年
	$dm = isset($_POST['datem']) ? test_input($_POST['datem']) : null; //月
	$dd = isset($_POST['dated']) ? test_input($_POST['dated']) : null; //日
	$gDate = $dy."-".$dm."-".$dd; //时间
	$isBack = 0;

	$gPlace = isset($_POST['place']) ? test_input($_POST['place']) : null;//丢失地点
	$gDePlace = isset($_POST['deplace']) ? test_input($_POST['deplace']) : null;//具体地点
	$gDetail = isset($_POST['detail']) ? test_input($_POST['detail']) : null;//物品详情
	$gContact = isset($_POST['contact']) ? test_input($_POST['contact']) : null;//联系方式
	$isLose = 1;//丢失

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

	if($gPlace == null){
		$gPlace = "未知";
	}

	// if($gDePlace == null){
	// 	$gDePlace = "NULL";
	// }

	// if($gDetail == null){
	// 	$gDetail = 
	// }

	$uID = isset($_SESSION['openid']) ? $_SESSION['openid'] : "olKuAwHPm_1vwuv1dh6GD20bRlMM";

	$gImage = null;
	$up_data = false;
	$up_image = false;
	$imghash = null;
	$response = "上传失败，请重试";

	if($gName && $gContact && $gCard && $cno_error){
		if ($_POST['sid'] != "null"){
			$up_image = download_image($_POST['sid'], $gImage, $imghash);
		}else{
			$up_image = true;
		}

		$sql = "insert into goods(gID,gName,gDate,isBack,uID,gPlace,gDePlace,gDetail,gContact,gImage,isLose,imghash) values ('$gID','$gName','$gDate','$isBack','$uID','$gPlace','$gDePlace','$gDetail','$gContact','$gImage','$isLose','$imghash')";
		$up_data = mysqli_query($con,$sql);

		if ($up_data && $up_image) {
			$response = "上传成功";
		}else{
			$response = "上传失败，请重试";
			mysqli_query($con,"delete from goods where gID = $gID");
			if($gImage){
				exec("rm ".$gImage, $res);
			}
		}
	}else{
		if($gContact == null)
			$response = "请留下联系方式";
		if($cno_error == 0)
			$response = "请输入正确的卡号";
		if($gCard == 0)
			$response = "请填写饭卡信息";
		if($gName == null)
			$response = "请选择物品名";
	}

	echo json_encode($response);

	mysqli_close($con);
	// session_unset();
	// session_destroy();

	function test_input($data) {
		$data = trim($data);// 去除用户输入数据中不必要的字符（多余的空格、制表符、换行）
		$data = stripslashes($data);// 删除用户输入数据中的反斜杠（\）
		$data = htmlspecialchars($data);// 特殊字符转换为 HTML 实体
		return $data;
	}

	function download_image($serverId, &$gImage, &$imghash){
		$up_image = false;
		// 图片命名
		$x = "00";
		$date = date('YmdHis');
		while (file_exists(dirname(__FILE__).'/photo/'.$date.$x.'.jpg')){
			$x++;
		}
		$gImage = "photo/".$date.$x.'.jpg';

		// 从微信服务器下载图片
		$access_token = getAccess_token();
		$url="http://file.api.weixin.qq.com/cgi-bin/media/get?access_token={$access_token}&media_id={$serverId}";
		$ch = curl_init();
		$fp = fopen($gImage, 'wb');
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_FILE, $fp);
		curl_setopt($ch, CURLOPT_HEADER, 0);
		$up_image = curl_exec($ch);
		curl_close($ch);
		fclose($fp);

		// 图片压缩(大于300kb)
		if(filesize($gImage) > 307200){
			$image = new edit_imagick($gImage);
			$image->reSize($gImage);
		}

		// 求图片的phash值
		$path = "./shash ".$gImage;
		exec($path, $res);
		$imghash = $res[0];
		return $up_image;
	}


	// 允许上传的图片后缀
	// $allowedExts = array("gif", "jpeg", "jpg", "png","pjpeg","x-png");
	// echo $_FILES["file"]["name"];
	// $temp = explode(".", $_FILES["file"]["name"]);
	// // echo $_FILES["file"]["size"];
	// $extension = end($temp);     // 获取文件后缀名
	// var_dump($_FILES);
	// echo "</br>";
	
	// if (($_FILES["file"]["type"] == "image/gif")
	// || ($_FILES["file"]["type"] == "image/jpeg")
	// || ($_FILES["file"]["type"] == "image/jpg")
	// || ($_FILES["file"]["type"] == "image/pjpeg")
	// || ($_FILES["file"]["type"] == "image/x-png")
	// || ($_FILES["file"]["type"] == "image/png"))
	// //&& in_array($extension, $allowedExts)))
	// {
	// 	if ($_FILES["file"]["size"]<10240000){
	// 		if ($_FILES["file"]["error"] > 0)
	// 		{
	// 			echo "错误：: " . $_FILES["file"]["error"] . "<br>";
	// 		}
	// 		else
	// 		{
	// 			$x="00";
	// 			while (file_exists("photo/".date('YmdHis').$x.".".$extension)){
	// 				$x++;
	// 			}
	// 			$gImage="photo/".date('YmdHis').$x.".".$extension;
				
	// 			if(is_uploaded_file($_FILES['file']['tmp_name'])){
	// 				// $up_path='photo/'.$_FILES["file"]["name"];
	// 				// if(!$up_path) echo "up_path地址不存在".$up_path."<br>";

	// 				//压缩图片
	// 				$image = new edit_imagick($_FILES['file']['tmp_name']);
	// 				$image->reSize($_FILES['file']['tmp_name']);

	// 				$up_image=move_uploaded_file($_FILES["file"]["tmp_name"], $gImage);
	// 				//clearstatcache();
	// 			}
	// 			if($up_image) echo "物品图片信息上传成功<br>";
	// 			else echo "物品图片信息上传失败<br>";
	// 			// rename("photo/".$_FILES["file"]["name"],$gImage);
	// 		}
	// 	}
	// 	else{
	// 		echo "图片大小不得超过10M";
	// 	}
	// }
	// else{
	// 	echo $_FILES["file"]["type"]."<br>";
	// 	echo "非法的文件格式";
	// }

?>