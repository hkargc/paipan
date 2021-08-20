<?php
/**
 * 此日历转换及排盘类完全源于以下项目,本人仅作为代码搬运工
 * 感谢项目作者的无私分享,其提供了详尽的历法转换原理,JS源码及部分PHP源码,项目地址:   @link http://www.bieyu.com/
 *
 * @author hkargv@139.com
 * @author tangfou@gmail.com
 */
class Calendar {

	/**
	 * 四柱是否区分 早晚子 时,true则23:00-24:00算成上一天
	 */
	public $zwz = true;

	/**
	 * 均值朔望月长 synodic month (new Moon to new Moon)
	 */
	private $synmonth = 29.530588853;

	/**
	 * 缓存数组,暂存一些中间结果
	 */
	private $MM = [];

	/**
	 * 是否输出错误信息
	 */
	public $debug = true;

	/**
	 * 星期 week day
	 */
	public $wkd = ['日', '一', '二', '三', '四', '五', '六'];

	/**
	 * 六十甲子
	 */
	public $gz = [
		'甲子', '乙丑', '丙寅', '丁卯', '戊辰', '己巳', '庚午', '辛未', '壬申', '癸酉',
		'甲戌', '乙亥', '丙子', '丁丑', '戊寅', '己卯', '庚辰', '辛巳', '壬午', '癸未',
		'甲申', '乙酉', '丙戌', '丁亥', '戊子', '己丑', '庚寅', '辛卯', '壬辰', '癸巳',
		'甲午', '乙未', '丙申', '丁酉', '戊戌', '己亥', '庚子', '辛丑', '壬寅', '癸卯',
		'甲辰', '乙巳', '丙午', '丁未', '戊申', '己酉', '庚戌', '辛亥', '壬子', '癸丑',
		'甲寅', '乙卯', '丙辰', '丁巳', '戊午', '己未', '庚申', '辛酉', '壬戌', '癸亥',
	];

	/**
	 * 十天干 char of TianGan
	 */
	public $ctg = ['甲', '乙', '丙', '丁', '戊', '己', '庚', '辛', '壬', '癸'];

	/**
	 * 十二地支 char of DiZhi
	 */
	public $cdz = ['子', '丑', '寅', '卯', '辰', '巳', '午', '未', '申', '酉', '戌', '亥'];

	/**
	 * 十二生肖 char of symbolic animals ShengXiao
	 */
	public $csx = ['鼠', '牛', '虎', '兔', '龙', '蛇', '马', '羊', '猴', '鸡', '狗', '猪'];

	/**
	 * 廿四节气(从春分开始) JieQi
	 */
	public $jq = ['春分', '清明', '谷雨', '立夏', '小满', '芒种', '夏至', '小暑', '大暑', '立秋', '处暑', '白露', '秋分', '寒露', '霜降', '立冬', '小雪', '大雪', '冬至', '小寒', '大寒', '立春', '雨水', '惊蛰'];

	/**
	 * 大写月份
	 */
	public $dxy = ['正月', '二月', '三月', '四月', '五月', '六月', '七月', '八月', '九月', '十月', '冬月', '腊月'];

	/**
	 * 大写日期
	 */
	public $dxd = ['初一', '初二', '初三', '初四', '初五', '初六', '初七', '初八', '初九', '初十', '十一', '十二', '十三', '十四', '十五', '十六', '十七', '十八', '十九', '廿十', '廿一', '廿二', '廿三', '廿四', '廿五', '廿六', '廿七', '廿八', '廿九', '三十'];

	/**
	 * 大写数字
	 */
	public $dxs = ['零', '一', '二', '三', '四', '五', '六', '七', '八', '九'];

	/**
	 * 五行 char of WuXing
	 */
	public $cwx = ['金', '水', '木', '火', '土'];

	/**
	 * 性别 XingBie
	 */
	public $xb = ['男', '女'];

	/**
	 * 命造 mingzao
	 */
	public $mz = ['乾', '坤'];

	/**
	 * 阴阳 char of YingYang
	 */
	public $cyy = ['阳', '阴'];

	/**
	 * 命局类型
	 */
	public $lx = ['命旺', '印重', '煞重', '财旺', '伤官'];

	/**
	 * 天干的五行属性,01234分别代表:金水木火土
	 */
	public $wxtg = [2, 2, 3, 3, 4, 4, 0, 0, 1, 1];

	/**
	 * 地支的五行属性,01234分别代表:金水木火土
	 */
	public $wxdz = [1, 4, 2, 2, 4, 3, 3, 4, 0, 0, 4, 1];

	/**
	 * 十神全称
	 */
	public $ssq = ['正印', '偏印', '比肩', '劫财', '伤官', '食神', '正财', '偏财', '正官', '偏官'];

	/**
	 * 十神缩写
	 */
	public $sss = ['印', '卩', '比', '劫', '伤', '食', '财', '才', '官', '杀'];

	/**
	 * 日干关联其余各干对应十神 Day Gan ShiShen
	 */
	public $dgs = [
		[2, 3, 1, 0, 9, 8, 7, 6, 5, 4],
		[3, 2, 0, 1, 8, 9, 6, 7, 4, 5],
		[5, 4, 2, 3, 1, 0, 9, 8, 7, 6],
		[4, 5, 3, 2, 0, 1, 8, 9, 6, 7],
		[7, 6, 5, 4, 2, 3, 1, 0, 9, 8],
		[6, 7, 4, 5, 3, 2, 0, 1, 8, 9],
		[9, 8, 7, 6, 5, 4, 2, 3, 1, 0],
		[8, 9, 6, 7, 4, 5, 3, 2, 0, 1],
		[1, 0, 9, 8, 7, 6, 5, 4, 2, 3],
		[0, 1, 8, 9, 6, 7, 4, 5, 3, 2],
	];

	/**
	 * 日干关联各支对应十神 Day Zhi ShiShen
	 */
	public $dzs = [
		[0, 1, 8, 9, 6, 7, 4, 5, 3, 2],
		[6, 7, 4, 5, 3, 2, 0, 1, 8, 9],
		[2, 3, 1, 0, 9, 8, 7, 6, 5, 4],
		[3, 2, 0, 1, 8, 9, 6, 7, 4, 5],
		[7, 6, 5, 4, 2, 3, 1, 0, 9, 8],
		[5, 4, 2, 3, 1, 0, 9, 8, 7, 6],
		[4, 5, 3, 2, 0, 1, 8, 9, 6, 7],
		[6, 7, 4, 5, 3, 2, 0, 1, 8, 9],
		[9, 8, 7, 6, 5, 4, 2, 3, 1, 0],
		[8, 9, 6, 7, 4, 5, 3, 2, 0, 1],
		[7, 6, 5, 4, 2, 3, 1, 0, 9, 8],
		[1, 0, 9, 8, 7, 6, 5, 4, 2, 3],
	];

	/**
	 * 十二星座 char of XingZuo
	 */
	public $cxz = ['摩羯', '宝瓶', '双鱼', '白羊', '金牛', '双子', '巨蟹', '狮子', '室女', '天平', '天蝎', '人马'];

	/**
	 * 地支藏干表 支藏干
	 */
	public $zcg = [
		[9, -1, -1],
		[5, 9, 7],
		[0, 2, 4],
		[1, -1, -1],
		[4, 1, 9],
		[2, 4, 6],
		[3, 5, -1],
		[5, 1, 3],
		[6, 8, 4],
		[7, -1, -1],
		[4, 7, 3],
		[8, 0, -1],
	];

	/**
	 * 十二长生 char of ZhangSheng
	 */
	public $czs  = ['长生(强)', '沐浴(凶)', '冠带(吉)', '临官(大吉)', '帝旺(大吉)', '衰(弱)', '病(弱)', '死(凶)', '墓(吉)', '绝(凶)', '胎(平)', '养(平)'];
	public $yyss = ['异', '同'];
	public $sxss = ['生我', '同我', '我生', '我克', '克我'];

	/**
	 * 方位 char of FangWei
	 */
	public $cfw = ['　中　', '　北　', '北北东', '东北东', '　东　', '东南东', '南南东', '　南　', '南南西', '西南西', '　西　', '西北西', '北北西'];

	/**
	 * 四季 char of SiJi
	 */
	public $csj = ['旺四季', '　春　', '　夏　', '　秋　', '　冬　'];

	/**
	 * 天干的方位属性 FangWei TianGan
	 */
	public $fwtg = [4, 4, 7, 7, 0, 0, 10, 10, 1, 1];

	/**
	 * 地支的方位属性 FangWei DiZhi
	 */
	public $fwdz = [1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11, 12];

	/**
	 * 天干的四季属性 SiJi TianGan
	 */
	public $sjtg = [1, 1, 2, 2, 0, 0, 3, 3, 4, 4];

	/**
	 * 地支的四季属性 SiJi DiZhi
	 */
	public $sjdz = [1, 1, 2, 2, 2, 3, 3, 3, 4, 4, 4, 1];

	/**
	 * 记录日志
	 * @$string s
	 */
	private function logs($n, $s = null) {
		$m     = [];
		$m[0]  = '超出计算能力';
		$m[1]  = '适用于西元-1000年至西元3000年,超出此范围误差较大';
		$m[2]  = '对应的干支不存在';
		$m[3]  = '干支非六十甲子';
		$m[4]  = '日期超出范围';
		$m[5]  = '日期错误';
		$m[6]  = '月份错误';
		$m[7]  = '此年非闰年';
		$m[8]  = '此月非闰月';
		$m[9]  = '不存在的时间';
		$m[10] = '参数非整数字符串';
		$m[11] = '参数非整数类型';
		$m[12] = '参数非浮点类型';
		$m[13] = '月份超出范围';
		$m[14] = '此年无闰月';
		$m[15] = '参数非整数';
		$m[16] = '参数非数字';
		if ($this->debug) {
			$ss = $m[$n] ? $m[$n] : $n;
			$ss .= ($s === null) ? '' : (':' . $s);

			echo $ss;
		}
		return false;
	}

