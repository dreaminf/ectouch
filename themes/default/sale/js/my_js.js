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
        if($("#checkAll").is(":checked")==true){
            $("#checkAll").attr("checked",false);
        }
    }

}


// 关闭指定class
function close_class(id){
    $("."+id).hide();
}

/**
 * 申请开店
 * @returns {Boolean}
 */
function submit_saleSet() {

    var shop_name	=	$("#shop_name").val();
    var real_name	=	$("#real_name").val();
    var shop_mobile	=	$("#shop_mobile").val();
    var msg			=	'';
    regPartton		=	/1[3-8]+\d{9}/;
	if(!shop_mobile || shop_mobile==null){
		msg += "手机号码不能为空！\n";
	}else if(!regPartton.test(shop_mobile) || shop_mobile.length != 11){
		msg += "手机号码格式不正确！\n";
	}
    if(!shop_name || shop_name==null){
        msg += "店铺名称不能为空！\n";
    }
    if(!real_name || real_name==null){
        msg += "真实姓名不能为空！\n";
    }
    if (msg.length > 0) {
        alert(msg);
        return false;
    }else{
        $("#formSub").submit();
        return true;
    }
}