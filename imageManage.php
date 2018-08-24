<?php 

/**
 *  图片合成文字处理
 *	1. 图片
 *  $imgPath = '相对路径'
 *  2. 字体 (全局)				//	实例化时定义
 *	$fontPath = '操作系统级的绝对路径'
 *  3. 颜色 键名自定义			//	实例化时定义		
 *  $colors = [					
 *		'black' => [
 *			'0','0','0'				// rgb 颜色
 *		],
 *		'yellow' => [
 *			'253','200','24'
 *		],
 *		'white' => [
 *			'255','255','255'
 *		]
 *		....
 *	]
 *	4. 需要合成的文字 
 *	$re_str = [
 *		'0' => [
 *			'str' => '文字',
 *			'left' => '左边距',
 *			'top' => '上边距',
 *			'color' => 'black',		// 必须先设置,填 $colors数组的键名
 *			'is_re' => '0',			// 可省略 默认不换行 1为换行
 *			'num' => '多少字一换行',	// 换行时必填
 *			'lineheight' => '行高',	// 换行时必填
 *			'size' => '文字大小',	// 可省略 默认22
 *			'deg' => '旋转角度',		// 可省略 默认0
 *			'font' => '字体路径'		// 可省略 自定义字体 默认为全局$fontPath
 *		],
 *		....
 *	]
 *  By saopanda 1.0
 */
class imageManage
{
	private $fontPath;
	private $colors;

	public function __construct($fontPath,$colors)
	{
		$this->fontPath = $fontPath;
		$this->colors = $colors;
	}
	//	图片合成 调用时传入图片路径和文字
	public function makeImage($imgPath,$re_str)
	{
    	$bigImgPath = $imgPath;
    	$img = imagecreatefromstring(file_get_contents($bigImgPath));
    	$font = $this->fontPath;
    	foreach ($this->colors as $key => $value) {
    		$$key = imagecolorallocate($img, $value['0'], $value['1'], $value['2']);
    	}
    	foreach ($re_str as $key => $value) {
    		isset($value['deg'])? :$value['deg']=0;
    		isset($value['size'])? :$value['size']=22;
    		isset($value['font'])? :$value['font']=$this->fontPath;
    		isset($value['is_re'])? :$value['is_re']='0';
    		if($value['is_re'] == '1'){
    			$new_str = $this->mb_str_split($value['str'],$value['num']);
		    	$top = $value['top'];
		    	foreach ($new_str as $k => $v) {
		    		imagefttext($img, $value['size'], $value['deg'], $value['left'], $top, $black, $value['font'], $value['str']);
		    		$top+=$value['lineheight'];
		    	}
    		}else{
    			imagefttext($img, $value['size'], $value['deg'], $value['left'], $value['top'], $white, $value['font'], $value['str']);
    		}
    	}
    	list($bgWidth, $bgHight, $bgType) = getimagesize($bigImgPath);
		    switch ($bgType) {
		        case 1: //gif
		            header('Content-Type:image/gif');
		            imagegif($img);
		            break;
		        case 2: //jpg
		            header('Content-Type:image/jpg');
		            imagejpeg($img);
		            break;
		        case 3: //jpg
		            header('Content-Type:image/png');
		            imagepng($img);
		            break;
		        default:
		            break;
		    }
		return imagedestroy($img);
	}

	// 	中文字符串进行 str_split 切割成数组
	public function mb_str_split($str,$split_length=1,$charset="UTF-8"){
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
}