{include file="pageheader"}
<div class="container-fluid" style="padding:0">
	<div class="row" style="margin:0">
	  <div class="col-md-2 col-sm-2 col-lg-1" style="padding-right:0;">{include file="wechat_left_menu"}</div>
	  <div class="col-md-10 col-sm-10 col-lg-11" style="padding-right:0;">
		<div class="panel panel-default">
			<div class="panel-heading">编辑功能扩展</div>
			<div class="panel-body">
			<form action="{url('function_edit')}" method="post" class="form-horizontal" role="form">
			<table class="table table-hover ectouch-table">
                <tr>
                    <td width="200">功能名称</td>
                    <td><div class="col-md-4">{$modules['name']}</div></td>
                </tr>
                <tr>
                    <td width="200">关键词</td>
                    <td><div class="col-md-4">{$modules['command']}</div></td>
                </tr>
                <tr>
                    <td width="200">扩展词</td>
                    <td>
                        <div class="col-md-4">
                            <input type="text" name="keywords" class="form-control" value="{$modules['keywords']}" />
                            <p class="help-block">多个变形词，请用“,”隔开</p>
                        </div>
                    </td>
                </tr>
                {if $modules['config']}
                {loop $modules['config'] $key $val}
                {if $val['type'] == 'hidden'}
                <input type="hidden" name="cfg_name[]" value="{$val['name']}" />
                <input type="hidden" name="cfg_value[]" value="{$val['value']}">
                {if $val['help']}
                    <p class="help-block">{$val['help']}</p>
                {/if}
                {elseif $val['type'] == 'checkbox'}
                <tr>
                    <td width="200">{$val['label']}</td>
                    <td>
                        <div class="col-md-6 col-sm-6">
                            <div class="form-group">
                            <script type="text/javascript">
                                //添加奖项
                                function addprize(obj){
                                	var html = '<tr><td class="text-center"><a href="javascript:;" class="glyphicon glyphicon-minus" onClick="delprize(this)"></a></td><td class="text-center"><input type="text" name="prize_num[]" class="form-control" placeholder="例如：一等奖"></td><td class="text-center"><input type="text" name="prize_name[]" class="form-control" placeholder="例如：法拉利跑车"></td><td class="text-center"><input type="text" name="prize_count[]" class="form-control" placeholder="例如：3"></td><td class="text-center"><input type="text" name="prob[]"  class="form-control" placeholder="例如：1%"></td></tr>';
                                    $(obj).parent().parent().parent().append(html);
                                }
                                //删除奖项
                                function delprize(obj){
                                    $(obj).parent().parent().remove();
                                }
                            </script>
                            <table class="table ectouch-table">
                                <tr>
                                    <th class="text-center" width="10%"><a href="javascript:;" class="glyphicon glyphicon-plus" onClick="addprize(this)"></a></th>
                                    <th class="text-center" width="20%">奖项</th>
                                    <th class="text-center" width="20%">奖品</th>
                                    <th class="text-center" width="20%">数量</th>
                                    <th class="text-center" width="20%">概率(总数为100%)</th>
                                </tr>
                                {loop $val['prize'] $v}
                                    <tr>
                                        <td class="text-center"><a href="javascript:;" class="glyphicon glyphicon-minus" onClick="delprize(this)"></a></td>
                                        <td class="text-center"><input type="text" name="prize_num[]" class="form-control" placeholder="例如：一等奖" value="{$v['prize_num']}"></td>
                                        <td class="text-center"><input type="text" name="prize_name[]" class="form-control" placeholder="例如：法拉利跑车" value="{$v['prize_name']}"></td>
                                        <td class="text-center"><input type="text" name="prize_count[]" class="form-control" placeholder="例如：3" value="{$v['prize_count']}"></td>
                                        <td class="text-center"><input type="text" name="prob[]"  class="form-control" placeholder="例如：1%" value="{$v['prob']}"></td></tr>
                                {/loop}
                            </table>
                            </div>
                            {if $val['help']}
                                <p class="help-block">{$val['help']}</p>
                            {/if}
                        </div>
                    </td>
                </tr>
                {else}
                <tr>
                    <td width="200">{$val['label']}</td>
                    <td>
                        <div class=" col-md-4 col-sm-4">
                            {if $val['type'] == 'radio'}
                                <input type="hidden" name="cfg_name[]" value="{$val['name']}" />
                                <div class="btn-group" data-toggle="buttons">
                                    <label class="btn btn-primary {if $val['value']}active{/if}">
                                        <input type="radio" name="cfg_value[]" {if $val['value']}checked{/if} value="1" />开启
                                    </label>
                                    <label class="btn btn-primary {if empty($val['value'])}active{/if}">
                                        <input type="radio" name="cfg_value[]" {if empty($val['value'])}checked{/if} value="0" />关闭
                                    </label>
                                </div>
                                {if $val['help']}
                                    <p class="help-block">{$val['help']}</p>
                                {/if}
                            {elseif $val['type'] == 'text'}
                                <input type="hidden" name="cfg_name[]" value="{$val['name']}" />
                                {if isset($val['calendar']) && !empty($val['calendar'])}
                                <link rel="stylesheet" type="text/css" href="__ASSETS__/css/jquery.datetimepicker.css" />
                                <script type="text/javascript" src="__ASSETS__/js/jquery.datetimepicker.js"></script>
                                <input type="text" name="cfg_value[]" class="form-control" id="datetime{$key}" value="{$val['value']}" />
                                {if $val['help']}
                                    <p class="help-block">{$val['help']}</p>
                                {/if}
                                <script type="text/javascript">
                                $("#datetime{$key}").datetimepicker({
                                	lang:'ch',
                                	format:'Y-m-d',
                                	timepicker:false    
                                });
                                </script>
                                {else}
                                <input type="text" name="cfg_value[]" class="form-control" value="{$val['value']}" />
                                {if $val['help']}
                                    <p class="help-block">{$val['help']}</p>
                                {/if}
                                {/if}
                            {elseif $val['type'] == 'select'}
                                <input type="hidden" name="cfg_name[]" value="{$val['name']}" />
                                <select name="cfg_value[]" class="form-control">
                                    {loop $val['options'] $k $v}
                                        <option value="{$v}" {if $v == $val['value']}selected{/if}>{$k}</option>
                                    {/loop}
                                </select>
                                {if $val['help']}
                                    <p class="help-block">{$val['help']}</p>
                                {/if}
                            {elseif $val['type'] == 'button'}
                            <p>
                                <a href="{url('wechat/auto_reply', array('type'=>'news', 'no_list'=>1))}" class="btn btn-primary iframe">选择素材</a>
                                <span class="text-muted">请选择单图文素材，否则不能使用</span>
                            </p>
                            <link rel="stylesheet" href="__PUBLIC__/colorbox/colorbox.css" />
                            <script src="__PUBLIC__/colorbox/jquery.colorbox-min.js"></script>
                            <style>
                            .article{border:1px solid #ddd;padding:5px 5px 0 5px;}
                            .cover{height:160px; position:relative;margin-bottom:5px;overflow:hidden;}
                            .article .cover img{width:100%; height:auto;}
                            .article span{height:40px; line-height:40px; display:block; z-index:5; position:absolute;width:100%;bottom:0px; color:#FFF; padding:0 10px; background-color:rgba(0,0,0,0.6)}
                            .article_list{padding:5px;border:1px solid #ddd;border-top:0;overflow:hidden;}
                            .thumbnail{padding:0;}
                            </style>
                            <input type="hidden" name="media" value="media" />
                            <div class="content thumbnail borderno">
                                <input type="hidden" name="media_id" value="{$val['media']['id']}">
                                <div class="article">
                                        <h4>{$val['media']['title']}</h4>
                                        <p>{date('Y年m月d日', $val['media']['add_time'])}</p>
                                        <div class="cover"><img src="{$val['media']['file']}" /></div>
                                        <p>{$val['media']['content']}</p>
                                    </div>
                            </div>
                            {if $val['help']}
                                <p class="help-block">{$val['help']}</p>
                            {/if}
                            <script type="text/javascript">
                            $(function(){
                            	$(".iframe").colorbox({iframe:true, width:"60%", height:"60%"});
                            })
                            </script>
                            {else}
                            <input type="text" name="cfg_value[]" value="{$val['value']}" class="form-control" readonly />
                            {if $val['help']}
                                <p class="help-block">{$val['help']}</p>
                            {/if}
                            {/if}
                        </div>
                    </td>
                </tr>
                {/if}
                {/loop}
                {/if}
                <tr>
                    <td></td>
                    <td>
                        <div class="col-md-4">
                            <input type="hidden" name="keywords" value="{$modules['keywords']}" />
                            <input type="submit" name="submit" class="btn btn-primary" value="确认" />
                            <input type="reset" name="reset" class="btn btn-default" value="重置" />
                        </div>
                    </td>
                </tr>
			</table>
			</form>
			</div>
		</div>
	  </div>
	</div>
</div>
{include file="pagefooter"}