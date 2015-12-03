//通用部分
//加关注
$(function($) {
	//关注
	$('.brand-favor').click(function() {
		if ($(this).hasClass('active')) {
			$(this).removeClass('active');
			$(this).html("+关注");
		} else {
			$(this).addClass('active');
			$(this).html("已关注");
		}
	});
	//brand_goods
	$('.goods-title-right').click(function() {
		$(this).toggleClass('active');
	});
	//tabmenu
	$('.description-tabmenu li').click(function() {
		$(this).addClass("active").siblings().removeClass('active');
		$('.description-cont').hide().eq($('.description-tabmenu li').index(this)).show();
	});
	//footer-menu
	$('.brand-footer-cart:first-child').click(function() {
		$(this).toggleClass('active');
	});
	//select-layer
	$('.goods-size').click(function() {
			$('.goods-list-layer').toggle();
			$('.goods-size i').toggleClass('active');
		})
		//选择尺寸颜色
	$('.goods-category label').click(function() {
		$(this).toggleClass('active').siblings().removeClass('active');

	});
	$('.goods-category label.multy').click(function() {
		$(this).toggleClass('active');

	});
	$('.goods-multy-category label').click(function() {
		$(this).toggleClass('active');
	});
	//category_index
	//	if ($('.comm-search-layer').css("display") == "none") {
	//		$('.menu-con').height($(window).height());
	//		$('.category-body').height($(window).height() - $('.menut-header').height());
	//	}
	//	if ($('.comm-search-layer').css("display") == "block") {
	//		$('.category-body').height($(window).height() - $('.menut-header').height());
	//	}
	//tabmenu
	$('.menu-tab ul li').click(function() {
		$(this).addClass("selected").siblings().removeClass('selected');
		$('.menu-cont-list').hide().eq($('.menu-tab ul li').index(this)).show();
	});
	//comment-goods
	//tabmenu
	$('.comm-goods-title ul li').click(function() {
		$(this).addClass("active").siblings().removeClass('active');
		$('.comm-goods-list').hide().eq($('.comm-goods-title ul li').index(this)).show();
	});
	//用户模块
	$('.user-tabs li').click(function() {
		$(".user-tabs li[class='active']").removeAttr("class");
		$(this).addClass("active");
		$('.user-conts').hide().eq($('.user-tabs li').index(this)).show();
	});

	$('.nav-tabs li').click(function() {
		$(".nav-tabs li[class='active']").removeAttr("class");
		$(this).addClass("active");
		$('.phone-main .tab-pane').hide().eq($('.nav-tabs li').index(this)).show();
	});
});

$(function($) {
	//login全屏
	$('.login-con').height($(window).height()); 
	$('.phone-number-o i').click(function() {
		$('.phone-number-o input').val('');
	});
	$(".phone-number-t a").bind("click", function() {
		$(this).html('发送中……');
	});

	$('.login-form-id input[type=text]').bind("input propertychange", function() {
		var Value = $(this).val().length;
		var isEmpty = false;
		if (Value == 0) {
			$('.login-form-id i').hide();
			isEmpty = true;
		}
		if (!isEmpty && Value != 0) {
			$('.login-form-id i').show();
			if (Value < 5) {
				$(this).css('color', 'red');
			} else {
				$(this).css('color', '#41A28E');
			}
		}
	});
	//手机号码长度判断
	$('.phone-number-o input[type=text]').bind("input propertychange", function() {
		var Value = $(this).val().length;
		var isEmpty = false;
		if (Value == 0) {
			isEmpty = true;
		}
		if (!isEmpty && Value != 0) {
			if (Value == 13) {
				$(this).css('color', '#41A28E');
			} else {
				$(this).css('color', 'red');
			}
		}
	});
	$('.login-form-id i').click(function() {
		$('.login-form-id input[type=text]').val('');
		//		$(this).hide();
	});
	//点击后颜色变深
});

//flow-done

