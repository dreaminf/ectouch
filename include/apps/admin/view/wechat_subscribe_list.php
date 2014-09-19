{include file="pageheader"}
<div class="container-fluid" style="padding:0">
	<div class="row" style="margin:0">
	  <div class="col-md-2 col-sm-2 col-lg-1" style="padding-right:0;">{include file="wechat_left_menu"}</div>
	  <div class="col-md-10 col-sm-10 col-lg-11" style="padding-right:0;">
		<form class="navbar-form navbar-right" role="search" action="{url('subscribe_search')}" method="post">
			<div class="form-group">	
				<input type="text" class="form-control" placeholder="{$lang['sub_search']}" name="keywords" />	
			</div>
			<input name="Submit" class="btn btn-primary" type="submit" value="{$lang['button_search']}">
		</form>
		<form action="{url('subscribe_move')}" method="post">
		<div class="well well-sm" style="margin-bottom:0;">{$lang['group_move']}
		  	<select name="group_id" style="padding:5px;">
		  		{loop $group_list $k $v}
		  		<option value="{$v['group_id']}">{$v['name']}</option>
		  		{/loop}
		  	</select>
		  	<input type="submit" class="btn btn-primary" value="{$lang['sub_move']}" />
		  	 	 	
		</div>
		<div class="panel panel-default">
			<div class="panel-heading">{$lang['sub_title']}</div>
			<div class="col-lg-10" style="padding:0;">
    			<table class="table table-hover table-bordered table-striped">
    				<tr>
    					<th class="text-center"><input type="checkbox" id="check_box" /></th>
    					<th class="text-center">{$lang['num_order']}</th>
    					<th class="text-center">{$lang['sub_headimg']}</th>
    					<th class="text-center">{$lang['sub_nickname']}</th>
    					<th class="text-center">{$lang['sub_group']}</th>
    					<th class="text-center">{$lang['sub_sex']}</th>
    					<th class="text-center">{$lang['sub_province']}</th>
    					<th class="text-center">{$lang['sub_city']}</th>
    					<th class="text-center">{$lang['sub_time']}</th>
    					<th class="text-center">{$lang['handler']}</th>
    				</tr>
    				{loop $list $key $val}
    				<tr>
    					<td class="text-center"><input type="checkbox" name="id[]" value="{$val['openid']}" class="checks"></td>
    					<td class="text-center">{php echo $key+1;}</td>
    					<td class="text-center">{if $val['headimgurl']}<a href="{$val['headimgurl']}" target="_blank"><img src="{$val['headimgurl']}" width="50" alt="{$val['nickname']}" />{/if}</td>
    					<td class="text-center">{$val['nickname']}</td>
    					<td class="text-center">{$val['name']}</td>
    					<td class="text-center">{if $val['sex'] == 1}男{elseif $val['sex'] == 0}女{else}{/if}</td>
    					<td class="text-center">{$val['province']}</td>
    					<td class="text-center">{$val['city']}</td>
    					<td class="text-center">{date('Y-m-d H:i:s', $val['subscribe_time'])}</td>
    					<td class="text-center">
    						<a href="{url('custom_message_list', array('uid'=>$val['uid']))}" class="btn btn-lg" title="{$lang['custom_message_list']}"><span class="glyphicon glyphicon-eye-open"> </span></a>
    						<a href="{url('send_custom_message', array('uid'=>$val['uid']))}" class="btn btn-lg" title="{$lang['send_custom_message']}" data-toggle="modal" data-target=".bs-edit-modal-lg"><span class="glyphicon glyphicon-comment"> </span></a>
    					</td>
    				</tr>
    				{/loop}
    			</table>
			</div>
			</form>
			<div class="col-lg-2">
                <div class="container-fluid" style="padding:0;">
                	<div class="row" style="margin:0">
                		<div class="panel panel-default">
                			<div class="panel-heading" style="overflow: hidden;">{$lang['group_title']}
                    			 <div class="pull-right ectouch-mb5">   
                            		<a href="{url('wechat/sys_groups')}" class="btn btn-primary">{$lang['group_update']}</a>
                            		<a href="{url('wechat/groups_edit')}" class="btn btn-primary" data-toggle="modal" data-target=".bs-edit-modal-lg">{$lang['add']}</a>
                            	</div>
                			</div>
                		<table class="table table-hover table-bordered table-striped">
                			{loop $group_list $key $val}
                			<tr>
                				<td align="center"><a href="{url('subscribe_search', array('group_id'=>$val['group_id']))}">{$val['name']} <span class="badge">{$val['count']}</span></a></td>
                				<td align="center">{if $val['group_id'] != 0  && $val['group_id'] != 1 && $val['group_id'] != 2}<a href="{url('wechat/groups_edit', array('id'=>$val['id']))}" class="btn btn-primary" data-toggle="modal" data-target=".bs-edit-modal-lg">{$lang['edit']}</a>{/if}</td>
                			</tr>
                			{/loop}
                		</table> 
                		</div>
                	</div>
                </div>
			</div>
		</div>
		{include file="pageview"}
		<script type="text/javascript">
		$(function(){
			$('#check_box').bind('click', function(){
				$('.checks').prop("checked", $(this).prop("checked"));
			})
		})
		</script>
	</div>
 </div>
</div>
<div class="modal fade bs-edit-modal-lg" tabindex="-1" role="dialog" aria-labelledby="myLargeModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg">
    <div class="modal-content"></div>
  </div>
</div>
{include file="pagefooter"}