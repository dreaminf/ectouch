{include file="pageheader"}
<style>
.but1{width:20pox;height:30px;}

</style>
<div class="container-fluid" style="padding:0">
	<div class="row" style="margin:0">
	  <div class="col-md-2 col-sm-2 col-lg-1" style="padding-right:0;"></div>
	  <div class="col-md-10 col-sm-10 col-lg-11"  style="padding-right:0;">
		<div class="panel panel-default">
					<div class="panel-heading" style="overflow:hidden;"> <a href="{url(set_template)}" class="btn btn-primary pull-right">{$lang['add_template']}</a>
		
		<div class="panel" style="overflow:hidden;"> <a href="{url(template_edit)}" class="btn btn-primary fancybox fancybox.iframe pull-right">{$lang['set_industry']}</a>
	
			<div>
				<p><b>请注意：</b></p>
				<p>1、所有服务号都可以在功能->添加功能插件处看到申请模板消息功能的入口，但只有认证后的服务号才可以申请模板消息的使用权限并获得该权限；</p>
				<p>2、需要选择公众账号服务所处的2个行业，每月可更改1次所选行业;</p>
				<p>3、在所选择行业的模板库中选用已有的模板进行调用；</p>
				<p>4、每个账号可以同时使用15个模板。</p>
				<p>5、当前每个账号的模板消息的日调用上限为10万次，单个模板没有特殊限制。</p>
			</div>
		</div>
				<table class="table table-hover table-bordered table-striped">
					<tr>
						<th style="text-align:center" width="15%">{$lang['template_list']}</th>
						<th style="text-align:center" width="15%">{$lang['serial']}</th>
						<th style="text-align:center" width="15%">{$lang['template_title']}</th>
						<th style="text-align:center" width="10%">{$lang['template_info']}</th>
						<th style="text-align:center" width="10%">{$lang['wechat_operating']}</th>
					</tr>
					<from method="get" action="{url(is_switch)}" >
                        {loop $list $key $val}
                        <tr>
                            <td align="center">{$val['template_list']}</td>
                            <td align="center" id="number">{$val['template_id']}</td>
                            <td align="center">{$val['grounds']}</td>
                            <td align="center">{$val['contents']}</td>
                            <td>
                                <div class="col-sm-2"style="width: 100%;padding: 0;">
                                    <div class="btn-group" style="margin: 0 auto;padding: 0 10%;display: block;overflow: hidden;" data-toggle="buttons" id="btn1">
                                        <label style="float: left;display: block;width: 50%;" class= " btn btn-primary {if $val['switch'] == 1}active{/if}" >
                                        <input type="radio" name="data[status]" value="1" {if $val['switch'] == 1}checked{/if} ></label>
                                        <label style="float: left;display: block;width: 50%;" class="btn btn-primary {if $val['switch'] == 0}active{/if}" >
                                        <input type="radio" name="data[status]" value="0"  {if $val['switch'] == 0}checked{/if} ></label>
                                        {$lang['wechat_close']}
                                    </div>
                                </div>
                            </td>
                        </tr>
                        {/loop}
										<form>
				</table>
			</div>
			
		</div>
	</div>
</div>
<script type="text/javascript">
$(function(){
	$('.btn-primary input').bind('change',function(){
		var toggle = 0,
			id = '';
		if(parseInt($(this).val())){
			toggle = 1;
		}else{
			toggle = 0;
			}
		id = $(this).parents('tr').find('#number').text();
		$.get('<?php echo url('wechat/is_switch');?>',{"id":id,"value":toggle},function(data){
			
			},'json');
		})













	
})
</script>
    {include file="pagefooter"}