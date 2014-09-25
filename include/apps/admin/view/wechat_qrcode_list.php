{include file="pageheader"}
<div class="row" style="margin:0">
	<div class="pull-right ectouch-mb5">
		<a href="{url('qrcode_edit')}" class="btn btn-primary fancybox fancybox.iframe">{$lang['add'].$lang['qrcode']}</a>
	</div>
</div>
<div class="container-fluid" style="padding:0">
	<div class="row" style="margin:0">
	  <div class="col-md-2 col-sm-2 col-lg-1" style="padding-right:0;">{include file="wechat_left_menu"}</div>
	  <div class="col-md-10 col-sm-10 col-lg-11"  style="padding-right:0;">
		<div class="panel panel-default">
			<div class="panel-heading">{$lang['qrcode']}</div>
				<table class="table table-hover table-bordered table-striped">
					<tr>
						<th style="text-align:center">{$lang['num_order']}</th>
						<th style="text-align:center">{$lang['qrcode_scene']}</th>
						<th style="text-align:center">{$lang['qrcode_type']}</th>
						<th style="text-align:center">{$lang['qrcode_function']}</th>
						<th style="text-align:center">{$lang['sort_order']}</th>
						<th style="text-align:center">{$lang['handler']}</th>
					</tr>
					{loop $list $key $val}
					<tr>
						<td align="center">{php echo $key+1;}</td>
						<td align="center">{$val['scene_id']}</td>
						<td align="center">{if $val['type'] == 0}{$lang['qrcode_short']}{else}{$lang['qrcode_forever']}{/if}</td>
						<td align="center">{$val['function']}</td>
						<td align="center">{$val['sort']}</td>
						<td align="center" width="20%">
							<a href="{url('qrcode_get', array('id'=>$val['id']))}" class="btn btn-primary fancybox fancybox.iframe">{$lang['qrcode_get']}</a>
							{if $val['status'] == 1}
							<a href="{url('qrcode_edit', array('id'=>$val['id'], 'status'=>0))}" class="btn btn-danger">{$lang['disabled']}</a>
							{else}
							<a href="{url('qrcode_edit', array('id'=>$val['id'], 'status'=>1))}" class="btn btn-success">{$lang['enabled']}</a>
							{/if}
							<a href="javascript:if(confirm('{$lang['confirm_delete']}')){window.location.href='{url('qrcode_del', array('id'=>$val['id']))}'}" class="btn btn-primary">{$lang['drop']}</a>
						</td>
					</tr>
					{/loop}
				</table>
			</div>
			{include file="pageview"}
		</div>
	</div>
</div>
{include file="pagefooter"}