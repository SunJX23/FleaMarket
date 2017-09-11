<?php

require_once('const.php');
require_once('Error.php');

class Model {
    protected $con;
    protected $error_code;
    
    public function __construct() {
        $this->con = mysqli_connect(HOST,USERNAME,PASSWORD,DB);
        $this->con->set_charset('utf8');
        $this->error_code = Error::_instance();
    }

    public function uploadGoods() {
        /* $key => $val, $key:前端传来数据对应的键，$val:数据库表中对应的键 */
        $forms = array('name' => 'category','goodsName' => 'name','sale' => 'price','detail' => 'detail');
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
        $time = time();
        $data['ID'] = $time;
        for ($i = 0; $i < 3; $i++)
            $data['ID'] .= rand(0,9);
        $data['uID'] = isset($_SESSION['openid']) ? $_SESSION['openid'] : 'olKuAwHPm_1vwuv1dh6GD20bRlMM';
        $result = $this->insertData($data, 'fleainfo');
        if (false === $result)
            return $this->error_code->INSERT_FAILED;
        return $response;
    }

    public function getPersonalData () {
        $uID = isset($_SESSION['openid']) ? $_SESSION['openid'] : "olKuAwHPm_1vwuv1dh6GD20bRlMM";
        $sql = "select ID,name,price,detail,image,category from fleainfo where uID = '$uID' order by ID desc";
        $query = mysqli_query($this->con,$sql);
        if($query){
            while($row=mysqli_fetch_array($query)){
                $result[]=$row;
            }
        }
        if (isset($result)){
            $null="无";
            $datas = array();
            foreach($result as $xinxi)
            {
                $id = $xinxi['ID'];
                $name = $xinxi['name'];
                $price = $xinxi['price'];
                $detail = $xinxi['detail'];
                $category = $xinxi['category'];
                $image = $xinxi['image'];

                $data = array();
                $data['id'] = $id;
                $data['name'] = $name;
                $data['price'] = $price;
                $data['detail'] = $detail;
                $data['category'] = $category;
                $data['image'] = $image;

                $datas[] = $data;
            }
            return $datas;
        } else {
            return $this->error_code->GETDATA_FAILED;
        }
    }

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

    public function closeCon () {
        mysqli_close($this->con);
    }

    protected function checkParam ($value, $rule) {
        if (empty($value))
            return $this->error_code->PARAM_DROP;
        if (!preg_match($rule['check_type'], $value))
            return $rule['error_code'];
        return array('ret' => 1);
    }

    final protected static function filter($formdata) {
        return addslashes(htmlspecialchars($formdata));
    }
}

// name 分类名
// goodsName 物品名称
// sale 价格
// detail 商品详情

5
1 2 3 3 5
3
1 2 1
2 4 5
3 5 3
