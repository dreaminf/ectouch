<div class="list-group">
	<a class="list-group-item disabled"><span class="glyphicon glyphicon-th-large"></span> 功能</a>
	<a class="list-group-item {if $action == 'mass_message'}active{/if}" href="{url('wechat/mass_message')}">群发消息</a>
	<a class="list-group-item {if $action == 'reply_subscribe' || $action == 'reply_msg' || $action == 'reply_keywords'}active{/if}" href="{url('wechat/reply_subscribe')}">自动回复</a>
	<a class="list-group-item {if $action == 'menu_list'}active{/if}" href="{url('wechat/menu_list')}">自定义菜单</a>
	
	<a class="list-group-item disabled"><span class="glyphicon glyphicon-inbox"></span> 管理</a>
	<a class="list-group-item {if $action == 'subscribe_list'}active{/if}" href="{url('wechat/subscribe_list')}">用户管理</a>
	<a class="list-group-item {if $action == 'article' || $action == 'image' || $action == 'voice' || $action == 'vedio'}active{/if}" href="{url('wechat/article')}">素材管理</a>
	<a class="list-group-item {if $action == 'qrcode_list'}active{/if}" href="{url('wechat/qrcode_list')}">渠道二维码<br></a>
	
	<a class="list-group-item disabled"><span class="glyphicon glyphicon-plus"></span> 扩展</a>
	<a class="list-group-item" href="{url('extend/index')}">功能扩展</a>
    <a class="list-group-item" href="{url('extend/oauth')}">微信OAuth</a>
    
<!-- 	<a class="list-group-item disabled"><span class="glyphicon glyphicon-bullhorn"></span> 推广活动</a> -->
<!-- 	<a class="list-group-item" href="{url('wechat/menu')}">促销活动</a> -->
<!-- 	<a class="list-group-item" href="{url('wechat/menu')}">幸运大转盘</a> -->
<!-- 	<a class="list-group-item" href="{url('wechat/menu')}">优惠券</a> -->
<!-- 	<a class="list-group-item" href="{url('wechat/menu')}">刮刮卡</a> -->
<!-- 	<a class="list-group-item" href="{url('wechat/menu')}">砸金蛋</a> -->
<!-- 	<a class="list-group-item" href="{url('wechat/menu')}">祝福贺卡</a> -->
<!-- 	<a class="list-group-item" href="{url('wechat/menu')}">摇一摇</a> -->
<!-- 	<a class="list-group-item" href="{url('wechat/menu')}">微信墙</a> -->
<!-- 	<a class="list-group-item" href="{url('wechat/menu')}">微信wifi</a> -->
</div>