	/**
	 * 将公历年月日时转换为儒略日历时间
	 * @param  int         $yy
	 * @param  int         $mm
	 * @param  int         $dd
	 * @param  int         $hh
	 * @param  int         $mt
	 * @param  int         $ss
	 * @return false|int
	 */
	public function Jdays($yy, $mm, $dd, $hh, $mt = 0, $ss = 0) {
		$yy = floatval($yy);
		$mm = floatval($mm);
		$dd = floatval($dd);
		$hh = floatval($hh);
		$mt = floatval($mt);
		$ss = floatval($ss);
		if ($yy < -7000 || $yy > 7000) {
			//超出计算能力
			$this->logs(0);
			return false;
		}
		$yp = $yy + floor(($mm - 3) / 10);
		if (($yy > 1582) || ($yy == 1582 && $mm > 10) || ($yy == 1582 && $mm == 10 && $dd >= 15)) {
			$init = 1721119.5;
			$jdy  = floor($yp * 365.25) - floor($yp / 100) + floor($yp / 400);
		} else {
			if (($yy < 1582) || ($yy == 1582 && $mm < 10) || ($yy == 1582 && $mm == 10 && $dd <= 4)) {
				$init = 1721117.5;
				$jdy  = floor($yp * 365.25);
			} else {
				//不存在的时间
				$this->logs(9);
				return false;
			}
		}
		$mp  = floor($mm + 9) % 12;
		$jdm = $mp * 30 + floor(($mp + 1) * 34 / 57);
		$jdd = $dd - 1;
		$hh  = $hh + (($ss / 60) + $mt) / 60;
		$jdh = $hh / 24;
		$jd  = $jdy + $jdm + $jdd + $jdh + $init;
		return $jd;
	}

	/**
	 * 将儒略日转换为公历年月日时分秒
	 * @param  float $jd
	 * @return array (年,月,日,时,分,秒)
	 */
	public function Jtime($jd) {
		$jd = floatval($jd);
		if ($jd >= 2299160.5) {
			$y4h  = 146097;
			$init = 1721119.5;
		} else {
			$y4h  = 146100;
			$init = 1721117.5;
		}
		$jdr = floor($jd - $init);
		$yh  = $y4h / 4;
		$cen = floor(($jdr + 0.75) / $yh);
		$d   = floor($jdr + 0.75 - $cen * $yh);
		$ywl = 1461 / 4;
		$jy  = floor(($d + 0.75) / $ywl);
		$d   = floor($d + 0.75 - $ywl * $jy + 1);
		$ml  = 153 / 5;
		$mp  = floor(($d - 0.5) / $ml);
		$d   = floor(($d - 0.5) - 30.6 * $mp + 1);
		$y   = (100 * $cen) + $jy;
		$m   = ($mp + 2) % 12 + 1;
		if ($m < 3) {
			$y = $y + 1;
		}
		$sd  = floor(($jd + 0.5 - floor($jd + 0.5)) * 24 * 60 * 60 + 0.00005);
		$mt  = floor($sd / 60);
		$ss  = $sd % 60;
		$hh  = floor($mt / 60);
		$mmt = $mt % 60;
		$yy  = floor($y);
		$mm  = floor($m);
		$dd  = floor($d);

		return [$yy, $mm, $dd, $hh, $mmt, $ss];
	}

	/**
	 * 验证公历日期是否有效
	 * @param  int    $yy
	 * @param  int    $mm
	 * @param  int    $dd
	 * @return bool
	 */
	public function ValidDate($yy, $mm, $dd) {
		$vd = true;
		if ($mm <= 0 || $mm > 12) {
			//月份超出范围
			$this->logs(13);
			$vd = false;
		} else {
			$ndf1 = -($yy % 4 == 0); //可被四整除
			$ndf2 = (($yy % 400 == 0) - ($yy % 100 == 0)) && ($yy > 1582);
			$ndf  = $ndf1 + $ndf2;
			$dom  = 30 + ((abs($mm - 7.5) + 0.5) % 2) - ($mm == 2) * (2 + $ndf);
			if ($dd <= 0 || $dd > $dom) {
				if ($ndf == 0 && $mm == 2 && $dd == 29) {
					//此年无闰月
					$this->logs(14);
				} else {
					//日期超出范围
					$this->logs(4);
				}
				$vd = false;
			}
		}
		if ($yy == 1582 && $mm == 10 && $dd >= 5 && $dd < 15) {
			//此日期不存在
			$this->logs(9);
			$vd = false;
		}
		return $vd;
	}

	/**
	 * 计算指定年(公历)的春分点(vernal equinox)理论值
	 * 因地球在绕日运行时会因受到其他星球之影响而产生摄动(perturbation),必须将此现象产生的偏移量加入.
	 * @param  int         $yy
	 * @return false|float 返回儒略日历时间
	 */
	private function VE($yy) {
		$yx = intval($yy);
		if ($yx >= 1000 && $yx <= 8001) {
			$m    = ($yx - 2000) / 1000;
			$jdve = 2451623.80984 + 365242.37404 * $m + 0.05169 * $m * $m - 0.00411 * $m * $m * $m - 0.00057 * $m * $m * $m * $m;
		} else {
			if ($yx >= -8000 && $yx < 1000) {
				$m    = $yx / 1000;
				$jdve = 1721139.29189 + 365242.1374 * $m + 0.06134 * $m * $m + 0.00111 * $m * $m * $m - 0.00071 * $m * $m * $m * $m;
			} else {
				//超出计算能力范围
				$this->logs(0);
				return false;
			}
		}
		return $jdve;
	}

	/**
	 * 获取指定公历年的春分开始的24节气理论值
	 * 大致原理是:把公转轨道进行24等分,每一等分为一个节气,此为理论值,再用摄动值(Perturbation)和固定参数DeltaT做调整得到实际值
	 * @param  int   $yy
	 * @param  int   $ini                        从0开始
	 * @param  int   $num                        1-24,若超过则有几秒的误差
	 * @return array 下标从1开始的数组
	 */
	private function MeanJQJD($yy, $ini, $num) {
		$yy  = intval($yy);
		$ini = intval($ini);
		$num = intval($num);

		$jdez = [];
		$jdve = $this->VE($yy);
		$ty   = $this->VE($yy + 1) - $jdve; //求指定年的春分点及回归年长

		$ath  = 2 * M_PI / 24;
		$tx   = ($jdve - 2451545) / 365250;
		$e    = 0.0167086342 - 0.0004203654 * $tx - 0.0000126734 * $tx * $tx + 0.0000001444 * $tx * $tx * $tx - 0.0000000002 * $tx * $tx * $tx * $tx + 0.0000000003 * $tx * $tx * $tx * $tx * $tx;
		$tt   = $yy / 1000;
		$vp   = 111.25586939 - 17.0119934518333 * $tt - 0.044091890166673 * $tt * $tt - 4.37356166661345E-04 * $tt * $tt * $tt + 8.16716666602386E-06 * $tt * $tt * $tt * $tt;
		$rvp  = $vp * 2 * M_PI / 360;
		$peri = [];
		for ($i = 1; $i <= ($ini + $num); $i++) {
			$flag = 0;
			$th   = $ath * ($i - 1) + $rvp;
			if ($th > M_PI && $th <= 3 * M_PI) {
				$th   = 2 * M_PI - $th;
				$flag = 1;
			}
			if ($th > 3 * M_PI) {
				$th   = 4 * M_PI - $th;
				$flag = 2;
			}
			$f1 = 2 * atan((sqrt((1 - $e) / (1 + $e)) * tan($th / 2)));
			$f2 = ($e * sqrt(1 - $e * $e) * sin($th)) / (1 + $e * cos($th));
			$f  = ($f1 - $f2) * $ty / 2 / M_PI;
			if ($flag == 1) {
				$f = $ty - $f;
			}
			if ($flag == 2) {
				$f = 2 * $ty - $f;
			}
			$peri[$i] = $f;
		}
		for ($i = max(1, $ini); $i <= ($ini + $num); $i++) {
			$jdez[$i] = $jdve + $peri[$i] - $peri[1];
		}
		return $jdez;
	}

	/**
	 * 地球在绕日运行时会因受到其他星球之影响而产生摄动(perturbation)
	 * @param  float $jdez                                             Julian day
	 * @return float 返回某时刻(儒略日历)的摄动偏移量
	 */
	private function Perturbation($jdez) {
		$jdez = floatval($jdez);
		$ptsa = [485, 203, 199, 182, 156, 136, 77, 74, 70, 58, 52, 50, 45, 44, 29, 18, 17, 16, 14, 12, 12, 12, 9, 8];
		$ptsb = [324.96, 337.23, 342.08, 27.85, 73.14, 171.52, 222.54, 296.72, 243.58, 119.81, 297.17, 21.02, 247.54, 325.15, 60.93, 155.12, 288.79, 198.04, 199.76, 95.39, 287.11, 320.81, 227.73, 15.45];
		$ptsc = [1934.136, 32964.467, 20.186, 445267.112, 45036.886, 22518.443, 65928.934, 3034.906, 9037.513, 33718.147, 150.678, 2281.226, 29929.562, 31555.956, 4443.417, 67555.328, 4562.452, 62894.029, 31436.921, 14577.848, 31931.756, 34777.259, 1222.114, 16859.074];
		$t    = ($jdez - 2451545) / 36525;
		$s    = 0;
		for ($k = 0; $k <= 23; $k++) {
			$s = $s + $ptsa[$k] * cos($ptsb[$k] * 2 * M_PI / 360 + $ptsc[$k] * 2 * M_PI / 360 * $t);
		}
		$w   = 35999.373 * $t - 2.47;
		$l   = 1 + 0.0334 * cos($w * 2 * M_PI / 360) + 0.0007 * cos(2 * $w * 2 * M_PI / 360);
		$ptb = 0.00001 * $s / $l;
		return $ptb;
	}

