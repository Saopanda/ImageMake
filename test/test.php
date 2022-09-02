<?php
require '../vendor/autoload.php';

use saopanda\ImageMake;
use saopanda\ImageMakeConfig;

$config = new ImageMakeConfig();
$config->exportType(2);

ImageMake::new($config)->img('./make2.jpg')
    ->img('./make.png',100,200,500,1000)
    ->str('测试测试',1200,1120,['size'  =>  60,'color'=>"#ff0066"])
    ->str('测试1测试',1200,1220,['size'  =>  60,'color'=>"#ff4566"])
    ->str('测试2测试',1400,1420,['size'  =>  60])
    ->str('测试3测试',1400,1620,['size'  =>  60])
    ->get();



