<?php
	require_once(dirname(__FILE__).'/back_end/base/ImgEdit.php');
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