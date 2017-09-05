<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="UTF-8">
	<title>lost_find</title>
	<link href="./css/bootstrap.min.css" rel="stylesheet">
	<link rel="stylesheet" href="./styles/search.css">
	<script src="./js/jquery-3.1.1.min.js"></script>
	<script src="./js/bootstrap.min.js"></script>
	<meta name="viewport" content="width=device-width,height=device-height,initial-scale=1.0, minimum-scale=1.0, maximum-scale=1.0, user-scalable=no">
</head>
<body>
<form action="search.php" method="post" enctype="multipart/form-data">
	<div id="app">
	    <div class="my_slide">
			<div class="my_sidebar">
				<nav class="title">小柚子招领</nav>
				<div class="content">
					<ul>
						<li>
						    <i class="glyphicon glyphicon-time"></i>
						    <select name="time" id="" class="form-control">
						    	<option value="" style="color: #aaa" disabled selected>时间</option>
						    	<option value="">全部时间</option>
								<option value="threedays">近3天</option>
								<option value="aweek">近一周</option>
								<option value="amonth">近一月</option>
								<option value="halfyear">近半年</option>
						    </select>
						</li>
						<li>
						    <i class="glyphicon glyphicon-tree-deciduous"></i>
						    <select name="place" id="" class="form-control">
								<option value="" style="color: #aaa" disabled selected>地点</option>
						    	<option value="">全部地点</option>
								<option value="教一">教一</option>
								<option value="教二">教二</option>
								<option value="教三">教三</option>
								<option value="教四">教四</option>
								<option value="教五">教五</option>
								<option value="图书馆">图书馆</option>
								<option value="南操">南操</option>
								<option value="北操">北操</option>
								<option value="大活">大活</option>
								<option value="其他">其他</option>
								<option value="未知">我不知道</option>
						    </select>
						</li>
						<li>
						    <i class="glyphicon glyphicon-heart"></i>
						    <select name="name" id="" class="form-control">
						    	<option value="" style="color: #aaa" disabled selected>种类</option>
						    	<option value="">全部种类</option>
								<option value="饭卡">饭卡</option>
								<option value="钱包">钱包</option>
								<option value="杯子">杯子</option>
								<option value="U盘">U盘</option>
								<option value="书本">书本</option>
								<option value="文具">文具</option>
								<option value="手机">手机</option>
								<option value="钥匙">钥匙</option>
								<option value="其他">其他</option>
						    </select>
						</li>
					</ul>
					<div class="btn">
						<div class="btn_left">
							<button type="button" class="btn btn-default navbar-btn">退出</button>
						</div>
						<div class="btn_right">
							<button type="submit" class="btn btn-info navbar-btn">筛选</button>
						</div>
					</div>
				</div>
			</div>
			<div class="main">
				<nav>
					<div class="drawer-toggle">
					    <i class="glyphicon glyphicon-align-justify"></i>
					</div>
					<div class="search">
						    <div class="input-group">
						      <input type="text" name="text" onkeyup="showHint(this.value)" class="form-control">
						      <span class="input-group-btn">
						        <button class="btn btn-default" type="button">Go!</button>
						      </span>
						    </div>
					    </div>
				</nav>
				<div class="main_body">

