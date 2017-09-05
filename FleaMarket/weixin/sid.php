<?php
	require_once('http_request.php');
	if(isset($_GET['sid'])){
		var_dump($_GET['sid']);
		file_put_contents('../demo/xx', $_GET['sid']);
		$access_token = getAccess_token();
		downlodimg($access_token,$_GET['sid']);
	}


	function downlodimg($access_token,$serverId){

		$targetName=dirname(__FILE__).'/demo/'.date('YmdHis').".jpg";
		$url="http://file.api.weixin.qq.com/cgi-bin/media/get?access_token={$access_token}&media_id={$serverId}";
		$ch=curl_init();
		$fp=fopen($targetName, 'wb');
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_FILE, $fp);
		curl_setopt($ch, CURLOPT_HEADER, 0);
		curl_exec($ch);
		curl_close($ch);
		fclose($fp);
		//return  $targetName;
  }
?>