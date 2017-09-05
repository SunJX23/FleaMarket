<?php
	require_once('config.php');
	session_save_path(dirname(dirname(__FILE__))."/cache/");
	session_start();
	$_SESSION['nickname'] = "啦啦啦";
	$openid = "olKuAwHPm_1vwuv1dh6GD20bRlMM";
	$con = mysqli_connect(HOST,USERNAME,PASSWORD,DB);
	$con -> set_charset('utf8');
	$query = mysqli_query($con, "select unick from users where uID = '$openid'");
	$result = mysqli_fetch_row($query);
	var_dump($result);
	if($result){
		$_SESSION['nickname'] = $result['unick'];
	}
	else{
		$url = "https://api.weixin.qq.com/sns/userinfo?access_token=$accesstoken&openid=$openid&lang=zh_CN";
		$output = https_request($url);
		$jsoninfo = json_decode($output, true);
		$unick = $jsoninfo['nickname'];
		$_SESSION['nickname'] = $unick;
		$query = mysqli_query($con, "insert into users (uID,unick) values ('$openid','$unick')");
	}
?>