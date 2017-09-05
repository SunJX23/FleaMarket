<?php
	require_once('http_request.php');

	$access_token = getAccess_token();

	$url = "https://api.weixin.qq.com/cgi-bin/menu/create?access_token=$access_token";

	$jsonmenu = '{
		"button":[
		{
			"name":"失物局",
			"sub_button":[
			
			{
				"type":"view",
				"name":"我丢东西啦",
				"url":"https://open.weixin.qq.com/connect/oauth2/authorize?appid=wx868a7ba7368b6e07&redirect_uri=http://115.29.38.30/weixin/oauth2.php&response_type=code&scope=snsapi_userinfo&state=1#wechat_redirect"
			},
			{
				"type":"view",
				"name":"我捡东西啦",
				"url":"https://open.weixin.qq.com/connect/oauth2/authorize?appid=wx868a7ba7368b6e07&redirect_uri=http://115.29.38.30/weixin/oauth2.php&response_type=code&scope=snsapi_userinfo&state=2#wechat_redirect"
			},
			{
				"type":"view",
				"name":"饭卡不见啦",
				"url":"https://open.weixin.qq.com/connect/oauth2/authorize?appid=wx868a7ba7368b6e07&redirect_uri=http://115.29.38.30/weixin/oauth2.php&response_type=code&scope=snsapi_userinfo&state=7#wechat_redirect"
			}]
		},
		{
			"name":"认领小街",
			"sub_button":[
			{
				"type":"view",
				"name":"找好心人~",
				"url":"https://open.weixin.qq.com/connect/oauth2/authorize?appid=wx868a7ba7368b6e07&redirect_uri=http://115.29.38.30/weixin/oauth2.php&response_type=code&scope=snsapi_userinfo&state=3#wechat_redirect"
			},
			{
				"type":"view",
				"name":"找失主~",
				"url":"https://open.weixin.qq.com/connect/oauth2/authorize?appid=wx868a7ba7368b6e07&redirect_uri=http://115.29.38.30/weixin/oauth2.php&response_type=code&scope=snsapi_userinfo&state=4#wechat_redirect"
			}]

		},
		{
			"name":"我的",
			"sub_button":[
			{
				"type":"view",
				"name":"我的物品",
				"url":"https://open.weixin.qq.com/connect/oauth2/authorize?appid=wx868a7ba7368b6e07&redirect_uri=http://115.29.38.30/weixin/oauth2.php&response_type=code&scope=snsapi_userinfo&state=5#wechat_redirect"
			},
			{
				"type":"view",
				"name":"感谢墙",
				"url":"https://open.weixin.qq.com/connect/oauth2/authorize?appid=wx868a7ba7368b6e07&redirect_uri=http://115.29.38.30/weixin/oauth2.php&response_type=code&scope=snsapi_userinfo&state=6#wechat_redirect"
			}]
		}]
	}';
	echo $jsonmenu;

	$result = https_request($url,$jsonmenu);
	var_dump($result);

?>