	/**
	 * 求∆t
	 * @param  int     $yy 公历年份
	 * @param  int     $mm 公历月份
	 * @return float
	 */
	private function DeltaT($yy, $mm) {
		$yy = intval($yy);
		$mm = intval($mm);

		$y = $yy + ($mm - 0.5) / 12;
		if ($y <= -500) {
			$u  = ($y - 1820) / 100;
			$dt = (-20 + 32 * $u * $u);
		} elseif ($y < 500) {
			$u  = $y / 100;
			$dt = (10583.6 - 1014.41 * $u + 33.78311 * $u * $u - 5.952053 * $u * $u * $u - 0.1798452 * $u * $u * $u * $u + 0.022174192 * $u * $u * $u * $u * $u + 0.0090316521 * $u * $u * $u * $u * $u * $u);
		} elseif ($y < 1600) {
			$u  = ($y - 1000) / 100;
			$dt = (1574.2 - 556.01 * $u + 71.23472 * $u * $u + 0.319781 * $u * $u * $u - 0.8503463 * $u * $u * $u * $u - 0.005050998 * $u * $u * $u * $u * $u + 0.0083572073 * $u * $u * $u * $u * $u * $u);
		} elseif ($y < 1700) {
			$t  = $y - 1600;
			$dt = (120 - 0.9808 * $t - 0.01532 * $t * $t + $t * $t * $t / 7129);
		} elseif ($y < 1800) {
			$t  = $y - 1700;
			$dt = (8.83 + 0.1603 * $t - 0.0059285 * $t * $t + 0.00013336 * $t * $t * $t - $t * $t * $t * $t / 1174000);
		} elseif ($y < 1860) {
			$t  = $y - 1800;
			$dt = (13.72 - 0.332447 * $t + 0.0068612 * $t * $t + 0.0041116 * $t * $t * $t - 0.00037436 * $t * $t * $t * $t + 0.0000121272 * $t * $t * $t * $t * $t - 0.0000001699 * $t * $t * $t * $t * $t * $t + 0.000000000875 * $t * $t * $t * $t * $t * $t * $t);
		} elseif ($y < 1900) {
			$t  = $y - 1860;
			$dt = (7.62 + 0.5737 * $t - 0.251754 * $t * $t + 0.01680668 * $t * $t * $t - 0.0004473624 * $t * $t * $t * $t + $t * $t * $t * $t * $t / 233174);
		} elseif ($y < 1920) {
			$t  = $y - 1900;
			$dt = (-2.79 + 1.494119 * $t - 0.0598939 * $t * $t + 0.0061966 * $t * $t * $t - 0.000197 * $t * $t * $t * $t);
		} elseif ($y < 1941) {
			$t  = $y - 1920;
			$dt = (21.2 + 0.84493 * $t - 0.0761 * $t * $t + 0.0020936 * $t * $t * $t);
		} elseif ($y < 1961) {
			$t  = $y - 1950;
			$dt = (29.07 + 0.407 * $t - $t * $t / 233 + $t * $t * $t / 2547);
		} elseif ($y < 1986) {
			$t  = $y - 1975;
			$dt = (45.45 + 1.067 * $t - $t * $t / 260 - $t * $t * $t / 718);
		} elseif ($y < 2005) {
			$t  = $y - 2000;
			$dt = (63.86 + 0.3345 * $t - 0.060374 * $t * $t + 0.0017275 * $t * $t * $t + 0.000651814 * $t * $t * $t * $t + 0.00002373599 * $t * $t * $t * $t * $t);
		} elseif ($y < 2050) {
			$t  = $y - 2000;
			$dt = (62.92 + 0.32217 * $t + 0.005589 * $t * $t);
		} elseif ($y < 2150) {
			$u  = ($y - 1820) / 100;
			$dt = (-20 + 32 * $u * $u - 0.5628 * (2150 - $y));
		} else {
			$u  = ($y - 1820) / 100;
			$dt = (-20 + 32 * $u * $u);
		}

		if ($y < 1955 || $y >= 2005) {
			$dt = $dt - (0.000012932 * ($y - 1955) * ($y - 1955));
		}
		$DeltaT = $dt / 60; //将秒转换为分
		return $DeltaT;
	}

	/**
	 * 获取指定公历年对Perturbaton作调整后的自春分点开始的24节气,可只取部份 (因此方法调用频繁,加上暂存)
	 * @param  int   $yy
	 * @param  int   $ini                  0-23
	 * @param  int   $num                  1-24 取的个数
	 * @return array $this->jq[(i-1)%24]
	 */
	public function GetAdjustedJQ($yy, $ini, $num) {
		$yy  = intval($yy);
		$ini = intval($ini);
		$num = intval($num);

		$jdez = [];
		$jdjq = [];

		if (!isset($this->MM['GetAdjustedJQ']) or !is_array($this->MM['GetAdjustedJQ'])) {
			$this->MM['GetAdjustedJQ'] = [];
		}
		if (!isset($this->MM['GetAdjustedJQ'][$yy]) or !is_array($this->MM['GetAdjustedJQ'][$yy])) {
			$jdez = $this->MeanJQJD($yy, 0, 26); //输入指定年,求该回归年各节气点
			for ($i = 1; $i <= 26; $i++) {
				$ptb      = $this->Perturbation($jdez[$i]); //取得受perturbation影响所需微调
				$dt       = $this->DeltaT($yy, floor($i / 2) + 3); //修正dynamical time to Universal time
				$jdez[$i] = $jdez[$i] + $ptb - $dt / 60 / 24; //加上摄动调整值ptb，减去对应的Delta T值(分钟转换为日)
				$jdez[$i] = $jdez[$i] + 1 / 3; //因中国时间比格林威治时间先行8小时，即1/3日
			}
			$this->MM['GetAdjustedJQ'][$yy] = $jdez;
		}

		for ($i = $ini + 1; $i <= ($ini + $num); $i++) {
			$jdjq[$i] = $this->MM['GetAdjustedJQ'][$yy][$i];
		}

		return $jdjq;
	}

	/**
	 * 求出自冬至点为起点的连续16个中气
	 * @param  int   $yy
	 * @return array $this->jq[(2*i+18)%24]
	 */
	private function GetZQsinceWinterSolstice($yy) {
		$yy = intval($yy);

		$jdzq = [];

		//求出以冬至为起点之连续16个中气（多取四个以备用）
		$dj = [];
		$dj = $this->GetAdjustedJQ($yy - 1, 18, 5); //求出指定年冬至开始之节气JD值,以前一年的值代入
		//转移春分前之节气至jdzq变数中，以重整index
		$jdzq[0] = $dj[19]; //此为冬至中气
		$jdzq[1] = $dj[21]; //此为大寒中气
		$jdzq[2] = $dj[23]; //此为雨水中气
		$dj      = $this->GetAdjustedJQ($yy, 0, 26); //求出指定年节气之JD值
		for ($i = 1; $i <= 13; $i++) {
			$jdzq[$i + 2] = $dj[2 * $i - 1]; //转移冬至后之节气至jdzq变数中，以重整index
		}
		return $jdzq;
	}

	/**
	 * 求出某公历年以立春点开始的不含中气之12节
	 * @param  int   $yy
	 * @return array $this->jq[(2*i+21)%24]
	 */
	private function GetPureJQsinceSpring($yy) {
		$yy = intval($yy);

		$jdpjq = [];
		$sjdjq = [];
		$yea   = $yy - 1;
		$sjdjq = $this->GetAdjustedJQ($yea, 21, 3); //求出含指定年立春开始之3个节气JD值,以前一年的年值代入
		//转移春分前之立春至惊蛰之节气至jdpjq变数中，以重整index
		$jdpjq[0] = $sjdjq[22]; //此为立春
		$jdpjq[1] = $sjdjq[24]; //此为惊蛰
		$yea      = $yy;
		$sjdjq    = $this->GetAdjustedJQ($yea, 0, 26); //求出指定年节气之JD值,从惊蛰开始，到雨水
		//转移春分至小寒之节气至jdpjq变数中，以重整index
		for ($i = 1; $i <= 13; $i++) {
			$jdpjq[$i + 1] = $sjdjq[2 * $i];
		}
		return $jdpjq;
	}

	/**
	 * 对于指定日期时刻所属的朔望月,求出其均值新月点的月序数
	 * @param  float $jd
	 * @return int
	 */
	private function MeanNewMoon($jd) {
		$jd = floatval($jd);

		//k为从2000年1月6日14时20分36秒起至指定年月日之阴历月数,以synodic month为单位
		$k   = floor(($jd - 2451550.09765) / $this->synmonth); //2451550.09765为2000年1月6日14时20分36秒之JD值。
		$jdt = 2451550.09765 + $k * $this->synmonth;
		//Time in Julian centuries from 2000 January 0.5.
		$t     = ($jdt - 2451545) / 36525; //以100年为单位,以2000年1月1日12时为0点
		$thejd = $jdt + 0.0001337 * $t * $t - 0.00000015 * $t * $t * $t + 0.00000000073 * $t * $t * $t * $t;
		//2451550.09765为2000年1月6日14时20分36秒，此为2000年后的第一个均值新月
		return $k;
	}

	/**
	 * 对于指定日期时刻所属的朔望月,求出其均值新月点的JD值
	 * @param  float   $jd
	 * @return float
	 */
	private function MeanNewMoonDay($jd) {
		$jd = floatval($jd);

		//k为从2000年1月6日14时20分36秒起至指定年月日之阴历月数,以synodic month为单位
		$k   = floor(($jd - 2451550.09765) / $this->synmonth); //2451550.09765为2000年1月6日14时20分36秒之JD值。
		$jdt = 2451550.09765 + $k * $this->synmonth;
		//Time in Julian centuries from 2000 January 0.5.
		$t     = ($jdt - 2451545) / 36525; //以100年为单位,以2000年1月1日12时为0点
		$thejd = $jdt + 0.0001337 * $t * $t - 0.00000015 * $t * $t * $t + 0.00000000073 * $t * $t * $t * $t;
		//2451550.09765为2000年1月6日14时20分36秒，此为2000年后的第一个均值新月
		return $thejd;
	}

