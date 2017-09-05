<?php
	//header("Content-type: text/html; charset=utf-8");
	class edit_imagick{
		private $image = null;
		private $type = null;

		//构造函数
		public function __construct($path){
			$this->image = new Imagick($path);
			if($this->image)
				$this->type = strtolower($this->image->getImageFormat());
		}

		//析构函数
		public function __destruct(){
			$this->image->clear();
			$this->image->destroy();
		}

		//显示图片
		public function show(){
			header('Content-Type: image/'.$this->type);
			//imagejpeg($this->image, null, 100);
			echo $this->image;
		}

		//图片压缩
		public function reSize($path){
			//去掉图片的profile信息
			$this->image->stripImage();
			//设置图片类型和图片压缩类型
			$this->image->setImageFormat('jpeg');
			$this->image->setImageCompression(Imagick::COMPRESSION_JPEG);
			//设置图片压缩质量
			$quality = $this->image->getImageCompressionQuality()* 0.6;
			$quality = 0 ? 60 : $quality;
			echo $quality;
			$this->image->setImageCompressionQuality($quality);

			//$this->image->writeImage($path);
			$this->image->writeImage("../img/03.jpg");
		}

		//图片剪裁
		public function thump(){
			$width = 400;
			$height = 300;
			$img_width = $this->image->getImageWidth();
			$img_height = $this->image->getImageHeight();
			if($img_height > $height || $img_width > $width)
				$this->image->cropThumbnailImage($width, $height);
			else
				$this->image->resizeImage($width,$height, Imagick::FILTER_CATROM, 1, true);
		}

		public function write($path){
			$this->image->writeImage($path);
		}

	}
	//$path = "../img/03.jpg";
	$path = $_GET['path'];
	$image = new edit_imagick($path);
	//$image->reSize($path);
	$image->thump();
	$image->show();
	
	//$image->showImage();
?>