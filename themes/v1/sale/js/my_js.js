$(function(){
	$("#dpg1_list1 a").click(function(){ 
		$(this).next(".index_nav").slideToggle("slow").siblings(".div3:visible").slideUp("slow");
	});
});
function select_cate(id){
    if($("#cate_"+id).hasClass("sp_box1")){
        $("#input_"+id).attr("checked",'true');
        $("#cate_"+id).removeClass("sp_box1").addClass("sp_box2");
        $("#cate_"+id+" span").removeClass("font-color-7a").addClass("font-color-fff");
    }else{
        $("#input_"+id).removeAttr("checked");
        $("#cate_"+id).removeClass("sp_box2").addClass("sp_box1");
        $("#cate_"+id+" span").removeClass("font-color-fff").addClass("font-color-7a7a7a");
    }

}


// 关闭指定class
function close_class(id){
    $("."+id).hide();
}