<?php
	require_once('config.php');
	$con=mysqli_connect(HOST,USERNAME,PASSWORD,DB);
	$con->set_charset("utf8");
	$time=$_POST['time'];
	$place=$_POST['place'];
	$name=$_POST['name'];
	$enddate = strtotime("now");
	switch ($time) {
		case 'threedays':
			$startdate=strtotime("-3 days");
			break;
		case 'aweek':
			$startdate=strtotime("-1 week");
			break;
		case 'amonth':
			$startdate=strtotime("-1 month");
			break;
		case 'halfyear':
			$startdate=strtotime("-6 months");
			break;
	}
	$sql="select gName,gDate,gPlace,gDePlace,gDetail,gContact,gImage from goods where ";
	$isand=0;
	if($time==null&&$place==null&&$name==null){
		$sql=$sql."isLose=1";
	}
	else{
		if($time!=null){
			$isand=1;
			$sql=$sql."UNIX_TIMESTAMP(gDate) >= $startdate and UNIX_TIMESTAMP(gDate) <= $enddate ";
		}
		else if($place!=null&&$place!="未知"){
			if($isand){
				$sql=$sql."and ";
			}
			else $isand=1;
			$sql=$sql."gPlace='$place' ";
		}
		else if($name!=null){
			if($isand){
				$sql=$sql."and ";
			}
			else $isand=1;
			$sql=$sql."gName='$name' ";
		}
		$sql=$sql."and isLose=1";
	}
	$sql=$sql." order by gDate desc";
	$query = mysqli_query($con,$sql);
	if($query){
		while($row=mysqli_fetch_array($query)){
			$result[]=$row;
		}
	}

	$size=sizeof($result);
	if ($size){
		$null="无";
		foreach($result as $xinxi)
		{
			$sname=$xinxi['gName'];
			$sdate=$xinxi['gDate'];
			$splace=$xinxi['gPlace'];
			$scontact=$xinxi['gContact'];

			if(!$splace||$splace=="未知")
				$splace = $xinxi['gDePlace'] ? $xinxi['gDePlace'] : $null;

			else if($xinxi['gDePlace']) 
				$splace=$splace."  ".$xinxi['gDePlace'];

			if(!$scontact) $scontact=$null;

			if($sname=="饭卡"){
				$card=$xinxi['gDetail'];
				switch (substr($card,0,2)){
					case 'CC':
						$cno=substr($card,2,15);
						if(substr($card,17,2)=="CN"){
							$cname=substr($card,19);
						}
						else $cname=$null;
						break;
					case 'CN':
						$cno=$null;
						$cname=substr($card,2);
						break;
					default:
						$cno=$null;
						$cname=$null;
						break;
				}
				if(!$xinxi['gDetail']) 
					$detail=$null;
				else 
					$detail=$xinxi['gDetail'];
?> 
				<figure class="">
					<div class="img">
						<img src=<?php echo "thumpimg.php?path=".$xinxi['gImage']?> max-height="150px" alt="">
					</div>
					<div class="details">
						<p>物品：<?php echo $sname ?></p>
						<p>姓名：<?php echo $cname ?></p>
						<p>卡号：<?php echo $cno ?></p>
						<p>时间：<?php echo $sdate ?></p>
						<p>地点：<?php echo $splace ?></p>
						<p>联系：<?php echo $scontact ?></p>
					</div>
				</figure>
<?php
			}else{
				if($xinxi['gName']=="其他" && $xinxi['gDetail'])
					$sname=$xinxi['gDetail'];
				else
					$sname=$sname." ".$xinxi['gDetail'];
?>
				<figure class="">
					<div class="img">
						<img src=<?php echo "thumpimg.php?path=".$xinxi['gImage']?> max-height="150px" alt="">
					</div>
					<div class="details">
						<p>物品：<?php echo $sname ?></p>
						<p>时间：<?php echo $sdate ?></p>
						<p>地点：<?php echo $splace ?></p>
						<p>联系：<?php echo $scontact ?></p>
					</div>
				</figure>
<?php
			}
		}
	}
	else{
		echo "对不起，无此类物品";
	}
?>
			</div>
		</div>
	</div>
</form>
</body>
<script>
	window.onload=function(){

		$('.drawer-toggle').click(function(e){
			var my_slide=$('.my_slide');
			if(my_slide.css('left')=='-250px'){
                my_slide.animate({
                	left:'0px'
                })
			}else{
				my_slide.animate({
                	left:'-250px'
                })
			}
		})
		$('.main_body').resize(function(e){
			console.log(document.body.clientWitdh)
		})

	}
	function showHint(str)
	{
		if (str.length==0)
		{ 
			document.getElementById("txtHint").innerHTML="";
			return;
		}
		if (window.XMLHttpRequest)
		{
			// IE7+, Firefox, Chrome, Opera, Safari 浏览器执行的代码
			xmlhttp=new XMLHttpRequest();
		}
		else
		{	
			//IE6, IE5 浏览器执行的代码
			xmlhttp=new ActiveXObject("Microsoft.XMLHTTP");
		}
		xmlhttp.onreadystatechange=function()
		{
			if (xmlhttp.readyState==4 && xmlhttp.status==200)
			{
				document.getElementById("txtHint").innerHTML=xmlhttp.responseText;
			}
		}
		xmlhttp.open("GET","wenzi.php?text="+str,true);
		xmlhttp.send();
	}
</script>
</html>