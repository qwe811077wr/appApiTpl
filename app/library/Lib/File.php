<?php
namespace Lib;
use \Helper\Helper;
use \Exception\InputException;
use \Params\EC;

/**
 * 文件上传类库
 */
class File
{
	//类实例
	public static $instance = NULL;
	//文件存储对象
	public $storage = NULL;

	//配置相关
	public $base_path = '';
	public $sub_path = '';
	public $mimetype = '';
	public $max_size = '';
	public $domain = '';

	private $file_name = '';

	public function __construct()
	{
		$conf = Helper::getConf('upload');
		$this->mimetype = $conf['mimetype'];
		$this->max_size = $conf['max_size'];
		$this->base_path = $conf['base_path'];
		$this->sub_path = vsprintf($conf['sub_path'], explode('-', date('Y-m-d')));
		$this->domain = $conf['domain']; 
		$this->storage = new \Upload\Storage\FileSystem($this->base_path.$this->sub_path);
	}

	static public function getInstance()
	{
		if (empty(self::$instance)) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	public function upload($key, $nums = 9, $zoom = false, $isRotate = false)
	{
		//验证个数
		$counts = count($_FILES[$key]['name']);

		if ($counts > $nums) {
			throw new InputException(EC::UPLOAD_OUT_NUMS_LIMIT, printf(LANG::$_errors[EC::UPLOAD_OUT_NUMS_LIMIT], $nums));
		}
		$FileArr = array();
		$file = new \Upload\File($key, $this->storage);
		$new_file_name = uniqid();
		for ($i=0; $i < $counts; $i++) {
			$file[$i]->setName($new_file_name . '_' . $i);
			$FileArr[] = $this->domain . $this->sub_path . $file[$i]->getNameWithExtension();
		}

		//验证格式和大小
		$file->addValidations(array(
		    new \Upload\Validation\Mimetype(explode(",", $this->mimetype)),
		    new \Upload\Validation\Size($this->max_size)
		));

		try {
		    $file->upload();
		    //是否需要旋转
		    if ($isRotate) {
		    	for ($i=0; $i < $counts; $i++) { 
		    		$file_name = $this->base_path . $file[$i]->getNameWithExtension();
		    		$this->rotate($file_name);
		    	}
		    }
		    //是否需要压缩
		    if ($zoom) {
 				for ($i=0; $i < $counts; $i++) { 
		    		$file_name = $this->base_path . $file[$i]->getNameWithExtension();
		    		$this->zoom($file_name);
		    	}
		    }
		    return [EC::SUCCESS, $FileArr];
		} catch (\Exception $e) {
		    // Fail!
		    return [EC::UPLOAD_FAILED, $errors = $file->getErrors()];
		}
	}

	public function rotate($srcFile, $imgQuality = 90)
	{
		$exif = @exif_read_data($srcFile, 'IFD0');//获取exif信息，需要安装exif.so
		if (isset($exif['Orientation'])) {
			$data=@getimagesize($srcFile);
			//根据不同的图片类型生成不同的图片资源
			switch ($data[2]) {
				case 1:
					$im=@imagecreatefromgif($srcFile);
					break;
				case 2:
					$im=@imagecreatefromjpeg($srcFile);
					break;
				case 3:
					$im=@imagecreatefrompng($srcFile);
					break;
			}

			//根据不同的角度旋转图片
			switch ($exif['Orientation']) {
				case 8:
					$im = imagerotate($im, 90, 0);
					break;
				case 3:
					$im = imagerotate($im, 180, 0);
					break;
				case 6:
					$im = imagerotate($im, -90, 0);
					break;
			}

			//输出图片到文件
			switch ($data[2]) {
				case 'gif':
					@imagegif($im,$srcFile, $imgQuality);
					break;
				
				case 'jpeg':
					@imagejpeg($im,$srcFile, $imgQuality);
					break;

				case 'png':
					@imagepng($im,$srcFile, $imgQuality);
					break;
				default:
					@imagejpeg($im,$srcFile, $imgQuality);
					break;
			}
		}
	}

	public function zoom($srcFile, $dtFile, $max_width=640, $max_height=700, $imgQuality=90)
	{
		$data = getimagesize($srcFile);
		$height = 0;
		//高大于宽，且高大于最大高度。则高取最大高度，宽按比例缩小
		if($data[0]<=$data[1] and $data[1]>=$max_height) {
	       $height=$max_height;
	       $width=intval($height*$data[0]/$data[1]);
	    }
	    //宽大于高，且宽大于最大宽度。则宽取最大宽度，高按比例缩小
	     if($data[0]>=$data[1] and $data[0]>=$max_width) {
	       $width=$max_width;
	       $height=intval($width*$data[1]/$data[0]);
	    }
	    //图片不大于限制
	    if($data[0]<$max_width and $data[1]<$max_height) {
	       $width=$data[0];
	       $height=$data[1];
	    }
	    switch($data[2]){
	           case 1:
	                $im=@imagecreatefromgif($srcFile);
	                  break;
	           case 2:
	                $im=@imagecreatefromjpeg($srcFile);
	                break;
	           case 3:
	                $im=@imagecreatefrompng($srcFile);
	                  break;
	    }
	    $srcW=@imagesx($im);
	    $srcH=@imagesy($im);
	    $ni=@imagecreatetruecolor($width,$height);
	    $color = imagecolorAllocate($ni,255,255,255);   //分配一个白色
	    imagefill($ni,0,0,$color);  
	    @imagecopyresampled($ni,$im,0,0,0,0,$width,$height,$srcW,$srcH);
	      switch($data[2]){
	          case 'gif':@imagepng($ni,$dstFile, $imgQuality); break;
	            case 'jpeg':@imagejpeg($ni,$dstFile, $imgQuality); break;
	          case 'png':@imagepng($ni,$dstFile, $imgQuality); break;
	          default:@imagejpeg($ni,$dstFile, $imgQuality); break;
	      }
        return $dstFile;
	}
}