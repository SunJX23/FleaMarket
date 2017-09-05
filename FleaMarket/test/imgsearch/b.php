<?php

class imghash{

    const FILE_NOT_FOUND = '-1';

    const FILE_EXTNAME_ILLEGAL = '-2';

    /**获取图像
     * @param $file-图像路径
     * @return mixed-返回图像资源句柄
     */
    public function getImage($file)
    {

        $exts = getimagesize($file);
        $extname = $exts[2];
        // 1 = GIF，2 = JPG，3 = PNG，4 = SWF，5 = PSD，6 = BMP，7 = TIFF(intel byte order)，
        //8 = TIFF(motorola byte order)，9 =JPC，10 = JP2，11 = JPX，
        //12 = JB2，13 = SWC，14 = IFF，15 = WBMP，16 = XBM
        switch ($extname) {
            case '1':
                $extname = 'gif';
                break;
            case '2':
                $extname = 'jpg';
                break;
            case '3':
                $extname = 'png';
                break;
        }

        if (!in_array($extname, ['jpg', 'jpeg', 'png', 'gif'])) exit(self::FILE_EXTNAME_ILLEGAL);

        $img = call_user_func('imagecreatefrom' . ($extname == 'jpg' ? 'jpeg' : $extname), $file);

        return $img;

    }

    /**
     * 获取图片指纹
     * @param $file-文件路径
     * @return string-返回64位phash指纹1和0组成
     */
    public function getHashValue($file)
    {
        $thumb = $this->resizeimg($file, 32, 32, true);
        //第二步：简化色彩
        $arr0 =  $this->pxarray_grayim($thumb);
        //第三步：计算DCT
        $dctv =  $this->DCT($arr0, 32);
        //第四步缩小DCT
        $iMatrix = array(array());
        for ($i = 0; $i < 8; $i++) {
            for ($j = 0; $j < 8; $j++) {
                $iMatrix[$i][$j] = (double)($dctv[$i * 8 + $j]);
            }
        }
        //第五步：计算平均值
        $avg =  $this->avg_Gray($arr0, 32, 32);
        //第六步：构造哈希值
        $str = '';
        for ($i = 0; $i < 8; $i++) {
            for ($j = 0; $j < 8; $j++) {
                if ($iMatrix[$i][$j] >= $avg) {
                    $str = $str . '1';
                } else {
                    $str = $str . '0';
                }
            }
        }
        return $str;

    }

    /**
     * 图片大小重置函数
     * $img：文件句柄
     * $w:目标图片宽度
     * $h:目标图片高度
     * $truecolor:是否真彩色
     * 返回类型：图片句柄
     */
    public function resizeimg($img,$w,$h,$truecolor)
    {
        list($width, $height) = getimagesize($img);
        // 创建一个图片。接收参数分别为宽高，返回生成的资源句柄
        //如果不是真彩，则返回256色
        if($truecolor==true)
        {
            $thumb = imagecreatetruecolor($w, $h);
        }
        else
        {
            $thumb = imagecreate($w, $h);
        }

        //获取源文件资源句柄。接收参数为图片路径，返回句柄
        $source =$this->getImage($img);
        // 将源文件剪切全部域并缩小放到目标图片上。前两个为资源句柄
        imagecopyresampled($thumb, $source, 0, 0, 0, 0, $w, $h, $width, $height);
        imagedestroy($source);
        return $thumb;
    }

    /**
     * 离散余弦变换
     * @param pix -原图像的数据矩阵
     * @param n -原图像(n*n)的高或宽
     * @return -变换后的矩阵数组
     */
    public function DCT($pix, $n)
    {
        $iMatrix = array(array());
        for ($i = 0; $i < $n; $i++) {
            for ($j = 0; $j < $n; $j++) {
                $iMatrix[$i][$j] = (double)($pix[$i * $n + $j]);
            }
        }
        $quotient = $this->coefficient($n);   //求系数矩阵
        $quotientT = $this->transposingMatrix($quotient, $n, $n);  //转置系数矩阵

        $temp = array(array());
        $temp = $this->matrixMultiply($quotient, $iMatrix, $n);
        $iMatrix = $this->matrixMultiply($temp, $quotientT, $n);

        $newpix = array();
        for ($i = 0; $i < $n; $i++) {
            for ($j = 0; $j < $n; $j++) {
                $newpix[$i * $n + $j] = (int)$iMatrix[$i][$j];
            }
        }
        return $newpix;
    }

