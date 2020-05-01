<?php
include(__DIR__ . '/lib/class.paipan.php');
$p = new paipan();

$a = $p->fatemaps(0, 1980, 1, 1, 12, 0, 0);
print_r($a);