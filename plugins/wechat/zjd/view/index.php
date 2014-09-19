<!doctype html>
<html lang="en">
<head>
	<meta charset="UTF-8">
	<title>砸金蛋</title>
	<meta name="viewport" content="initial-scale=1, minimum-scale=1, maximum-scale=1">
    <link href="<?php echo __ADDONS__;?>/wechat/zjd/view/css/style.css" rel="stylesheet">
</head>
<body>
	<div class="grid">
		<div id="hammer"><img src="<?php echo __ADDONS__;?>/wechat/zjd/view/images/img-6.png" height="87" width="74" alt=""></div>
		<div id="f"><img src="<?php echo __ADDONS__;?>/wechat/zjd/view/images/img-4.png" /></div>
		<div id="banner">
			  <dl>
			    <dt>
			      <a href="javascript:;"><img src="<?php echo __ADDONS__;?>/wechat/zjd/view/images/egg_1.png" ></a>
			      <a href="javascript:;"><img src="<?php echo __ADDONS__;?>/wechat/zjd/view/images/egg_1.png" ></a>
			      <a href="javascript:;"><img src="<?php echo __ADDONS__;?>/wechat/zjd/view/images/egg_1.png" ></a>
			      <a href="javascript:;"><img src="<?php echo __ADDONS__;?>/wechat/zjd/view/images/egg_1.png" ></a>
			      <a href="javascript:;"><img src="<?php echo __ADDONS__;?>/wechat/zjd/view/images/egg_1.png" ></a>
			      <a href="javascript:;"><img src="<?php echo __ADDONS__;?>/wechat/zjd/view/images/egg_1.png" ></a>
			      <a href="javascript:;"><img src="<?php echo __ADDONS__;?>/wechat/zjd/view/images/egg_1.png" ></a>
			    </dt>
			    <dd></dd>
			  </dl>
		</div>
		<div class="block">
			<div class="title">剩余次数</div>
			<p >你还可抽奖的次数：<span class="num"><?php echo $config['prize_num'];?></span></p>
		</div>
		<div class="block">
			<div class="title">活动规则</div>
				<p><?php echo $config['description'];?></p>
		</div>
		<div class="block">
			<div class="title">奖项设置</div>
                <?php
                    if(!empty($config['prize'])){
                        foreach($config['prize'] as $key=>$val){
                ?>
                    <p><?php echo $val['prize_level'];?>:<?php echo $val['prize_name'];?>(奖品数量：<?php echo $val['prize_count'];?>)</p>
                <?php
                        }
                    }
                ?>
		</div>
		<div class="block">
			<div class="title">中奖记录</div>
				<?php
                if(!empty($list)){
                    foreach($list as $key=>$val){
                ?>
                    <p><?php echo $val['nickname'];?> 获得奖品 ：<?php echo $val['prize_name'];?><a href="<?php echo url('wechat/plugin_action', array('name'=>'zjd', 'id'=>$val['id']));?>">领取</a></p>
                <?php
                    }
                }
                else{
                ?>
                <p>暂无获奖记录</p>
                <?php
                }
                ?>
		</div>
	</div>
	<div id="mask"></div>
	<div id="dialog" class="yes">
		<div id="content"></div>
		<a href="javascript:;" id="link">去看看</a>
		<button id="close">关闭</button>
	</div>
	
</body>
</html>
<script src="<?php echo __PUBLIC__;?>/js/jquery.min.js"></script>
<script>
    $(function() {
        var timer,forceStop;
        var wxch_Marquee = function(id){
            try{document.execCommand("BackgroundImageCache", false, true);}catch(e){};
            var container = document.getElementById(id),
                original = container.getElementsByTagName("dt")[0],
                clone = container.getElementsByTagName("dd")[0],
                speed = arguments[1] || 10;
            clone.innerHTML=original.innerHTML;
            var rolling = function(){
                if(container.scrollLeft == clone.offsetLeft){
                    container.scrollLeft = 0;
                }else{
                    container.scrollLeft++;
                }
            }
            this.stop = function() {
                clearInterval(timer);
            }
            timer = setInterval(rolling,speed);//设置定时器
            container.onmouseover=function() {clearInterval(timer)}//鼠标移到marquee上时，清除定时器，停止滚动
            container.onmouseout=function() {
                if (forceStop) return;
                timer=setInterval(rolling,speed);
            }//鼠标移开时重设定时器
        };

        var wxch_stop = function() {
            clearInterval(timer);
            forceStop = true;
        };
        var wxch_start = function() {
            forceStop = false;
            wxch_Marquee("banner",20);
        };

        wxch_Marquee("banner",20);

        var $egg;

        $("#banner a").on('click',function() {
            wxch_stop();
            $egg = $(this);
            var offset = $(this).position();
            $hammer = $("#hammer");
            $hammer.animate({left:(offset.left+30)}, 1000,function(){
                $(this).addClass('hit');
                $("#f").css('left',offset.left).show();
                $egg.find('img').attr('src','<?php echo __ADDONS__;?>/wechat/zjd/view/images/egg_2.png');
                setTimeout(function() {
                    wxch_result.call(window);
                }, 500);
            });
        });

        $("#mask").on('click',function() {
            $(this).hide();
            $("#dialog").hide();
            $egg.find('img').attr('src','<?php echo __ADDONS__;?>/wechat/zjd/view/images/egg_1.png');
            $("#f").hide();
            $("#hammer").css('left','-74px').removeClass('hit');
            wxch_start();
        });

        $("#close").click(function() {
            $("#mask").trigger('click');
        });

        function wxch_result () {
            var url = '<?php echo url('wechat/plugin_action', array('name'=>'zjd'))?>';
            $.get(url,{}, function(data){
            	$("#mask").show();
                if(data.msg == 1){
                	$("#content").html(data.yes);
                    $(".num").html(data.num);
                    $("#link").attr("href", data.link);
                	$("#dialog").attr("class",'yes').show();
                }
                else if(data.msg == 0){
                	$("#content").html(data.yes);
                    $(".num").html(data.num);
                	$("#dialog").attr("class",'no').show();
                }
                else if(data.msg == 2){
                	$("#content").html(data.yes);
                	$("#dialog").attr("class",'no').show();
                }
            	
                
                
            }, 'json');
        }
    });

</script>