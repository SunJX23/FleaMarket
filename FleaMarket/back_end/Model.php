<?php

require_once('const.php');

class Model {
    protected $con;
    
    public function __construct() {
        $this->con = mysqli_connect(HOST,USERNAME,PASSWORD,DB);
        $this->con = set_charset('utf8');
    }

    public function uploadGoods() {
        $forms = array('name','goodsName','sale','detail');
        $formdata = array();
        foreach ($forms as $form) {
            $formdata[$form] = isset($_POST[$form]) ? self::filter($_POST[$form]) : null;
        }
        var_dump($formdata);
    }

    final public static function filter($formdata) {
        return addslashes(htmlspecialchars($formdata));
    }
}

// name 分类名
// goodsName 物品名称
// sale 价格
// detail 商品详情