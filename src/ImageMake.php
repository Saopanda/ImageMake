<?php

namespace saopanda;

class ImageMake
{
    /**
     * @var ImageMake
     */
    private static $instance;
    /**
     * @var ImageMakeConfig
     */
    private $config;
    /**
     * @var false|\GdImage|resource
     */
    private $bgImg;

    private function __construct(){}

    /**
     * @param ImageMakeConfig $config
     * @return ImageMake
     */
    public static function new(ImageMakeConfig $config = null): ImageMake
    {
        if(is_null(self::$instance))
            self::$instance = new self();
        if (is_null($config))
            $config = new ImageMakeConfig();
        self::$instance->config = $config->config;
        return self::$instance;
    }

    /**
     * 处理背景图
     * @param string $value
     * @return bool 是否需要继续处理
     */
    private function makeBGImg(string $value): bool
    {
        if ($this->bgImg) return true;
        list($width,$height) = [$this->config['width'],$this->config['height']];

        if ($width != 0 && $height != 0){
            //  创建指定大小的空底图
            $this->bgImg = imagecreatetruecolor($width,$height);
            imagesavealpha($this->bgImg,$this->config['alpha']);
            return true;
        }else{
            //  根据图片创建底图
            $this->bgImg = imagecreatefromstring($value);
            imagesavealpha($this->bgImg,$this->config['alpha']);
            $this->config['width'] = $width;
            $this->config['height'] = $height;
            return false;
        }
    }

    /**
     * 叠加图片
     * @param string $value 图片路径｜二进制字符串
     * @param int $x 相对于底图的 X 坐标
     * @param int $y 相对于底图的 Y 坐标
     * @param int $new_width 图片的新宽度
     * @param int $new_height 图片的新高度
     * @return $this
     */
    public function img(string $value, int $x = 0, int $y = 0, int $new_width = 0, int $new_height = 0): ImageMake
    {
        if(is_file($value))
            $value=file_get_contents($value);
        //  处理底图
        $result = $this->makeBGImg($value);
        if (!$result)
            return $this;

        list($width, $height) = getimagesizefromstring($value);
        $img = imagecreatefromstring($value);
        imagesavealpha($img,$this->config['alpha']);

        $new_height != 0 ? : $new_height = $height;
        $new_width != 0 ? : $new_width = $width;

        imagecopyresampled($this->bgImg, $img, $x, $y, 0, 0, $new_width, $new_height,$width,$height);
        imagedestroy($img);
        return $this;
    }

    /**
     * 叠加文字
     * @param string $value 要叠加的文字
     * @param int $x 相对于底图的 X 坐标
     * @param int $y 相对于底图的 Y 坐标 注意：基点在字体左下角，非左上角
     * @param array $config 配置数组：
     * [
     *      'color'    =>  string 字体颜色,
     *      'size'     =>  int 字体大小,
     *      'wrap'     =>  false | array [
     *          20, //  int 多少字换行
     *          10  //  int 行高
     *      ],
     *      'font'     =>  string 字体,
     *      'deg'      =>  int 旋转角度，设置排列方向，效果：左到右、上到下
     * ]
     * @return $this
     * @throws \Exception
     */
    public function str(string $value, int $x = 0, int $y = 10, array $config = []): ImageMake
    {
        $config = array_merge([
            'color'     =>  '#000',
            'size'      =>  $this->config['font_size'],
            'wrap'      =>  false,
            'font'      =>  $this->config['font'],
            'deg'       =>  0,
        ],$config);

        if (!is_file($config['font']))
            throw new \Exception('错误的字体路径');

        $color_config = $this->toRgb($config['color']);
	    $color = imagecolorallocatealpha($this->bgImg, $color_config['r'], $color_config['g'], $color_config['b'], $color_config['a']);

        if ($color === false)
            throw new \Exception('颜色添加失败，检查底图是否过大。'.json_encode($color_config));

	    if ($config['wrap']){
	    	$new_value = $this->mb_str_split($value,$config['wrap'][0]);
	    	foreach ($new_value as $v) {
	    		imagefttext($this->bgImg, $config['size'], $config['deg'], $x, $y, $color, $config['font'], $v);
                $y += $config['wrap'][1];
	    	}
	    }else{
	    	imagefttext($this->bgImg, $config['size'], $config['deg'], $x, $y, $color, $config['font'], $value);
	    }
    	return $this;
    }

    /**
     * 获取图片文件或直接输出
     * @param string|null $filename 指定生成图片文件名，null直接输出图像
     * @return string|null
     */
    public function get(string $filename = null)
    {
        $type = $this->config['img_type'];
        switch ($type) {
            case 1:
                $filename ? $filename .= 'gif' : header('Content-Type:image/gif');
                imagegif($this->bgImg, $filename);
                break;
            case 2:
                $filename ? $filename .= 'jpg' : header('Content-Type:image/jpg');
                imagejpeg($this->bgImg, $filename);
                break;
            case 3:
                $filename ? $filename .= 'png' : header('Content-Type:image/png');
                imagepng($this->bgImg, $filename);
                break;
        }
        imagedestroy($this->bgImg);
        return $filename;
    }

    /**
     * 支持带透明度的十六进制
     * @throws \Exception
     */
    private function toRgb($hexColor) {
        $color = str_replace('#', '', $hexColor);
        $length = strlen($color);
        $a = 0;
        if ( $length == 3) {
            $r = substr($color, 0, 1) . substr($color, 0, 1);
            $g = substr($color, 1, 1) . substr($color, 1, 1);
            $b = substr($color, 2, 1) . substr($color, 2, 1);
        } elseif( $length == 6 || $length == 8) {
            $r = substr($color, 0, 2);
            $g = substr($color, 2, 2);
            $b = substr($color, 4, 2);
            if ($length == 8){
                $a = (int)(hexdec(substr($color, 6, 2) )/2);
            }
        }else{
            throw new \Exception('错误的颜色值');
        }
        return array(
            'r' => hexdec($r),
            'g' => hexdec($g),
            'b' => hexdec($b),
            'a' => $a
        );
    }
    // 	中文字符串进行 str_split 切割成数组
    private function mb_str_split($str,$split_length=1){
        if(func_num_args()==1){
            return preg_split('/(?<!^)(?!$)/u', $str);
        }
        if($split_length<1)return false;
        $len = mb_strlen($str, 'UTF-8');
        $arr = array();
        for($i=0;$i<$len;$i+=$split_length){
            $s = mb_substr($str, $i, $split_length, 'UTF-8');
            $arr[] = $s;
        }
        return $arr;
    }
    //	图片加透明圆角 弃用
    private function radius_img($value)
    {
        list($Width, $Hight) = getimagesizefromstring($value);
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
    }


}