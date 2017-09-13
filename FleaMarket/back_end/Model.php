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

    // 新增聊天记录
    protected function createChatLogs () {
        $i_uID = $this->i_uID;
        $u_uID = isset($_POST['uID']) ? $_POST['uID'] : null;
        // $u_uID = isset($_POST['uID']) ? $_POST['uID'] : null;
        if (is_null($u_uID))
            return $this->error_code->USER_LOST;
        $log_name = $i_uID < $u_uID ? $i_uID.'___'.$u_uID : $u_uID.'___'.$i_uID;
        $query = mysqli_query($this->con, "create table if not exists $log_name (logid bigint(10) not null, chatlog text, isme enum('i','u') not null)");
        if (!$query)
            return $this->error_code->CREATE_LOG_FAILED;
        return $log_name;
    }

    // 插入聊天记录
    public function insertChatLogs () {
        $log_name = $this->createChatLogs();
        $time = time();
        if (is_null($log_name)) return;
        $data['logid'] = $time;
        $data['chatlog'] = isset($_POST['chatlog']) ? $_POST['chatlog'] : null;
        $data['isme'] = explode('___', $log_name)[0] === $this->i_uID ? 'i' : 'u';
        $this->insertData($data, $log_name);
    }

    // 获取聊天记录
    public function getChatLogs () {
        $u_uID = isset($_POST['uID']) ? $_POST['uID'] : null;
        if (is_null($u_uID))
            return $this->error_code->USER_LOST;
        $log_name = $this->createChatLogs();
        $query = mysqli_query($this->con, "select * from $log_name order by logid desc");
        var_dump($query);
        $response = array();
        $response['total'] = mysqli_num_rows($query);
        if ($contact = $this->getContact($u_uID) === false)
            return $this->error_code->GETDATA_FAILED;
        $response['contact'] = $contact;
        $response['data'] = array();
        $isme = $this->i_uID < $u_uID ? true : false;
        while ($row = mysqli_fetch_array($query)) {
            $data['time'] = date('Y-m-d H:i:s',$row['logid']);
            $data['chatlog'] = $row['chatlog'];
            if (!$isme)
                $data['isme'] = $row['isme'] === 'i' ? 'u' : 'i';
            $response['data'][] = $data;
        }
        return $response;
    }

    // 获取聊天记录
    public function getContact ($u_uID) {
        $query = mysqli_query($this->con, "select * from user where uID = '$u_uID' or uID = '$this->i_uID'");
        if (!$query)
            return false;
        $result = array();
        while ($row = mysqli_fetch_array($query, MYSQL_NUM)) {
            if ($row['uID'] === $this->i_uID)
                $result['i'] = $row;
            else
                $result['u'] = $row;
        }
        return $result;
    }

    // 获取联系人信息（多人）
    public function getContacts () {
        $i_uID = $this->i_uID;
        $sql = "show tables like '%$i_uID%'";
        $query = mysqli_query($this->con, $sql);
        if ($query) {
            while ($row = mysqli_fetch_array($query, MYSQL_NUM)) {
                $result[] = $row;
            }
        }
        $response = array();
        $response['total'] = mysqli_num_rows($query);
        $response['data'] = array();
        if (isset($result)) {
            foreach ($result as $contact) {
                $cont = explode('___', $contact[0]);
                $response['data'][] = $cont[0] === $i_uID ? $cont[1] : $cont[0];
            }
        }
        return $response;
    }

    // 获取个人物品信息列表
    public function getPersonalData () {
        $uID = $this->i_uID;
        $sql = "select ID,name,price,detail,image,category from fleainfo where uID = '$uID' order by ID desc";
        $query = mysqli_query($this->con,$sql);
        if($query) {
            while ($row = mysqli_fetch_array($query)) {
                $result[] = $row;
            }
        }
        if (!isset($result))
            return $this->error_code->GETDATA_FAILED;
        $null="无";
        $datas = array();
        foreach($result as $xinxi)
        {
            $id = $xinxi['ID'];
            $name = $xinxi['name'];
            $price = $xinxi['price'];
            $uID = $xinxi['uID'];
            $detail = $xinxi['detail'];
            $category = $xinxi['category'];
            $image = $xinxi['image'];

            $data = array();
            $data['id'] = $id;
            $data['name'] = $name;
            $data['price'] = $price;
            $data['uID'] = $uID;
            $data['detail'] = $detail;
            $data['category'] = $category;
            $data['image'] = $image;

            $datas[] = $data;
        }
        return $datas;
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
// {
//     'ret' => -1,
//     'msg' => '错误原因'
// }