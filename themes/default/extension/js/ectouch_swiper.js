$(function($) {
	//商品图片滚动
	var swiper = new Swiper('.brand-goods-swiper', {
		paginationClickable: true,
		pagination: '.brand-goods-pagination',
	});
	//推荐商品滚动
	var swiper = new Swiper('.brand-record-swiper', {
		paginationClickable: true,
		slidesPerView: 3,
	});
});
//brand-index
$(function() {
	//top
	var mySwiper = new Swiper('.brand-today-container', {
			loop: true,

			// 如果需要分页器
			pagination: '.swiper-pagination',
		})
		//recommend
	var mySwiper = new Swiper('.brand-hot-div', {
		slidesPerView: 3,
		paginationClickable: true,
	})

});
$(function($) {
	var mySwiper = new Swiper('.confirm-view-container', {
		slidesPerView: 3,
		paginationClickable: true,
		spaceBetween: 30,
		freeMode: true
	})
});
$(function($) {
	var mySwiper = new Swiper('.index-buy-container', {
		pagination: '.index-buy-pagination',
		paginationClickable: true,
		slidesPerView: 3,
		spaceBetween: 30
	})
	var mySwiper = new Swiper('.index-discount-container', {
		pagination: '.index-discount-pagination',
		paginationClickable: true,
		slidesPerView: 3,
		spaceBetween: 30
	})
	var mySwiper = new Swiper('.index-new-container', {
		pagination: '.index-new-pagination',
		paginationClickable: true,
		slidesPerView: 3,
		spaceBetween: 30
	})
	var mySwiper = new Swiper('.index-hot-container', {
			pagination: '.index-hot-pagination',
			paginationClickable: true,
			slidesPerView: 3,
			spaceBetween: 30
		})
		//wholesale
	var mySwiper = new Swiper('.wholesale-header', {
			slidesPerView: 'auto',
			paginationClickable: true,
			spaceBetween: 5
		})
	var mySwiper = new Swiper('.wholesale-container', {
		slidesPerView : 5,
		paginationClickable: true,
	});
});
$(function($) {
	$('.wholesale-container li').css('width', 'auto');
});