	/**
	 * 求出实际新月点
	 * 以2000年初的第一个均值新月点为0点求出的均值新月点和其朔望月之序数k代入此副程式来求算实际新月点
	 * @param  int     $k
	 * @return float
	 */
	private function TrueNewMoon($k) {
		$k = intval($k);

		$jdt = 2451550.09765 + $k * $this->synmonth;
		$t   = ($jdt - 2451545) / 36525; //2451545为2000年1月1日正午12时的JD
		$t2  = $t * $t; //square for frequent use
		$t3  = $t2 * $t; //cube for frequent use
		$t4  = $t3 * $t; //to the fourth
		//mean time of phase
		$pt = $jdt + 0.0001337 * $t2 - 0.00000015 * $t3 + 0.00000000073 * $t4;
		//Sun's mean anomaly(地球绕太阳运行均值近点角)(从太阳观察)
		$m = 2.5534 + 29.10535669 * $k - 0.0000218 * $t2 - 0.00000011 * $t3;
		//Moon's mean anomaly(月球绕地球运行均值近点角)(从地球观察)
		$mprime = 201.5643 + 385.81693528 * $k + 0.0107438 * $t2 + 0.00001239 * $t3 - 0.000000058 * $t4;
		//Moon's argument of latitude(月球的纬度参数)
		$f = 160.7108 + 390.67050274 * $k - 0.0016341 * $t2 - 0.00000227 * $t3 + 0.000000011 * $t4;
		//Longitude of the ascending node of the lunar orbit(月球绕日运行轨道升交点之经度)
		$omega = 124.7746 - 1.5637558 * $k + 0.0020691 * $t2 + 0.00000215 * $t3;
		//乘式因子
		$es = 1 - 0.002516 * $t - 0.0000074 * $t2;
		//因perturbation造成的偏移：
		$apt1 = -0.4072 * sin((M_PI / 180) * $mprime);
		$apt1 += 0.17241 * $es * sin((M_PI / 180) * $m);
		$apt1 += 0.01608 * sin((M_PI / 180) * 2 * $mprime);
		$apt1 += 0.01039 * sin((M_PI / 180) * 2 * $f);
		$apt1 += 0.00739 * $es * sin((M_PI / 180) * ($mprime - $m));
		$apt1 -= 0.00514 * $es * sin((M_PI / 180) * ($mprime + $m));
		$apt1 += 0.00208 * $es * $es * sin((M_PI / 180) * (2 * $m));
		$apt1 -= 0.00111 * sin((M_PI / 180) * ($mprime - 2 * $f));
		$apt1 -= 0.00057 * sin((M_PI / 180) * ($mprime + 2 * $f));
		$apt1 += 0.00056 * $es * sin((M_PI / 180) * (2 * $mprime + $m));
		$apt1 -= 0.00042 * sin((M_PI / 180) * 3 * $mprime);
		$apt1 += 0.00042 * $es * sin((M_PI / 180) * ($m + 2 * $f));
		$apt1 += 0.00038 * $es * sin((M_PI / 180) * ($m - 2 * $f));
		$apt1 -= 0.00024 * $es * sin((M_PI / 180) * (2 * $mprime - $m));
		$apt1 -= 0.00017 * sin((M_PI / 180) * $omega);
		$apt1 -= 0.00007 * sin((M_PI / 180) * ($mprime + 2 * $m));
		$apt1 += 0.00004 * sin((M_PI / 180) * (2 * $mprime - 2 * $f));
		$apt1 += 0.00004 * sin((M_PI / 180) * (3 * $m));
		$apt1 += 0.00003 * sin((M_PI / 180) * ($mprime + $m - 2 * $f));
		$apt1 += 0.00003 * sin((M_PI / 180) * (2 * $mprime + 2 * $f));
		$apt1 -= 0.00003 * sin((M_PI / 180) * ($mprime + $m + 2 * $f));
		$apt1 += 0.00003 * sin((M_PI / 180) * ($mprime - $m + 2 * $f));
		$apt1 -= 0.00002 * sin((M_PI / 180) * ($mprime - $m - 2 * $f));
		$apt1 -= 0.00002 * sin((M_PI / 180) * (3 * $mprime + $m));
		$apt1 += 0.00002 * sin((M_PI / 180) * (4 * $mprime));
		$apt2 = 0.000325 * sin((M_PI / 180) * (299.77 + 0.107408 * $k - 0.009173 * $t2));
		$apt2 += 0.000165 * sin((M_PI / 180) * (251.88 + 0.016321 * $k));
		$apt2 += 0.000164 * sin((M_PI / 180) * (251.83 + 26.651886 * $k));
		$apt2 += 0.000126 * sin((M_PI / 180) * (349.42 + 36.412478 * $k));
		$apt2 += 0.00011 * sin((M_PI / 180) * (84.66 + 18.206239 * $k));
		$apt2 += 0.000062 * sin((M_PI / 180) * (141.74 + 53.303771 * $k));
		$apt2 += 0.00006 * sin((M_PI / 180) * (207.14 + 2.453732 * $k));
		$apt2 += 0.000056 * sin((M_PI / 180) * (154.84 + 7.30686 * $k));
		$apt2 += 0.000047 * sin((M_PI / 180) * (34.52 + 27.261239 * $k));
		$apt2 += 0.000042 * sin((M_PI / 180) * (207.19 + 0.121824 * $k));
		$apt2 += 0.00004 * sin((M_PI / 180) * (291.34 + 1.844379 * $k));
		$apt2 += 0.000037 * sin((M_PI / 180) * (161.72 + 24.198154 * $k));
		$apt2 += 0.000035 * sin((M_PI / 180) * (239.56 + 25.513099 * $k));
		$apt2 += 0.000023 * sin((M_PI / 180) * (331.55 + 3.592518 * $k));
		$tnm = $pt + $apt1 + $apt2;
		return $tnm;
	}

	/**
	 * 求算以含冬至中气为阴历11月开始的连续16个朔望月
	 * @param  int     $yy 年份
	 * @return array
	 */
	private function GetSMsinceWinterSolstice($yy) {
		$yy = intval($yy);

		$dj = $this->GetAdjustedJQ($yy - 1, 18, 5); //求出指定年冬至开始之节气JD值,以前一年的值代入
		//转移春分前之节气至jdzq变数中，以重整index
		$jdws = $dj[19]; //此为冬至中气

		$jdnm  = [];
		$spcjd = $this->Jdays($yy - 1, 11, 0, 0); //求年初前两个月附近的新月点(即前一年的11月初)
		$kn    = $this->MeanNewMoon($spcjd); //求得自2000年1月起第kn个平均朔望日及其JD值
		for ($i = 0; $i <= 19; $i++) {
			//求出连续20个朔望月
			$k = $kn + $i;
			// $mjd = $thejd + $this->synmonth * $i;
			$tjd[$i] = $this->TrueNewMoon($k) + 1 / 3; //以k值代入求瞬时朔望日,因中国比格林威治先行8小时，加1/3天
			//下式为修正dynamical time to Universal time
			$tjd[$i] = $tjd[$i] - $this->DeltaT($yy, $i - 1) / 1440; //1为1月，0为前一年12月，-1为前一年11月(当i=0时，i-1=-1，代表前一年11月)
		}
		for ($j = 0; $j <= 18; $j++) {
			if (floor($tjd[$j] + 0.5) > floor($jdws + 0.5)) {
				break;
			} //已超过冬至中气(比较日期法)
		}
		$jj = $j; //取此时的索引值
		for ($k = 0; $k <= 15; $k++) {
			$jdnm[$k] = $tjd[$jj - 1 + $k]; //重排索引，使含冬至朔望月的索引为0
		}
		return $jdnm;
	}

	/**
	 * 以比较日期法求算冬月及其余各月名称代码，包含闰月，冬月为0，腊月为1，正月为2，余类推。闰月多加0.5
	 * @param int $yy
	 */
	private function GetZQandSMandLunarMonthCode($yy) {
		$yy = intval($yy);

		$mc    = [];
		$jdzq  = $this->GetZQsinceWinterSolstice($yy); //取得以前一年冬至为起点之连续17个中气
		$jdnm  = $this->GetSMsinceWinterSolstice($yy); //求出以含冬至中气为阴历11月(冬月)开始的连续16个朔望月的新月点
		$yz    = 0; //设定旗标，0表示未遇到闰月，1表示已遇到闰月
		$mc[0] = 0;
		if (floor($jdzq[12] + 0.5) >= floor($jdnm[13] + 0.5)) {
			//若第13个中气jdzq(12)大于或等于第14个新月jdnm(13)
			for ($i = 1; $i <= 14; $i++) {
				//表示此两个冬至之间的11个中气要放到12个朔望月中，
				//至少有一个朔望月不含中气，第一个不含中气的月即为闰月
				//若阴历腊月起始日大于冬至中气日，且阴历正月起始日小于或等于大寒中气日，则此月为闰月，其余同理
				if (floor(($jdnm[$i] + 0.5) > floor($jdzq[$i - 1 - $yz] + 0.5) && floor($jdnm[$i + 1] + 0.5) <= floor($jdzq[$i - $yz] + 0.5))) {
					$mc[$i] = $i - 0.5;
					$yz     = 1; //标示遇到闰月
				} else {
					$mc[$i] = $i - $yz; //遇到闰月开始，每个月号要减1
				}
			}
		} else {
			//否则表示两个连续冬至之间只有11个整月，故无闰月
			for ($i = 1; $i <= 12; $i++) {
				//直接赋予这12个月月代码
				$mc[$i] = $i;
			}
			for ($i = 13; $i <= 14; $i++) {
				//处理次一置月年的11月与12月，亦有可能含闰月
				//若次一阴历腊月起始日大于附近的冬至中气日，且阴历正月起始日小于或等于大寒中气日，则此月为闰月，次一正月同理。
				if (floor(($jdnm[$i] + 0.5) > floor($jdzq[$i - 1 - $yz] + 0.5) && floor($jdnm[$i + 1] + 0.5) <= floor($jdzq[$i - $yz] + 0.5))) {
					$mc[$i] = $i - 0.5;
					$yz     = 1; //标示遇到闰月
				} else {
					$mc[$i] = $i - $yz; //遇到闰月开始，每个月号要减1
				}
			}
		}
		return $mc;
	}

