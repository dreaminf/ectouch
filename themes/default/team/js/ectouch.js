/*common*/
var handler = function(e) { //禁止浏览器默认行为
	e.preventDefault();
};
/*判断文本框是否有值显示隐藏清空按钮*/
var input_texts = $(".j-input-text");
var is_nulls = $(".j-text-all").find(".j-is-null");
var is_yanjing = $(".j-text-all").find(".j-yanjing");
input_texts.bind('focus', function() {
	is_nulls.removeClass('active');
	//$(this).parents(".j-text-all").addClass("active").siblings().removeClass("active");//开启后 文本框获得焦点即可改变下边框颜色
	if($(this).val() != "") {
		$(this).siblings('.j-is-null').addClass('active');
	}
});
input_texts.bind('input', function() {
	if($(this).val() == "") {
		$(this).siblings('.j-is-null').removeClass('active');
	} else {
		$(this).siblings('.j-is-null').addClass('active');
	}
});

is_nulls.click(function() { /*点击清空标签文本框内容删*/
	$(this).siblings(".j-input-text").val("");
	$(this).siblings(".j-input-text").focus();
});

is_yanjing.click(function() { /*密码框点击切换普通文本*/
	input_text_atr = $(this).siblings(".input-text").find(".j-input-text");
	if(input_text_atr.attr("type") == "password" && $(this).hasClass("disabled")) {
		input_text_atr.attr("type", "text");
	} else {
		input_text_atr.attr("type", "password");
	}
	input_text_atr.focus();
	$(this).toggleClass("disabled");
});

/*单选*/
$(".j-get-one .ect-select").click(function() {
	get_tjiantou = $(this).parent(".j-get-one").prev(".select-title").find(".t-jiantou");
	$(this).find("label").addClass("active").parent(".ect-select").siblings().find("label").removeClass("active");
	get_tjiantou.find("em").text($(this).find("label").text());
	if($(this).hasClass("j-checkbox-all")) {
		get_tjiantou.removeClass("active");
	} else {
		get_tjiantou.addClass("active");
	}
	if($(this).parents("show-goods-attr")) { //赋值给goods-attr
		s_get_label = $(".show-goods-attr .s-g-attr-con").find("label.active"); //获取被选中label
		var get_text = '';
		s_get_label.each(function() {
			get_text += $(this).text() + "、";
		});
		$(".j-goods-attr").find(".t-goods1").text(get_text.substring(0, get_text.length - 1));
	}
});

/*页面向上滚动js*/

$(".filter-top").click(function() {
	$("html,body").animate({
		scrollTop: 0
	}, 200);
});

$(window).scroll(function() {
	var prevTop = 0,
		currTop = 0;
	currTop = $(window).scrollTop();
	win_height = $(window).height() * 2;
	if(currTop >= win_height) {
		$(".filter-top").stop().fadeIn(200);
	} else {
		$(".filter-top").stop().fadeOut(200);
	}
	//prevTop = currTop; //IE下有BUG，所以用以下方式
	setTimeout(function() {
		prevTop = currTop
	}, 0);
});

/*商品数量*/
$(function($) {
	$(".j-index-sum").click(function() {
		$(this).parents(".product-div").addClass("active");
	});
	$(".icon-minus").click(function() {
		$(this).parents(".product-div").removeClass("active");
	});
});

