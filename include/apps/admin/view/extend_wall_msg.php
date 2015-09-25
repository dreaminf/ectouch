{include file="pageheader"}
<div class="container-fluid" style="padding:0">
    <div class="row" style="margin:0">
        <div class="col-md-12 col-sm-12 col-lg-12" style="padding:0;">
            <div class="panel panel-default">
                <div class="panel-heading" style="overflow:hidden;">
                    数据信息
                </div>
                <table class="table table-hover table-bordered table-striped">
                    <tr class="text-center">
                        <th class="text-center">内容</th>
                        <th class="text-center">留言时间</th>
                        <th class="text-center">审核时间</th>
                        <th class="text-center">状态</th>
                        <th class="text-center">操作</th>
                    </tr>
                    {loop $list $key $l}
                    <tr class="text-center">
                        <td>{$l['content']}</td>
                        <td>{$l['addtime']}</td>
                        <td>{$l['checktime']}</td>
                        <td>{$l['status']}</td>
                        <td>
                            {$l['handler']}
                            <a class="btn btn-primary" href="javascript:if(confirm('确定要删除吗？')){location.href='{url('wall_data_del', array('wall_id'=>$wall_id, 'user_id'=>$user_id, 'msg_id'=>$l['id']))}'}">删除</a>
                        </td>
                    </tr>
                    {/loop}
                </table>
                {include file="pageview"}
            </div>
        </div>
    </div>
</div>
{include file="pagefooter"}