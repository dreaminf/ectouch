{include file="pageheader"}
<div class="container-fluid">
	<div class="row">
	  <div class="col-md-2 col-sm-2 col-lg-1"></div>
	  <div class="col-md-12 col-sm-12 col-lg-11">
		<div class="panel panel-default">
            <div class="panel-heading">
				<table class="table table-hover table-bordered table-striped">
					<tr>
						<th class="text-center">{$lang['template_title']}</th>
						<th class="text-center">{$lang['serial']}</th>
						<th class="text-center">{$lang['add_time']}</th>
						<th></th>
					</tr>
					<from method="get" action="{url(is_switch)}" >
                        {loop $list $key $val}
                        <tr>
                            <td  class="text-center">{$val['title']}</td>
                            <td  class="text-center">{$val['template_id']}</td>
                            <td  class="text-center">{$val['add_time']}</td>
                            <td  class="text-center">
                                <input id="number" value="{$val['open_id']}" type="hidden">
                                    <div class="btn-group"  data-toggle="buttons" id="btn1">
                                        <label class="btn btn-primary {if $val['switch'] == 0}active{/if}" ><input type="radio" name="data[status]" value="0"  {if $val['switch'] == 0}checked{/if} >{$lang['wechat_close']}</label>
                                        <label class="btn btn-primary {if $val['switch'] == 1}active{/if}" ><input type="radio" name="data[status]" value="1" {if $val['switch'] == 1}checked{/if} > {$lang['wechat_open']}</label>
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
		var toggle = 0,id = '';
		if(parseInt($(this).val())){
			toggle = 1;
		}else{
			toggle = 0;
			}
		id = $(this).parents('td').find('#number').val();
		$.get('<?php echo url('wechat/is_switch');?>',{"open_id":id,"value":toggle},function(data){
			},'json');
		})
})
</script>
{include file="pagefooter"}