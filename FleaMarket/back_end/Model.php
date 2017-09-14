<?php

require_once('const.php');
require_once('Error.php');
require_once(dirname(__FILE__).'/wechat/DownImg.php');

class Model {
    protected $con;
    protected $error_code;
    protected $i_uID;
    
    public function __construct() {
        $this->con = mysqli_connect(HOST,USERNAME,PASSWORD,DB);
        $this->con->set_charset('utf8');
        $this->error_code = Error::_instance();
        $this->i_uID = isset($_SESSION['openid']) ? $_SESSION['openid'] : 'olKuAwHPm_1vwuv1dh6GD20bRlMM';
    }

    // 上传物品
    public function uploadGoods() {
        /* $key => $val, $key:前端传来数据对应的键，$val:数据库表中对应的键 */
        $forms = array('name' => 'category', 'goodsName' => 'name', 'sale' => 'price', 'detail' => 'detail');
        $data = array();
        $response = array();
        foreach ($forms as $key => $val) {
            $col = empty($val) ? $key : $val;
            $data[$col] = isset($_POST[$key]) ? self::filter($_POST[$key]) : null;
            if ($val !== 'detail') {
                $code_type = 'TEST__'.strtoupper($val);
                $response = $this->checkParam($data[$col], $this->error_code->$code_type);
                if ($response['ret'] !== 1)
                    return $response;
            }
        }
        $serverIDs = isset($_POST['sid']) ? explode(',', $_POST['sid']) : null;
        $total = isset($_POST['total']) ? $_POST['total'] : 0;
        for ($i = $total; $i < count($serverIDs); $i++)
            unset($serverIDs[i]);
        $data['image'] = downloadImg($serverIDs);
        $time = time();
        $data['ID'] = $time;
        for ($i = 0; $i < 3; $i++)
            $data['ID'] .= rand(0,9);
        $data['uID'] = $this->i_uID;
        $result = $this->insertData($data, 'fleainfo');
        if (false === $result)
            return $this->error_code->INSERT_FAILED;
        return $response;
    }

    /* 按类别获取商品信息
       前端参数(POST)：商品分类：name
                      商品价格：sale
                      排序方式：order
       后端返回(JSON)：成功 ['ret' => 1]
                      失败 ['ret' => -1, 'msg' => string(错误信息)]*/
    public function getGoodsByCategory () {
        $forms = array('name' => 'category', 'sale' => 'price', 'order' => 'order');;
        $data = array();
        $response = array();
        $sql = 'select * from fleainfo ';
        $first = true;
        foreach ($forms as $key => $val) {
            $data[$val] = isset($_POST[$key]) ? $_POST[$key] : null;
            if (!$data[$val]) continue;
            if ($val !== 'order')
                $sql .= $first ? 'where ' : ' and ';
            switch ($val) {
                case 'category':
                    $sql .= "category = '$data[$val]'";
                    $first = false;
                    break;
                case 'price':
                    $low = explode('_', $data[$val])[0];
                    $high = explode('_', $data[$val])[1];
                    $sql .= $low === '1000以上' ? "price > 1000" : "price < $high and price > $low";
                    $first = false;
                    break;
                case 'order':
                    $sql .= ' order by price '.$data[$val];
                    break;
            }
        }
        return $this->getGoodDatas($sql);
    }

    public function getGoodData () {
        $ID = isset($_POST['ID']) ? $_POST['ID'] : null;
        if (!$ID)
            return $this->error_code->PARAM_DROP;
        $sql = "select * from fleainfo where ID = $ID";
        $query = mysqli_query($this->con, $sql);
        if ($query) {
            $row = mysqli_fetch_array($query, MYSQL_ASSOC);
            $row['image'] = str_split($row['image'], 25);
            $result = $row;
        }
        $response['ret'] = 1;
        $response['uID'] = $this->i_uID;
        $response['data'] = $result;
        return $response;
    }


    // 获取个人物品信息列表
    public function getPersonalData () {
        $uID = $this->i_uID;
        $sql = "select * from fleainfo where uID = '$uID' order by ID desc";
        return $this->getGoodDatas($sql);
    }

