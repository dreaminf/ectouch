{include file="pageheader"}
<div class="container-fluid" style="padding:0">
	<div class="row" style="margin:0">
	  <div class="col-md-12 col-sm-12 col-lg-12" style="padding:0;">
		<div class="panel panel-default">
			<div class="panel-heading">功能扩展</div>
			<table class="table table-hover table-striped table-bordered">
                <tr>
                    <th class="text-center">功能名称</th>
                    <th class="text-center">关键词</th>
                    <th class="text-center">扩展词</th>
                    <th class="text-center">插件作者</th>
                    <th class="text-center">插件来源</th>
                    <th class="text-center">操作</th>
                </tr>
                {loop $modules $val}
                <tr>
                    <td class="text-center">{$val['name']}</td>
                    <td class="text-center">{$val['command']}</td>
                    <td class="text-center">{$val['keywords']}</td>
                    <td class="text-center">{$val['author']}</td>
                    <td class="text-center">{$val['website']}</td>
                    <td class="text-center">
                        {if isset($val['enable']) && !empty($val['enable'])}
                        <a href="{url('edit', array('ks'=>$val['command'], 'handler'=>'edit'))}" class="btn btn-primary">编辑</a>
                        <a href="javascript:if(confirm('{$lang['confirm_uninstall']}')){window.location.href='{url('uninstall', array('ks'=>$val['command']))}'};" class="btn btn-default">卸载</a>
                        {else}
                        <a href="{url('edit', array('ks'=>$val['command']))}" class="btn btn-primary">安装</a>
                        {/if}
                        {if $val['enable'] == 1 && $val['config']['haslist'] == 1}<a href="{url('winner_list', array('ks'=>$val['command']))}" class="btn btn-default">查看记录</a>{/if}
                    </td>
                </tr>
                {/loop}
			</table>
		</div>
	  </div>
	</div>
</div>
{include file="pagefooter"}