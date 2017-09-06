<?php
    require_once(dirname(dirname(__FILE__)).'/base/Cache.php');
    require_once(dirname(dirname(__FILE__)).'/base/Http.php');
    require_once(dirname(dirname(__FILE__)).'const.php');
	function getAccess_token(){
		$appid = APPID;
		$appsecret = APPSECRET;
		$cache = new Cache();
		$access_token = $cache -> get('access_token');
		if($access_token == false){
			$url = "https://api.weixin.qq.com/cgi-bin/token?grant_type=client_credential&appid=$appid&secret=$appsecret";
			$output = https_request($url);
			$jsoninfo = json_decode($output, true);
			$access_token = $jsoninfo['access_token'];
			$cache -> put('access_token', $access_token);
		}
		return $access_token;
	}

	function getJsapi_ticket(){
		$cache = new Cache();
		$jsapi_ticket = $cache -> get('jsapi_ticket');
		if($jsapi_ticket == false){
			$access_token = getAccess_token();
			$url = "https://api.weixin.qq.com/cgi-bin/ticket/getticket?access_token=$access_token&type=jsapi";
			$output = https_request($url);
			$jsoninfo = json_decode($output, true);
			$jsapi_ticket = $jsoninfo['ticket'];
			$cache -> put('jsapi_ticket', $jsapi_ticket);
		}
		return $jsapi_ticket;
	}

	function getNonceStr(){
		$noncestr = '';
		for($i = 0; $i < 16; $i++){
			$noncestr .= chr(mt_rand(48,122));
		}
		return $noncestr;
	}

	function getSignature($noncestr, $jsapi_ticket, $timestamp){
		$sign = 'jsapi_ticket='.$jsapi_ticket.'&noncestr='.$noncestr.'&timestamp='.$timestamp.'&url='.$_POST['url'];
		$signature = sha1($sign);
		return $signature;
	}

	function getConfig(){
		$appid = APPID;
		$jsapi_ticket = getJsapi_ticket(); //获取jsapi_ticket
		$noncestr = getNonceStr(); //获取noncestr随机串
		$timestamp = time(); //获取timestamp时间戳
		$signature = getSignature($noncestr, $jsapi_ticket, $timestamp); //获取签名
		$result = array('appId' => $appid, 'timestamp' => $timestamp, 'jsapi_ticket' => $jsapi_ticket, 'signature' => $signature, 'noncestr' => $noncestr);
		return json_encode($result);
	}

    header('Content-type','application/json');

	switch ($_POST['type']) {
		case 'access_token':
			echo getAccess_token();
			break;

		case 'jsapi_ticket':
			echo getJsapi_ticket();
			break;

		case 'config':
			echo getConfig();
			break;

		default:
			break;
	}