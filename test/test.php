<?php
require '../vendor/autoload.php';

use saopanda\ImageMake;
use saopanda\ImageMakeConfig;

$config = new ImageMakeConfig();
$config->exportType(2);

ImageMake::new($config)->img('./make.jpeg')
    ->img('./make2.jpeg',100,100)
    ->str('测试测试',200,120,['color'=>"#ff0066"])
    ->str('测试1测试',200,220,['size'=>40])
    ->str('测试2测试',400,420)
    ->str('测试3测试',440,620)
    ->str('欢迎使用 ImageMake',780,420,['size'=>60])
    ->get();



