<?php
	require_once('class/imgedit.class.php');

	if(isset($_GET['path']) && $_GET['path']){
		$image = new edit_imagick($_GET['path']);
		$image->thump();
		$image->show();
	}
	else{
		$image = new edit_imagick('images/a.jpg');
		$image->show();
	}

?>