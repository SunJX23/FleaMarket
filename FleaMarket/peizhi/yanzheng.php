<?php
	//将timestamp，nonce，token按字典序排序
	$timestamp = $_GET['timestamp'];//微信通过get方法传递的
	$nonce = $_GET['nonce'];//随机参数
	$token = 'youzishiwu';//在微信公众平台是填写的
	$signature = $_GET['signature'];//微信公众平台加密好的
	$array = array($timestamp,$nonce,$token);
	sort($array);
	//将排序后的三个参数拼接后用sha1加密
	$tmpstr = implode('', $array);//或用join 拼接
	$tmpstr = sha1($tmpstr);//加密
	//将加密后的字符串与singature进行对比，判断该请求是否来自微信
	if($tmpstr == $signature){
		echo $_GET['echostr'];//微信传过来的参数
		exit;//退出程序
	}
?>