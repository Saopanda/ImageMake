<?php

namespace saopanda;

class ImageMakeConfig
{

    /**
     * 配置数组
     * @var array
     */
    public $config = [
        'width'     =>  0,
        'height'    =>  0,
        'alpha'     =>  true,
        'font'      =>  null,
        'font_size' =>  16,
        'img_type'  =>  3,
    ];

    /**
     * 输出图片大小，默认为底图宽高
     * @param int $width
     * @param int $height
     * @return ImageMakeConfig
     */
    public function exportSize(int $width, int $height): ImageMakeConfig
    {
        $this->config['width'] = $width;
        $this->config['height'] = $height;
        return $this;
    }

    /**
     * 设置输出图片格式
     * @param int $type 1 = GIF，2 = JPG，3 = PNG
     * @return ImageMakeConfig
     * @throws \Exception
     */
    public function exportType(int $type): ImageMakeConfig
    {
        if (preg_match('/[1-3]/',$type) == 0)
            throw new \Exception('错误的图片格式');

        $this->config['img_type'] = $type;
        return $this;
    }

    /**
     * 是否启用透明度
     * @param bool $boolean
     * @return ImageMakeConfig
     */
    public function alpha(bool $boolean ): ImageMakeConfig
    {
        $this->config['alpha'] = $boolean;
        return $this;
    }

    /**
     * 设置默认字体
     * @param string $font
     * @return ImageMakeConfig
     * @throws \Exception
     */
    public function font(string $font ): ImageMakeConfig
    {
        if (!is_file($font))
            throw new \Exception('错误的字体路径');

        $this->config['font'] = $font;
        return $this;
    }

    /**
     * 设置默认字体大小
     * @param int $font_size
     * @return $this
     */
    public function fontSize(int $font_size): ImageMakeConfig
    {
        $this->config['font_size'] = $font_size;
        return $this;
    }

    function __construct(){
        $this->config['font'] = __DIR__.'/SourceHanSansSC-Medium.ttf';
    }
}