//内部内容滚动
function swiper_scroll() {
	var scorll_swiper = new Swiper('.swiper-scroll', {
		scrollbar: false,
		direction: 'vertical',
		slidesPerView: 'auto',
		mousewheelControl: true,
		freeMode: true
	});
}
$(function($) {

	/*全部分类 -s*/
	/*分类导航*/
	$(".j-cata-all").click(function() {
		$(".ec-fresh-bg").addClass("active");
		$(".catalog-nav-box").addClass("active");
		$(".t-jiantou-all").addClass("active");
	});
	$(".cate-nav-top,.ec-fresh-bg").click(function() {
		$(".ec-fresh-bg").removeClass("active");
		$(".catalog-nav-box").removeClass("active");
		$(".t-jiantou-all").removeClass("active");
	});

	//内部内容滚动
	$(".con-filter-div .swiper-scroll").css("max-height", $(window).height());
	if($(".swiper-scroll").hasClass("swiper-scroll")) { //滚动相关js
		swiper_scroll();
	}
	/*全部分类 -e*/

	/*地址选择 -s*/
	/*search-address*/
	$(".j-search-address").click(function() {
		$(".ec-fresh-bg").addClass("active");
		$(".t-search-footer").addClass("active");
	});
	$(".ec-fresh-bg").click(function() {
		$(".ec-fresh-bg").removeClass("active");
		$(".t-search-footer").removeClass("active");
	});	
	/*点击弹出搜索层*/
	$(".j-ec-search").click(function() {
		document.addEventListener("touchmove", handler, false);
		$(".search-div").addClass("active");
		$('#newinput').focus();
	});

	/*关闭搜索层*/
	$(".j-close-search").click(function() {
		document.removeEventListener("touchmove", handler, false);
		$(".search-div").removeClass("active");
	});
	/*地址选择 -e*/

});
/*首页点击弹出搜索层*/
function openSearch(){	
	document.addEventListener("touchmove", handler, false);
	$(".search-div").addClass("active");
	$('#newinput').focus();
}

/*详情*/
$(function() {
	$('#loading').hide();
	/*商品详情 红心*/
	/* $(".j-heart").click(function() {
		$(this).toggleClass("active");
	}); */
	/*弹出层方式*/
	$(".j-show-div").click(function() {
		document.addEventListener("touchmove", handler, false);
		$(this).find(".j-filter-show-div").addClass("show");
		$(".mask-filter-div").addClass("show");
	});
	$(".j-show-div-1").click(function() {
		document.addEventListener("touchmove", handler, false);
		$(this).find(".j-filter-show-div-1").addClass("show");
		$(".mask-filter-div").addClass("show");
	});
	/*关闭弹出层*/
	$(".mask-filter-div,.show-div-guanbi").click(function() {
		document.removeEventListener("touchmove", handler, false);
		if($(".j-filter-show-div,.j-filter-show-div-1").hasClass("show")) {
			$(".j-filter-show-div,.j-filter-show-div-1").removeClass("show");
			$(".mask-filter-div").removeClass("show");
			return false;
		}
		if($(".j-filter-show-list").hasClass("show")) {
			$(".j-filter-show-list").removeClass("show");
			$(".mask-filter-div").removeClass("show");
			return false;
		}
	});

	$(".j-goods-box").click(function() {
		document.addEventListener("touchmove", handler, false);
		$(".goods-banner").addClass("active");
		$(".goods-bg-box").addClass("active");

	});
	$(".goods-bg-box").click(function() {
		document.removeEventListener("touchmove", handler, false);
		$(".goods-banner").removeClass("active");
		$(".goods-bg-box").removeClass("active");

	});

});

/*购物车*/

/*购物车悬浮按钮编辑状态*/
$(".f-cart-filter-btn .span-bianji").click(function() {
	$(".f-cart-filter-btn").addClass("active");
})
$(".f-cart-filter-btn .j-btn-default").click(function() {
	$(".f-cart-filter-btn").removeClass("active");
})

/*多选*/
/* $(".j-get-more .ect-select").click(function() {
	if(!$(this).find("label").hasClass("active")) {
		$(this).find("label").addClass("active");
		if($(this).find("label").hasClass("label-all")) {
			$(".j-select-all").find(".ect-select label").addClass("active");
		}
	} else {
		$(this).find("label").removeClass("active");
		if($(this).find("label").hasClass("label-all")) {
			$(".j-select-all").find(".ect-select label").removeClass("active");
		}
	}
}); */
/*多选只点击单选按钮 - 全选，全不选*/
$(".j-get-i-more .j-select-btn").click(function() {
	if($(this).parents(".ect-select").hasClass("j-flowcoupon-select-disab")) {
		d_messages("同商家只能选择一个", 2);
	} else {
		is_select_all = true;
		if($(this).parent("label").hasClass("label-this-all")) {
			if(!$(this).parent("label").hasClass("active")) {
				$(this).parents(".j-get-i-more").find(".ect-select label").addClass("active");
			} else {
				$(this).parents(".j-get-i-more").find(".ect-select label").removeClass("active");
			}
		}

		if(!$(this).parent("label").hasClass("label-this-all") && !$(this).parent("label").hasClass("label-all")) {
			$(this).parent("label").toggleClass("active");
			is_select_this_all = true;
			select_this_all = $(this).parents(".j-get-i-more").find(".ect-select label").not(".label-this-all");

			select_this_all.each(function() {
				if(!$(this).hasClass("active")) {
					is_select_this_all = false;
					return false;
				}
			})
			if(is_select_this_all) {
				$(this).parents(".j-get-i-more").find(".label-this-all").addClass("active");
			} else {
				$(this).parents(".j-get-i-more").find(".label-this-all").removeClass("active");
			}
		}

		var select_all = $(".j-select-all").find(".ect-select label");
		select_all.each(function() {
			if(!$(this).hasClass("active")) {
				is_select_all = false;
				return false;
			}
		});
		if(is_select_all) {
			$(".label-all").addClass("active");
		} else {
			$(".label-all").removeClass("active");
		}
	}
});

