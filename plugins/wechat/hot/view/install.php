<link rel="stylesheet" href="/ECTouch/uploads/data/common/colorbox/colorbox.css" />
<link rel="stylesheet" type="text/css" href="/ECTouch/uploads/data/assets/admin/css/jquery.datetimepicker.css" />
<style>
.article{border:1px solid #ddd;padding:5px 5px 0 5px;}
.cover{height:160px; position:relative;margin-bottom:5px;overflow:hidden;}
.article .cover img{width:100%; height:auto;}
.article span{height:40px; line-height:40px; display:block; z-index:5; position:absolute;width:100%;bottom:0px; color:#FFF; padding:0 10px; background-color:rgba(0,0,0,0.6)}
.article_list{padding:5px;border:1px solid #ddd;border-top:0;overflow:hidden;}
.thumbnail{padding:0;}
</style>
<form action="{url('edit')}" method="post" class="form-horizontal" role="form">
<table class="table table-hover ectouch-table">
    <tr>
        <td width="200">功能名称</td>
        <td><div class="col-md-4">{$config['name']}</div></td>
    </tr>
    <tr>
        <td width="200">关键词</td>
        <td><div class="col-md-4">{$config['command']}</div></td>
    </tr>
    <tr>
        <td width="200">扩展词</td>
        <td>
            <div class="col-md-4">
                <input type="text" name="data[keywords]" class="form-control" value="{$config['keywords']}" />
                <p class="help-block">多个变形词，请用“,”隔开</p>
            </div>
        </td>
    </tr>
    <tr>
        <td width="200">积分赠送</td>
        <td>
            <div class="col-md-4 col-sm-4">
                <div class="btn-group" data-toggle="buttons">
                    <label class="btn btn-primary {if $config['config']['point_status']}active{/if}">
                        <input type="radio" name="cfg_value[point_status]" {if $config['config']['point_status']}checked{/if} value="1" />开启
                    </label>
                    <label class="btn btn-primary {if empty($config['config']['point_status'])}active{/if}">
                        <input type="radio" name="cfg_value[cfg_value[point_status]]" {if $config['config']['point_status']}checked{/if} value="0" />关闭
                    </label>
                </div>
           </div>
        </td>
    </tr>
    <tr>
        <td width="200">积分值</td>
        <td>
            <div class="col-md-4 col-sm-4">
                <input type="text" name="cfg_value[point_value]" class="form-control" value="{$config['config']['point_value']}" />
           </div>
        </td>
    </tr>
   <tr>
        <td width="200">有效次数</td>
        <td>
            <div class="col-md-4 col-sm-4">
                <input type="text" name="cfg_value[point_num]" class="form-control" value="{$config['config']['point_num']}" />
           </div>
        </td>
    </tr>
    <tr>
        <td width="200">时间间隔</td>
        <td>
            <div class="col-md-4 col-sm-4">
                <select name="cfg_value[point_interval]" class="form-control">
                        <option value="86400" {if $config['config']['point_interval'] == 86400}selected{/if}>24小时</option>
                        <option value="3600" {if $config['config']['point_interval'] == 3600}selected{/if}>1小时</option>
                        <option value="60" {if $config['config']['point_interval'] == 60}selected{/if}>1分钟</option>
                </select>
           </div>
        </td>
    </tr>                                     
    <tr>
        <td></td>
        <td>
            <div class="col-md-4">
                <input type="hidden" name="data[command]" value="{$config['command']}" />
                <input type="hidden" name="data[name]" value="{$config['name']}" />
                <input type="hidden" name="data[author]" value="{$config['author']}">
                <input type="hidden" name="data[website]" value="{$config['website']}">
                <input type="hidden" name="handler" value="{$config['handler']}">
                <input type="submit" name="submit" class="btn btn-primary" value="确认" />
                <input type="reset" name="reset" class="btn btn-default" value="重置" />
            </div>
        </td>
    </tr>
</table>
</form>
<script src="__PUBLIC__/colorbox/jquery.colorbox-min.js"></script>
<script src="__ASSETS__/js/jquery.datetimepicker.js"></script>
<script type="text/javascript">
    //iframe显示
    $(".iframe").colorbox({iframe:true, width:"60%", height:"60%"});
    //日历显示
    $("#starttime, #endtime").datetimepicker({
    	lang:'ch',
    	format:'Y-m-d',
    	timepicker:false    
    });
    //添加奖项
    function addprize(obj){
    	var html = '<tr><td class="text-center"><a href="javascript:;" class="glyphicon glyphicon-minus" onClick="delprize(this)"></a></td><td class="text-center"><input type="text" name="cfg_value[prize_num][]" class="form-control" placeholder="例如：一等奖"></td><td class="text-center"><input type="text" name="cfg_value[prize_name][]" class="form-control" placeholder="例如：法拉利跑车"></td><td class="text-center"><input type="text" name="cfg_value[prize_count][]" class="form-control" placeholder="例如：3"></td><td class="text-center"><input type="text" name="cfg_value[prize_prob][]"  class="form-control" placeholder="例如：1%"></td></tr>';
        $(obj).parent().parent().parent().append(html);
    }
    //删除奖项
    function delprize(obj){
        $(obj).parent().parent().remove();
    }
</script>