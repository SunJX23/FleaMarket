<?php
define("TOKEN", "q135vn4QA31NoNo5V10A4a4j65E31l33"); //TOKEN值
$wechatObj = new wechat();
$wechatObj->valid();
class wechat {
	public function valid() {
		$echoStr = $_GET["echostr"];
		if($this->checkSignature()){
			echo $echoStr;
			exit;
		}
	}
	private function checkSignature() {
		$signature = $_GET["signature"];
		$timestamp = $_GET["timestamp"];
		$nonce = $_GET["nonce"];
		$token = TOKEN;
		$tmpArr = array($token, $timestamp, $nonce);
		sort($tmpArr);
		$tmpStr = implode( $tmpArr );
		$tmpStr = sha1( $tmpStr );
		if( $tmpStr == $signature ) {
			return true;
		} else {
			return false;
		}
	}
}
?>