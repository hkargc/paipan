<?php
/**
 * @author hkargc@139.com
 * 本日历及排盘类完全源于以下项目,本人仅作为代码搬运工,感谢项目作者的无私分享
 * 日历部分由bieyu.com搬运而来,其提供了详尽的历法转换原理,JS源码及部分PHP源码,项目地址: http://www.bieyu.com/
 * 农历校正部分来自寿星万年历,感谢福建莆田第十中学许剑伟老师,项目地址: http://www.nongli.net/sxwnl/
 */
class paipan{
    /**
	 * 标准时间发出地经度(角度表示,东经为正西经为负),北京时间的经度为+120度0分
	 */
    private $J = 120;
	/**
	 * 默认纬度(角度表示,北纬为正南纬为负),这里是中国标准时间发出地(陕西省渭南市蒲城县)
	 */
	private $W = 35;
    /**
     * 缓存每年的节气计算结果
     */
    private $JQ = [];
    /**
     * 均值朔望月長 synodic month (new Moon to new Moon)
     */
    private $synmonth = 29.530588853;
    /**
     * 四柱是否区分 早晚子 时,true则23:00-24:00算成上一天
     */
    public $zwz = true;
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
        '甲寅', '乙卯', '丙辰', '丁巳', '戊午', '己未', '庚申', '辛酉', '壬戌', '癸亥'
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
     * 天干的五行屬性,01234分別代表:金水木火土
     */
    public $wxtg = [2, 2, 3, 3, 4, 4, 0, 0, 1, 1];
    /**
     * 地支的五行屬性,01234分別代表:金水木火土
     */
    public $wxdz = [1, 4, 2, 2, 4, 3, 3, 4, 0, 0, 4, 1];
    /**
     * 十神全称
     */
    public $ssq = ['正印', '偏印', '比肩', '劫財', '傷官', '食神', '正財', '偏財', '正官', '偏官'];
    /**
     * 十神缩写
     */
    public $sss = ['印', '卩', '比', '劫', '伤', '食', '财', '才', '官', '杀'];
    /**
     * 日干關聯其餘各干對應十神 Day Gan ShiShen
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
        [0, 1, 8, 9, 6, 7, 4, 5, 3, 2]
    ];
    /**
     * 日干關聯各支對應十神 Day Zhi ShiShen
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
        [1, 0, 9, 8, 7, 6, 5, 4, 2, 3]
    ];
    /**
     * 十二星座 char of XingZuo
     */
    public $cxz = ['摩羯', '水瓶', '双鱼', '白羊', '金牛', '双子', '巨蟹', '狮子', '处女', '天秤', '天蝎', '射手'];
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
        [8, 0, -1]
    ];
    /**
     * 十二长生 char of ZhangSheng
     */
    public $czs = ["長生(強)", "沐浴(凶)", "冠帶(吉)", "臨官(大吉)", "帝旺(大吉)", "衰(弱)", "病(弱)", "死(凶)", "墓(吉)", "絕(凶)", "胎(平)", "養(平)"];
    public $yyss = ['異', '同'];
    public $sxss = ['生我', '同我', '我生', '我克', '克我'];
    /**
     * 方位 char of FangWei
     */
    public $cfw = ["　中　", "　北　", "北北東", "東北東", "　東　", "東南東", "南南東", "　南　", "南南西", "西南西", "　西　", "西北西", "北北西"];
    /**
     * 四季 char of SiJi
     */
    public $csj = ["旺四季", "　春　", "　夏　", "　秋　", "　冬　"];
    /**
     * 天干的方位屬性 FangWei TianGan
     */
    public $fwtg = [4, 4, 7, 7, 0, 0, 10, 10, 1, 1];
    /**
     * 地支的方位屬性 FangWei DiZhi
     */
    public $fwdz = [1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11, 12];
    /**
     * 天干的四季屬性 SiJi TianGan
     */
    public $sjtg = [1, 1, 2, 2, 0, 0, 3, 3, 4, 4];
    /**
     * 地支的四季屬性 SiJi DiZhi
     */
    public $sjdz = [1, 1, 2, 2, 2, 3, 3, 3, 4, 4, 4, 1];
    /**
     * 记录日志
     * @$string s
     */
    private function logs($n, $s=null) {
        $m = array();
        $m[0] = "超出計算能力";
        $m[1] = "適用於西元-1000年至西元3000年,超出此範圍誤差較大";
        $m[2] = "对应的干支不存在";
        $m[3] = "干支非六十甲子";
        $m[4] = "日期超出範圍";
        $m[5] = "日期錯誤";
        $m[6] = "月份錯誤";
        $m[7] = "此年非閏年";
        $m[8] = "此月非閏月";
        $m[9] = "不存在的时间";
        $m[10] = "参数非整数字符串";
        $m[11] = "参数非整数类型";
        $m[12] = "参数非浮点类型";
        $m[13] = "月份超出範圍";
        $m[14] = "此年無閏月";
        $m[15] = "参数非整数";
        $m[16] = "参数非数字";
        if ($this->debug) {
            $ss = $m[$n] ? $m[$n] : $n;
            $ss .= ($s === null) ? '' : (":" . $s);
            
            echo $ss;
        }
        return false;
    }
    /**
	 * 真太阳时模块,cn代表cosine
	 */
	private function cn($x) {
		return cos($x * 1.74532925199433E-02);
	}
	/**
	 * 真太阳时模块,sn代表sin
	 */
	private function sn($x) {
		return sin($x * 1.74532925199433E-02);
	}
	/**
	 * 真太阳时模块,返回小数部分(负数特殊) returns fractional part of a number
	 */
	private function fpart($x) {
		$x = $x - floor($x);
		if ($x < 0) {
			$x = $x + 1;
		}
		return $x; //只取小数部份
	}
	/**
	 * 真太阳时模块,只取整数部份
	 */
	private function ipart($x) {
		return ($x / abs($x)) * floor(abs($x));
	}
	/**
	 * 真太阳时模块,finds a parabola through three points and returns values of coordinates of extreme value (xe, ye) and zeros if any (z1, z2) assumes that the x values are -1, 0, +1
	 */
	private function quad($ym, $y0, $yp) {
		$nz = 0;
		$A = 0.5 * ($ym + $yp) - $y0;
		$b = 0.5 * ($yp - $ym);
		$c = $y0;
		$xe = -$b / (2 * $A); //x coord of symmetry line
		$ye = ($A * $xe + $b) * $xe + $c; //extreme value for y in interval
		$dis = $b * $b - 4 * $A * $c; //discriminant
		if ($dis > 0) { //there are zeros
			$dx = 0.5 * sqrt($dis) / abs($A);
			$z1 = $xe - $dx;
			$z2 = $xe + $dx;
			if (abs($z1) <= 1) {
				$nz = $nz + 1;
			} //This zero is in interval
			if (abs($z2) <= 1) {
				$nz = $nz + 1;
			} //This zero is in interval
			if ($z1 < -1) {
				$z1 = $z2;
			}
		}
		return array($xe, $ye, $z1, $z2, $nz);
	}
	/**
	 * 真太阳时模块,returns sine of the altitude of either the sun or the moon given the modified julian day number at midnight UT and the hour of the UT day
	 */
	private function sinalt($instant, $J, $W) {
		$t = ($instant - 51544.5) / 36525; //sun: Returns RA and DEC of Sun to roughly 1 arcmin for few hundred years either side of J2000.0
		$p2 = 2 * M_PI;
		$COSEPS = 0.91748;
		$SINEPS = 0.39778;
		$m = $p2 * $this->fpart(0.993133 + 99.997361 * $t); //Mean anomaly
		$dL = 6893 * sin($m) + 72 * sin(2 * $m); //Eq centre
		$L = $p2 * $this->fpart(0.7859453 + $m / $p2 + (6191.2 * $t + $dL) / 1296000);
		
		$sl = sin($L); //convert to RA and DEC - ecliptic latitude of Sun taken as zero
		$x = cos($L);
		$y = $COSEPS * $sl;
		$Z = $SINEPS * $sl;
		$rho = sqrt(1 - $Z * $Z);
		$dec = (360 / $p2) * atan($Z / $rho);
		$ra = (48 / $p2) * atan($y / ($x + $rho));
		if ($ra < 0) {
			$ra = $ra + 24;
		}
		
		$mjd = $this->ipart($instant); //lmst: returns the local siderial time for the instant and longitude specified
		$ut = ($instant - $mjd) * 24;
		$t2 = ($mjd - 51544.5) / 36525;
		$gmst = 6.697374558 + 1.0027379093 * $ut;
		$gmst = $gmst + (8640184.812866 + (0.093104 - 0.0000062 * $t2) * $t2) * $t2 / 3600;
		$lmst = 24 * $this->fpart(($gmst - $J / 15) / 24); //取得观测地区的恒星时
		$tau = 15 * ($lmst - $ra); //hour angle of object
		$sinho = $this->sn(-50 / 60); //sunrise - classic value for refraction
		return $this->sn($W) * $this->sn($dec) + $this->cn($W) * $this->cn($dec) * $this->cn($tau) - $sinho;
	}
	/**
	 * 真太阳时模块,改编自 https://bieyu.com/ (月亮與太陽出没時間) 原理:用天文方法计算出太阳升起和落下时刻,中间则为当地正午(自创),与12点比较得到时差;与寿星万年历比较,两者相差在20秒内
	 * @param float jd
	 * @param float J 经度
	 * @param float W 纬度,太阳并不是严格从正东方升起,所以纬度也有影响,只是相对影响较小
	 */
	private function zty($jd, $J, $W=null) {
		$jd = floatval($jd);
		$J = (is_null($J) === true) ? -1 * $this->J : -1 * floatval($J); //此模块西经为正 routines use east longitude negative convention
		$W = (is_null($W) === true) ? +1 * $this->W : +1 * floatval($W); //北纬为正,南纬为负
		
		$thedate = round($jd) - 2400001 - $this->J / 360; //儒略日中午12点,減去2400001再減去8小時時差,再减51544.5为2000年01月01日零点
		
		$rise = 0; //是否有升起动作
		$utrise = 0; //升起的时间
		
		$sett = 0; //是否有落下动作
		$utset = 0; //落下的时间

		$hour = 1; //起始时间
		$zero2 = 0; //两小时内是否进行了升起和落下两个动作(极地附近有这种情况,如1999年12月25日,经度0,纬度67.43,当天的太阳只有8分钟-_-)
		
		$ym = $this->sinalt($thedate + ($hour - 1) / 24, $J, $W); //See STEP 1 and 2 of Web page description.
		$above = ($ym > 0) ? 1 : 0; //used later to classify non-risings 是否在地平线上方
		
		do { //STEP 1 and STEP 3 of Web page description
			$y0 = $this->sinalt($thedate + ($hour + 0) / 24, $J, $W);
			$yp = $this->sinalt($thedate + ($hour + 1) / 24, $J, $W);
			
			[$xe, $ye, $z1, $z2, $nz] = $this->quad($ym, $y0, $yp); //STEP 4 of web page description 大概是三点确定一条抛物线?
			switch ($nz) { //cases depend on values of discriminant - inner part of STEP 4
				case 0: //nothing  - go to next time slot
				break; 
				case 1: //simple rise / set event
					if ($ym < 0) { //must be a rising event
						$rise = 1;
						$utrise = $hour + $z1;
					} else { //must be setting
						$sett = 1;
						$utset = $hour + $z1;
					}
				break;
				case 2: //rises and sets within interval
					if ($ye < 0) { //minimum - so set then rise
						$utrise = $hour + $z2;
						$utset = $hour + $z1;
					} else { //maximum - so rise then set
						$utrise = $hour + $z1;
						$utset = $hour + $z2;
					}
					$rise = 1;
					$sett = 1;
					$zero2 = 1;
				break;
			}
			$ym = $yp; //reuse the ordinate in the next interval
			$hour = $hour + 2;
		} while (($hour < 25) && ($rise * $sett == 0)); //STEP 5 of Web page description - have we finished for this object?
		if($rise * $sett == 0){ //极昼极夜存在此种情况(above==1极昼,above==0极夜)
			return $jd;
		}
		while($utset < $utrise){ //太阳先升起再落下,时区与经度不匹配的情况下会出现此种情况
			$utset += 24;
		}
		$noon = $utrise + ($utset - $utrise) / 2; //太阳升起和落下时刻,中点则为当地正午
		return $jd - ($noon - 12) / 24; //与12点比较得到时差,单位由小时转为天
	}
    /**
     * 將公历年月日時轉换爲儒略日历时间
     * @param int $yy
     * @param int $mm
     * @param int $dd
     * @param int $hh
     * @param int $mt
     * @param int $ss
     * @return false|number
     */
    public function Jdays($yy, $mm, $dd, $hh, $mt = 0, $ss = 0) {
        $yy = floatval($yy);
        $mm = floatval($mm);
        $dd = floatval($dd);
        $hh = floatval($hh);
        $mt = floatval($mt);
        $ss = floatval($ss);
        if ($yy < -7000 || $yy > 7000) { //超出計算能力
            $this->logs(0);
            return false;
        }
        $yp = $yy + floor(($mm - 3) / 10);
        if (($yy > 1582) || ($yy == 1582 && $mm > 10) || ($yy == 1582 && $mm == 10 && $dd >= 15)) {
            $init = 1721119.5;
            $jdy = floor($yp * 365.25) - floor($yp / 100) + floor($yp / 400);
        } else {
            if (($yy < 1582) || ($yy == 1582 && $mm < 10) || ($yy == 1582 && $mm == 10 && $dd <= 4)) {
                $init = 1721117.5;
                $jdy = floor($yp * 365.25);
            } else { //不存在的时间
                $this->logs(9);
                return false;
            }
        }
        $mp = floor($mm + 9) % 12;
        $jdm = $mp * 30 + floor(($mp + 1) * 34 / 57);
        $jdd = $dd - 1;
        $hh = $hh + (($ss / 60) + $mt) / 60;
        $jdh = $hh / 24;
        $jd = $jdy + $jdm + $jdd + $jdh + $init;
        return $jd;
    }
    /**
     * 將儒略日轉换爲公历(即陽曆或格里曆)年月日時分秒
     * @param float $jd
     * @return array(年,月,日,时,分,秒)
     */
    public function Jtime($jd) {
        $jd = floatval($jd);
        if ($jd >= 2299160.5) { //以1582年的10月15日0時(JD值2299160.5)為分界點,在這之前為儒略曆,之後為格里曆
            $y4h = 146097;
            $init = 1721119.5;
        } else {
            $y4h = 146100;
            $init = 1721117.5;
        }
        $jdr = floor($jd - $init);
        $yh = $y4h / 4;
        $cen = floor(($jdr + 0.75) / $yh);
        $d = floor($jdr + 0.75 - $cen * $yh);
        $ywl = 1461 / 4;
        $jy = floor(($d + 0.75) / $ywl);
        $d = floor($d + 0.75 - $ywl * $jy + 1);
        $ml = 153 / 5;
        $mp = floor(($d - 0.5) / $ml);
        $d = floor(($d - 0.5) - 30.6 * $mp + 1);
        $y = (100 * $cen) + $jy;
        $m = ($mp + 2) % 12 + 1;
        if ($m < 3) {
            $y = $y + 1;
        }
        $sd = floor(($jd + 0.5 - floor($jd + 0.5)) * 24 * 60 * 60 + 0.00005);
        $mt = floor($sd / 60);
        $ss = $sd % 60;
        $hh = floor($mt / 60);
        $mmt = $mt % 60;
        $yy = floor($y);
        $mm = floor($m);
        $dd = floor($d);
        
        return array($yy, $mm, $dd, $hh, $mmt, $ss);
    }
    /**
     * 驗證公历日期是否有效
     * @param int $yy
     * @param int $mm
     * @param int $dd
     * @return boolean
     */
    public function ValidDate($yy, $mm, $dd) {
        $vd = true;
        if ($mm <= 0 || $mm > 12) { //月份超出範圍
            $this->logs(13);
            $vd = false;
        } else {
            $ndf1 = -($yy % 4 == 0); //可被四整除
            $ndf2 = (($yy % 400 == 0) - ($yy % 100 == 0)) && ($yy > 1582);
            $ndf = $ndf1 + $ndf2;
            $dom = 30 + ((abs($mm - 7.5) + 0.5) % 2) - ($mm == 2) * (2 + $ndf);
            if ($dd <= 0 || $dd > $dom) {
                if ($ndf == 0 && $mm == 2 && $dd == 29) { //此年無閏月
                    $this->logs(14);
                } else { //日期超出範圍
                    $this->logs(4);
                }
                $vd = false;
            }
        }
        if ($yy == 1582 && $mm == 10 && $dd >= 5 && $dd < 15) { //此日期不存在
            $this->logs(9);
            $vd = false;
        }
        return $vd;
    }
    /**
     * 计算指定年(公历)的春分点(vernal equinox)理论值
     * 因地球在繞日运行時會因受到其他星球之影響而產生攝動(perturbation),必須將此現象產生的偏移量加入.
     * @param int $yy
     * @return false|number 返回儒略日历时间
     */
    private function VE($yy) {
        $yx = intval($yy);
        if ($yx >= 1000 && $yx <= 8001) {
            $m = ($yx - 2000) / 1000;
            $jdve = 2451623.80984 + 365242.37404 * $m + 0.05169 * $m * $m - 0.00411 * $m * $m * $m - 0.00057 * $m * $m * $m * $m;
        } else {
            if ($yx >= -8000 && $yx < 1000) {
                $m = $yx / 1000;
                $jdve = 1721139.29189 + 365242.1374 * $m + 0.06134 * $m * $m + 0.00111 * $m * $m * $m - 0.00071 * $m * $m * $m * $m;
            } else { //超出计算能力范围
                $this->logs(0);
                return false;
            }
        }
        return $jdve;
    }
    /**
     * 获取指定公历年的春分开始的24节气理论值
     * 大致原理是:把公转轨道进行24等分,每一等分为一个节气,此为理论值,再用摄动值(Perturbation)和固定参数DeltaT做调整得到实际值
     * @param int $yy
     * @return array 下标从0开始的数组
     */
    private function MeanJQJD($yy) {
        $yy = intval($yy);
        
        $jdez = array();
        $jdve = $this->VE($yy);
        $ty = $this->VE($yy + 1) - $jdve; //求指定年的春分點及回歸年長
        
        $ath = 2 * M_PI / 24;
        $tx = ($jdve - 2451545) / 365250;
        $e = 0.0167086342 - 0.0004203654 * $tx - 0.0000126734 * $tx * $tx + 0.0000001444 * $tx * $tx * $tx - 0.0000000002 * $tx * $tx * $tx * $tx + 0.0000000003 * $tx * $tx * $tx * $tx * $tx;
        $tt = $yy / 1000;
        $vp = 111.25586939 - 17.0119934518333 * $tt - 0.044091890166673 * $tt * $tt - 4.37356166661345E-04 * $tt * $tt * $tt + 8.16716666602386E-06 * $tt * $tt * $tt * $tt;
        $rvp = $vp * 2 * M_PI / 360;
        $peri = array();
        for ($i = 1; $i <= 24; $i++) {
            $flag = 0;
            $th = $ath * ($i - 1) + $rvp;
            if ($th > M_PI && $th <= 3 * M_PI) {
                $th = 2 * M_PI - $th;
                $flag = 1;
            }
            if ($th > 3 * M_PI) {
                $th = 4 * M_PI - $th;
                $flag = 2;
            }
            $f1 = 2 * atan((sqrt((1 - $e) / (1 + $e)) * tan($th / 2)));
            $f2 = ($e * sqrt(1 - $e * $e) * sin($th)) / (1 + $e * cos($th));
            $f = ($f1 - $f2) * $ty / 2 / M_PI;
            if ($flag == 1) {
                $f = $ty - $f;
            }
            if ($flag == 2) {
                $f = 2 * $ty - $f;
            }
            $peri[$i] = $f;
        }
        for ($i = 1; $i <= 24; $i++) {
            $jdez[$i - 1] = $jdve + $peri[$i] - $peri[1];
        }
        return $jdez;
    }
    /**
     * 地球在繞日运行時會因受到其他星球之影響而產生攝動(perturbation)
     * @param float $jdez Julian day
     * @return number 返回某时刻(儒略日历)的攝動偏移量
     */
    private function Perturbation($jdez) {
        $jdez = floatval($jdez);
        $ptsa = [485, 203, 199, 182, 156, 136, 77, 74, 70, 58, 52, 50, 45, 44, 29, 18, 17, 16, 14, 12, 12, 12, 9, 8];
        $ptsb = [324.96, 337.23, 342.08, 27.85, 73.14, 171.52, 222.54, 296.72, 243.58, 119.81, 297.17, 21.02, 247.54, 325.15, 60.93, 155.12, 288.79, 198.04, 199.76, 95.39, 287.11, 320.81, 227.73, 15.45];
        $ptsc = [1934.136, 32964.467, 20.186, 445267.112, 45036.886, 22518.443, 65928.934, 3034.906, 9037.513, 33718.147, 150.678, 2281.226, 29929.562, 31555.956, 4443.417, 67555.328, 4562.452, 62894.029, 31436.921, 14577.848, 31931.756, 34777.259, 1222.114, 16859.074];
        $t = ($jdez - 2451545) / 36525;
        $s = 0;
        for ($k = 0; $k <= 23; $k++) {
            $s = $s + $ptsa[$k] * cos($ptsb[$k] * 2 * M_PI / 360 + $ptsc[$k] * 2 * M_PI / 360 * $t);
        }
        $w = 35999.373 * $t - 2.47;
        $l = 1 + 0.0334 * cos($w * 2 * M_PI / 360) + 0.0007 * cos(2 * $w * 2 * M_PI / 360);
        return 0.00001 * $s / $l;
    }
    /**
     * 求∆t
     * @param int $yy 公历年份
     * @param int $mm 公历月份
     * @return number 单位为分钟
     */
    private function DeltaT($yy, $mm) {
        $yy = intval($yy);
        $mm = intval($mm);
        
        $y = $yy + ($mm - 0.5) / 12;
        if ($y <= -500) {
            $u = ($y - 1820) / 100;
            $dt = (-20 + 32 * $u * $u);
        } else {
            if ($y < 500) {
                $u = $y / 100;
                $dt = (10583.6 - 1014.41 * $u + 33.78311 * $u * $u - 5.952053 * $u * $u * $u - 0.1798452 * $u * $u * $u * $u + 0.022174192 * $u * $u * $u * $u * $u + 0.0090316521 * $u * $u * $u * $u * $u * $u);
            } else {
                if ($y < 1600) {
                    $u = ($y - 1000) / 100;
                    $dt = (1574.2 - 556.01 * $u + 71.23472 * $u * $u + 0.319781 * $u * $u * $u - 0.8503463 * $u * $u * $u * $u - 0.005050998 * $u * $u * $u * $u * $u + 0.0083572073 * $u * $u * $u * $u * $u * $u);
                } else {
                    if ($y < 1700) {
                        $t = $y - 1600;
                        $dt = (120 - 0.9808 * $t - 0.01532 * $t * $t + $t * $t * $t / 7129);
                    } else {
                        if ($y < 1800) {
                            $t = $y - 1700;
                            $dt = (8.83 + 0.1603 * $t - 0.0059285 * $t * $t + 0.00013336 * $t * $t * $t - $t * $t * $t * $t / 1174000);
                        } else {
                            if ($y < 1860) {
                                $t = $y - 1800;
                                $dt = (13.72 - 0.332447 * $t + 0.0068612 * $t * $t + 0.0041116 * $t * $t * $t - 0.00037436 * $t * $t * $t * $t + 0.0000121272 * $t * $t * $t * $t * $t - 0.0000001699 * $t * $t * $t * $t * $t * $t + 0.000000000875 * $t * $t * $t * $t * $t * $t * $t);
                            } else {
                                if ($y < 1900) {
                                    $t = $y - 1860;
                                    $dt = (7.62 + 0.5737 * $t - 0.251754 * $t * $t + 0.01680668 * $t * $t * $t - 0.0004473624 * $t * $t * $t * $t + $t * $t * $t * $t * $t / 233174);
                                } else {
                                    if ($y < 1920) {
                                        $t = $y - 1900;
                                        $dt = (-2.79 + 1.494119 * $t - 0.0598939 * $t * $t + 0.0061966 * $t * $t * $t - 0.000197 * $t * $t * $t * $t);
                                    } else {
                                        if ($y < 1941) {
                                            $t = $y - 1920;
                                            $dt = (21.2 + 0.84493 * $t - 0.0761 * $t * $t + 0.0020936 * $t * $t * $t);
                                        } else {
                                            if ($y < 1961) {
                                                $t = $y - 1950;
                                                $dt = (29.07 + 0.407 * $t - $t * $t / 233 + $t * $t * $t / 2547);
                                            } else {
                                                if ($y < 1986) {
                                                    $t = $y - 1975;
                                                    $dt = (45.45 + 1.067 * $t - $t * $t / 260 - $t * $t * $t / 718);
                                                } else {
                                                    if ($y < 2005) {
                                                        $t = $y - 2000;
                                                        $dt = (63.86 + 0.3345 * $t - 0.060374 * $t * $t + 0.0017275 * $t * $t * $t + 0.000651814 * $t * $t * $t * $t + 0.00002373599 * $t * $t * $t * $t * $t);
                                                    } else {
                                                        if ($y < 2050) {
                                                            $t = $y - 2000;
                                                            $dt = (62.92 + 0.32217 * $t + 0.005589 * $t * $t);
                                                        } else {
                                                            if ($y < 2150) {
                                                                $u = ($y - 1820) / 100;
                                                                $dt = (-20 + 32 * $u * $u - 0.5628 * (2150 - $y));
                                                            } else {
                                                                $u = ($y - 1820) / 100;
                                                                $dt = (-20 + 32 * $u * $u);
                                                            }
                                                        }
                                                    }
                                                }
                                            }
                                        }
                                    }
                                }
                            }
                        }
                    }
                }
            }
        }
        if ($y < 1955 || $y >= 2005) {
            $dt = $dt - (0.000012932 * ($y - 1955) * ($y - 1955));
        }
        return $dt / 60; //將秒轉換為分
    }
    /**
     * 获取指定公历年對Perturbaton作調整後的自春分點開始的24節氣
     * @param int $yy
     * @return array $this->jq[i%24]
     */
    public function GetAdjustedJQ($yy) {
        $yy = intval($yy);
        
        if($this->JQ && $this->JQ[$yy]){
            return $this->JQ[$yy];
        }
        
        $jdjq = array();
        $jdez = $this->MeanJQJD($yy); //輸入指定年,求該回歸年各節氣点
        for ($i = 0; $i < 24; $i++) {
            $ptb = $this->Perturbation($jdez[$i]); //取得受perturbation影響所需微調
            $dt = $this->DeltaT($yy, ceil($i / 2) + 3); //修正dynamical time to Universal time
            $jdjq[$i] = $jdez[$i] + $ptb - $dt / 60 / 24; //加上攝動調整值ptb，減去對應的Delta T值(分鐘轉換為日)
            $jdjq[$i] = $jdjq[$i] + 8 / 24; //因中國時間比格林威治時間先行8小時，即1/3日(由于农历基于此数据,此处必须为北京时间)
        }
        
        $this->JQ[$yy] = $jdjq;
        
        return $jdjq;
    }
    /**
     * 求出自冬至點為起點的連續16個中氣
     * @param int $yy
     * @return array $this->jq[(2*i+18)%24]
     */
    private function GetZQsinceWinterSolstice($yy) {
        $yy = intval($yy);
        
        $jdzq = array();
        
        //求出以冬至為起點之連續16個中氣（多取四個以備用）
        $dj = array();
        $dj = $this->GetAdjustedJQ($yy - 1); //求出指定年冬至開始之節氣JD值,以前一年的值代入
        //轉移春分前之節氣至jdzq變數中，以重整index
        $jdzq[0] = $dj[18]; //此為冬至中氣
        $jdzq[1] = $dj[20]; //此為大寒中氣
        $jdzq[2] = $dj[22]; //此為雨水中氣
        $dj = $this->GetAdjustedJQ($yy); //求出指定年節氣之JD值
        for ($i = 0; $i < 12; $i++) {
            $jdzq[$i + 3] = $dj[2 * $i]; //轉移冬至後之節氣至jdzq變數中，以重整index
        }
        $dj = $this->GetAdjustedJQ($yy + 1); //求出指定年節氣之JD值
        $jdzq[15] = $dj[0]; //此為春分中氣
        
        return $jdzq;
    }
    /**
     * 求出某公历年以立春點開始的不含中氣之12節
     * @param int $yy
     * @return array $this->jq[(2*i+21)%24]
     */
    private function GetPureJQsinceSpring($yy) {
        $yy = intval($yy);
        
        $jdpjq = array();
        $sjdjq = $this->GetAdjustedJQ($yy - 1); //求出含指定年立春開始之3個節氣JD值,以前一年的年值代入
        //轉移春分前之立春至驚蟄之節氣至jdpjq變數中，以重整index
        $jdpjq[0] = $sjdjq[21]; //此為立春
        $jdpjq[1] = $sjdjq[23]; //此為驚蟄
        $sjdjq = $this->GetAdjustedJQ($yy); //求出指定年節氣之JD值,從驚蟄開始，到雨水
        //轉移春分至小寒之節氣至jdpjq變數中，以重整index
        for ($i = 0; $i < 12; $i++) {
            $jdpjq[$i + 2] = $sjdjq[2 * $i + 1];
        }
        $sjdjq = $this->GetAdjustedJQ($yy + 1);
        $jdpjq[14] = $sjdjq[1]; //此為清明
        
        return $jdpjq;
    }
    /**
     * 對於指定日期時刻所屬的朔望月,求出其均值新月點的月序數
     * @param float $jd
     * @return int
     */
    private function MeanNewMoon($jd) {
        $jd = floatval($jd);
        
        //k為從2000年1月6日14時20分36秒起至指定年月日之陰曆月數,以synodic month為單位
        $k = floor(($jd - 2451550.09765) / $this->synmonth); //2451550.09765為2000年1月6日14時20分36秒之JD值。
        $jdt = 2451550.09765 + $k * $this->synmonth;
        //Time in Julian centuries from 2000 January 0.5.
        $t = ($jdt - 2451545) / 36525; //以100年為單位,以2000年1月1日12時為0點
        $thejd = $jdt + 0.0001337 * $t * $t - 0.00000015 * $t * $t * $t + 0.00000000073 * $t * $t * $t * $t;
        //2451550.09765為2000年1月6日14時20分36秒，此為2000年後的第一個均值新月
        return $k;
    }
    /**
     * 求出實際新月點
     * 以2000年初的第一個均值新月點為0點求出的均值新月點和其朔望月之序數k代入此副程式來求算實際新月點
     * @param int $k
     * @return number
     */
    private function TrueNewMoon($k) {
        $k = intval($k);
        
        $jdt = 2451550.09765 + $k * $this->synmonth;
        $t = ($jdt - 2451545) / 36525; //2451545為2000年1月1日正午12時的JD
        $t2 = $t * $t; //square for frequent use
        $t3 = $t2 * $t; //cube for frequent use
        $t4 = $t3 * $t; //to the fourth
        //mean time of phase
        $pt = $jdt + 0.0001337 * $t2 - 0.00000015 * $t3 + 0.00000000073 * $t4;
        //Sun's mean anomaly(地球繞太陽運行均值近點角)(從太陽觀察)
        $m = 2.5534 + 29.10535669 * $k - 0.0000218 * $t2 - 0.00000011 * $t3;
        //Moon's mean anomaly(月球繞地球運行均值近點角)(從地球觀察)
        $mprime = 201.5643 + 385.81693528 * $k + 0.0107438 * $t2 + 0.00001239 * $t3 - 0.000000058 * $t4;
        //Moon's argument of latitude(月球的緯度參數)
        $f = 160.7108 + 390.67050274 * $k - 0.0016341 * $t2 - 0.00000227 * $t3 + 0.000000011 * $t4;
        //Longitude of the ascending node of the lunar orbit(月球繞日運行軌道升交點之經度)
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
        return $pt + $apt1 + $apt2;
    }
    /**
     * 求算以含冬至中氣為陰曆11月開始的連續16個朔望月
     * @param int $yy 公历年份
     * @return array
     */
    private function GetSMsinceWinterSolstice($yy) {
        $yy = intval($yy);
        
        $dj = $this->GetAdjustedJQ($yy - 1); //求出指定年冬至開始之節氣JD值,以前一年的值代入
        //轉移春分前之節氣至jdzq變數中，以重整index
        $jdws = $dj[18]; //此為冬至中氣
        
        $jdnm = array();
        $spcjd = $this->Jdays($yy - 1, 11, 0, 0); //求年初前兩個月附近的新月點(即前一年的11月初)
        $kn = $this->MeanNewMoon($spcjd); //求得自2000年1月起第kn個平均朔望日及其JD值
        for ($i = 0; $i <= 19; $i++) { //求出連續20個朔望月
            $k = $kn + $i;
            $tjd[$i] = $this->TrueNewMoon($k) + 8 / 24; //以k值代入求瞬時朔望日,因中國比格林威治先行8小時，加1/3天 (农历为中华文化,无需做真太阳时调整)
            //下式為修正dynamical time to Universal time
            $tjd[$i] = $tjd[$i] - $this->DeltaT($yy, $i - 1) / 1440; //1為1月，0為前一年12月，-1為前一年11月(當i=0時，i-1=-1，代表前一年11月)
        }
        for ($j = 0; $j <= 18; $j++) {
            if (floor($tjd[$j] + 0.5) > floor($jdws + 0.5)) {
                break;
            } //已超過冬至中氣(比較日期法)
        }
        $XFu = array( //修复 XiuFu 使农历1800年至2300年与寿星万年历匹配
            1804 => [8 => -310],
            1831 => [4 => -61],
            1842 => [1 => -904],
            1863 => [1 => -107],
            1880 => [11 => 293],
            1896 => [2 => -751],
            1914 => [12 => -102],
            1916 => [2 => -315],
            1920 => [11 => -289]
        );
        $jj = $j; //取此時的索引值
        for ($k = 0; $k <= 15; $k++) {
            $jdnm[$k] = $tjd[$jj - 1 + $k]; //重排索引，使含冬至朔望月的索引為0
            if($XFu[$yy] && $XFu[$yy][$k]){
                $jdnm[$k] += $XFu[$yy][$k] / 86400;
            }
        }
        return $jdnm;
    }
    /**
     * 以比較日期法求算冬月及其餘各月名稱代碼，包含閏月，冬月為0，臘月為1，正月為2，餘類推。閏月多加0.5
     * @param int $yy
     */
    private function GetZQandSMandLunarMonthCode($yy) {
        $yy = intval($yy);
        
        $mc = array();
        $jdzq = $this->GetZQsinceWinterSolstice($yy); //取得以前一年冬至為起點之連續17個中氣
        $jdnm = $this->GetSMsinceWinterSolstice($yy); //求出以含冬至中氣為陰曆11月(冬月)開始的連續16個朔望月的新月點
        $yz = 0; //設定旗標，0表示未遇到閏月，1表示已遇到閏月
        $mc[0] = 0;
        if (floor($jdzq[12] + 0.5) >= floor($jdnm[13] + 0.5)) { //若第13個中氣jdzq(12)大於或等於第14個新月jdnm(13)
            for ($i = 1; $i <= 14; $i++) { //表示此兩個冬至之間的11個中氣要放到12個朔望月中，
                //至少有一個朔望月不含中氣，第一個不含中氣的月即為閏月
                //若陰曆臘月起始日大於冬至中氣日，且陰曆正月起始日小於或等於大寒中氣日，則此月為閏月，其餘同理
                if (floor(($jdnm[$i] + 0.5) > floor($jdzq[$i - 1 - $yz] + 0.5) && floor($jdnm[$i + 1] + 0.5) <= floor($jdzq[$i - $yz] + 0.5))) {
                    $mc[$i] = $i - 0.5;
                    $yz = 1; //標示遇到閏月
                } else {
                    $mc[$i] = $i - $yz; //遇到閏月開始，每個月號要減1
                }
            }
        } else { //否則表示兩個連續冬至之間只有11個整月，故無閏月
            for ($i = 1; $i <= 12; $i++) { //直接賦予這12個月月代碼
                $mc[$i] = $i;
            }
            for ($i = 13; $i <= 14; $i++) { //處理次一置月年的11月與12月，亦有可能含閏月
                //若次一陰曆臘月起始日大於附近的冬至中氣日，且陰曆正月起始日小於或等於大寒中氣日，則此月為閏月，次一正月同理。
                if (floor(($jdnm[$i] + 0.5) > floor($jdzq[$i - 1 - $yz] + 0.5) && floor($jdnm[$i + 1] + 0.5) <= floor($jdzq[$i - $yz] + 0.5))) {
                    $mc[$i] = $i - 0.5;
                    $yz = 1; //標示遇到閏月
                } else {
                    $mc[$i] = $i - $yz; //遇到閏月開始，每個月號要減1
                }
            }
        }
        return $mc;
    }
    /**
     * 将农历时间转换成公历时间
     * @param int $yy
     * @param int $mm
     * @param int $dd
     * @param boolean $ry 是否闰月
     * @return false/array(年,月,日)
     */
    public function Lunar2Solar($yy, $mm, $dd, $ry) { //此為將陰曆日期轉換為陽曆日期的主程式
        $yy = intval($yy);
        $mm = intval($mm);
        $dd = intval($dd);
        $ry = boolval($ry);
        
        //限定範圍
        if ($yy < -7000 || $yy > 7000) { //超出計算能力
            $this->logs(0);
            return false;
        }
        if ($yy < -1000 || $yy > 3000) { //適用於西元-1000年至西元3000年,超出此範圍誤差較大
            $this->logs(1);
        }
        $sjd = $this->GetSMsinceWinterSolstice($yy); //求出以含冬至中氣為陰曆11月(冬月)開始的連續16個朔望月的新月點
        $mc = $this->GetZQandSMandLunarMonthCode($yy);
        $runyue = 0; //若閏月旗標為0代表無閏月
        for ($j = 1; $j <= 14; $j++) { //確認指定年前一年11月開始各月是否閏月
            if ($mc[$j] - floor($mc[$j]) > 0) { //若是，則將此閏月代碼放入閏月旗標內
                $runyue = floor($mc[$j] + 0.5);
                //runyue=0對應陰曆11月,1對應陰曆12月，2對應陰曆隔年1月，依此類推。
                break;
            }
        }
        $mx = $mm + 2; //11月對應到1，12月對應到2，1月對應到3，2月對應到4，依此類推
        //求算陰曆各月之大小，大月30天，小月29天
        for ($i = 0; $i <= 14; $i++) {
            $nofd[$i] = floor($sjd[$i + 1] + 0.5) - floor($sjd[$i] + 0.5); //每月天數,加0.5是因JD以正午起算
        }
        $er = 0; //若輸入值有錯誤，er值將被設定為1
        if ($mx >= 3 && $mx <= 14) { //輸入月份必須在1-12月之內
            if ($dd >= 1 && $dd <= 30) { //輸入日期必須在1-30日之內
                if ($ry == true) { //若有勾選閏月
                    if ($runyue < 3) { //而旗標非閏月或非本年閏月，則表示此年不含閏月
                        //runyue=0代表無閏月,=1代表閏月為前一年的11月,=2代表閏月為前一年的12月
                        $er = 1;
                        $this->logs(7); //此年非閏年
                    } else { //若本年內有閏月
                        if ($runyue != $mx) { //但不為輸入的月份
                            $er = 1; //則此輸入的月份非閏月
                            $this->logs(8); //此月非閏月
                        } else { //若輸入的月份即為閏月
                            if ($dd <= $nofd[$mx]) { //若輸入的日期不大於當月的天數
                                $jdx = $sjd[$mx] + $dd - 1; //則將當月之前的JD值加上日期之前的天數
                            } else { //日期超出範圍
                                $er = 1;
                                $this->logs(4);
                            }
                        }
                    }
                } else { //若沒有勾選閏月則
                    if ($runyue == 0) { //若旗標非閏月，則表示此年不含閏月(包括前一年的11月起之月份)
                        if ($dd <= $nofd[$mx - 1]) { //若輸入的日期不大於當月的天數
                            $jdx = $sjd[$mx - 1] + $dd - 1; //則將當月之前的JD值加上日期之前的天數
                        } else { //日期超出範圍
                            $er = 1;
                            $this->logs(4);
                        }
                    } else { //若旗標為本年有閏月(包括前一年的11月起之月份)
                        //公式nofd(mx - (mx > runyue) - 1)的用意為:若指定月大於閏月，則索引用mx，否則索引用mx-1
                        if ($dd <= $nofd[$mx + ($mx > $runyue) - 1]) { //若輸入的日期不大於當月的天數
                            $jdx = $sjd[$mx + ($mx > $runyue) - 1] + $dd - 1; //則將當月之前的JD值加上日期之前的天數
                        } else { //日期超出範圍
                            $er = 1;
                            $this->logs(4);
                        }
                    }
                }
                if ($er == 0) { //若沒有錯誤，則印出陽曆年月日
                    
                }
            } else { //日期錯誤
                $er = 1;
                $this->logs(5);
            }
        } else { //月份錯誤
            $er = 1;
            $this->logs(6);
        }
        return $er ? false : array_slice($this->Jtime($jdx), 0, 3);
    }
    /**
     * 将公历时间转换成农历时间
     * @param int $yy
     * @param int $mm
     * @param int $dd
     * @return false/array(年,月,日,是否闰月)
     */
    public function Solar2Lunar($yy, $mm, $dd) {
        $yy = intval($yy);
        $mm = intval($mm);
        $dd = intval($dd);
        
        $flag = 0;
        
        //限定範圍
        if ($yy < -7000 || $yy > 7000) { //超出計算能力
            $this->logs(0);
            return false;
        }
        if ($yy < -1000 || $yy > 3000) { //適用於西元-1000年至西元3000年,超出此範圍誤差較大
            $this->logs(1);
        }
        //驗證輸入日期的正確性,若不正確則跳離
        if ($this->ValidDate($yy, $mm, $dd) === false) {
            return false;
        }
        $sjd = $this->GetSMsinceWinterSolstice($yy); //求出以含冬至中氣為陰曆11月(冬月)開始的連續16個朔望月的新月點
        $mc = $this->GetZQandSMandLunarMonthCode($yy);
        $jdx = $this->Jdays($yy, $mm, $dd, 12); //求出指定年月日之JD值
        if (floor($jdx) < floor($sjd[0] + 0.5)) {
            $flag = 1;
            $sjd = $this->GetSMsinceWinterSolstice($yy - 1); //求出以含冬至中氣為陰曆11月(冬月)開始的連續16個朔望月的新月點
            $mc = $this->GetZQandSMandLunarMonthCode($yy - 1);
        }
        for ($i = 0; $i <= 14; $i++) {
            //下面的指令中加0.5是為了改為從0時算起而不從正午算起
            if (floor($jdx) >= floor($sjd[$i] + 0.5) && floor($jdx) < floor($sjd[$i + 1] + 0.5)) {
                $mi = $i;
                break;
            }
        }
        $dz = floor($jdx) - floor($sjd[$mi] + 0.5) + 1; //此處加1是因為每月初一從1開始而非從0開始
        if ($mc[$mi] < 2 || $flag == 1) {
            $yi = $yy - 1;
        } else {
            $yi = $yy;
        } //因mc(mi)=0對應到前一年陰曆11月，mc(mi)=1對應到前一年陰曆12月
        //mc(mi)=2對應到本年1月，依此類推
        if (($mc[$mi] - floor($mc[$mi])) * 2 + 1 == 1) {
            $ry = false;
        } else {
            $ry = true;
        }
        $mis = (floor($mc[$mi] + 10) % 12) + 1; //對應到月份
        
        return array($yi, $mis, $dz, $ry);
    }
    /**
     * 计算公历的某天是星期几(PHP中的date方法,此处演示儒略日历的转换作用)
     * @param int $yy
     * @param int $mm
     * @param int $dd
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
        
        return (((floor($spcjd + 1) % 7)) + 7) % 7; //模數(或餘數)為0代表星期日(因为西元前4713年1月1日12時为星期一).spcjd加1是因起始日為星期一
    }
    /**
     * 获取公历某个月有多少天
     * @param int $yy
     * @param int $mm
     * @return number
     */
    public function GetSolarDays($yy, $mm){
        $yy = intval($yy);
        $mm = intval($mm);
        
        if ($mm < 1 || $mm > 12) { //月份超出範圍
            $this->logs(13);
            return 0;
        }
        if ($yy == 1582 && $mm == 10) { //这年这个月的5到14日不存在,所以1582年10月只有21天
            return 21;
        }
        $ndf1 = -($yy % 4 == 0); //可被四整除
        $ndf2 = (($yy % 400 == 0) - ($yy % 100 == 0)) && ($yy > 1582);
        $ndf = $ndf1 + $ndf2;
        return 30 + ((abs($mm - 7.5) + 0.5) % 2) - ($mm == 2) * (2 + $ndf);
    }
    /**
     * 获取农历某个月有多少天
     * @param int $yy
     * @param int $mm
     * @param bool $ry 是否闰月
     * @return false/number
     */
    public function GetLunarDays($yy, $mm, $ry){
        $yy = intval($yy);
        $mm = intval($mm);
        $ry = boolval($ry);
        
        //限定範圍
        if ($yy < -7000 || $yy > 7000) { //超出計算能力
            $this->logs(0);
            return false;
        }
        if ($yy < -1000 || $yy > 3000) { //適用於西元-1000年至西元3000年,超出此範圍誤差較大
            $this->logs(1);
        }
        $sjd = $this->GetSMsinceWinterSolstice($yy); //求出以含冬至中氣為陰曆11月(冬月)開始的連續16個朔望月的新月點
        $mc = $this->GetZQandSMandLunarMonthCode($yy);
        $runyue = 0; //若閏月旗標為0代表無閏月
        for ($j = 1; $j <= 14; $j++) { //確認指定年前一年11月開始各月是否閏月
            if ($mc[$j] - floor($mc[$j]) > 0) { //若是，則將此閏月代碼放入閏月旗標內
                $runyue = floor($mc[$j] + 0.5);
                //$runyue=0對應陰曆11月,1對應陰曆12月，2對應陰曆隔年1月，依此類推。
                break;
            }
        }
        $mx = $mm + 2; //11月對應到1，12月對應到2，1月對應到3，2月對應到4，依此類推
        //求算陰曆各月之大小，大月30天，小月29天
        for ($i = 0; $i <= 14; $i++) {
            $nofd[$i] = floor($sjd[$i + 1] + 0.5) - floor($sjd[$i] + 0.5); //每月天數,加0.5是因JD以正午起算
        }
        $er = 0; //若輸入值有錯誤，er值將被設定為1
        if ($mx >= 3 && $mx <= 14) { //輸入月份必須在1-12月之內
            if ($ry == true) { //若有勾選閏月
                if ($runyue < 3) { //而旗標非閏月或非本年閏月，則表示此年不含閏月
                    //$runyue=0代表無閏月,=1代表閏月為前一年的11月,=2代表閏月為前一年的12月
                    $er = 1;
                    $this->logs(7); //此年非閏年
                } else { //若本年內有閏月
                    if ($runyue != $mx) { //但不為輸入的月份
                        $er = 1; //則此輸入的月份非閏月
                        $this->logs(8); //此月非閏月
                    } else { //若輸入的月份即為閏月
                        $dd = $nofd[$mx]; //當月的天數
                    }
                }
            } else { //若沒有勾選閏月則
                if ($runyue == 0) { //若旗標非閏月，則表示此年不含閏月(包括前一年的11月起之月份)
                    $dd = $nofd[$mx - 1]; //當月的天數
                } else { //若旗標為本年有閏月(包括前一年的11月起之月份)
                    //公式nofd($mx - ($mx > $runyue) - 1)的用意為:若指定月大於閏月，則索引用mx，否則索引用mx-1
                    $dd = $nofd[$mx + ($mx > $runyue) - 1]; //當月的天數
                }
            }
        } else { //月份錯誤
            $er = 1;
            $this->logs(6);
        }
        return $er ? false : $dd;
    }
    /**
     * 获取农历某年的闰月,0为无闰月
     * @param int $yy
     * @return number
     */
    public function GetRunyue($yy){
        $yy = intval($yy);
        
        $mc = $this->GetZQandSMandLunarMonthCode($yy);
        $runyue = 0; //若閏月旗標為0代表無閏月
        for ($j = 1; $j <= 14; $j++) { //確認指定年前一年11月開始各月是否閏月
            if ($mc[$j] - floor($mc[$j]) > 0) { //若是，則將此閏月代碼放入閏月旗標內
                $runyue = floor($mc[$j] + 0.5);
                //$runyue=0對應陰曆11月,1對應陰曆12月，2對應陰曆隔年1月，依此類推。
                break;
            }
        }
        return max(0, $runyue-2);
    }
    /**
     * 根据公历年月日精确计算星座下标
     * @param int $yy
     * @param int $mm
     * @param int $dd
     * @param int hh 时间(0-23)
     * @param int mt 分钟数(0-59)
     * @param int ss 秒数(0-59)
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
        
        //顯示星座,根据公历的中气判断
        $zr = $this->GetZQsinceWinterSolstice($yy);
        if ($spcjd < $zr[0]) {
            $zr = $this->GetZQsinceWinterSolstice($yy - 1);
        } //若小於雨水，則歸前一年
        for ($i = 0; $i <= 13; $i++) { //先找到指定時刻前後的中氣月首
            if ($spcjd < $zr[$i]) {
                $xz = ($i + 12 - 1) % 12;
                break;
            } //即為指定時刻所在的節氣月首JD值
        }
        return $xz;
    }
    /**
     * 四柱計算,分早子时晚子时,传公历
     * @param int $yy
     * @param int $mm [1-12]
     * @param int $dd
     * @param int hh
     * @param int mt 分钟数(0-59),在跨节的时辰上会需要,有的排盘忽略跨节
     * @param int ss 秒数(0-59)
     * @return false/array(天干, 地支)
     */
    public function GetGZ($yy, $mm, $dd, $hh, $mt = 0, $ss = 0) {
        $yy = floatval($yy);
        $mm = floatval($mm);
        $dd = floatval($dd);
        $hh = floatval($hh);
        $mt = floatval($mt);
        $ss = floatval($ss);
        
		if($mt + $ss == 0){ //避免整点模糊
			$ss = 10;
		}
		
        if ($this->ValidDate($yy, $mm, $dd) === false) {
            return false;
        }
        
        $spcjd = $this->Jdays($yy, $mm, $dd, $hh, $mt, $ss);
        if ($spcjd === false) {
            return false;
        }
        //比較求算節氣年ty,求出年干支
        $jr = array();
        $ty = $yy;
        $jr = $this->GetPureJQsinceSpring($yy); //取得自立春開始的非中氣之24節氣
        if ($spcjd < $jr[0]) { //jr[0]為立春，約在2月5日前後，
            $ty = $yy - 1; //若小於jr[0],則屬於前一個節氣年
            $jr = $this->GetPureJQsinceSpring($ty); //取得自立春開始的不含中氣之12節氣
        }
        $tg = array();
        $dz = array();
        $ygz = (($ty + 4712 + 24) % 60 + 60) % 60;
        $tg[0] = $ygz % 10; //年干
        $dz[0] = $ygz % 12; //年支
        //比較求算節氣月,求出月干支
        for ($j = 0; $j <= 13; $j++) {
            if ($jr[$j] >= $spcjd) {
                $tm = $j - 1;
                break;
            } //已超過指定時刻，故應取前一個節氣
        }
        $tmm = (($ty + 4712) * 12 + ($tm) + 60) % 60;
        $mgz = ($tmm + 50) % 60;
        $tg[1] = $mgz % 10; //月干
        $dz[1] = $mgz % 12; //月支
        //計算日柱之干支
        $jda = $spcjd + 0.5; //加0.5是將起始點從正午改為從0點開始
        $thes = (($jda - floor($jda)) * 86400) + 3600; //將jd的小數部份化為秒，並加上起始點前移的一小時(3600秒)，取其整數值
        $dayjd = floor($jda) + $thes / 86400; //將秒數化為日數，加回到jd的整數部份
        $dgz = (floor($dayjd + 49) % 60 + 60) % 60;
        $tg[2] = $dgz % 10; //日干
        $dz[2] = $dgz % 12; //日支
        if ($this->zwz && ($hh >= 23)) { //区分早晚子时,日柱前移一柱
            $tg[2] = ($tg[2] + 10 - 1) % 10;
            $dz[2] = ($dz[2] + 12 - 1) % 12;
        }
        //計算時柱之干支
        $dh = $dayjd * 12;
        $hgz = (floor($dh + 48) % 60 + 60) % 60;
        $tg[3] = $hgz % 10; //時干
        $dz[3] = $hgz % 12; //時支
        
        return array($tg, $dz);
    }
    /**
     * 根据年干支计算所有合法的月干支
     * @param int $ygz 年柱干支代码
     * @return array 月柱干支代码列表
     */
    public function MGZ($ygz) {
        $ygz = intval($ygz);
        
        $mgz = array();
        
        //$ygz = array_search($ygz, $this->gz);
        
        $nv = 2 + 12 * ($ygz % 10);
        for ($i = 0; $i <= 11; $i++) {
            $pv = ($i + $nv) % 60;
            $mgz[$pv] = $this->gz[$pv];
        }
        return $mgz;
    }
    /**
     * 根据日干支计算所有合法的时干支
     * @param int $dgz 日柱干支代码
     * @return array 时柱干支代码列表
     */
    public function HGZ($dgz) {
        $dgz = intval($dgz);
        
        $hgz = array();
        
        //$dgz = array_search($dgz, $this->gz);
        
        $nv = 12 * ($dgz % 10);
        for ($i = 0; $i <= ($this->zwz?12:11); $i++) {
            $pv = ($i + $nv) % 60;
            $hgz[$pv] = $this->gz[$pv] . ($i == 12 ? "+" : ""); //+号在查找方法中要用到
        }
        return $hgz;
    }
    /**
     * 根据一柱天干地支代码计算该柱的六十甲子代码
     * @param int $tg 天干代码
     * @param int $dz 地支代码
     * @return false/int 干支代码
     */
    public function GZ($tg, $dz){
        $tg = intval($tg);
        $dz = intval($dz);
        
        if($tg < 0 || $tg > 59){
            $this->logs(3,11);
            return false;
        }
        
        if($dz < 0 || $dz > 59){
            $this->logs(3,12);
            return false;
        }
        
        if(($tg % 2) != ($dz % 2)){ //偶数对偶数,奇数对奇数才能组成一柱
            $this->logs(3,13);
            return false;
        }
        return ((10 + $tg - $dz) % 10) / 2 * 12 + $dz;
    }
    /**
     * 根据八字干支查找对应的公历日期(GanZhi To GongLi)
     * @param int ygz
     * @param int mgz
     * @param int dgz
     * @param int hgz
     * @param int yeai 起始年 year initial
     * @param int mx 查找多少个甲子
     * @return false/array
     */
    public function gz2gl($ygz, $mgz, $dgz, $hgz, $yeai, $mx) {
        $ygz = intval($ygz);
        $mgz = intval($mgz);
        $dgz = intval($dgz);
        $hgz = intval($hgz);
        $yeai = intval($yeai);
        $mx = intval($mx);
        
        if ($ygz < 0 || $ygz >= 60) { //年干支非六十甲子
            $this->logs(3,0);
            return false;
        }
        if ($mgz < 0 || $mgz >= 60) { //月干支非六十甲子
            $this->logs(3,1);
            return false;
        }
        if ($dgz < 0 || $dgz >= 60) { //日干支非六十甲子
            $this->logs(3,2);
            return false;
        }
        if ($hgz < 0 || $hgz >= 60) { //时干支非六十甲子
            $this->logs(3,3);
            return false;
        }
        
        if (! key_exists($mgz, $this->MGZ($ygz))) { //对应的月干支不存在
            $this->logs(2,0);
            return false;
        }
        if (! key_exists($hgz, $this->HGZ($dgz))) { //对应的时干支不存在
            $this->logs(2,1);
            return false;
        }
        $hgzs = $this->HGZ($dgz); //该日下所有时柱
        if($this->zwz && (substr($hgzs[$hgz], -1) == '+')){ //晚子时,日柱后挪一天
            $dgz = ($dgz + 1) % 60;
        }
        $yeaf = $yeai + $mx * 60;
        
        if ($yeai < -1000 || $yeaf > 3000) { //說明大誤差區域:適用於西元-1000年至西元3000年,超出此範圍誤差較大
            $this->logs(1);
        }
        
        $ifs = array(); //initial-final 返回一个含起止时间的数组
        
        for ($m = 0; $m <= $mx - 1; $m++) {
            $yea = $yeai + $m * 60;
            
            //將年月干支對應到指定年的節氣月起始時刻
            $syc = ($yea + 56) % 60; //已知公元0年为庚申年,庚申的六十甲子代码为56,这里求得yea的六十甲子代码syc
            $asyc = ($ygz + 60 - $syc) % 60; //年干支代码相对yea干支代码偏移了多少
            $iy = $yea + $asyc; //加上偏移即得一个ygz年
            
            $jdpjq = $this->GetPureJQsinceSpring($iy); //该年的立春开始的节
            $mgzo = ($mgz + 60 - 2) % 12; //已知干支代碼,要求干支名,只需將干支代碼除以10,所得的餘數即為天干的代碼;將干支代碼除以12,所得的餘數即為地支的代碼.这里求得mgz在第几个月
            $ijd = $jdpjq[$mgzo]; // 節氣月頭JD initial jd
            $fjd = $jdpjq[$mgzo + 1]; // 節氣月尾JD final jd
            
            $sdc = (floor($ijd) + 49) % 60; // 節氣月頭的日干支代碼,儒略日历时间0日为癸丑日,六十甲子代码为49
            $asdc = ($dgz + 60 - $sdc) % 60; // 生日相對於節氣月頭的日數
            $idd = floor($ijd + $asdc); // 生日JD值(未加上時辰)
            $ihh = $hgz % 12; // 時辰代碼
            $id = $idd + ($ihh * 2 - 13) / 24;
            $fd = $idd + ($ihh * 2 - 11) / 24;
            
            if ($fd < $ijd || $id > $fjd) { //此八字在此60年中不存在
                
            } else {
                if ($id > $ijd && $fd < $fjd) { //没有跨节
                    $ids = $id;
                    $fds = $fd;
                }
                if ($id < $ijd && $fd > $ijd) { //同一个时辰跨越了节:在節氣月頭,只包含時辰後段
                    $ids = $ijd;
                    $fds = $fd;
                }
                if ($id < $fjd && $fd > $fjd) { //同一个时辰跨越了节:在節氣月尾,只包含時辰前段
                    $ids = $id;
                    $fds = $fjd;
                }
                $ifs[] = [$this->Jtime($ids), $this->Jtime($fds)]; //儒略日历时间转成公历时间.如果开启早晚子并且是子时这里有点瑕疵,但考虑到跨节这里有点复杂
            }
        }
        return $ifs;
    }
    /**
     * 根据公历年月日计算命盘信息 fate:命运 map:图示
     * @param int $xb 性别0男1女
     * @param int $yy 年份.确保传的是$this->J对应的时间
     * @param int $mm 月份(1-12)
     * @param int $dd 日期(1-31)
     * @param int $hh 时间(0-23)
     * @param int $mt 分钟(0-59),在跨节的时辰上会需要,有的排盘忽略了跨节,不需要考虑跨节则请把时间置为对应时辰的初始值
     * @param int $ss 秒数(0-59),在跨节的时辰上会需要,有的排盘忽略了跨节
     * @param float $J 所在经度(角度表示)用于计算真太阳时,不传则用标准时间排盘
	 * @param float $W 所在纬度(角度表示)不传则默认北纬35度
     * @return false/array
     */
    public function fatemaps($xb, $yy, $mm, $dd, $hh, $mt = 0, $ss = 0, $J = null, $W = null) {
        $xb = intval($xb) ? 1 : 0; //确保准确
        $yy = intval($yy);
        $mm = intval($mm);
        $dd = intval($dd);
        $hh = intval($hh);
        $mt = intval($mt);
        $ss = intval($ss);

        //說明大誤差區域
        if ($yy < -1000 || $yy > 3000) { //適用於西元-1000年至西元3000年,超出此範圍誤差較大
            $this->logs(1);
        }
        
        $spcjd = $this->Jdays($yy, $mm, $dd, $hh, $mt, $ss); //special jd,这里依然是标准时间,即$this->J处的平太阳时
        if ($spcjd === false) {
            return false;
        }
        $rt = array(); //要返回的数组 return
        
        if(is_null($J) === false){ //需要转地方真太阳时
            $rt['pty'] = $spcjd + (floatval($J) - $this->J) * 4 * 60 / 86400; //计算地方平太阳时,每经度时差4分钟
            $rt['pty'] = $this->Jtime($rt['pty']); //地方平太阳时
            
            $spcjd = $this->zty($spcjd, $J, $W); //采用真太阳时排盘,这里有点疑问: 对应的廿四节气的计算是否也要转为真太阳时呢?
            $rt['zty'] = $this->Jtime($spcjd); //地方真太阳时
        }
        
        [$yy, $mm, $dd, $hh, $mt, $ss] = $this->Jtime($spcjd); //假设hh传了>24的数字,此处修正
        
        $ta = 365.24244475; //一個廻歸年的天數
        $nwx = [0, 0, 0, 0, 0]; //五行数量 number of WuXing 这里不计算藏干里的
        $nyy = [0, 0]; //阴阳数量 number of YinYang 这里不计算藏干里的
        
        $szs = [1,6,10,9,10,9,7,0,4,3]; //日干對地支爲"子"者所對應的運程代碼
        
        $ty = $yy;
        $jr = $this->GetPureJQsinceSpring($ty); //取得自立春開始的非中氣之24節氣
        if ($spcjd < $jr[0]) { //jr[0]為立春，約在2月5日前後，
            $ty = $yy - 1; //若小於jr[0],則屬於前一個節氣年
            $jr = $this->GetPureJQsinceSpring($ty); //取得自立春開始的非中氣之12節氣
        }
        
        [$tg, $dz] = $this->GetGZ($yy, $mm, $dd, $hh, $mt, $ss);
        
        //計算年月日時辰等四柱干支的陰陽屬性和個數及五行屬性和個數
        $yytg = array(); //YinYang TianGan
        $yydz = array(); //YinYang DiZhi
        $ewxtg = array(); //各天干对应的五行
        $ewxdz = array(); //各地支对应的五行
        for ($k = 0; $k <= 3; $k++) { //yytg:八字各柱天干之陰陽屬性,yydz:八字各柱地支之陰陽屬性，nyy[0]為陽之總數，nyy[1]為陰之總數
            $yytg[$k] = $tg[$k] % 2;
            $nyy[$yytg[$k]] = $nyy[$yytg[$k]] + 1; //求天干的陰陽並計算陰陽總數
            
            $yydz[$k] = $dz[$k] % 2;
            $nyy[$yydz[$k]] = $nyy[$yydz[$k]] + 1; //求地支的陰陽並計算陰陽總數
            
            $ewxtg[$k] = $this->wxtg[$tg[$k]];
            $nwx[$ewxtg[$k]] = $nwx[$ewxtg[$k]] + 1; //wxtg為天干之五行屬性
            
            $ewxdz[$k] = $this->wxdz[$dz[$k]];
            $nwx[$ewxdz[$k]] = $nwx[$ewxdz[$k]] + 1; //wxdz為地支之五行屬性
        }
        
        $rt['nyy'] = $nyy; //阴阳数量
        $rt['nwx'] = $nwx; //五行数量
        
        $rt['yytg'] = $yytg; //各天干对应的阴阳
        $rt['yydz'] = $yydz; //各地支对应的阴阳
        
        $rt['ewxtg'] = $ewxtg; //各天干对应的五行
        $rt['ewxdz'] = $ewxdz; //各地支对应的五行
        
        //日主與地支藏干決定十神
        $bzcg = array(); //各地支的藏干
        $wxcg = array(); //各地支的藏干对应的五行
        $yycg = array(); //各地支的藏干对应的阴阳
        $bctg = array(); //各地支的藏干对应的文字
        for ($i = 0; $i <= 3; $i++) { //0,1,2,3等四個
            $wxcg[$i] = array();
            $yycg[$i] = array();
            for ($j = 0; $j <= 2; $j++) { //0,1,2等三個
                $nzcg = $this->zcg[$dz[$i]][$j]; //取得藏干表中的藏干代碼,zcg為一 4X3 之array
                if ($nzcg >= 0) { //若存在則取出(若為-1，則代表空白)
                    $bctg[3 * $i + $j] = $this->ctg[$nzcg]; //暫存其干支文字
                    $bzcg[3 * $i + $j] = $this->sss[$this->dgs[$nzcg][$tg[2]]]; //暫存其所對應之十神文字
                    
                    $wxcg[$i][$j] = $this->wxtg[$nzcg]; //其五行屬性
                    $yycg[$i][$j] = $nzcg % 2; //其陰陽屬性
                } else {
                    $bctg[3 * $i + $j] = ""; //若nzcg為-1，則代表空白，設定藏干文字變數為空白
                    $bzcg[3 * $i + $j] = ""; //若nzcg為-1，則代表空白，設定十神文字變數為空白
                }
            }
        }
        
        $rt['bctg'] = $bctg;
        $rt['bzcg'] = $bzcg;
        $rt['wxcg'] = $wxcg;
        $rt['yycg'] = $yycg;
        
        //求算起運時刻
        for ($i = 0; $i <= 14; $i++) { //先找到指定時刻前後的節氣月首
            if ($jr[$i] > $spcjd) {
                $ord = $i - 1;
                break;
            } //ord即為指定時刻所在的節氣月首JD值
        }
        $xf = $spcjd - $jr[$ord]; //xf代表節氣月的前段長，單位為日，以指定時刻為分界點
        $yf = $jr[$ord + 1] - $spcjd; //yf代表節氣月的後段長
        if ((($xb == 0) && ($yytg[0] == 0)) || (($xb == 1) && ($yytg[0] == 1))) {
            $zf = $ta * 10 * ($yf / ($yf + $xf)); //zf為指定日開始到起運日之間的總日數(精確法)
            //$zf = 360 * 10 * ($yf / 30); //zf為指定日開始到起運日之間的總日數(粗略法）三天折合一年,一天折合四个月,一个时辰折合十天,一个小时折合五天,反推得到一年按360天算,一个月按30天算
            $forward = 0; //陽年男或陰年女，其大運是順推的
        } else {
            $zf = $ta * 10 * ($xf / ($yf + $xf)); //陰年男或陽年女,其大運是逆推的
            //$zf = 360 * 10 * ($xf / 30); //(粗略法)
            $forward = 1;
        }
        $qyt = $spcjd + $zf; //起運時刻為指定時刻加上推算出的10年內比例值zf
        $jt = $this->Jtime($qyt); //將起運時刻的JD值轉換為年月日時分秒
        $qyy = $jt[0]; //起運年(公历)
        
        $rt['qyy'] = $qyy; //起運年
        $rt['qyy_desc'] = "出生后" . intval($zf / $ta) . "年" . intval($zf % $ta / ($ta / 12)) . "个月" . intval($zf % $ta % ($ta / 12)) . "天起运"; //一年按ta天算,一个月按ta/12天算
        
        //求算起運年(指節氣年,农历)
        $qjr = $this->GetPureJQsinceSpring($qyy); //取得自立春開始的非中氣之12節氣
        if ($qyt >= $qjr[0]) { //qjr[0]為立春，約在2月5日前後，
            $jqyy = $qyy;
        } else {
            $jqyy = $qyy - 1; //若小於jr[0],則屬於前一個節氣年
        }
        
        //求算起運年及其後第五年的年干支及起運歲
        $jtd = (($jqyy + 4712 + 24) % 10 + 10) % 10;
        $jtd = $this->ctg[(($jqyy + 4712 + 24) % 10 + 10) % 10] . " " . $this->ctg[(($jqyy + 4712 + 24 + 5) % 10 + 10) % 10];
        $rt['qyy_desc2'] = "每逢 " . $jtd . " 年" . $jt[1] . "月" . $jt[2] . "日交大運"; //顯示每十年為一階段之起運時刻，分兩個五年以年天干和陽曆日期表示
        $qage = $jqyy - $ty; //起運年減去出生年再加一即為起運之歲數,從懷胎算起,出生即算一歲
        
        $rt['dy'] = array(); //大运
        
        //下面的回圈計算起迄歲，大運干支(及其對應的十神)，衰旺吉凶
        $zqage = array(); //起始歲數
        $zboz = array(); //末端歲數
        $zfman = array(); //大運月干代码
        $zfmbn = array(); //大運月支代码
        $zfma = array(); //大運月干文字
        $zfmb = array(); //大運月支文字
        $nzs = array(); //大运对应的十二长生
        $mgz = ((10 + $tg[1] - $dz[1]) % 10) / 2 * 12 + $dz[1]; //这里是根据天干地支代码计算月柱的六十甲子代码
        for ($k = 0; $k <= 8; $k++) { //求各階段的起迄歲數及該階段的大運
            if (empty($rt['dy'][$k])) {
                $rt['dy'][$k] = array();
            }
            //求起迄歲
            $rt['dy'][$k]['zqage'] = $zqage[$k] = $qage + 1 + $k * 10; //求各階段的起始歲數
            $rt['dy'][$k]['zboz'] = $zboz[$k] = $qage + 1 + $k * 10 + 9; //求各階段的末端歲數
            
            //排大運
            //求大運的數值表示值,以出生月份的次月干支開始順排或以出生月份的前一個月干支開始逆排
            //大運月干
            $rt['dy'][$k]['zfman'] = $zfman[$k] = ($mgz + 60 + pow(-1, $forward) * ($k + 1)) % 10; //加60是為保證在Mod之前必為正數
            //大運月支
            $rt['dy'][$k]['zfmbn'] = $zfmbn[$k] = ($mgz + 60 + pow(-1, $forward) * ($k + 1)) % 12; //加60是為保證在Mod之前必為正數
            
            $rt['dy'][$k]['zfma'] = $zfma[$k] = $this->ctg[$zfman[$k]];
            $rt['dy'][$k]['zfmb'] = $zfmb[$k] = $this->cdz[$zfmbn[$k]];
            
            //算衰旺吉凶ncs
            //szs(tg(2))爲日干對大運地支爲"子"者所對應之運程代碼
            //tg(2)爲生日天干(以整數0~11表示)之代碼
            //(-1)^tg(2)表示若日干爲陽則取加號,若日干爲陰則取减號
            //第一個大運之地支數值爲zfmbn(0)
            //下式中szs(tg(2)) + (-1) ^ tg(2) * (zfmbn(0))為決定起始運勢,(-1) ^ forward * (-1) ^ tg(2) 為決定順推或逆推,可合併簡化為次一式
            $rt['dy'][$k]['nzs'] = $nzs[$k] = (24 + $szs[$tg[2]] + pow(-1, $tg[2]) * ($zfmbn[0] + pow(-1, $forward) * $k)) % 12;
            $rt['dy'][$k]['nzsc'] = $this->czs[$nzs[$k]];
            //此處加24是爲了使Mod之前總值不爲負值
        }
        
        //求流年的數值表示值及對應的文字
        $lyean = array(); //流年天干
        $lyebn = array(); //流年地支
        $lye = array(); //流年所對應的干支文字
        for ($j = 0; $j <= 89; $j++) {
            $k = intval($j / 10); //大运
            $i = $j % 10; //流年
            if (empty($rt['dy'][$k]['ly'])) { //大运对应的流年
                $rt['dy'][$k]['ly'] = array();
            }
            if (empty($rt['dy'][$k]['ly'][$i])) {
                $rt['dy'][$k]['ly'][$i] = array();
            }
            //lyean[j]=(ygz + j + qage) % 10;
            $rt['dy'][$k]['ly'][$i]['age'] = $j + $qage + 1; //年龄(虚岁)
            $rt['dy'][$k]['ly'][$i]['year'] = $j + $qage + $ty; //流年(农历)
            $rt['dy'][$k]['ly'][$i]['lyean'] = $lyean[$j] = ($tg[0] + $j + $qage) % 10; //流年天干
            $rt['dy'][$k]['ly'][$i]['lyebn'] = $lyebn[$j] = ($dz[0] + $j + $qage) % 12; //流年地支
            $rt['dy'][$k]['ly'][$i]['lye'] = $lye[$j] = $this->ctg[$lyean[$j]] . $this->cdz[$lyebn[$j]]; //取流年所對應的干支文字
        }
        
        //顯示星座,根据公历的中气判断
        $zr = $this->GetZQsinceWinterSolstice($yy);
        if ($spcjd < $zr[0]) {
            $zr = $this->GetZQsinceWinterSolstice($yy - 1);
        } //若小於雨水，則歸前一年
        for ($i = 0; $i <= 13; $i++) { //先找到指定時刻前後的中氣月首
            if ($spcjd < $zr[$i]) {
                $xz = ($i + 12 - 1) % 12;
                break;
            } //即為指定時刻所在的節氣月首JD值
        }
        
        $rt['mz'] = $this->mz[$xb]; //命造乾坤
        $rt['xb'] = $this->xb[$xb]; //性别0男1女
        $rt['gl'] = [$yy, $mm, $dd]; //公历生日
        $rt['nl'] = $this->Solar2Lunar($yy, $mm, $dd); //农历生日
        $rt['tg'] = $tg; //八字天干数组
        $rt['dz'] = $dz; //八字地支数组
        $rt['sz'] = array(); //四柱字符
        $rt['ctg'] = array(); //天干字符
        $rt['cdz'] = array(); //地支字符
        for($i = 0; $i <= 3; $i++){
            $rt['sz'][$i] = $this->ctg[$tg[$i]] . $this->cdz[$dz[$i]];
            $rt['ctg'][$i] = $this->ctg[$tg[$i]];
            $rt['cdz'][$i] = $this->cdz[$dz[$i]];
        }
        $rt['sx'] = $this->csx[$dz[0]]; //生肖,與年地支對應
        $rt['xz'] = $this->cxz[$xz]; //星座
        $rt['cyy'] = $this->cyy[$yytg[2]]; //日干阴阳
        
        return $rt;
    }
}