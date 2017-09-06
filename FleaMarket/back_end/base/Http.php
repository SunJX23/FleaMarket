<?php

function https_request($url, $data = null, $timeout=30)
{
    if($url == '')
        return;
    $curl = curl_init();
    curl_setopt($curl, CURLOPT_URL, $url);//需要获取的 URL 地址，也可以在curl_init() 初始化会话的时候。
    curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, FALSE);//跳过证书检查
    //curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, FALSE);//验证证书状态
    if (!empty($data)){
        curl_setopt($curl, CURLOPT_POST, true);//想PHP去做一个正规的HTTP POST，设置这个选项为一个非零值。这个POST是普通的 application/x-www-from-urlencoded 类型，多数被HTML表单使用。
        curl_setopt($curl, CURLOPT_POSTFIELDS, $data);//全部数据使用HTTP协议中的 "POST" 操作来发送。 要发送文件，在文件名前面加上@前缀并使用完整路径。 文件类型可在文件名后以 ';type=mimetype' 的格式指定。
    }
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);//TRUE 将curl_exec()获取的信息以字符串返回，而不是直接输出。
    curl_setopt($curl, CURLOPT_TIMEOUT, $timeout);
    $output = curl_exec($curl);
    curl_close($curl);
    return $output;
}