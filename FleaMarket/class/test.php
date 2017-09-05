<?php
	require_once('Cache.php');

	$cache = new Cache(7200,'../cache/');

	echo $cache -> fileName('access_token');
	echo "<br>";

	$access_token = $cache -> get('access_token');
	if($access_token == false){
		$access_token = "abcd";
		$cache -> put('access_token',$access_token);
	}

?>