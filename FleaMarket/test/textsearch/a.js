// index.js

const BASE_SITE = 'http://115.28.180.202:3000';
var id = 0;
function getJson() {
	// body...
	var meeting = document.getElementsByTagName("table");
	console.log(meeting);
	//最近会议，我发起的会议，我参加的会议
	var url = ["/meeting/findList", "/meeting/findStartedList", "/meeting/findJoinedList"];
	for (var i = 0; i < meeting.length; i++) {
		// meeting[i]
		// var data = getData(BASE_SITE + url[i]);
		// var id = "meeting" + i;
		getDate(BASE_SITE + url[i], meeting[i]);
	}
}

//发起请求
function getData(url, meeting) {
	// body...
	var request = new XMLHttpRequest();
	request.open("get", url);
	request.setRequestHeader('Content-type', 'application/json');
	// 指定服务端返回的数据类型
	request.responseType = 'json';
	request.send(null);
 	request.onload = function(e) {
 		if(this.status === 200) {
 			if(this.response.code === 200){
 				var jsonData =  this.response.data;

 				for (var i = 0; i < this.response.data.length; i++) {

 					createTd(this.response.data[i], meeting, id);
 				}
	 			
	 			// return this.response.data;
 			}
 			else{
 				alert(this.response.message);
 			}
 		}
 		else{
 			alert('网络错误');
 		}
 	}
	// console.log(request.data);
	// return request;
}

//向前端展示
function createTd(obj, Meeting) {

	// body...
	var tr = document.createElement("tr");
	id ++;
	tr.setAttribute("id",id);
	Meeting.appendChild(tr);

	for(var key in obj){
		// if (key == "name") {}
		console.log(obj[key]);
		var td = document.createElement("td");
		var node = document.createTextNode(obj[key]);
		td.appendChild(node);
		var element = document.getElementById(id);
		element.appendChild(td);
	}
}

(function(){
	getJson();
}());
