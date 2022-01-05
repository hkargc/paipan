//与寿星万年历进行比对,在index.htm页脚引入: <script language="javascript" src="/js/diff.js"></script>
function loadJS(url, callback) {
    var script = document.createElement('script');
    script.type = 'text/javascript';
	script.charset = 'UTF-8';
    if (script.readyState) { //IE
        script.onreadystatechange = function() {
            if (script.readyState == 'loaded' || script.readyState == 'complete') {
                script.onreadystatechange = null;
                callback();
            }
        };
    } else {
        script.onload = function() { //其他浏览器
            callback();
        };
    }
    script.src = url;
    document.getElementsByTagName('head')[0].appendChild(script);
}
function log(o){
	for(var i in arguments){
		console.log(arguments[i]);
	}
}
function go(){
	var h = [];
	var c = 0; //1为秋分
	var start = 2322147.76 - 7;
	var year = 0;
	var fs = [];
	
	for0:for(var st=0; ; st++){ //1645, 9, 23 秋分
		if(start + st >= 2561118){ //2436935,2561118
			break;
		}
		
		var jd = start + st - 2451545;
		var W = Math.floor((jd - 355 + 183) / 365.2422) * 365.2422 + 355; //355是2000.12冬至,得到较靠近jd的冬至估计值
		if (SSQ.calc(W, '气') > jd) {
			W -= 365.2422;
		}

		var A = [];
		var B = [];
		for (var i = 0; i < 24; i++) {
			A[i] = SSQ.calc(W + 15.2184 * i, '气') + 2451545; //24个节气时刻(北京时间),从冬至开始到下一个冬至以后
		}
		
		var [year] = p.Jtime(A[0]);
		var dj = p.GetAdjustedJQ(year);
		B[0] = dj[18];
		B[1] = dj[19];
		B[2] = dj[20];
		B[3] = dj[21];
		B[4] = dj[22];
		B[5] = dj[23];
		var dj = p.GetAdjustedJQ(year+1);
		for(var j=6; j<=23; j++){
			B[j] = dj[j-6];
		}
		for(var j=0; j<24; j++){
			if(A[j] <= 2322147){
				continue;
			}
			
			var i = (18 + j)%24;
			
			var [yy, mm, dd, hh, mt, ss] = p.Jtime(A[j]);
			
			var [yy2, mm2, dd2, hh2, mt2, ss2] = p.Jtime(B[j]);
			
			if((yy != yy2) || (mm != mm2) || (dd != dd2)){
				if(j <= 5){
					var fy = year;
					var fi = j + 18;
				}
				if(j >= 6){
					var fy = year + 1;
					var fi = j - 6;
				}
				var fd = A[j] - p.Jdays(yy2, mm2, dd2, 12, 0, 0);
				
				if(fs[fy] == undefined){
					fs[fy] = new Array();
				}
				fs[fy][fi] = fd;
				
				//log(p.jq[i])
				
				//log([yy, mm, dd, hh, mt, ss]);
				//log([yy2, mm2, dd2, hh2, mt2, ss2]);
				//break for0;
			}
		}
	}
	
	var sp = '';
	var sj = "var jqXFu = {\n";
	for(var year in fs){
		sp += (year + "=>[");
		sj += (year + ":{");
		for(var i in fs[year]){
			sp += (i+"=>"+fs[year][i]+",");
			sj += (i+":"+fs[year][i]+",");
		}
		sp = sp.substr(0, sp.length-1);
		sj = sj.substr(0, sj.length-1);
		sp += "],\n";
		sj += "},\n";
	}
	sj += "}\n";
	log(sp);
	log(sj)
}
function go2(){
	var h = [];
	var c = 0; //1为秋分
	var start = 2322147.76;
	var year = 0;
	var fs = [];
	
	for0:for(var st=0; ; st++){ //1645, 9, 23 秋分
		if(start + st >= 2561118){ //2436935,2561118
			break;
		}
		var jd = start + st - 2451545;
		var W = Math.floor((jd - 355 + 183) / 365.2422) * 365.2422 + 355; //355是2000.12冬至,得到较靠近jd的冬至估计值
		if (SSQ.calc(W, '气') > jd) {
			W -= 365.2422;
		}

		var A = [];
		var B = [];
		for (var i = 0; i < 24; i++) {
			A[i] = SSQ.calc(W + 15.2184 * i, '气') + 2451545; //24个节气时刻(北京时间),从冬至开始到下一个冬至以后
		}
		
		w = SSQ.calc(A[0] - 2451545, '朔') + 2451545; //求较靠近冬至的朔日
        if (w > A[0]) {
            w -= 29.53;
        }
		var A = [];
        //该年所有朔,包含14个月的始末
        for (i = 0; i <= 15; i++) {
            A[i] = SSQ.calc(w - 2451545 + 29.5306 * i, '朔') + 2451545;
        }

		var [year] = p.Jtime(A[0]);
		var B = p.GetSMsinceWinterSolstice(year+1);

		for(var j=0; j<15; j++){
			
			var [yy, mm, dd, hh, mt, ss] = p.Jtime(A[j]);
			
			var [yy2, mm2, dd2, hh2, mt2, ss2] = p.Jtime(B[j]);
			
			if((yy != yy2) || (mm != mm2) || (dd != dd2)){
				var fy = year + 1;
				var fi = j;

				var fd = A[j] - p.Jdays(yy2, mm2, dd2, 12, 0, 0);
				
				if(fs[fy] == undefined){
					fs[fy] = new Array();
				}
				fs[fy][fi] = fd;
				
				log(p.jq[i])
				
				log([yy, mm, dd, hh, mt, ss]);
				log([yy2, mm2, dd2, hh2, mt2, ss2]);
				break for0;
			}
		}
	}
	
	var sp = '';
	var sj = "var smXFu = {\n";
	for(var year in fs){
		sp += (year + "=>[");
		sj += (year + ":{");
		for(var i in fs[year]){
			sp += (i+"=>"+fs[year][i]+",");
			sj += (i+":"+fs[year][i]+",");
		}
		sp = sp.substr(0, sp.length-1);
		sj = sj.substr(0, sj.length-1);
		sp += "],\n";
		sj += "},\n";
	}
	sj += "}\n";
	log(sp);
	log(sj)
}
loadJS('/js/paipan.js', function(){
		var ym = SSQ.ym.slice(2);
		p.debug = false;
		for(var Y=1646,M=1; Y <= 2300; M++){
			lun.yueLiCalc(Y, M);
			for(var i = 0; i <= 31; i++){
				var a = lun.lun[i];
				if(a == undefined){
					break;
				}

				var [y, m, d, r] = p.Solar2Lunar(a.y, a.m, a.d);
				
				var y2 = a.Lyear0 + 1984;
				var m2 = ym.indexOf(a.Lmc) + 1;
				var d2 = a.Ldi+1;
				var r2 = a.Lleap == '' ? false : true;
				
				var flag = true;
				if(y != y2){
					flag = false;
				}
				if(m != m2){
					flag = false;
				}
				if(d != d2){
					flag = false;
				}
				if(r != r2){
					flag = false;
				}
				if(flag == false){
					
					var jd = p.Jdays(a.y, a.m, a.d, 24, 0, 0);
					
					log([a.y, a.m, a.d]);
					log([y, m, d, r]);
					log([y2, m2, d2, r2]);
					var f = 1;
					if(y > y2){
						f = 1;
					}else if(y < y2){
						f = -1;
					}else if(m>m2){
						f = 1;
					}else if(m<m2){
						f = -1;
					}else if(d > d2){
						f = 1;
					}else if(d < d2){
						f = -1;
					}
					var W = p.GetSMsinceWinterSolstice(Y); //朔望月
					for(var i=0; i<=15; i++){
						if(W[i+1] > jd){
							if(f < 0){
								var [WY, WM, WD, WH, WI, WS] = p.Jtime(W[i+1]);
								f = p.Jdays(WY, WM, WD-1, 23, 59, 59) - W[i+1];
								f = parseInt(f*86400);
								
								log(Y+":{"+(i+1)+":"+f+"},");
							}
							if(f > 0){
								var [WY, WM, WD, WH, WI, WS] = p.Jtime(W[i]);
								f = p.Jdays(WY, WM, WD+1, 0, 0, 1) - W[i];
								f = parseInt(f*86400);
								
								log(Y+":{"+i+":"+f+"},");
							}
							break;
						}
					}
					break;
				}
			}
			if(flag === false){
				break;
			}
			if(M >= 12){
				M = 0; 
				Y += 1;
				
				log(Y)
			}
		}

});