    // 获取商品数据
    protected function getGoodDatas ($sql) {
        $response = array();
        $response['ret'] = 1;
        $result = array();
        $query = mysqli_query($this->con,$sql);
        if($query) {
            while ($row = mysqli_fetch_array($query, MYSQL_ASSOC)) {
                $row['image'] = str_split($row['image'], 25);
                $result[] = $row;
            }
        }
        $response['total'] = mysqli_num_rows($query);
        $response['data'] = $result;
        return $response;
    }

    // 新增聊天记录表
    protected function createChatLogs () {
        $i_uID = $this->i_uID;
        $u_uID = isset($_POST['uID']) ? $_POST['uID'] : null;
        if ($i_uID === $u_uID)
            return $this->error_code->CHAT_YOURSELF;
        if (is_null($u_uID) || is_null($i_uID) || $u_uID === 'undefined' || $i_uID === 'undefined' || $u_uID === 'null' || $i_uID === 'null')
            return $this->error_code->USER_LOST;
        $log_name = $i_uID < $u_uID ? $i_uID.'___'.$u_uID : $u_uID.'___'.$i_uID;
        $query = mysqli_query($this->con, "create table if not exists $log_name (logid bigint(10) not null, chatlog text, isme enum('i','u') not null)");
        if (!$query)
            return $this->error_code->CREATE_LOG_FAILED;
        return $log_name;
    }

    /* 插入聊天记录
       前端参数(POST)：聊天对方：uID
                      聊天内容：chatlog
       后端返回(JSON)：成功 ['ret' => 1]
                      失败 ['ret' => -1, 'msg' => string(错误信息)]*/
    public function insertChatLogs () {
        $log_name = $this->createChatLogs();
        if (is_null($log_name))
            return $this->error_code->UNKNOW_ERROR;
        if ($log_name && isset($log_name['ret']) && $log_name['ret'] === -1)
            return $log_name;
        $time = time();
        $data['logid'] = $time;
        $data['chatlog'] = isset($_POST['chatlog']) ? $_POST['chatlog'] : null;
        $data['isme'] = explode('___', $log_name)[0] === $this->i_uID ? 'i' : 'u';
        $query = $this->insertData($data, $log_name);
        if (!$query)
            return $this->error_code->INSERT_FAILED;
        $response['ret'] = 1;
        $response['total'] = 1;
        $response['contact'] = $this->getContact($_POST['uID']);
        $response['data'] = array(array('time' => $time, 'chatlog' => $data['chatlog'], 'isme' => 'i'));
        return $response;
    }

    /* 获取聊天记录
       前端参数(POST)：聊天对方：uID
       后端返回(JSON)：成功 ['ret' => 1, ]
                      失败 ['ret' => -1, 'msg' => string(错误信息)]*/
    public function getChatLogs () {
        $u_uID = isset($_POST['uID']) ? $_POST['uID'] : null;
        if (is_null($u_uID))
            return $this->error_code->USER_LOST;
        $log_name = $this->createChatLogs();
        if (is_null($log_name))
            return $this->error_code->UNKNOW_ERROR;
        if ($log_name && isset($log_name['ret']) && $log_name['ret'] === -1)
            return $log_name;
        $time = (isset($_POST['time'])) ? $_POST['time'] : null;
        $sql = "select * from $log_name";
        $sql .= $time ? " where logid > '$time'" : '';
        $sql .= " order by logid asc";
        $query = mysqli_query($this->con, $sql);
        $response = array();
        $response['ret'] = 1;
        $response['total'] = mysqli_num_rows($query);
        $contact = $this->getContact($u_uID);
        if (false === $contact)
            return $this->error_code->GETDATA_FAILED;
        $response['contact'] = $contact;
        $response['data'] = array();
        $isme = $this->i_uID < $u_uID ? true : false;
        while ($row = mysqli_fetch_array($query)) {
            $data['time'] = $row['logid'];
            $data['chatlog'] = $row['chatlog'];
            if (!$isme)
                $data['isme'] = $row['isme'] === 'i' ? 'u' : 'i';
            else
                $data['isme'] = $row['isme'];
            $response['data'][] = $data;
        }
        return $response;
    }