//cart-fitting
$(function($) {
	//flow-done
	$('.confirm-pay-title').click(function() {
		$(this).toggleClass('active');
		$('.confirm-pay-layer').eq($('.confirm-pay-title').index(this)).toggle();
	});
	$('.confirm-select li').click(function() {
		$('.layer').show();
		$('.confirm-layer-rem').hide();
		$('.confirm-layer').show();
		$('html').addClass('active');
	});
	$('.confirm-select-rem').click(function() {
		$('.layer').show();
		$('.confirm-layer').hide();
		$('.confirm-layer-rem').show();
		$('html').addClass('active');
	});
	$('.layer').click(function() {
		$('.layer').hide();
		$('html').removeClass('active');
	});
	//register
	$('.register-psw-show').click(function() {
			$(this).toggleClass('active');
		})
		//user-profile
	$('.profile-layer-select').click(function() {
		$('.profile-layer-con').eq($('.profile-layer-select').index(this)).addClass('active');
	});
	$('.phone-layer button').click(function() {
		$('.profile-layer-con').removeClass('active');
	});
	$('.layer-sex-select li').click(function() {
		$('.layer-sex-select li i').hide().eq($('.layer-sex-select li').index(this)).show();
		$('.profile-layer-con').removeClass('active');
	});
	//auction-detail
	$('.auction-footer-record').click(function() {
		$('.auction-layer-list').addClass('active');
	});
	$('.auction-layer-back').click(function() {
		$('.auction-layer-list').removeClass('active');
	});
	//goods-list
	//menu
	$('.list-menu-bar li').not('.list-menu-bar-icon').click(function() {
		$(this).toggleClass('active').siblings().removeClass('active');
		$('.list-menu-bar-icon span').removeClass('active');
	});
	$('.list-menu-bar li.list-menu-bar-icon').click(function() {
		$(this).addClass('active').siblings().removeClass('active');
		//		$(this).find("span:first-child").addClass("active")
		if ($(this).find("span.list-menu-bar-up").hasClass("active")) {
			$(this).find("span.list-menu-bar-up").removeClass("active").siblings("span").addClass("active");
		} else {
			$(this).find("span.list-menu-bar-down").removeClass("active").siblings("span").addClass("active");
		}
	});
	//menu
	$('.head-right i').click(function() {
		$('.goods-list-goods').toggleClass('active');
	});
	//user-account-bonus
	//tabmenu
	$('.account-bonus-tabs li').click(function() {
		$(this).addClass("active").siblings().removeClass('active');
		$('.account-bonus-cont').hide().eq($('.account-bonus-tabs li').index(this)).show();
	});
	$('.cart-fittings-add').bind("click", function() {
		var html = $(this).html();
		if (html == "加入购物车") {
			$(this).html("从购物车中删除");
		} else {
			$(this).html("加入购物车");
		}
	});

	//wholesale
	$('.wholesale-header li').bind("click", function() {
		$(this).addClass("active").siblings().removeClass('active');
		$('.wholesale-goods').hide().eq($('.wholesale-header li').index(this)).show();
	});
//	if($('.wholesale-tab-cont').hasClass('active')){
//		var width=$(this).find('.wholesale-price-quantity li').width();
//		console.log(width);
//	}
//	var width = $('.wholesale-price-quantity li').width();
//	var n = $('.wholesale-price-quantity').children().length;
////	console.log(n);
//	width = $('.wholesale-price-quantity').width() / (n + 1);
//	$('.wholesale-price-quantity li').css({
//		"width": width
//	});
//	$('.wholesale-price-discount dd').css({
//		"width": width
//	});

	//tabmenu
	$('.wholesale-tab li').click(function() {
		$(this).addClass("active").siblings().removeClass('active');
		$('.wholesale-tab-cont').hide().eq($('.wholesale-tab li').index(this)).show();
	});
	//comm-layer
	$('.comm-search input').bind("click", function() {
		$('.comm-search-layer').show();
	});
	$('.comm-search-layer .layer-search-close').bind("click", function() {
		$('body').removeClass('active');
		$('.comm-search-layer').hide();
	});
	//article-detail
	$('.article-comment-form textarea').focus(function() {
		$('.article-comment-button').addClass('active');
		if ($('.article-comment-button').show()) {
			$('.article-comment-button button:first').click(function() {
				$(this).parent().removeClass('active');
			})
		}
	});
	//footer-menu:search
	$('.footer-search').click(function() {
			$('body').addClass('active');
			$('.comm-search-layer').show();
		})
		//goods-list-filter-layer
	$('.list-menu-bar-filter').click(function() {
		$('.filter-layer').show();
	})
	$('.filter-layer .auction-layer-back').click(function() {
		$('.filter-layer').hide();
		$('.filter-list-o a').removeClass('active');
		$('.filter-list-t span').html('全部');
	})
	$('.filter-list-t').click(function() {
		$(this).find('i').toggleClass('active');
		$(this).siblings('.filter-list-o').toggle();
		var value = $(this).find('span').html();
		var value2 = $(this).siblings('.filter-list-o').find('a.active').html();
	})
	$('.filter-list-o a').click(function() {
		$(this).toggleClass('active').siblings().removeClass('active');
		var value = $(this).html();
		$(this).parent().siblings('.filter-list-t').find('span').html(value);
	});
	//user-index-input
	$('.account-charge-input input').bind("input propertychange", function() {
		var Value = $(this).val().length;
		if (Value != 0) {
			$('.account-charge-input i').show();
		}
	});
	$('.account-charge-input i').click(function(){
		$('.account-charge-input input').val('');
	})
});