	/**
	 * 将农历时间转换成公历时间
	 * @param  int         $yy
	 * @param  int         $mm
	 * @param  int         $dd
	 * @param  boolean     $ry             是否闰月
	 * @return false/array (年,月,日)
	 */
	public function Lunar2Solar($yy, $mm, $dd, $ry) {
		//此为将阴历日期转换为阳历日期的主程式
		$yy = intval($yy);
		$mm = intval($mm);
		$dd = intval($dd);
		$ry = boolval($ry);

		//限定范围
		if ($yy < -7000 || $yy > 7000) {
			//超出计算能力
			$this->logs(0);
			return false;
		}
		if ($yy < -1000 || $yy > 3000) {
			//适用于西元-1000年至西元3000年,超出此范围误差较大
			$this->logs(1);
			return false;
		}
		$sjd    = $this->GetSMsinceWinterSolstice($yy); //求出以含冬至中气为阴历11月(冬月)开始的连续16个朔望月的新月点
		$mc     = $this->GetZQandSMandLunarMonthCode($yy);
		$runyue = 0; //若闰月旗标为0代表无闰月
		for ($j = 1; $j <= 14; $j++) {
			//确认指定年前一年11月开始各月是否闰月
			if ($mc[$j] - floor($mc[$j]) > 0) {
				//若是，则将此闰月代码放入闰月旗标内
				$runyue = floor($mc[$j] + 0.5);
				//runyue=0对应阴历11月,1对应阴历12月，2对应阴历隔年1月，依此类推。
				break;
			}
		}
		$mx = $mm + 2; //11月对应到1，12月对应到2，1月对应到3，2月对应到4，依此类推
		//求算阴历各月之大小，大月30天，小月29天
		for ($i = 0; $i <= 14; $i++) {
			$nofd[$i] = floor($sjd[$i + 1] + 0.5) - floor($sjd[$i] + 0.5); //每月天数,加0.5是因JD以正午起算
		}
		$er = 0; //若输入值有错误，er值将被设定为1
		if ($mx >= 3 && $mx <= 14) {
			//输入月份必须在1-12月之内
			if ($dd >= 1 && $dd <= 30) {
				//输入日期必须在1-30日之内
				if ($ry == true) {
					//若有勾选闰月
					if ($runyue < 3) {
						//而旗标非闰月或非本年闰月，则表示此年不含闰月
						//runyue=0代表无闰月,=1代表闰月为前一年的11月,=2代表闰月为前一年的12月
						$er = 1;
						$this->logs(7); //此年非闰年
					} else {
						//若本年内有闰月
						if ($runyue != $mx) { //但不为输入的月份
							$er = 1; //则此输入的月份非闰月
							$this->logs(8); //此月非闰月
						} else {
							//若输入的月份即为闰月
							if ($dd <= $nofd[$mx]) { //若输入的日期不大于当月的天数
								$jdx = $sjd[$mx] + $dd - 1; //则将当月之前的JD值加上日期之前的天数
							} else {
								//日期超出范围
								$er = 1;
								$this->logs(4);
							}
						}
					}
				} else {
					//若没有勾选闰月则
					if ($runyue == 0) {
						//若旗标非闰月，则表示此年不含闰月(包括前一年的11月起之月份)
						if ($dd <= $nofd[$mx - 1]) { //若输入的日期不大于当月的天数
							$jdx = $sjd[$mx - 1] + $dd - 1; //则将当月之前的JD值加上日期之前的天数
						} else {
							//日期超出范围
							$er = 1;
							$this->logs(4);
						}
					} else {
						//若旗标为本年有闰月(包括前一年的11月起之月份)
						//公式nofd(mx - (mx > runyue) - 1)的用意为:若指定月大于闰月，则索引用mx，否则索引用mx-1
						if ($dd <= $nofd[$mx + ($mx > $runyue) - 1]) { //若输入的日期不大于当月的天数
							$jdx = $sjd[$mx + ($mx > $runyue) - 1] + $dd - 1; //则将当月之前的JD值加上日期之前的天数
						} else {
							//日期超出范围
							$er = 1;
							$this->logs(4);
						}
					}
				}
				if ($er == 0) {
					//若没有错误，则印出阳历年月日

				}
			} else {
				//日期错误
				$er = 1;
				$this->logs(5);
			}
		} else {
			//月份错误
			$er = 1;
			$this->logs(6);
		}
		return $er ? false : array_slice($this->Jtime($jdx), 0, 3);
	}

	/**
	 * 将公历时间转换成农历时间
	 * @param  int         $yy
	 * @param  int         $mm
	 * @param  int         $dd
	 * @return false/array (年,月,日,是否闰月)
	 */
	public function Solar2Lunar($yy, $mm, $dd) {
		$yy = intval($yy);
		$mm = intval($mm);
		$dd = intval($dd);

		$flag = 0;

		//限定范围
		if ($yy < -7000 || $yy > 7000) {
			//超出计算能力
			$this->logs(0);
			return false;
		}
		if ($yy < -1000 || $yy > 3000) {
			//适用于西元-1000年至西元3000年,超出此范围误差较大
			$this->logs(1);
			return false;
		}
		//验证输入日期的正确性,若不正确则跳离
		if ($this->ValidDate($yy, $mm, $dd) === false) {
			return false;
		}
		$sjd = $this->GetSMsinceWinterSolstice($yy); //求出以含冬至中气为阴历11月(冬月)开始的连续16个朔望月的新月点
		$mc  = $this->GetZQandSMandLunarMonthCode($yy);
		$jdx = $this->Jdays($yy, $mm, $dd, 12); //求出指定年月日之JD值
		if (floor($jdx) < floor($sjd[0] + 0.5)) {
			$flag = 1;
			$sjd  = $this->GetSMsinceWinterSolstice($yy - 1); //求出以含冬至中气为阴历11月(冬月)开始的连续16个朔望月的新月点
			$mc   = $this->GetZQandSMandLunarMonthCode($yy - 1);
		}
		for ($i = 0; $i <= 14; $i++) {
			//下面的指令中加0.5是为了改为从0时算起而不从正午算起
			if (floor($jdx) >= floor($sjd[$i] + 0.5) && floor($jdx) < floor($sjd[$i + 1] + 0.5)) {
				$mi = $i;
				break;
			}
		}
		$dz = floor($jdx) - floor($sjd[$mi] + 0.5) + 1; //此处加1是因为每月初一从1开始而非从0开始
		if ($mc[$mi] < 2 || $flag == 1) {
			$yi = $yy - 1;
		} else {
			$yi = $yy;
		} //因mc(mi)=0对应到前一年阴历11月，mc(mi)=1对应到前一年阴历12月
		//mc(mi)=2对应到本年1月，依此类推
		if (($mc[$mi] - floor($mc[$mi])) * 2 + 1 == 1) {
			$ry = false;
		} else {
			$ry = true;
		}
		$mis = (floor($mc[$mi] + 10) % 12) + 1; //对应到月份

		return [$yi, $mis, $dz, $ry];
	}

	/**
	 * 计算公历的某天是星期几(PHP中的date方法,此处演示儒略日历的转换作用)
	 * @param  int       $yy
	 * @param  int       $mm
	 * @param  int       $dd
	 * @return false/int wkd[i]
	 */
	public function GetWeek($yy, $mm, $dd) {
		$yy = intval($yy);
		$mm = intval($mm);
		$dd = intval($dd);

		$spcjd = $this->Jdays($yy, $mm, $dd, 12, 0, 0);
		if ($spcjd === false) {
			return false;
		}

		return (((floor($spcjd + 1) % 7)) + 7) % 7; //模数(或余数)为0代表星期日(因为西元前4713年1月1日12时为星期一).spcjd加1是因起始日为星期一
	}

	/**
	 * 获取公历某个月有多少天
	 * @param  int      $yy
	 * @param  int      $mm
	 * @return number
	 */
	public function GetSolarDays($yy, $mm) {
		$yy = intval($yy);
		$mm = intval($mm);

		if ($mm < 1 || $mm > 12) {
			//月份超出范围
			$this->logs(13);
			return 0;
		}
		if ($yy == 1582 && $mm == 10) {
			//这年这个月的5到14日不存在,所以1582年10月只有21天
			return 21;
		}
		$ndf1 = -($yy % 4 == 0); //可被四整除
		$ndf2 = (($yy % 400 == 0) - ($yy % 100 == 0)) && ($yy > 1582);
		$ndf  = $ndf1 + $ndf2;
		return 30 + ((abs($mm - 7.5) + 0.5) % 2) - ($mm == 2) * (2 + $ndf);
	}

