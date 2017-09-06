<?php
class Cache {
    private $cache_path;
    //path for the cache //cache 的路径
    private $cache_expire;
    //seconds that the cache expires //cache 的时间

    //cache constructor, optional expiring time and cache path
    public function __construct($exp_time = 7200) {
        $path = dirname(dirname(dirname(dirname(dirname(dirname(__FILE__))))))."/cache/";
        $this -> cache_expire = $exp_time;
        $this -> cache_path = $path;
        echo $this -> cache_path;
    }

    //returns the filename for the cache
    private function fileName($key) {
        return $this -> cache_path . md5($key);
    }

    //creates new cache files with the given data, $key== name of the cache, data the info/values to store
    public function put($key, $data) {
        $values = serialize($data); // 产生一个可存储的值的表示
        $filename = $this -> fileName($key);
        $file = fopen($filename, 'w');
        if ($file) {//able to create the file
            fwrite($file, $values);
            fclose($file);
        } else
            return false;
    }

    //returns cache for the given key
    public function get($key) {
        $filename = $this -> fileName($key);
        if (!file_exists($filename) || !is_readable($filename)) {//can't read the cache
            return false;
        }
        if (time() < (filemtime($filename) + $this -> cache_expire)) {//cache for the key not expired
            $file = fopen($filename, "r");
            // read data file
            if ($file) {//able to open the file
                $data = fread($file, filesize($filename));
                fclose($file);
                return unserialize($data);// 从已存储的表示中创建 PHP 的值
                //return the values
            } else
                return false;
        } else
            return false;
        //was expired you need to create new
    }

}
$cache = new Cache();
?>