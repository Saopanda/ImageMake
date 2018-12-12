<?php 

namespace App\service;

/**
 *  By saopanda 2.0
 */

class imageManage
{
	private static $instance;
	private $bgImg;
	private $fonts;
	private $bgWidth;
	private $bgHight;
	private $bgType;


    private function __construct(){}

    public static function new()
    {
        if(is_null(self::$instance))
        {
            self::$instance = new self();
        }
        return self::$instance;
    }

    public function font($value)
    {
    	$this->fonts = $value;
    	return $this;
    }

    //	十六进制 转 RGB
    public function toRgb($hexColor) {
        $color = str_replace('#', '', $hexColor);
        if (strlen($color) > 3) {
            $rgb = array(
                'r' => hexdec(substr($color, 0, 2)),
                'g' => hexdec(substr($color, 2, 2)),
                'b' => hexdec(substr($color, 4, 2))
            );
        } else {
            $color = $hexColor;
            $r = substr($color, 0, 1) . substr($color, 0, 1);
            $g = substr($color, 1, 1) . substr($color, 1, 1);
            $b = substr($color, 2, 1) . substr($color, 2, 1);
            $rgb = array(
                'r' => hexdec($r),
                'g' => hexdec($g),
                'b' => hexdec($b)
            );
        }
        return $rgb;
    }

    // 	中文字符串进行 str_split 切割成数组
	function mb_str_split($str,$split_length=1,$charset="UTF-8"){
		if(func_num_args()==1){
			return preg_split('/(?<!^)(?!$)/u', $str);
		}
		if($split_length<1)return false;
			$len = mb_strlen($str, $charset);
			$arr = array();
			for($i=0;$i<$len;$i+=$split_length){
			$s = mb_substr($str, $i, $split_length, $charset);
			$arr[] = $s;
		}
		return $arr;
	}

    //	创建背景大图 GD 对象
    # $value 可以是路径 (长度不超 1000) 或 图像流字符串
    # $alpha = true 保存透明信息
    public function imgPath($value,$width=null,$height=null,$alpha=null)
    {
    	if(strlen($value) < 1000){
			$value=file_get_contents($value);
		}
    	$this->bgImg = imagecreatefromstring($value);
    	list($this->bgWidth, $this->bgHight, $this->bgType) = getimagesizefromstring($value);
    	if($alpha == 'true'){
    		imagesavealpha($this->bgImg,true);
    	}
    	return $this;
    }

    //	字体合成
    # $site = [x,y] 位置, 默认左上角
    # $size = 14 字体大小, 默认14
    # $color = ['#000','0'] 16进制颜色, 第二参数透明度 127全透明
    # $is_re = [num, lineheight] 自动换行 默认 false  num多少字一换 lineheight 行高
    # $font = null 字体 默认null使用全局字体 自定义字体传绝对路径
    # $deg 倾斜角度 默认 0
    public function str($value,$site=['0','0'],$size=14,$color=['#000000','0'],$is_re=null,$font=null,$deg=0)
    {
    	is_null($font)?$font=$this->fonts:$font=$font;
    	isset($color[1])? :$color[1]=0;
    	isset($color[0])? :$color[0]=$color;
    	$colors = $this->toRgb($color['0']);
	    $color = imagecolorallocatealpha($this->bgImg, $colors['r'], $colors['g'], $colors['b'],$color['1']);

	    if(isset($is_re[1])){
	    	$new_str = $this->mb_str_split($value,$is_re['0']);
	    	$top = $is_re['1'];
	    	foreach ($new_str as $k => $v) {
	    		imagefttext($this->bgImg, $size, $deg, $site['0'], $top, $color, $font, $v);
	    		$top+=$is_re['1'];
	    	}
	    }else{
	    	imagefttext($this->bgImg, $size, $deg, $site['0'], $site['1'], $color, $font, $value);
	    }
    	return $this;
    }

    //	图片合成
    # $value 可以是路径 (长度不超 1000) 或 图像流字符串
    # $site = [x,y] 原图上的位置, 默认左上角
    # $size = [w,h] 原图上宽高, 默认合成图大小
    # $full = null 合成图的宽高(默认true全图大小, 被拉伸到 $size 尺寸)
    # $full = [x,y[,w,h]] 
    # (无 w,h ) 合成图不会被拉伸 从 x,y 坐标 取 $size 大小
    # (有 w,h ) 合成图被自定义拉伸 从 x,y 坐标 取 [w,h] 大小
    # $is_radius = true 切透明圆角
    public function img($value,$site=['0','0'],$size=null,$full=null,$is_radius=null)
    {
		if(strlen($value) < 1000){
			$value=file_get_contents($value);
		}
		list($widths, $hights, $types) = getimagesizefromstring($value);
		if($is_radius == 'true'){
    		$imgs = $this->radius_img($value);
    	}else{
			$imgs = imagecreatefromstring($value);
    	}
    	isset($size['0'])? :$size['0']=$widths;
    	isset($size['1'])? :$size['1']=$hights;
    	if(!isset($full[2]) && isset($full[0])){
    		$full[2] = $size['0'];
    		$full[3] = $size['1'];
    	}elseif(!isset($full[0])){
			$full[0] = '0';
    		$full[1] = '0';
    		$full[2] = $widths;
    		$full[3] = $hights;
    	}
		imagecopyresampled($this->bgImg, $imgs, $site['0'], $site['1'], $full[0], $full[1], $size['0'], $size['1'],$full[2],$full[3]);
    	return $this;
    }

    // 输出
    # $type = 'default' 输出类型 默认输出浏览器
    # $type = 'src' 输出为文件
    public function create($type='default')
    {
    	if($type != 'default'){
    		$name = $type.'/'.uniqid('make_').'.';
    		switch ($this->bgType) {
		        case 1: //gif
		            imagegif($this->bgImg,$name.'gif');
					imagedestroy($this->bgImg);
					return $name.'gif';
		            break;
		        case 2: //jpg
		            imagejpeg($this->bgImg,$name.'jpg');
					imagedestroy($this->bgImg);
					return $name.'jpg';
		            break;
		        case 3: //png
		            imagepng($this->bgImg,$name.'png');
					imagedestroy($this->bgImg);
					return $name.'png';
		            break;
		    }
    	}else{
    		switch ($this->bgType) {
		        case 1: //gif
		            header('Content-Type:image/gif');
		            imagegif($this->bgImg);
					imagedestroy($this->bgImg);
		            break;
		        case 2: //jpg
		            header('Content-Type:image/jpg');
		            imagejpeg($this->bgImg);
					imagedestroy($this->bgImg);
		            break;
		        case 3: //png
		            header('Content-Type:image/png');
		            imagepng($this->bgImg);
					imagedestroy($this->bgImg);
		            break;
		    }
    	}
    }

	//	图片加透明圆角 
	public function radius_img($value)
	{
    	list($Width, $Hight, $Type) = getimagesizefromstring($value);
    	$srcImg = imagecreatefromstring($value);
    	$Width = min($Width,$Hight);
    	$img = imagecreatetruecolor($Width,$Width);
    	imagesavealpha($img,true);
		$bg = imagecolorallocatealpha($img, 255, 255, 255, 127);
		imagefill($img, 0, 0, $bg);
		$r = $Width/2;
		for ($i=0; $i < $Width; $i++) { 
			for ($y=0; $y < $Width; $y++) { 
				if (($i-$r)*($i-$r)+($y-$r)*($y-$r) <= $r*$r) {
					imagesetpixel($img,$i,$y,imagecolorat($srcImg, $i, $y));
				}
			}
		}
		return $img;
		imagedestroy($img);
	}

    
}