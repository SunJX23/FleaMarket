<?php
	require_once(dirname(dirname(__FILE__)).'/class/Cache.php');
	require_once('const.php');
	
	function https_request($url, $data = null, $timeout=30)
	{
		if($url == '')
			return;
		$curl = curl_init();
		curl_setopt($curl, CURLOPT_URL, $url);//需要获取的 URL 地址，也可以在curl_init() 初始化会话的时候。
		curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, FALSE);//跳过证书检查
		//curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, FALSE);//验证证书状态
		if (!empty($data)){
			curl_setopt($curl, CURLOPT_POST, true);//想PHP去做一个正规的HTTP POST，设置这个选项为一个非零值。这个POST是普通的 application/x-www-from-urlencoded 类型，多数被HTML表单使用。
			curl_setopt($curl, CURLOPT_POSTFIELDS, $data);//全部数据使用HTTP协议中的 "POST" 操作来发送。 要发送文件，在文件名前面加上@前缀并使用完整路径。 文件类型可在文件名后以 ';type=mimetype' 的格式指定。
		}
		curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);//TRUE 将curl_exec()获取的信息以字符串返回，而不是直接输出。
		curl_setopt($curl, CURLOPT_TIMEOUT, $timeout);
		$output = curl_exec($curl);
		curl_close($curl);
		return $output;
	}


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

?>