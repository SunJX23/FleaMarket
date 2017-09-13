<?php
	require_once(dirname(dirname(__FILE__)).'/base/Http.php');
	require_once(dirname(dirname(__FILE__)).'/base/ImgEdit.php');
	require_once('WechatInterface.php');

	function downloadImg($serverIds) {
		if (!count($serverIds) || empty($serverIds))
			return null;
		$images = null;
		foreach($serverIds as $serverId) {
			$gname = null;
			if (download_image($serverId, $gname))
				$images .= $gname;
		}
		return $images;
	}

	function download_image($serverId, &$gname) {
		$up_image = false;
		// 图片命名
		$x = '00';
		$date = date('YmdHis');
		while (file_exists(dirname(dirname(dirname(__FILE__))).'/photo/'.$date.$x.'.jpg')){
			$x++;
		}
		$gImage = dirname(dirname(dirname(__FILE__))).'/photo/'.$date.$x.'.jpg';
		$gname = 'photo/'.$date.$x.'.jpg';

		// 从微信服务器下载图片
		$access_token = getAccess_token();
		$url="http://file.api.weixin.qq.com/cgi-bin/media/get?access_token={$access_token}&media_id={$serverId}";
		$ch = curl_init();
		$fp = fopen($gImage, 'wb');
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_FILE, $fp);
		curl_setopt($ch, CURLOPT_HEADER, 0);
		$up_image = curl_exec($ch);
		curl_close($ch);
		fclose($fp);

		// 图片压缩(大于300kb)
		if(filesize($gImage) > 307200){
			$image = new edit_imagick($gImage);
			$image->reSize($gImage);
		}
		return $up_image;
	}
?>