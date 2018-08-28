<?php 

/**
 *  图片 + 图片 + 文字 综合合成处理
 *	中文文字支持自动换行
 *	图片支持切成四角透明圆形
 *	radius_img()方图切原图方法(一般用于头像)可以单独调用,需要传第二参数,为任意值
 *	可以只合成 图+图 或 图+字
 *	要合成文字的话,请传$fontPath,$colors,$re_str三者缺一不可
 *	要合成图片的话,请传$re_img
 *	
 *	最终输出为图片,可用于活动的动态海报
 *  By saopanda 1.0
 */
class imageManage
{
	/**
	 *	1. 字体 (全局)
	 *	$fontPath = '操作系统级的绝对路径'
	 *
	 *  2. 颜色 键名自定义 (rgb颜色)
	 *  $colors = [
	 *		'black' => [
	 *			'0','0','0'				
	 *		],
	 *		'yellow' => [
	 *			'253','200','24'
	 *		],
	 *		'white' => [
	 *			'255','255','255'
	 *		]
	 *		....
	 *	]
	 *	3. 图片
	 *  $imgPath = '相对路径'
	 *  
	 *	4. 需要合成的文字 
	 *	$re_str = [
	 *		'0' => [
	 *			'str' => '文字',
	 *			'color' => 'black',		// 必须先设置,填 $colors数组的键名
	 *			'is_re' => '0',			// 默认不换行 1为换行
	 *			'left' => '左边距',		// 默认0
	 *			'top' => '上边距',		// 默认0
	 *			'num' => '多少字一换行',	// 换行时必填
	 *			'lineheight' => '行高',	// 换行时必填
	 *			'size' => '文字大小',	// 默认22
	 *			'deg' => '旋转角度',		// 默认 0
	 *			'font' => '字体路径'		// 默认为全局$fontPath 自定义字体 
	 *		],
	 *		....
	 *	]
	 *  5. 需要合成的图片
	 *	$re_img = [
	 *		'0'=>[
	 *			'src' => '',			// 路径
	 *			'width' => '',			// 默认原图大小,宽度
	 *			'hight' => '',			// 默认原图大小,高度
	 *			'img_left' => '',		// 默认0,左边距
	 *			'img_top' => '',		// 默认0,上边距
	 *			'is_radius' => '1',		// 默认0,不需要圆图切割 
	 *		]
	 *	]
	 *	
	 */
	public function makeImage($imgPath,$fontPath=null,$colors=null,$re_str=null,$re_img=null)
	{
		
    	$bigImgPath = $imgPath;
    	$img = imagecreatefromstring(file_get_contents($bigImgPath));
    	if($fontPath != null && $colors != null && $re_str != null){
    		$font = $fontPath;
	    	foreach ($colors as $key => $value) {
	    		$$key = imagecolorallocate($img, $value['0'], $value['1'], $value['2']);
	    	}
	    	foreach ($re_str as $key => $value) {
	    		isset($value['deg'])? :$value['deg']=0;
	    		isset($value['size'])? :$value['size']=22;
	    		isset($value['font'])? :$value['font']=$fontPath;
	    		isset($value['is_re'])? :$value['is_re']='0';
			    $tmpcolor = $value['color'];
	    		if($value['is_re'] == '1'){
	    			$new_str = $this->mb_str_split($value['str'],$value['num']);
			    	$top = $value['top'];
			    	foreach ($new_str as $k => $v) {
			    		imagefttext($img, $value['size'], $value['deg'], $value['left'], $top, $$tmpcolor, $value['font'], $value['str']);
			    		$top+=$value['lineheight'];
			    	}
	    		}else{
	    			imagefttext($img, $value['size'], $value['deg'], $value['left'], $value['top'], $$tmpcolor, $value['font'], $value['str']);
	    		}
	    	}
    	}
    	if ($re_img != null) {
    		foreach ($re_img as $key => $value) {
		    	isset($value['is_radius'])? :$value['is_radius']=0;
		    	isset($value['img_left'])? :$value['img_left']=0;
		    	isset($value['img_top'])? :$value['img_top']=0;
	    		list($widths, $hights, $types) = getimagesize($value['src']);
		    	if($value['is_radius'] == '1'){
		    		$imgs = $this->radius_img($value['src']);
		    	}else{
	    			$imgs = imagecreatefromstring(file_get_contents($value['src']));
		    	}
	    		imagecopyresized($img, $imgs, $value['img_left'], $value['img_top'], 0, 0,$value['width'],$value['hight'],$widths,$hights);	//拷贝同时会拉伸
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
	        case 3: //png
	            header('Content-Type:image/png');
	            imagepng($img);
	            break;
	    }
		imagedestroy($img);
	}

	//	图片切成圆形透明
	public function radius_img($imgPath,$type=null)
	{
		$srcImg = null;
    	list($Width, $Hight, $Type) = getimagesize($imgPath);
    	switch ($Type) {
			case '1':
				$srcImg = imagecreatefromgif($imgPath);
				break;
			case '2':
				$srcImg = imagecreatefromjpeg($imgPath);
				break;
			case '3':
				$srcImg = imagecreatefrompng($imgPath);
				break;
		}
    	$Width = min($Width,$Hight);
    	$img = imagecreatetruecolor($Width,$Width);		//新建一张图
    	imagesavealpha($img,true);		//	保存png完整通道（透明）
		$bg = imagecolorallocatealpha($img, 255, 255, 255, 127);//拾取一个完全透明的颜色,最后一个参数127为全透明
		imagefill($img, 0, 0, $bg);		//	填充了透明色
		$r = $Width/2;
		for ($i=0; $i < $Width; $i++) { 
			for ($y=0; $y < $Width; $y++) { 
				if (($i-$r)*($i-$r)+($y-$r)*($y-$r) <= $r*$r) {
					$rgbColor = imagecolorat($srcImg, $i, $y);
					imagesetpixel($img,$i,$y,$rgbColor);
				}
			}
		}
		if($type == null){
			return $img;
			imagedestroy($img);
		}else{
			header('Content-Type:image/png');
	        imagepng($img);
			imagedestroy($img);
			die();
		}
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