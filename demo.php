<?php
include(__DIR__ . '/lib/class.paipan.php');
include(__DIR__ . '/lib/class.paipan.gx.php');
$p = new paipan();

$fm = $p->fatemaps(0, 1980, 1, 1, 0, 0, 0);

print_r($fm);

$tg = $fm['tg'];
$dz = $fm['dz'];

$ckey = array(
    0 => '年柱',
    1 => '月柱',
    2 => '日柱',
    3 => '时柱',
    4 => '大运',
    5 => '流年',
    6 => '流月',
    7 => '流日',
    8 => '流时'
);
$tgdz = array(
    0 => '天干',
    1 => '地支'
);

//$tg = [0,1,2,3,4,5,6,7,5];
//$dz = [0,1,4,8,11,6,2,5,6];
$gxs = GetGX($tg, $dz);
foreach ($gxs as $gx){
    $a = [];
    foreach ($gx[0] as $k => $v){
        $a[] = $ckey[$k] . $tgdz[$gx[1][0]];
    }
    
    echo implode('+', $a) . ':' . $gx[1][4]."<br />\n";
}