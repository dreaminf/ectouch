$(function($) {
	var currYear = (new Date()).getFullYear();
	var opt = {};
	opt.date = {
		preset: 'date'
	};
	opt.datetime = {
		preset: 'datetime'
	};
	opt.time = {
		preset: 'time'
	};
	opt.default = {
		theme: 'android-ics light',
		display: 'modal',
		mode: 'scroller',
		dateFormat: 'yyyy-mm-dd',
		lang: 'zh',
		showNow: true,
		nowText: "今天",
		startYear: currYear - 10, //开始年份
		endYear: currYear //结束年份
	};
	$("#appDate").mobiscroll($.extend(opt['date'], opt['default']));

})