	/**
	 * 获取农历某个月有多少天
	 * @param  int         $yy
	 * @param  int         $mm
	 * @param  bool        $ry   是否闰月
	 * @return false/int
	 */
	public function GetLunarDays($yy, $mm, $ry) {
		$yy = intval($yy);
		$mm = intval($mm);
		$ry = boolval($ry);

		//限定范围
		if ($yy < -7000 || $yy > 7000) {
			//超出计算能力
			$this->logs(0);
			return false;
		}
		if ($yy < -1000 || $yy > 3000) {
			//适用于西元-1000年至西元3000年,超出此范围误差较大
			$this->logs(1);
			return false;
		}
		$sjd    = $this->GetSMsinceWinterSolstice($yy); //求出以含冬至中气为阴历11月(冬月)开始的连续16个朔望月的新月点
		$mc     = $this->GetZQandSMandLunarMonthCode($yy);
		$runyue = 0; //若闰月旗标为0代表无闰月
		for ($j = 1; $j <= 14; $j++) {
			//确认指定年前一年11月开始各月是否闰月
			if ($mc[$j] - floor($mc[$j]) > 0) {
				//若是，则将此闰月代码放入闰月旗标内
				$runyue = floor($mc[$j] + 0.5);
				//$runyue=0对应阴历11月,1对应阴历12月，2对应阴历隔年1月，依此类推。
				break;
			}
		}
		$mx = $mm + 2; //11月对应到1，12月对应到2，1月对应到3，2月对应到4，依此类推
		//求算阴历各月之大小，大月30天，小月29天
		for ($i = 0; $i <= 14; $i++) {
			$nofd[$i] = floor($sjd[$i + 1] + 0.5) - floor($sjd[$i] + 0.5); //每月天数,加0.5是因JD以正午起算
		}
		$er = 0; //若输入值有错误，er值将被设定为1
		if ($mx >= 3 && $mx <= 14) {
			//输入月份必须在1-12月之内
			if ($ry == true) {
				//若有勾选闰月
				if ($runyue < 3) {
					//而旗标非闰月或非本年闰月，则表示此年不含闰月
					//$runyue=0代表无闰月,=1代表闰月为前一年的11月,=2代表闰月为前一年的12月
					$er = 1;
					$this->logs(7); //此年非闰年
				} else {
					//若本年内有闰月
					if ($runyue != $mx) { //但不为输入的月份
						$er = 1; //则此输入的月份非闰月
						$this->logs(8); //此月非闰月
					} else { //若输入的月份即为闰月
						$dd = $nofd[$mx]; //当月的天数
					}
				}
			} else {
				//若没有勾选闰月则
				if ($runyue == 0) { //若旗标非闰月，则表示此年不含闰月(包括前一年的11月起之月份)
					$dd = $nofd[$mx - 1]; //当月的天数
				} else { //若旗标为本年有闰月(包括前一年的11月起之月份)
					//公式nofd($mx - ($mx > $runyue) - 1)的用意为:若指定月大于闰月，则索引用mx，否则索引用mx-1
					$dd = $nofd[$mx + ($mx > $runyue) - 1]; //当月的天数
				}
			}
		} else {
			//月份错误
			$er = 1;
			$this->logs(6);
		}
		return $er ? false : $dd;
	}

	/**
	 * 获取农历某年的闰月,0为无闰月
	 * @param  int   $yy
	 * @return int
	 */
	public function GetRunyue($yy) {
		$yy = intval($yy);

		$mc     = $this->GetZQandSMandLunarMonthCode($yy);
		$runyue = 0; //若闰月旗标为0代表无闰月
		for ($j = 1; $j <= 14; $j++) {
			//确认指定年前一年11月开始各月是否闰月
			if ($mc[$j] - floor($mc[$j]) > 0) {
				//若是，则将此闰月代码放入闰月旗标内
				$runyue = floor($mc[$j] + 0.5);
				//$runyue=0对应阴历11月,1对应阴历12月，2对应阴历隔年1月，依此类推。
				break;
			}
		}
		return max(0, $runyue - 2);
	}

	/**
	 * 根据公历年月日精确计算星座下标
	 * @param  int       $yy
	 * @param  int       $mm
	 * @param  int       $dd
	 * @param  int       hh               时间(0-23)
	 * @param  int       mt               分钟数(0-59)
	 * @param  int       ss               秒数(0-59)
	 * @return int|false $this->cxz[xz]
	 */
	public function GetXZ($yy, $mm, $dd, $hh, $mt = 0, $ss = 0) {
		$yy = intval($yy);
		$mm = intval($mm);
		$dd = intval($dd);
		$hh = intval($hh);
		$mt = intval($mt);
		$ss = intval($ss);

		if ($this->ValidDate($yy, $mm, $dd) === false) {
			return false;
		}

		$spcjd = $this->Jdays($yy, $mm, $dd, $hh, $mt, $ss); //special jd
		if ($spcjd === false) {
			return false;
		}

		//显示星座,根据公历的中气判断
		$zr = $this->GetZQsinceWinterSolstice($yy);
		if ($spcjd < $zr[0]) {
			$zr = $this->GetZQsinceWinterSolstice($yy - 1);
		} //若小于雨水，则归前一年
		for ($i = 0; $i <= 13; $i++) {
			//先找到指定时刻前后的中气月首
			if ($spcjd < $zr[$i]) {
				$xz = ($i + 12 - 1) % 12;
				break;
			} //即为指定时刻所在的节气月首JD值
		}
		return $xz;
	}

	/**
	 * 求出含某公历年立春点开始的24节气的儒略日历时间
	 * @param  int   $yy
	 * @return array $this->jq[(i+21)%24]
	 */
	public function Get24JQ($yy) {
		$yy = intval($yy);

		$yea   = $yy - 1;
		$sjdjq = $this->GetAdjustedJQ($yea, 21, 3); //求出含指定年立春开始之3个节气JD值,以前一年的年值代入
		//转移春分前之立春至惊蛰之节气至jdpjq变数中，以重整index
		$jdpjq[0] = $sjdjq[22]; //此为立春
		$jdpjq[1] = $sjdjq[23]; //此为雨水
		$jdpjq[2] = $sjdjq[24]; //此为惊蛰
		$yea      = $yy;
		$sjdjq    = $this->GetAdjustedJQ($yea, 0, 21); //求出指定年节气之JD值,从春分开始，到大寒
		//转移春分至大寒之节气至jdpjq变数中，以重整index
		for ($i = 1; $i <= 21; $i++) {
			$jdpjq[$i + 2] = $sjdjq[$i];
		}
		return $jdpjq;
	}

	/**
	 * 四柱计算,分早子时晚子时,传公历
	 * @param  int         $yy
	 * @param  int         $mm      [1-12]
	 * @param  int         $dd
	 * @param  int         hh
	 * @param  int         mt       分钟数(0-59),在跨节的时辰上会需要,有的排盘忽略跨节
	 * @param  int         ss       秒数(0-59)
	 * @return false/array (天干, 地支)
	 */
	public function GetGZ($yy, $mm, $dd, $hh, $mt = 0, $ss = 0) {
		$yy = floatval($yy);
		$mm = floatval($mm);
		$dd = floatval($dd);
		$hh = floatval($hh);
		$mt = floatval($mt);
		$ss = floatval($ss);

		if ($mt + $ss == 0) {
			//避免整点模糊
			$ss = 10;
		}

		if ($this->ValidDate($yy, $mm, $dd) === false) {
			return false;
		}

		$spcjd = $this->Jdays($yy, $mm, $dd, $hh, $mt, $ss);
		if ($spcjd === false) {
			return false;
		}
		//比较求算节气年ty,求出年干支
		$jr = [];
		$ty = $yy;
		$jr = $this->GetPureJQsinceSpring($yy); //取得自立春开始的非中气之24节气
		if ($spcjd < $jr[0]) { //jr[0]为立春，约在2月5日前后，
			$ty = $yy - 1; //若小于jr[0],则属于前一个节气年
			$jr = $this->GetPureJQsinceSpring($ty); //取得自立春开始的不含中气之12节气
		}
		$tg    = [];
		$dz    = [];
		$ygz   = (($ty + 4712 + 24) % 60 + 60) % 60;
		$tg[0] = $ygz % 10; //年干
		$dz[0] = $ygz % 12; //年支

		//比较求算节气月,求出月干支
		for ($j = 0; $j <= 13; $j++) {
			if ($jr[$j] >= $spcjd) {
				$tm = $j - 1;
				break;
			} //已超过指定时刻，故应取前一个节气
		}
		$tmm   = (($ty + 4712) * 12 + ($tm) + 60) % 60;
		$mgz   = ($tmm + 50) % 60;
		$tg[1] = $mgz % 10; //月干
		$dz[1] = $mgz % 12; //月支

		//计算日柱之干支
		$jda   = $spcjd + 0.5; //加0.5是将起始点从正午改为从0点开始
		$thes  = (($jda - floor($jda)) * 86400) + 3600; //将jd的小数部份化为秒，并加上起始点前移的一小时(3600秒)，取其整数值
		$dayjd = floor($jda) + $thes / 86400; //将秒数化为日数，加回到jd的整数部份
		$dgz   = (floor($dayjd + 49) % 60 + 60) % 60;
		$tg[2] = $dgz % 10; //日干
		$dz[2] = $dgz % 12; //日支
		if ($this->zwz && ($hh >= 23)) {
			//区分早晚子时,日柱前移一柱
			$tg[2] = ($tg[2] + 10 - 1) % 10;
			$dz[2] = ($dz[2] + 12 - 1) % 12;
		}

		//计算时柱之干支
		$dh    = $dayjd * 12;
		$hgz   = (floor($dh + 48) % 60 + 60) % 60;
		$tg[3] = $hgz % 10; //时干
		$dz[3] = $hgz % 12; //时支

		return [$tg, $dz];
	}

	/**
	 * 根据年干支计算所有合法的月干支
	 * @param  int   $ygz                       年柱干支代码
	 * @return array 月柱干支代码列表
	 */
	public function MGZ($ygz) {
		$mgz = [];

		//$ygz = array_search($ygz, $this->gz);

		$nv = 2 + 12 * ($ygz % 10);
		for ($i = 0; $i <= 11; $i++) {
			$pv       = ($i + $nv) % 60;
			$mgz[$pv] = $this->gz[$pv];
		}
		return $mgz;
	}

