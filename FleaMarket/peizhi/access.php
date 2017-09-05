<?php
	//获取网站
	//初始化curl
	$ch = curl_init();
	$url = "http://www.baidu.com";
	//设置curl的参数
	curl_setopt($ch, CURLOPT_URL, $url);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
	//采集
	$output = curl_exec($ch);
	curl_close($ch);
	var_dump($output);
?>