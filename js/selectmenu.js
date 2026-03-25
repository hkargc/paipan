"use strict";
/**
 * 无限级下拉菜单[依赖jquery]
 * @author hkargc at gmail dot com
 */
function selectmenu() {
	var _this = this;
	this.elm = $("body"); //挂载点
	this.datalist = []; //分类数组,格式为: datalist[id] = [上级id, '显示文本'];
	this.callback = function() {};
	this.config = {
		cb: {}, //响应原样返回的数据
		force: false, //强制选择一个
		selectWidth: 100, //宽度
		optionText: '--请选择--', //下拉表默认显示
		selectSize: 1, //下拉表显示的行数
		optionValue: 0 //下拉表默认值
	};
	/**
	 * @param Object elm SELECT的挂载点
	 * @param array datalist 分类数组,格式为: datalist[id] = [上级id, '显示文本'];
	 * @param int val 默认选中的值
	 * @param function callback
	 * @param Object config
	 */
	this.init = function(elm, datalist, val, callback, config) {
		_this.elm = $(elm); //要挂载SELECT元素的对象
		_this.datalist = datalist;
		if (typeof(callback) == 'function') {
			_this.callback = callback;
		}
		_this.config = Object.assign(_this.config, config);
		_this.set(val);
	};
	this.mkselect = function() {
		return $("<select></select>").attr({
			size: _this.config.selectSize,
			name: "selectmenu[]"
		}).css({
			width: _this.config.selectWidth
		}).on("change", function() {
			$(this).nextAll("select").remove();
			_this.next($(this).val());
			_this.callback(_this.config.cb);
		});
	};
	this.mkoption = function(value, text, val) {
		var value = value ? value : _this.config.optionValue;
		var text = text ? text : _this.config.optionText;
		return $("<option></option>").attr({
			"value": value
		}).text(text).prop("selected", value == val);
	};
	this.recursion = function(val) {
		var me = _this.mkselect(); //创建一个SELECT元素
		if ((_this.datalist[val] == null) || (_this.datalist[val][0] == 0) || (_this.config.force == false)) {
			var opt = _this.mkoption();
			$(me).append(opt); //附加一个'请选择'下拉
		}
		for (var i in _this.datalist) { //如果默认为空或不存在则显示第一级
			if (_this.datalist[i] == null) { //占位数据
				continue;
			}
			if (_this.datalist[i].length <= 1) { //非法数据
				continue;
			}
			if (_this.datalist[i][0] == (_this.datalist[val] ? _this.datalist[val][0] : 0)) {
				var opt = _this.mkoption(i, _this.datalist[i][1], val);
				$(me).append(opt);
			}
		}
		if (_this.elm.children("select").length) { //确保紧挨着
			_this.elm.children("select").first().before(me);
		} else {
			_this.elm.append(me);
		}

		if (_this.datalist[val] && _this.datalist[val][0] != 0) { //如果上级分类不为0,向上递归
			_this.recursion(_this.datalist[val][0])
		}
	};
	this.next = function(val) {
		if (_this.datalist[val] == undefined) { //异常
			return false;
		}
		if (_this.datalist[val] == null) {
			return false;
		}
		if (_this.datalist[val].length <= 1) { //非法数据
			return false;
		}
		for (var i in _this.datalist) { //查找是否有子类
			if (_this.datalist[i] == null) {
				continue;
			}
			if (_this.datalist[i].length <= 1) {
				continue;
			}
			if (_this.datalist[i][0] == val) {
				if (typeof(me) == 'undefined') {
					var me = _this.mkselect();
					if (_this.config.force == false) {
						var opt = _this.mkoption();
						$(me).append(opt);
					}
				}
				var opt = _this.mkoption(i, _this.datalist[i][1], val);
				me.append(opt);
			}
		}
		if (typeof(me) == 'undefined') {
			return true;
		}
		if (_this.elm.children("select").length) { //确保紧挨着
			_this.elm.children("select").last().after(me);
		} else {
			_this.elm.append(me);
		}

		_this.next($(me).val()); //如果还有子类
	};
	this.set = function(val) {
		_this.elm.children("select").remove();
		_this.recursion(val); //向上递归,选中默认的值
		_this.next(val); //如果还有子类
	};
	this.get = function() {
		var me = _this.elm.children("select").last();
		while ($(me).val() == _this.config.optionValue) {
			if (_this.config.force) { //如果必须选中最末一级
				break;
			}
			if ($(me).prev("select").length == 0) { //到顶了
				break;
			}
			me = $(me).prev("select"); //一直往前找
		}
		return $(me).val();
	};
}