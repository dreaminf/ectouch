{include file="pageheader"}
<div class="container-fluid" style="padding:0">
    <div class="row" style="margin:0">
        <div class="col-md-12 col-sm-12 col-lg-12" style="padding:0;">
            <div class="panel panel-default">
                <div class="panel-heading" style="overflow:hidden;">
                    微信墙
                    <a href="{url('wall_edit')}" class="btn btn-primary pull-right">创建活动</a>
                </div>
                <table class="table table-hover table-bordered table-striped">
                    <tr class="text-center">
                        <th class="text-center">活动名称</th>
                        <th class="text-center">活动时间</th>
                        <th class="text-center">上墙信息</th>
                        <th class="text-center">参与人数</th>
                        <th class="text-center">状态</th>
                        <th class="text-center">操作</th>
                    </tr>
                    {loop $list $key $l}
                    <tr class="text-center">
                        <td>{$l['name']}</td>
                        <td>{$l['starttime']} ~ {$l['endtime']}</td>
                        <td>{$l['msg_count']}</td>
                        <td>{$l['user_count']}</td>
                        <td>{$l['status']}</td>
                        <td>
                            <a class="btn btn-primary" href="{url('wall_edit', array('id'=>$l['id']))}">设置</a>
                            <a class="btn btn-default" href="{url('wall_user', array('id'=>$l['id']))}">数据</a>
                            <a class="btn btn-default" href="">上墙地址</a>
                            <a class="btn btn-default" href="">大屏幕</a>
                            <a class="btn btn-primary" href="javascript:if(confirm('确定要删除吗？')){location.href='{url('wall_del', array('id'=>$l['id']))}'}">删除</a>
                        </td>
                    </tr>
                    {/loop}
                </table>
            </div>
        </div>
    </div>
</div>
{include file="pagefooter"}