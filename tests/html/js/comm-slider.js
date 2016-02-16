$(function($) {
	//goods_list
	var Sliders = function() {
		// 筛选价格区间 js
		$("#slider-range").slider({
			range: true,
			min: 0,
			max: 1000,
			step: 100,
			values: [0, 300],
			slide: function(event, ui) {
				$("#slider-range-amount").text(ui.values[0] + " ~ " + ui.values[1]);
			}
		});
		$("#slider-range-amount").text($("#slider-range").slider("values", 0) + " ~ " + $("#slider-range").slider("values", 1));
	}();
});