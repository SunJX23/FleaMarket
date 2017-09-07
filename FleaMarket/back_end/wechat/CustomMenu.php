<?php
	require_once(dirname(dirname(__FILE__)).'/base/Http.php');
	require_once('WechatInterface.php');

	$access_token = getAccess_token();

	echo $access_token;

	$url = "https://api.weixin.qq.com/cgi-bin/menu/create?access_token=$access_token";

	$baseurl = 'https://open.weixin.qq.com/connect/oauth2/authorize?appid=wx868a7ba7368b6e07&redirect_uri=http://115.29.38.30/FleaMarket/FleaMarket/back_end/Control.php&response_type=code&scope=snsapi_userinfo';
	$jsonmenu = '{
		"button":[
		{
			"type":"view",
			"name":"聊天",
			"url":"https://open.weixin.qq.com/connect/oauth2/authorize?appid=wx868a7ba7368b6e07&redirect_uri=http://115.29.38.30/FleaMarket/FleaMarket/back_end/Control.php&response_type=code&scope=snsapi_userinfo&state=1#wechat_redirect"
		},
		{
			"type":"view",
			"name":"物品交易",
			"url":"https://open.weixin.qq.com/connect/oauth2/authorize?appid=wx868a7ba7368b6e07&redirect_uri=http://115.29.38.30/FleaMarket/FleaMarket/back_end/Control.php&response_type=code&scope=snsapi_userinfo&state=2#wechat_redirect"

		},
		{
			"name":"我的",
			"sub_button":[
			{
				"type":"view",
				"name":"我的",
				"url":"https://open.weixin.qq.com/connect/oauth2/authorize?appid=wx868a7ba7368b6e07&redirect_uri=http://115.29.38.30/FleaMarket/FleaMarket/back_end/Control.php&response_type=code&scope=snsapi_userinfo&state=3#wechat_redirect"
			},
			{
				"type":"view",
				"name":"测试",
				"url":"https://open.weixin.qq.com/connect/oauth2/authorize?appid=wx868a7ba7368b6e07&redirect_uri=http://115.29.38.30/FleaMarket/FleaMarket/back_end/Control.php&response_type=code&scope=snsapi_userinfo&state=4#wechat_redirect"
			}]
		}]
	}';
	echo $jsonmenu;

	$result = https_request($url,$jsonmenu);
	var_dump($result);

?>