	/**
	 * 根据日干支计算所有合法的时干支
	 * @param  int   $dgz                       日柱干支代码
	 * @return array 时柱干支代码列表
	 */
	public function HGZ($dgz) {
		$hgz = [];

		//$dgz = array_search($dgz, $this->gz);

		$nv = 12 * ($dgz % 10);
		for ($i = 0; $i <= 11; $i++) {
			$pv       = ($i + $nv) % 60;
			$hgz[$pv] = $this->gz[$pv];
		}
		return $hgz;
	}

	/**
	 * 根据一柱天干地支代码计算该柱的六十甲子代码
	 * @param  int       $tg            天干代码
	 * @param  int       $dz            地支代码
	 * @return false/int 干支代码
	 */
	public function GZ($tg, $dz) {
		$tg = intval($tg);
		$dz = intval($dz);

		if ($tg < 0 || $tg > 59) {
			$this->logs(3, 11);
			return false;
		}

		if ($dz < 0 || $dz > 59) {
			$this->logs(3, 12);
			return false;
		}

		if (($tg % 2) != ($dz % 2)) {
			//偶数对偶数,奇数对奇数才能组成一柱
			$this->logs(3, 13);
			return false;
		}
		return ((10 + $tg - $dz) % 10) / 2 * 12 + $dz;
	}

	/**
	 * 根据八字干支查找对应的公历日期(GanZhi To GongLi),这里没有考虑早晚子时
	 * @param int ygz
	 * @param int mgz
	 * @param int dgz
	 * @param int hgz
	 * @param int yeai  起始年 year initial
	 * @param int mx    查找多少个甲子
	 */
	public function gz2gl($ygz, $mgz, $dgz, $hgz, $yeai, $mx) {
		$ygz  = intval($ygz);
		$mgz  = intval($mgz);
		$dgz  = intval($dgz);
		$hgz  = intval($hgz);
		$yeai = intval($yeai);
		$mx   = intval($mx);

		if ($ygz < 0 || $ygz >= 60) {
			//年干支非六十甲子
			$this->logs(3, 0);
			return false;
		}
		if ($mgz < 0 || $mgz >= 60) {
			//月干支非六十甲子
			$this->logs(3, 1);
			return false;
		}
		if ($dgz < 0 || $dgz >= 60) {
			//日干支非六十甲子
			$this->logs(3, 2);
			return false;
		}
		if ($hgz < 0 || $hgz >= 60) {
			//时干支非六十甲子
			$this->logs(3, 3);
			return false;
		}

		if (!key_exists($mgz, $this->MGZ($ygz))) {
			//对应的月干支不存在
			$this->logs(2, 0);
			return false;
		}
		if (!key_exists($hgz, $this->HGZ($dgz))) {
			//对应的时干支不存在
			$this->logs(2, 1);
			return false;
		}
		$yeaf = $yeai + $mx * 60;

		if ($yeai < -1000 || $yeaf > 3000) {
			//说明大误差区域:适用于西元-1000年至西元3000年,超出此范围误差较大
			$this->logs(1);
			return false;
		}

		$ifs = []; //initial-final 返回一个含起止时间的数组

		for ($m = 0; $m <= $mx - 1; $m++) {
			$yea = $yeai + $m * 60;

			//将年月干支对应到指定年的节气月起始时刻
			$syc  = ($yea + 56) % 60; //已知公元0年为庚申年,庚申的六十甲子代码为56,这里求得yea的六十甲子代码syc
			$asyc = ($ygz + 60 - $syc) % 60; //年干支代码相对yea干支代码偏移了多少
			$iy   = $yea + $asyc; //加上偏移即得一个ygz年

			$jdpjq = $this->GetPureJQsinceSpring($iy); //该年的立春开始的节
			$mgzo  = ($mgz + 60 - 2) % 12; //已知干支代码,要求干支名,只需将干支代码除以10,所得的余数即为天干的代码;将干支代码除以12,所得的余数即为地支的代码.这里求得mgz在第几个月
			$ijd   = $jdpjq[$mgzo]; // 节气月头JD initial jd
			$fjd   = $jdpjq[$mgzo + 1]; // 节气月尾JD final jd

			$sdc  = (floor($ijd) + 49) % 60; // 节气月头的日干支代码,儒略日历时间0日为癸丑日,六十甲子代码为49
			$asdc = ($dgz + 60 - $sdc) % 60; // 生日相对于节气月头的日数
			$idd  = floor($ijd + $asdc); // 生日JD值(未加上时辰)
			$ihh  = $hgz % 12; // 时辰代码
			$id   = $idd + ($ihh * 2 - 13) / 24;
			$fd   = $idd + ($ihh * 2 - 11) / 24;

			if ($fd < $ijd || $id > $fjd) {
				//此八字在此60年中不存在

			} else {
				if ($id > $ijd && $fd < $fjd) {
					//没有跨节
					$ids = $id;
					$fds = $fd;
				}
				if ($id < $ijd && $fd > $ijd) {
					//同一个时辰跨越了节:在节气月头,只包含时辰后段
					$ids = $ijd;
					$fds = $fd;
				}
				if ($id < $fjd && $fd > $fjd) {
					//同一个时辰跨越了节:在节气月尾,只包含时辰前段
					$ids = $id;
					$fds = $fjd;
				}
				$ifs[] = [$this->Jtime($ids), $this->Jtime($fds)]; //儒略日历时间转成公历时间
			}
		}
		return $ifs;
	}