    /**
     * 获取图片像素矩阵
     * @param $img -图片资源句柄
     * @param $w-图像宽度
     * @param $h-图像高度
     * @return array-返回像素矩阵
     */
    public function pxarray_grayim($img,$w=0,$h=0)
    {
        if($w==0||$h==0)
        {
            $w=imagesx($img);
            $h=imagesy($img);
        }
        $pix=array();
        //存储图像像素点矩阵
        for ($i=0;$i<$w;$i++)
        {
            for ($j=0;$j<$h;$j++)
            {
                $temp=imagecolorsforindex($img,imagecolorat($img,$i,$j));
                $pix[$i*$h+$j]=round($temp['red']*0.228+$temp['green']*0.587+$temp['blue']*0.114);
            }
        }
        return $pix;
    }

    /**
     * 图片灰度化方法
     * $img 图片资源句柄
     * $w 目标图片宽度
     * $h 目标图片高度
     * 返回值 灰度图片资源句柄
     */
    public function grayimg($img,$w,$h)
    {
        //创建目标大小画布，准备接受像素
        $grayimg=imagecreatetruecolor($w,$h);
        $pix=array();
        //存储图像像素点矩阵
        for ($i=0;$i<$w;$i++)
        {
            for ($j=0;$j<$h;$j++)
            {
                $pix[$i*$h+$j]=imagecolorsforindex($img,imagecolorat($img,$i,$j));
            }
        }
        for ($i=0; $i <$w ; $i++) {
            for ($j=0; $j <$h; $j++) {
                $r=$pix[$i*$h+$j]['red']*0.228;
                $g=$pix[$i*$h+$j]['green']*0.587;
                $b=$pix[$i*$h+$j]['blue']*0.114;
                $gray=round($r+$g+$b);
                $m=imagecolorallocate($grayimg, $gray, $gray, $gray);
                imagesetpixel($grayimg, $i, $j,$m);
            }
        }
        return $grayimg;

    }

    /**
     * 求灰度图像的均值
     * param pix 图像的像素矩阵
     * param w 图像的宽
     * param h 图像的高
     * return 灰度均值
     */
    public function avg_Gray($pix, $w, $h)
    {
        $sum = 0;
        for ($i = 0; $i < $w; $i++) {
            for ($j = 0; $j < $h; $j++) {
                $sum = $sum + $pix[$i * $h + $j];
            }

        }
        return (int)($sum / ($w * $h));
    }

    /**
     * 二阶矩阵转置
     * @param matrix -原矩阵
     * @param n -矩阵(n*n)的高或宽
     * @return -转置后的矩阵
     */
    public function transposingMatrix($matrix, $w,$h)
    {
        $nMatrix =array(array());
        for ($i = 0; $i < $w; $i++) {
            for ($j = 0; $j < $h; $j++) {
                $nMatrix[$i][$j] = $matrix[$j][$i];
            }
        }
        return $nMatrix;
    }

    /**
     * 求离散余弦变换的系数矩阵
     * @param n -n*n矩阵的大小
     * @return -系数矩阵
     */
    public function coefficient($n)
    {
        $coeff = array(array());
        $sqrt = 1.0 / sqrt($n);
        for ($i = 0; $i < $n; $i++) {
            $coeff[0][$i] = $sqrt;
        }
        for ($i = 1; $i < $n; $i++) {
            for ($j = 0; $j < $n; $j++) {
                $coeff[$i][$j] = sqrt(2.0 / $n) * cos($i * pi() * ($j + 0.5) / (double)$n);
            }
        }
        return $coeff;
    }

    /**
     * 矩阵相乘
     * @param A -矩阵A
     * @param B -矩阵B
     * @param n -矩阵的大小n*n
     * @return -结果矩阵
     */
    public function matrixMultiply($A, $B, $n)
    {
        $nMatrix = array(array());
        $t = 0.0;
        for ($i = 0; $i < $n; $i++) {
            for ($j = 0; $j < $n; $j++) {
                $t = 0;
                for ($k = 0; $k < $n; $k++) {
                    $t += $A[$i][$k] * $B[$k][$j];
                }
                $nMatrix[$i][$j] = $t;
            }
        }
        return $nMatrix;
    }

}


?>