/*订单提交页*/
$(".j-flow-checkout-pro span.t-jiantou").click(function() {
	$(this).parents(".flow-checkout-pro").toggleClass("active");
})

/*优惠券赋值*/
$(".flow-coupon .c-btn-submit").click(function() {
	if($("body").hasClass("show-coupon-div")) {
		document.removeEventListener("touchmove", handler, false);
		$("body").removeClass("show-coupon-div");
		coupon_list = $(this).parents(".flow-coupon").find(".ect-select label.active");
		coupon_price = $(this).parents(".j-f-c-s-coupon").find(".t-goods1 .coupon-price");
		coupon_num = 0;
		coupon_list.each(function() {
			coupon_num += parseInt($(this).attr("data"));
		});
		coupon_price.text("¥" + coupon_num + ".00");
		return false;
	}
});
/*点击弹出层 － 订单提交页优惠券*/
$(".j-f-c-s-coupon").click(function() {
	document.addEventListener("touchmove", handler, false);
	$("body").addClass("show-coupon-div");
});
/*发票弹出*/
$(".j-f-c-receipt").click(function() {
	document.addEventListener("touchmove", handler, false);
	$("body").addClass("show-receipt-div");
});
/*点击积分切换－滑动选择按钮*/
$(".j-radio-switching").click(function() {
	if($(this).hasClass("active")) {
		$(this).removeClass("active");
		$(this).attr("data", 0);
	} else {
		$(this).addClass("active");
		$(this).attr("data", 1);
	}
});

/*文本框获得焦点下拉*/
$(".text-all-select .j-input-text").focus(function() {
	$(this).parents(".text-all-select").find(".text-all-select-div").show();
});
$(".text-all-select-div li").click(function() {
	text_select = $(this).text();
	$(this).parents(".text-all-select").find(".j-input-text").val(text_select);
	$(this).parents(".text-all-select").find(".text-all-select-div").hide();
	return false;
});

/*发票赋值*/
$(".flow-receipt .r-btn-submit").click(function() {
	if($("body").hasClass("show-receipt-div")) {
		document.removeEventListener("touchmove", handler, false);
		$("body").removeClass("show-receipt-div");
		is_no = $(".flow-receipt-type .active").attr("data");
		f_r_type = $(".flow-receipt-type .active").text();
		f_r_title = $(".flow-receipt-title .j-input-text").val();
		f_r_cont = $(".flow-receipt-cont .active").text();
		if(is_no == "no" || is_no == "") {
			f_r_title = "无";
		}
		if(f_r_title == "") {
			f_r_title = "个人";
		}
		receipt_title = $(this).parents(".j-f-c-receipt").find(".receipt-title");
		receipt_name = $(this).parents(".j-f-c-receipt").find(".receipt-name");
		receipt_title.text(f_r_type);
		receipt_name.text(f_r_title);
		return false;
	}
});
/*默认地址*/
/*单选consignee*/
$(".j-get-consignee-one label").click(function() {
	$(this).addClass("active").parents(".flow-checkout-adr").siblings().find("label").removeClass("active");
});
/*选择收货人信息*/
$(".j-flow-get-consignee .flow-checkout-adr").click(function() {
	$(this).addClass("active").siblings(".flow-checkout-adr").removeClass("active");
});

/*城市筛选单选city*/
$(".j-filter-city").click(function() {
	cityTop = $(window).scrollTop();
	$("body").addClass("show-city-div");
});

