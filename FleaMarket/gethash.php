<?php
	$gID = "11";
	$path = "./phash ".$gID;
	exec($path, $res);
	var_dump($res);
	file_put_contents('demo/xx', $res);
?>