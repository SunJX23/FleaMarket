<?php

class Error
{
    static public $instance;

    private $error = array(
        'PARAM_DROP'    => array(101, '参数缺失'),
        'USER_LOST'     => array(102, '无用户信息'),
        'INSERT_FAILED' => array(201, '数据插入失败'),
        'UPDATE_FAILED' => array(202, '数据更新失败'),
        'DELETE_FAILED' => array(203, '数据删除失败'),
        'UPLOADIMG_FAILED' => array(204, '图片上传失败'),
        'GETDATA_FAILED'=> array(205, '获取数据失败'),
    );

    private $flea = array(
        /* 分类必须为汉字、英文字母、数字，长度不超过10 */
        'CHECK_CATEGORY' => '/^[a-zA-Z0-9\x{4e00}-\x{9fa5}]{0,10}$/u',
        /* 分类必须为汉字、英文字母、数字，长度不超过30 */
        'CHECK_NAME' => '/^[a-zA-Z0-9\x{4e00}-\x{9fa5}]{0,30}$/u',
        /* 分类必须为八位数字，小数点后保留两位 */
        'CHECK_PRICE' => '/^[0-9]{0,6}([.][0-9]{1,2})?$/',

        'CATEGORY_INVALID' => array(301, '关键字格式错误'),
        'NAME_INVALID' => array(302, '物品名格式错误'),
        'PRICE_INVALID' => array(303, '价钱格式错误'),
    );

    static public function _instance() {
        if (is_null(self::$instance))
            self::$instance = new static();
        return self::$instance;
    }

    public function __construct() {
    }

    public function __clone() {
    }

    public function __get($param) {
        $const = explode('__', $param);
        if ($const[0] === 'TEST') {
            $response = array(
                'ret'  => -1,
                'code' => $this->flea[$const[1].'_INVALID'][0],
                'msg'  => $this->flea[$const[1].'_INVALID'][1]
            );
            return array(
                'error_code' => $response,
                'check_type' => $this->flea['CHECK_'.$const[1]]
            );
        } else {
            return array(
                'ret'  => -1,
                'code' => $this->error[$param][0],
                'msg'  => $this->error[$param][1]
            );
        }
    }

}