/*悬浮菜单点击显示*/
$(".filter-menu-title").click(function() {
	$(".filter-menu").toggleClass("active");
});
/*帮助*/
/*手风琴下拉效果*/
$(".j-sub-menu").hide();
$(".j-menu-select").click(function() {
	$(this).next(".j-sub-menu").slideToggle().siblings('.j-sub-menu').slideUp();
	$(this).toggleClass("active").siblings().removeClass("active");
	var scorll_swiper = new Swiper('.swiper-scroll', {
		scrollbar: false,
		direction: 'vertical',
		slidesPerView: 'auto',
		mousewheelControl: true,
		freeMode: true
	});

});
/*商品详情所在地区*/
$(".j-get-city-one .ect-select").click(function() {
	city_span = $(".j-filter-city span.text-all-span");
	city_txt = $(".j-city-left li.active").text() + " " + $(this).parents(".j-sub-menu").prev(".j-menu-select").find("label").text() + " " + $(this).find("label").text();
	$(".j-get-city-one").find(".ect-select label").removeClass("active");
	$(this).find("label").addClass("active");
	city_span.text(city_txt);
	if($(".j-filter-city span.text-all-span").hasClass("j-city-scolor")) {
		$(".j-filter-city span.text-all-span").css("color", "#ec5151");
	}
	$("body").removeClass("show-city-div");
	$("html,body").animate({
		scrollTop: cityTop
	}, 0);
});
/*点击关闭筛选弹出层*/
$(".j-close-filter-div").click(function() {
	if($("body").hasClass("show-site-div")) {
		document.removeEventListener("touchmove", handler, false);
		$("body").removeClass("show-site-div");
		return false;
	}
	if($("body").hasClass("show-city-div")) {
		$("body").removeClass("show-city-div");
		$("html,body").animate({
			scrollTop: cityTop
		}, 0);

		return false;
	}
	if($("body").hasClass("show-filter-div")) {
		$("body").removeClass("show-filter-div");
		$("html,body").animate({
			scrollTop: cityTop
		}, 0);

		return false;
	}
	if($("body").hasClass("show-depot-div")) {
		$("body").removeClass("show-depot-div");
		$("html,body").animate({
			scrollTop: cityTop
		}, 0);
		return false;
	}
});

/*评价*/
// 初始化图片上传插件
if($("#demo").hasClass("demo")) {
	$("#demo").zyUpload({ // 宽度
		itemWidth: "120px", // 文件项的宽度
		itemHeight: "100px", // 文件项的高度
		url: "../upload/UploadAction/", // 上传文件的路径
		multiple: false, // 是否可以多个文件上传
		dragDrop: false, // 是否可以拖动上传文件
		del: true, // 是否可以删除文件
		finishDel: false, // 是否在上传文件完成后删除预览
		/* 外部获得的回调接口 */
		onSelect: function(files, allFiles) { // 选择文件的回调方法
			console.info("当前选择了以下文件：");
			console.info(files);
			console.info("之前没上传的文件：");
			console.info(allFiles);
		},
		onDelete: function(file, surplusFiles) { // 删除一个文件的回调方法
			console.info("当前删除了此文件：");
			console.info(file);
			console.info("当前剩余的文件：");
			console.info(surplusFiles);
		},
		onSuccess: function(file) { // 文件上传成功的回调方法
			console.info("此文件上传成功：");
			console.info(file);
		},
		onFailure: function(file) { // 文件上传失败的回调方法
			console.info("此文件上传失败：");
			console.info(file);
		},
		onComplete: function(responseInfo) { // 上传完成的回调方法
			console.info("文件上传完成");
			console.info(responseInfo);
		}
	});
}
/*签到弹框*/
$(".j-sign").click(function() {
	document.addEventListener("touchmove", handler, false);
	$(".sign-modal").addClass("active");
	$(".sign-modal-bg").addClass("active");

})
$(".j-modal-close,.sign-modal-bg").click(function() {
	document.removeEventListener("touchmove", handler, false);
	$(".sign-modal").removeClass("active");
	$(".sign-modal-bg").removeClass("active");
})

/*签到规则*/
$(".j-sign-rule").click(function() {
	$(".user-sign-rule").addClass("active");
	$(".sign-modal-bg").addClass("active");
});
$(".j-close-rule,.sign-modal-bg").click(function() {
	$(".user-sign-rule").removeClass("active");
	$(".sign-modal-bg").removeClass("active");
})