	/**
	 * 根据公历年月日计算命盘信息 fate:命运 map:图示
	 * @param  int           $xb   性别0男1女
	 * @param  int           $yy
	 * @param  int           $mm
	 * @param  int           $dd
	 * @param  int           $hh   时间(0-23)
	 * @param  int           $mt   分钟数(0-59),在跨节的时辰上会需要,有的排盘忽略了跨节,不需要考虑跨节则请把时间置为对应时辰的初始值
	 * @param  int           $ss   秒数(0-59),在跨节的时辰上会需要,有的排盘忽略了跨节
	 * @return false/array
	 */
	public function fatemaps($xb, $yy, $mm, $dd, $hh, $mt = 0, $ss = 0) {
		$xb = intval($xb) ? 1 : 0; //确保准确
		$yy = intval($yy);
		$mm = intval($mm);
		$dd = intval($dd);
		$hh = intval($hh);
		$mt = intval($mt);
		$ss = intval($ss);

		//说明大误差区域
		if ($yy < -1000 || $yy > 3000) {
			//适用于西元-1000年至西元3000年,超出此范围误差较大
			$this->logs(1);
			return false;
		}

		$spcjd = $this->Jdays($yy, $mm, $dd, $hh, $mt, $ss); //special jd
		if ($spcjd === false) {
			return false;
		}

		[$yy, $mm, $dd, $hh, $mt, $ss] = $this->Jtime($spcjd); //假设hh传了>24的数字,此处修正

		$ta = 365.24244475; //一个迴归年的天数

		$rt  = []; //要返回的数组 return
		$nwx = [0, 0, 0, 0, 0]; //五行数量 number of WuXing 这里不计算藏干里的
		$nyy = [0, 0]; //阴阳数量 number of YinYang 这里不计算藏干里的

		$szs = [1, 6, 10, 9, 10, 9, 7, 0, 4, 3]; //日干对地支为"子"者所对应的运程代码

		$ty = $yy;
		$jr = $this->GetPureJQsinceSpring($ty); //取得自立春开始的非中气之24节气
		if ($spcjd < $jr[0]) { //jr[0]为立春，约在2月5日前后，
			$ty = $yy - 1; //若小于jr[0],则属于前一个节气年
			$jr = $this->GetPureJQsinceSpring($ty); //取得自立春开始的非中气之12节气
		}

		[$tg, $dz] = $this->GetGZ($yy, $mm, $dd, $hh, $mt, $ss);

		//计算年月日时辰等四柱干支的阴阳属性和个数及五行属性和个数
		$yytg  = []; //YinYang TianGan
		$yydz  = []; //YinYang DiZhi
		$ewxtg = []; //各天干对应的五行
		$ewxdz = []; //各地支对应的五行
		for ($k = 0; $k <= 3; $k++) {
			//yytg:八字各柱天干之阴阳属性,yydz:八字各柱地支之阴阳属性，nyy[0]为阳之总数，nyy[1]为阴之总数
			$yytg[$k]       = $tg[$k] % 2;
			$nyy[$yytg[$k]] = $nyy[$yytg[$k]] + 1; //求天干的阴阳并计算阴阳总数

			$yydz[$k]       = $dz[$k] % 2;
			$nyy[$yydz[$k]] = $nyy[$yydz[$k]] + 1; //求地支的阴阳并计算阴阳总数

			$ewxtg[$k]       = $this->wxtg[$tg[$k]];
			$nwx[$ewxtg[$k]] = $nwx[$ewxtg[$k]] + 1; //wxtg为天干之五行属性

			$ewxdz[$k]       = $this->wxdz[$dz[$k]];
			$nwx[$ewxdz[$k]] = $nwx[$ewxdz[$k]] + 1; //wxdz为地支之五行属性
		}

		$rt['nyy'] = $nyy; //阴阳数量
		$rt['nwx'] = $nwx; //五行数量

		$rt['yytg'] = $yytg; //各天干对应的阴阳
		$rt['yydz'] = $yydz; //各地支对应的阴阳

		$rt['ewxtg'] = $ewxtg; //各天干对应的五行
		$rt['ewxdz'] = $ewxdz; //各地支对应的五行

		//日主与地支藏干决定十神
		$bzcg = []; //各地支的藏干
		$wxcg = []; //各地支的藏干对应的五行
		$yycg = []; //各地支的藏干对应的阴阳
		$bctg = []; //各地支的藏干对应的文字
		for ($i = 0; $i <= 3; $i++) {
			//0,1,2,3等四个
			$wxcg[$i] = [];
			$yycg[$i] = [];
			for ($j = 0; $j <= 2; $j++) {
				//0,1,2等三个
				$nzcg = $this->zcg[$dz[$i]][$j]; //取得藏干表中的藏干代码,zcg为一 4X3 之array
				if ($nzcg >= 0) {
					//若存在则取出(若为-1，则代表空白)
					$bctg[3 * $i + $j] = $this->ctg[$nzcg]; //暂存其干支文字
					$bzcg[3 * $i + $j] = $this->sss[$this->dgs[$nzcg][$tg[2]]]; //暂存其所对应之十神文字

					$wxcg[$i][$j] = $this->wxtg[$nzcg]; //其五行属性
					$yycg[$i][$j] = $nzcg % 2; //其阴阳属性
				} else {
					$bctg[3 * $i + $j] = ''; //若nzcg为-1，则代表空白，设定藏干文字变数为空白
					$bzcg[3 * $i + $j] = ''; //若nzcg为-1，则代表空白，设定十神文字变数为空白
				}
			}
		}

		$rt['bctg'] = $bctg;
		$rt['bzcg'] = $bzcg;
		$rt['wxcg'] = $wxcg;
		$rt['yycg'] = $yycg;

		//求算起运时刻
		for ($i = 0; $i <= 14; $i++) {
			//先找到指定时刻前后的节气月首
			if ($jr[$i] > $spcjd) {
				$ord = $i - 1;
				break;
			} //ord即为指定时刻所在的节气月首JD值
		}
		$xf = $spcjd - $jr[$ord]; //xf代表节气月的前段长，单位为日，以指定时刻为分界点
		$yf = $jr[$ord + 1] - $spcjd; //yf代表节气月的后段长
		if ((($xb == 0) && ($yytg[0] == 0)) || (($xb == 1) && ($yytg[0] == 1))) {
			$zf = $ta * 10 * ($yf / ($yf + $xf)); //zf为指定日开始到起运日之间的总日数(精确法)
			//$zf = 360 * 10 * ($yf / 30); //zf为指定日开始到起运日之间的总日数(粗略法）三天折合一年,一天折合四个月,一个时辰折合十天,一个小时折合五天,反推得到一年按360天算,一个月按30天算
			$forward = 0; //阳年男或阴年女，其大运是顺推的
		} else {
			$zf = $ta * 10 * ($xf / ($yf + $xf)); //阴年男或阳年女,其大运是逆推的
			//$zf = 360 * 10 * ($xf / 30); //(粗略法)
			$forward = 1;
		}
		$qyt = $spcjd + $zf; //起运时刻为指定时刻加上推算出的10年内比例值zf
		$jt  = $this->Jtime($qyt); //将起运时刻的JD值转换为年月日时分秒
		$qyy = $jt[0]; //起运年(公历)

		$rt['qyy']      = $qyy; //起运年
		$rt['qyy_desc'] = '出生后' . intval($zf / $ta) . '年' . intval($zf % $ta / ($ta / 12)) . '个月' . intval($zf % $ta % ($ta / 12)) . '天起运'; //一年按ta天算,一个月按ta/12天算

		//求算起运年(指节气年,农历)
		$qjr = $this->GetPureJQsinceSpring($qyy); //取得自立春开始的非中气之12节气
		if ($qyt >= $qjr[0]) {
			//qjr[0]为立春，约在2月5日前后，
			$jqyy = $qyy;
		} else {
			$jqyy = $qyy - 1; //若小于jr[0],则属于前一个节气年
		}

		//求算起运年及其后第五年的年干支及起运岁
		$jtd             = (($jqyy + 4712 + 24) % 10 + 10) % 10;
		$jtd             = $this->ctg[(($jqyy + 4712 + 24) % 10 + 10) % 10] . ' ' . $this->ctg[(($jqyy + 4712 + 24 + 5) % 10 + 10) % 10];
		$rt['qyy_desc2'] = '每逢 ' . $jtd . ' 年' . $jt[1] . '月' . $jt[2] . '日交大运'; //显示每十年为一阶段之起运时刻，分两个五年以年天干和阳历日期表示
		$qage            = $jqyy - $ty; //起运年减去出生年再加一即为起运之岁数,从怀胎算起,出生即算一岁

		$rt['dy'] = []; //大运

		//下面的回圈计算起迄岁，大运干支(及其对应的十神)，衰旺吉凶
		$zqage = []; //起始岁数
		$zboz  = []; //末端岁数
		$zfman = []; //大运月干代码
		$zfmbn = []; //大运月支代码
		$zfma  = []; //大运月干文字
		$zfmb  = []; //大运月支文字
		$nzs   = []; //大运对应的十二长生
		$mgz   = ((10 + $tg[1] - $dz[1]) % 10) / 2 * 12 + $dz[1]; //这里是根据天干地支代码计算月柱的六十甲子代码
		for ($k = 0; $k <= 8; $k++) {
			//求各阶段的起迄岁数及该阶段的大运
			// if (! is_array($rt['dy'][$k])) {
			$rt['dy'][$k] = [];
			// }
			//求起迄岁
			$rt['dy'][$k]['zqage'] = $zqage[$k] = $qage + 1 + $k * 10; //求各阶段的起始岁数
			$rt['dy'][$k]['zboz']  = $zboz[$k]  = $qage + 1 + $k * 10 + 9; //求各阶段的末端岁数

			//排大运
			//求大运的数值表示值,以出生月份的次月干支开始顺排或以出生月份的前一个月干支开始逆排
			//大运月干
			$rt['dy'][$k]['zfman'] = $zfman[$k] = ($mgz + 60 + pow(-1, $forward) * ($k + 1)) % 10; //加60是为保证在Mod之前必为正数
			//大运月支
			$rt['dy'][$k]['zfmbn'] = $zfmbn[$k] = ($mgz + 60 + pow(-1, $forward) * ($k + 1)) % 12; //加60是为保证在Mod之前必为正数

			$rt['dy'][$k]['zfma'] = $zfma[$k] = $this->ctg[$zfman[$k]];
			$rt['dy'][$k]['zfmb'] = $zfmb[$k] = $this->cdz[$zfmbn[$k]];

			//算衰旺吉凶ncs
			//szs(tg(2))为日干对大运地支为"子"者所对应之运程代码
			//tg(2)为生日天干(以整数0~11表示)之代码
			//(-1)^tg(2)表示若日干为阳则取加号,若日干为阴则取减号
			//第一个大运之地支数值为zfmbn(0)
			//下式中szs(tg(2)) + (-1) ^ tg(2) * (zfmbn(0))为决定起始运势,(-1) ^ forward * (-1) ^ tg(2) 为决定顺推或逆推,可合并简化为次一式
			$rt['dy'][$k]['nzs']  = $nzs[$k]  = (24 + $szs[$tg[2]] + pow(-1, $tg[2]) * ($zfmbn[0] + pow(-1, $forward) * $k)) % 12;
			$rt['dy'][$k]['nzsc'] = $this->czs[$nzs[$k]];
			//此处加24是为了使Mod之前总值不为负值
		}

		//求流年的数值表示值及对应的文字
		$lyean = []; //流年天干
		$lyebn = []; //流年地支
		$lye   = []; //流年所对应的干支文字
		for ($j = 0; $j <= 89; $j++) {
			$k = intval($j / 10); //大运
			$i = $j % 10; //流年
			// if (! is_array($rt['dy'][$k]['ly'])) { //大运对应的流年
			//     $rt['dy'][$k]['ly'] = array();
			// }
			// if (! is_array($rt['dy'][$k]['ly'][$i])) {
			$rt['dy'][$k]['ly'][$i] = [];
			// }

			//lyean[j]=(ygz + j + qage) % 10;
			$rt['dy'][$k]['ly'][$i]['age']   = $j + $qage + 1; //年龄(虚岁)
			$rt['dy'][$k]['ly'][$i]['year']  = $j + $qage + $jqyy; //流年(农历)
			$rt['dy'][$k]['ly'][$i]['lyean'] = $lyean[$j] = ($tg[0] + $j + $qage) % 10; //流年天干
			$rt['dy'][$k]['ly'][$i]['lyebn'] = $lyebn[$j] = ($dz[0] + $j + $qage) % 12; //流年地支
			$rt['dy'][$k]['ly'][$i]['lye']   = $lye[$j]   = $this->ctg[$lyean[$j]] . $this->cdz[$lyebn[$j]]; //取流年所对应的干支文字
		}

		//显示星座,根据公历的中气判断
		$zr = $this->GetZQsinceWinterSolstice($yy);
		if ($spcjd < $zr[0]) {
			$zr = $this->GetZQsinceWinterSolstice($yy - 1);
		} //若小于雨水，则归前一年
		for ($i = 0; $i <= 13; $i++) {
			//先找到指定时刻前后的中气月首
			if ($spcjd < $zr[$i]) {
				$xz = ($i + 12 - 1) % 12;
				break;
			} //即为指定时刻所在的节气月首JD值
		}

		$rt['mz']  = $this->mz[$xb]; //命造乾坤
		$rt['xb']  = $this->xb[$xb]; //性别0男1女
		$rt['gl']  = [$yy, $mm, $dd]; //公历生日
		$rt['nl']  = $this->Solar2Lunar($yy, $mm, $dd); //农历生日
		$rt['tg']  = $tg; //八字天干数组
		$rt['dz']  = $dz; //八字地支数组
		$rt['sz']  = []; //四柱字符
		$rt['ctg'] = []; //天干字符
		$rt['cdz'] = []; //地支字符
		for ($i = 0; $i <= 3; $i++) {
			$rt['sz'][$i]  = $this->ctg[$tg[$i]] . $this->cdz[$dz[$i]];
			$rt['ctg'][$i] = $this->ctg[$tg[$i]];
			$rt['cdz'][$i] = $this->cdz[$dz[$i]];
		}
		$rt['sx']  = $this->csx[$dz[0]]; //生肖,与年地支对应
		$rt['xz']  = $this->cxz[$xz]; //星座
		$rt['cyy'] = $this->cyy[$yytg[2]]; //日干阴阳

		return $rt;
	}
}
