# imageManage
## 图片 + 图片 + 文字 综合合成处理

**********************
# 2.0版本
* 支持连贯操作了! 解决上版本图片文字先后顺序会覆盖的问题, 用连贯操作自己排序即可
* 图片合并使用了 `imagecopyresampled` 平滑插帧, 图片高质量同时减少大小, 提升性能
* 可直接输出到浏览器或者到文件, 详见 `create` 方法
* 图片合并提供了拉伸自定义
* 字体颜色可直接使用16进制颜色, 并且能方便的使用透明色

> @`imgPath($value,$width=null,$height=null,$alpha=null)`

	$value 可以是路径 (长度不超 1000) 或 图像流字符串
	$alpha = true 保存透明信息
> @`font($value)`

	$value 字体路径 系统级绝对路径
> @`str($value,$site=['0','0'],$size=14,$color=['#000000','0'],$is_re=null,$font=null,$deg=0)`

	 $site = [x,y] 位置, 默认左上角
     $size = 14 字体大小, 默认14
     $color = ['#000','0'] 16进制颜色, 第二参数透明度 127全透明
     $is_re = [num, lineheight] 自动换行 默认 false  num多少字一换 lineheight 行高
     $font = null 字体 默认null使用全局字体 自定义字体传绝对路径
     $deg 倾斜角度 默认 0
> @`img($value,$site=['0','0'],$size=null,$full=null,$is_radius=null)`

	 $value 可以是路径 (长度不超 1000) 或 图像流字符串
     $site = [x,y] 原图上的位置, 默认左上角
     $size = [w,h] 原图上宽高, 默认合成图大小
     $full = null 合成图的宽高(默认true全图大小, 被拉伸到 $size 尺寸)
     $full = [x,y[,w,h]] 
     (无 w,h ) 合成图不会被拉伸 从 x,y 坐标 取 $size 大小
     (有 w,h ) 合成图被自定义拉伸 从 x,y 坐标 取 [w,h] 大小
     $is_radius = true 切透明圆角
> @`create($type='default')`

	 $type = 'default' 输出类型 默认输出浏览器
     $type = 'src' 输出为文件
	

示例:
```
return imageManage::new()
	->imgPath('./'.$track->banner,null,null,true)
	->font($fontPath)
	->img('./images/wbg.png')
	->img($imgcode,[520,245],[120,120])
	->str($track->title,[40,360],22,'#000')
	->str('(共 '.$track->points.' 站)',[$lefts,360],15,'#000')
	->create();
```


<hr/>

### 1.0
 *	中文文字支持自动换行
 *	图片支持切成四角透明圆形
 *	radius_img()方图切圆图方法(一般用于头像)可以单独调用,需要传第二参数,为任意值
 *	可以只合成 图+图 或 图+字，只合字 `makeImage()`第二项传null，只合图，后三项可以不用传
 *	要合成文字的话,请传$fontPath,$colors,$re_str三者缺一不可
 *	要合成图片的话,请传$re_img
 *	最终输出为图片,可用于活动的动态海报

 > 1. 字体 (全局)
 * $fontPath = '操作系统级的绝对路径'
 > 2. 颜色 键名自定义 (rgb颜色)
 *  ```
    $colors = [
	 'black' => [
	 	'0','0','0'				
	 ],
	 'yellow' => [
	 	'253','200','24'
	 ],
	 'white' => [
	 	'255','255','255'
	 ]
	 ....
    ]
    ```
 > 3. 图片
 * $imgPath = '相对路径'
 > 4. 需要合成的文字
 *  ```
    $re_str = [
	  '0' => [
	 	'str' => '文字',
	 	'color' => 'black',		// 必须先设置,填 $colors数组的键名
	 	'is_re' => '0',			// 默认不换行 1为换行
	 	'left' => '左边距',		// 默认0
	 	'top' => '上边距',			// 默认0
	 	'num' => '多少字一换行',		// 换行时必填
	 	'lineheight' => '行高',		// 换行时必填
	  	'size' => '文字大小',		// 默认22
	 	'deg' => '旋转角度',		// 默认 0
	 	'font' => '字体路径'		// 默认为全局$fontPath 自定义字体 
	 ],
	 ....
    ]
    ```
 > 5. 需要合成的图片
 *  ```
    $re_img = [
	  '0'=>[
	 	'src' => '',			// 路径
	 	'width' => '',			// 默认原图大小,宽度
	 	'hight' => '',			// 默认原图大小,高度
	 	'img_left' => '',		// 默认0,左边距
	 	'img_top' => '',		// 默认0,上边距
	 	'is_radius' => '1',		// 默认0,不需要圆图切割 
	 ],
      ....
    ]
    ```