/*文章
$(".j-s-nav-select,.j-shopping-menu-close").click(function() {
	$(".shopping-menu").toggleClass("active");
});

$(function() {
	$('.oncle-color').click(function() {
		for(var i = 0; i < $('.oncle-color').size(); i++) {
			if(this == $('.oncle-color').get(i)) {
				$('.oncle-color').eq(i).children('a').addClass('active');
			} else {
				$('.oncle-color').eq(i).children('a').removeClass('active');
			}
		}
	})
});
*/


/*微筹详情js*/
function adv_index() {
	if($(window).scrollTop() > 150) {
		$(".goods-fixed").addClass("active");
		$(".goods-left-jiat").addClass("active");
		$(".goods-header-nav-box").addClass("active");
	} else {
		$(".goods-fixed").removeClass("active");
		$(".goods-left-jiat").removeClass("active");
		$(".goods-header-nav-box").removeClass("active");
	}

};

$(function($) {
	adv_index();
	$(window).scroll(function() {
		adv_index();
	});
	$(".icon-gengduo").click(function() {
		$(".goods-scoll-bg").addClass("active");
		if(!$(".goods-nav").hasClass("active")){
			$(".goods-nav").addClass("active");
			$(".goods-scoll-bg").addClass("active");
			return false;
		}else{
			$(".goods-nav").removeClass("active");
			$(".goods-scoll-bg").removeClass("active");
			return false;
		}
	});
	$(".goods-scoll-bg").click(function() {
		$(".goods-scoll-bg").removeClass("active");
		$(".goods-nav").removeClass("active");
	});	
	//详情导航滚动隐藏
	$(window).scroll(function() {			
			if($(window).scrollTop() > 0) {	
				 $(".goods-scoll-bg").removeClass("active");
				$(".goods-nav").removeClass("active"); 				  	 
		};										
	});
	
	/*拼团分享*/
$(".j-fengxiang-box").click(function(){
								document.addEventListener("touchmove", handler, false);
							$(".fengxiang-img-box").addClass("active");
							$(".fengxing-bg").addClass("active");
						});
							$(".fengxing-bg").click(function(){
								document.removeEventListener("touchmove", handler, false);
							$(".fengxiang-img-box").removeClass("active");
							$(".fengxing-bg").removeClass("active");
						});	
});


	//拼团规则
		$(document).ready(function(){
			$(".j-goods-guize-box").click(function(){
				    $(".goods-show-box,.goods-mn-jiantou .icon-mn-jiantoul").toggleClass("active");					   
				 });		  				  
			});


// 本地存储
localData = {
	hname: location.hostname ? location.hostname : 'localStatus',
	isLocalStorage: window.localStorage ? true : false,
	dataDom: null,

	initDom: function() { //初始化userData
		if(!this.dataDom) {
			try {
				this.dataDom = document.createElement('input'); //这里使用hidden的input元素
				this.dataDom.type = 'hidden';
				this.dataDom.style.display = "none";
				this.dataDom.addBehavior('#default#userData'); //这是userData的语法
				document.body.appendChild(this.dataDom);
				var exDate = new Date();
				exDate = exDate.getDate() + 30;
				this.dataDom.expires = exDate.toUTCString(); //设定过期时间
			} catch(ex) {
				return false;
			}
		}
		return true;
	},
	set: function(key, value) {
		if(this.isLocalStorage) {
			window.localStorage.setItem(key, value);
		} else {
			if(this.initDom()) {
				this.dataDom.load(this.hname);
				this.dataDom.setAttribute(key, value);
				this.dataDom.save(this.hname)
			}
		}
	},
	get: function(key) {
		if(this.isLocalStorage) {
			return window.localStorage.getItem(key);
		} else {
			if(this.initDom()) {
				this.dataDom.load(this.hname);
				return this.dataDom.getAttribute(key);
			}
		}
	},
	remove: function(key) {
		if(this.isLocalStorage) {
			localStorage.removeItem(key);
		} else {
			if(this.initDom()) {
				this.dataDom.load(this.hname);
				this.dataDom.removeAttribute(key);
				this.dataDom.save(this.hname)
			}
		}
	}
}

