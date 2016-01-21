$(function($) {
	var header = $('.main-left').outerWidth();
	$('.sidemenu-header').click(function() {
		$('.main-left').toggleClass('active');
		header = $('.main-left').outerWidth();
		$.cookie("menuheader", header);
		if ($(this).parent().hasClass('active')) {
			$('.main-content').addClass('active');
		} else {
			$('.main-content').removeClass('active');
		}
	});
	$(".nav-list").collapse("show");
	//tabmenu
	$('.main-sidemenu-cont li').bind('click',function() {
		$('.main-sidemenu-cont li').removeClass('active');
		$(this).addClass('active');
		$('.main-content-content').hide().eq($('.main-sidemenu-list li').index(this)).show();
	})
	$(".main-collapse").click(function() {
		$('.main-content-sidemenu').toggleClass("active");
		$('.main-content-content').toggleClass("select");
		$(this).toggleClass("active");
		//$('.layer-shade').toggleClass('active');
	});
	//cookie
	//menu-state
	if ($.cookie("menuheader") != undefined) {
		//cookie记录的index
		if ($.cookie("menuheader") <= 50) {
			$('.main-left').removeClass('active');
			$('.main-content').removeClass('active');
		}
	} else {
		$.cookie("menuheader", header);
	}
	//pagination
	$('.pagination li').click(function () {
		$(this).addClass('active').siblings().removeClass('active');
	})
	//menu-state
	$('.main-left').find('.nav-list li').click(function() {
		$('.main-left').find('.nav-list li').removeClass('active');
		var index = $('.main-left').find('.nav-list li').index(this);
		$.cookie("current", index);
		$(this).addClass("active");
	});
	//cookie记录的index
	if ($.cookie("current") != null) {
		var num = $.cookie("current");
		$('.main-left').find('.nav-list li').eq(num).addClass('active').siblings().removeClass('active');
	}
	//编辑
	$('.edit,.btn-addArticle').click(function() {
		//$('.layer-shade').show();
		layer.open({
			type: 2,
			title: '内容编辑',
			shadeClose: true,
			shade: 0,
			maxmin: true,
			area: ['893px', '600px'],
			content: 'comm-edit.html'
		});

	});
	//datapicker
	$.cxCalendar.defaults.type = "datetime";
	$.cxCalendar.defaults.format = "YYYY-MM-DD HH:mm:ss";
	$('.date ').cxCalendar({
		//type: 'datetime',
		//format: 'YYYY-MM-DD HH:mm:ss',
	});
});

$(function($) {
	//pjax
	$('#loading').hide();
	$(document).pjax('a', '#pjax-container');
});