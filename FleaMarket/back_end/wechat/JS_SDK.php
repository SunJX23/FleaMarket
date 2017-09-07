<?php
	require_once('WechatInterface.php');
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
