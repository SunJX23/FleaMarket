<?php
	session_save_path(dirname(dirname(__FILE__))."/cache/");
	session_start();
	header("Content-type: text/html; charset=utf-8");
	require_once('http_request.php');
	require_once(dirname(dirname(__FILE__))."/config.php");
	$appid = APPID;
	$appsecret = APPSECRET;
	if (isset($_GET['code'])){
		$code = $_GET['code'];
		$url = "https://api.weixin.qq.com/sns/oauth2/access_token?appid=$appid&secret=$appsecret&code=$code&grant_type=authorization_code";
		$output = https_request($url);
		$jsoninfo = json_decode($output, true);

		$accesstoken = $jsoninfo['access_token'];
		$openid = $jsoninfo['openid'];

		$_SESSION['access_token'] = $accesstoken;
		$_SESSION['openid'] = $openid;

		//将用户信息存入users表中
		$con = mysqli_connect(HOST,USERNAME,PASSWORD,DB);
		$con -> set_charset('utf8');
		$query = mysqli_query($con, "select unick from users where uID = '$openid'");
		$result = mysqli_fetch_array($query);
		
		$url = "https://api.weixin.qq.com/sns/userinfo?access_token=$accesstoken&openid=$openid&lang=zh_CN";
		$output = https_request($url);
		$jsoninfo = json_decode($output, true);
		$unick = $jsoninfo['nickname'];
		$_SESSION['nickname'] = $unick;
		$query = mysqli_query($con, "select unick from users where uID = '$openid'");
		$result = mysqli_fetch_array($query);
		if($result){
			if($result['unick'] != $unick)
				$query = mysqli_query($con, "update users set unick = '$unick' where uID = '$openid' ");
		}
		else{
			$query = mysqli_query($con, "insert into users (uID,unick) values ('$openid','$unick')");
		}

		$baseurl = 'http://115.29.38.30/FleaMarket/FleaMarket';

		switch ($_GET['state']) {
			case '1':
				header("Location:$baseurl/lost.html");
				break;
			case '2':
				header("Location:$baseurl/searchlost.html");
				break;
			case '3':
				header("Location:$baseurl/Personal_Center.html");
				break;
		}
	}else{
	    echo "授权失败";
	}
?>