    /* 获取联系人信息（单人）*/
    public function getContact ($u_uID) {
        $query = mysqli_query($this->con, "select * from user where uID = '$u_uID' or uID = '$this->i_uID'");
        if (!$query)
            return false;
        $result = array();
        while ($row = mysqli_fetch_array($query, MYSQL_ASSOC)) {
            if ($row['uID'] === $this->i_uID)
                $result['i'] = $row;
            else
                $result['u'] = $row;
        }
        return $result;
    }

    /* 获取联系人信息（多人）
       前端参数(POST)：无
       后端返回(JSON)：成功 ['ret' => 1, 'total' => int, 'data' => [
                              {'uID' => string(用户ID), 'unick' => string(用户昵称), 'uimage' => string(用户头像)},...
                           ]]
                      失败 ['ret' => -1, 'msg' => string(错误信息)]*/
    public function getContacts () {
        $i_uID = $this->i_uID;
        if (!$i_uID)
            return $this->error_code->USER_LOST;
        $sql = "show tables like '%$i_uID%'";
        $query = mysqli_query($this->con, $sql);
        if ($query) {
            while ($row = mysqli_fetch_array($query, MYSQL_NUM)) {
                $result[] = $row;
            }
        }
        $response = array();
        $response['ret'] = 1;
        $response['total'] = mysqli_num_rows($query);
        $response['data'] = array();
        if (isset($result)) {
            foreach ($result as $contact) {
                $cont = explode('___', $contact[0]);
                $response['data'][] = $cont[0] === $i_uID ? $cont[1] : $cont[0];
            }
        }
        $sql = 'select * from user where ';
        foreach ($response['data'] as $contact) {
            $sql .= "uID = '$contact' or ";
        }
        $sql = substr($sql, 0, -4);
        $query = mysqli_query($this->con, $sql);
        $response['data'] = array();
        if (!$query)
            return $this->error_code->GETDATA_FAILED;
        while ($row = mysqli_fetch_array($query, MYSQL_ASSOC)) {
            $response['data'][]  = $row;
        }
        return $response;
    }

    // 向数据库表插入数据
    protected function insertData ($data, $db) {
        if (count($data) <= 0)
            return false;
        $column = '';
        $values = '';
        foreach ($data as $col => $val) {
            $column .= "$col, ";
            $values .= "'$val', ";
        }
        $column = substr($column, 0, -2);
        $values = substr($values, 0, -2);
        $sql = "insert into $db ($column) values ($values)";
        return mysqli_query($this->con, $sql);
    }

    // 关闭数据库连接
    public function closeCon () {
        mysqli_close($this->con);
    }

    // 检查表单数据
    protected function checkParam ($value, $rule) {
        if (empty($value))
            return $this->error_code->PARAM_DROP;
        if (!preg_match($rule['check_type'], $value))
            return $rule['error_code'];
        return array('ret' => 1);
    }

    // xss注入
    final protected static function filter($formdata) {
        return addslashes(htmlspecialchars($formdata));
    }

    // 输出测试
    protected function testfile($str) {
        $myfile = fopen("testfile.txt", "w");
        fwrite($myfile, $str);
    }
}

// name 分类名
// goodsName 物品名称
// sale 价格
// detail 商品详情


// {
//     'ret' => 1,
//     'total' => 0,
//     'contact' => array(
//         'i' => array(
//             'uID' => '',
//             'nick' => '九夏三冬',
//             'headimg' => 'http/'
//         ),
//         'u' => array(
//             'uID' => '',
//             'nick' => '',
//             'headimg' => 'http/'
//         )
//     )
//     'data' => array(
//         0 => array(
//             'time' => '',
//             'chatlog' => 'soeijaesejf',
//             'isme' => 'i'/'u'
//         ),
//         1 => array(
//             'time' => '',
//             'chatlog' => 'soeijaesejf',
//             'isme' => 'i'/'u'
//         )
//     )
// }