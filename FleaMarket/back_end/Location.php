<?php
	session_save_path(dirname(dirname(dirname(dirname(__FILE__))))."/cache/");
	session_start();
	require_once(dirname(__FILE__).'/base/Http.php');
	require_once("const.php");
	$appid = APPID;
	$appsecret = APPSECRET;
	if (isset($_GET['code'])){
		$code = $_GET['code'];
		$url = "https://api.weixin.qq.com/sns/oauth2/access_token?appid=$appid&secret=$appsecret&code=$code&grant_type=authorization_code";
		$output = https_request($url);
		$jsoninfo = json_decode($output, true);

		if (!isset($jsoninfo['errcode'])) {

			$accesstoken = $jsoninfo['access_token'];
			$openid = $jsoninfo['openid'];
			$_SESSION['access_token'] = $accesstoken;
			$_SESSION['openid'] = $openid;

			// 获取用户昵称和头像			
			$url = "https://api.weixin.qq.com/sns/userinfo?access_token=$accesstoken&openid=$openid&lang=zh_CN";
			$output = https_request($url);
			$jsoninfo = json_decode($output, true);

			if (isset($jsoninfo['nickname']) && isset($jsoninfo['headimgurl'])) {

				$unick = $jsoninfo['nickname'];
				$headimgurl = $jsoninfo['headimgurl'];
				$_SESSION['nickname'] = $unick;

				// 将用户信息存入user表中
				$con = mysqli_connect(HOST,USERNAME,PASSWORD,DB);
				$con -> set_charset('utf8');
				$query = mysqli_query($con, "select unick,uimage from user where uID = '$openid'");
				if (false === $query) {
					$query = mysqli_query($con, "insert into user (uID,unick,uimage) values ('$openid','$unick','$headimgurl')");
				} else {
					$result = mysqli_fetch_array($query, MYSQL_ASSOC);
					if($result) {
						if ($result['unick'] != $unick || $result['uimage'] != $headimgurl)
							$query = mysqli_query($con, "update user set unick = '$unick', uimage = '$headimgurl' where uID = '$openid' ");
					}
					else {
						$query = mysqli_query($con, "insert into user (uID,unick,uimage) values ('$openid','$unick','$headimgurl')");
					}
				}
			}
			Redirection();
		} else {
			echo '<h1>用户授权失败，请退出重试</h1>';
		}
	}else{
	    echo '<h1>用户授权失败，请退出重试</h1>';
	}

	function Redirection () {

		$baseurl = 'http://115.29.38.30/FleaMarket/FleaMarket';

		if (empty($_GET['state'])) return;

		$url = null;

		switch ($_GET['state']) {
			case '1':
				$url = 'chat';
				break;
			case '2':
				$url = 'searchlost';
				break;
			case '3':
				$url = 'Personal_Center';
				break;
			case '4':
				$url = 'test';
				break;
			case '5':
				header("Location:http://115.29.38.30/searchlost.html");
				return;
		}
		header("Location:$baseurl/$url.html");
	}
?>