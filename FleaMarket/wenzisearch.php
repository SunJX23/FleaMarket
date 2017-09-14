<?php
	//关键字查询
	function Select1($con, $detext, &$result, &$except){
		$goodsdetail = array('name', 'category', 'detail');
		foreach ($goodsdetail as $searchtext) {
			$sql = "select * from fleainfo where {$searchtext} LIKE '%{$detext}%' {$except} ";
			$query = mysqli_query($con,$sql);
			if($query){
				while($row = mysqli_fetch_array($query, MYSQL_ASSOC)){
					$result[]=$row;
					$except = $except." and ID != '".$row['ID']."'";
				}
			}
		}
	}


	require_once(dirname(__FILE__).'/back_end/const.php');
	$con = mysqli_connect(HOST,USERNAME,PASSWORD,DB);
	$con -> set_charset('utf8');

	$text = isset($_POST['text']) ? $_POST['text'] : null;
	$result = array();
	$except = '';

	//精准关键字查询
	Select1($con, $text, $result, $except);

	//部分关键字查询
	$len = mb_strlen($text,'utf8');
	if($len > 1 && preg_match('/^[\x{4e00}-\x{9fa5}]+$/u', $text)>0){
		$i = 0;
		while($i < $len){
			$texts[] = mb_substr($text,$i++,1,'utf8');
		}
		foreach ($texts as $t) {
			Select1($con, $t, $result, $except);
		}
	}

	//拼音关键字查询

	$response = array();
	$response['ret'] = 1;
	$response['total'] = count($result);
	$response['data'] = $result;

	echo json_encode($response);
	mysqli_close($con);
?>