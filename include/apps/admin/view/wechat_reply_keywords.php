{include file="pageheader"}
<style>
.article{border:1px solid #ddd;padding:5px 5px 0 5px;}
.cover{height:160px; position:relative;margin-bottom:5px;overflow:hidden;}
.article .cover img{width:100%; height:auto;}
.article span{height:40px; line-height:40px; display:block; z-index:5; position:absolute;width:100%;bottom:0px; color:#FFF; padding:0 10px; background-color:rgba(0,0,0,0.6)}
.article_list{padding:5px;border:1px solid #ddd;border-top:0;overflow:hidden;}
</style>
<div class="container-fluid" style="padding:0">
	<div class="row" style="margin:0">
	  <div class="col-md-2 col-sm-2 col-lg-1" style="padding-right:0;">{include file="wechat_left_menu"}</div>
	  <div class="col-md-10 col-sm-10 col-lg-11" style="padding-right:0;">
		<div class="panel panel-default">
			<div class="panel-heading">自动回复</div>
			<div class="panel-body">
			     <ul class="nav nav-tabs" role="tablist" id="myTab">
			         <li><a href="{url('reply_subscribe')}">关注自动回复</a></li>
			         <li><a href="{url('reply_msg')}">消息自动回复</a></li>
			         <li class="active"><a href="{url('reply_keywords')}">关键词自动回复</a></li>
			     </ul>
			</div>
			
			<div class="panel-body">
                <div class="form-group">
                    <a href="javascript:;" class="btn btn-success rule_add">添加规则</a>
                </div>
                <div class="form-group hidden rule_form">
        			<form action="{url('rule_edit')}" method="post" class="form-horizontal" role="form">
                      <table id="general-table" class="table table-hover ectouch-table">
                       <tr>
                          <td width="200">规则名称:</td>
                          <td><div class="col-md-6">
                              <input type='text' name='rule_name' class="form-control input-sm" />
                              <span class="help-block">规则名最多60个字</span>
                            </div></td>
                        </tr>
                        <tr>
                          <td width="200">关键字:</td>
                          <td><div class="col-md-6">
                              <input type='text' name='rule_keywords' class="form-control input-sm" />
                              <span class="help-block">添加多个关键字，用","隔开（建议在一个规则里设置一个关键字，以便粉丝获得想要的答案。）</span>
                            </div></td>
                        </tr>
                        <tr>
                          <td width="200">回复:</td>
                          <td>
                            <div class="col-md-6">
                                <div class="panel panel-default">
                                     <div class="panel-heading">
                                      <ul class="nav nav-pills" role="tablist">
                                        <li role="presentation"><a href="javascript:;" class="glyphicon glyphicon-pencil ectouch-fs18" title="文字" type="text"></a></li>
                                        <li role="presentation"><a href="{url('auto_reply', array('type'=>'image'))}" class="glyphicon glyphicon-picture ectouch-fs18 fancybox fancybox.iframe" title="图片" type="image"></a></li>
                                        <li role="presentation"><a href="{url('auto_reply', array('type'=>'voice'))}" class="glyphicon glyphicon-volume-up ectouch-fs18 fancybox fancybox.iframe"  title="语音" type="voice"></a></li>
                                        <li role="presentation"><a href="{url('auto_reply', array('type'=>'video'))}" class="glyphicon glyphicon-film ectouch-fs18 fancybox fancybox.iframe"  title="视频" type="video"></a></li>
                                        <li role="presentation"><a href="{url('auto_reply', array('type'=>'news'))}" class="glyphicon glyphicon-list-alt ectouch-fs18 fancybox fancybox.iframe"  title="图文消息" type="news"></a></li>
                                      </ul>
                                      </div>
                                      <div class="panel-body" style="padding:0;">
                                        <div class="text"><textarea name="content" class="form-control" placeholder="文本内容" rows="6"></textarea></div>
                                        <div class="content col-xs-6 thumbnail borderno hidden"></div>
                                      </div>
                                </div>
                            </div>
                          </td>
                        </tr>
                        <tr>
                          <td width="200"></td>
                          <td><div class="col-md-6">
                                <input type="hidden" name="content_type" value="text" />
                				<input type="submit" value="{$lang['button_submit']}" class="btn btn-primary" />
                              	<input type="reset" value="{$lang['button_reset']}" class="btn btn-default" />
                            </div></td>
                        </tr>
                        </table>
                	</form>
                </div>
                {loop $list $val}
                <div class="panel panel-default">
                    <div class="panel-heading rule_title" style="cursor: pointer;overflow:hidden;">
                        <b>规则名称：</b>{$val['rule_name']}
                        <span class="caret pull-right"></span>
                    </div>
                    <ul class="list-group view hidden">
                        <li class="list-group-item">
                        <div class="row">
                            <div class="col-xs-1 col-md-1"><b>关键词：</b></div>
                            <div class="col-xs-6 col-md-3 borderno">
                                {loop $val['rule_keywords'] $v}
                                    <span class="label label-default">{$v['rule_keywords']}</span>
                                {/loop}
                            </div>
                        </li>
                        <li class="list-group-item">
                        <div class="row">
                            <div class="col-xs-1 col-md-1"><b>回复内容：</b></div>
                            <div class="col-xs-6 col-md-3 thunbnail borderno">
                            {if $val['medias']}<!-- 多图文 -->
                                {loop $val['medias'] $k $v}
                                    {if $k == 0}
                                    <div class="article">
                                        <p></p>
                                        <div class="cover"><img src="{$v['file']}" /><span>{$v['title']}</span></div>
                                    </div>
                                        {else}
                                    <div class="article_list">
                                        <span>{$v['title']}</span>
                                        <img src="{$v['file']}" width="78" height="78" class="pull-right" />
                                    </div>
                                    {/if}
                                {/loop}
                            {elseif $val['media']}<!-- 单图文，图片，语音，视频 -->
                                {if $val['media']['type'] == 'news' && $val['reply_type'] == 'news'}
                                    <div class="article">
                                        <h4>{$val['media']['title']}</h4>
                                        <p>{date('Y年m月d日', $val['media']['add_time'])}</p>
                                        <div class="cover"><img src="{$val['media']['file']}" /></div>
                                        <p>{$val['media']['content']}</p>
                                    </div>
                                {elseif $val['media']['type'] == 'voice'}
                                    <img src="__PUBLIC__/images/voice.png" /><span>{$val['media']['file_name']}</span>
                                {elseif $val['media']['type'] == 'video'}
                                    <img src="__PUBLIC__/images/video.png" /><span>{$val['media']['file_name']}</span>
                                {else}
                                    <img src="{$val['media']['file']}" style="max-width:300px;" />
                                {/if}
                            {else}
                                <span>{$val['content']}</span>
                            {/if}
                            </div>    
                        </div>
                        </li>
                    </ul>
                    <div class="panel-footer">
                        <a href="javascript:;" class="btn btn-primary edit">编辑</a>
                        <a href="javascript:if(confirm('{$lang['confirm_delete']}')){window.location.href='{url('reply_del', array('id'=>$val['id']))}'};" class="btn btn-default">删除</a>
                    </div>
                    <form action="{url('rule_edit')}" method="post" class="form-horizontal hidden" role="form">
                      <table id="general-table" class="table table-hover ectouch-table">
                       <tr>
                          <td width="200">规则名称:</td>
                          <td><div class="col-md-6">
                              <input type='text' name='rule_name' value="{$val['rule_name']}" class="form-control input-sm" />
                              <span class="help-block">规则名最多60个字</span>
                            </div></td>
                        </tr>
                        <tr>
                          <td width="200">关键字:</td>
                          <td><div class="col-md-6">
                              <input type='text' name='rule_keywords' value="{$val['rule_keywords_string']}" class="form-control input-sm" />
                              <span class="help-block">添加多个关键字，用","隔开</span>
                            </div></td>
                        </tr>
                        <tr>
                          <td width="200">回复:</td>
                          <td>
                            <div class="col-md-6">
                                <div class="panel panel-default">
                                     <div class="panel-heading">
                                      <ul class="nav nav-pills" role="tablist">
                                        <li role="presentation"><a href="javascript:;" class="glyphicon glyphicon-pencil ectouch-fs18" title="文字" type="text"></a></li>
                                        <li role="presentation"><a href="{url('auto_reply', array('type'=>'image'))}" class="glyphicon glyphicon-picture ectouch-fs18 fancybox fancybox.iframe" title="图片" type="image"></a></li>
                                        <li role="presentation"><a href="{url('auto_reply', array('type'=>'voice'))}" class="glyphicon glyphicon-volume-up ectouch-fs18 fancybox fancybox.iframe"  title="语音" type="voice"></a></li>
                                        <li role="presentation"><a href="{url('auto_reply', array('type'=>'video'))}" class="glyphicon glyphicon-film ectouch-fs18 fancybox fancybox.iframe"  title="视频" type="video"></a></li>
                                        <li role="presentation"><a href="{url('auto_reply', array('type'=>'news'))}" class="glyphicon glyphicon-list-alt ectouch-fs18 fancybox fancybox.iframe"  title="图文消息" type="news"></a></li>
                                      </ul>
                                      </div>
                                      <div class="panel-body" style="padding:0;">
                                      <div class="{if $val['medias'] || $val['media']}hidden{/if} text"><textarea name="content" class="form-control" placeholder="文本内容" rows="6">{$val['content']}</textarea></div>
                                      <div class="content col-xs-6 thumbnail borderno {if empty($val['medias']) && empty($val['media'])}hidden{/if}">
                                        <input type="hidden" name="media_id" value="{$val['media_id']}" />
                                        {if $val['medias']}<!-- 多图文 -->
                                            {loop $val['medias'] $k $v}
                                                {if $k == 0}
                                                <div class="article">
                                                    <p></p>
                                                    <div class="cover"><img src="{$v['file']}" /><span>{$v['title']}</span></div>
                                                </div>
                                                    {else}
                                                <div class="article_list">
                                                    <span>{$v['title']}</span>
                                                    <img src="{$v['file']}" width="78" height="78" class="pull-right" />
                                                </div>
                                                {/if}
                                            {/loop}
                                        {elseif $val['media']}<!-- 单图文，图片，语音，视频 -->
                                            {if $val['media']['type'] == 'news' && $val['reply_type'] == 'news'}
                                                <div class="article">
                                                    <h4>{$val['media']['title']}</h4>
                                                    <p>{date('Y年m月d日', $val['media']['add_time'])}</p>
                                                    <div class="cover"><img src="{$val['media']['file']}" /></div>
                                                    <p>{$val['media']['content']}</p>
                                                </div>
                                            {elseif $val['media']['type'] == 'voice'}
                                                <img src="__PUBLIC__/images/voice.png" /><span>{$val['media']['file_name']}</span>
                                            {elseif $val['media']['type'] == 'video'}
                                                <img src="__PUBLIC__/images/video.png" /><span>{$val['media']['file_name']}</span>
                                            {else}
                                                <img src="{$val['media']['file']}" style="max-width:300px;" />
                                            {/if}
                                        {/if}
                                        </div>
                                </div>
                            </div>
                          </td>
                        </tr>
                        <tr>
                          <td width="200"></td>
                          <td><div class="col-md-6">
                                <input type="hidden" name="content_type" value="{if $val['medias'] || $val['media']}media{else}text{/if}" />
                                <input type="hidden" name="id" value="{$val['id']}" />
                				<input type="submit" value="{$lang['button_submit']}" class="btn btn-primary" />
                              	<input type="reset" value="{$lang['button_reset']}" class="btn btn-default" />
                            </div></td>
                        </tr>
                        </table>
                	</form>
                </div>
                {/loop}
            </div>
			</div>
		</div>
	</div>
</div>
<script type="text/javascript">
$(function(){
    //添加规则显示隐藏
	$(".rule_add").click(function(){
		if($(".rule_form").hasClass("hidden")){
			$(".rule_form").removeClass("hidden");
		}
		else{
			$(".rule_form").addClass("hidden");
		}
	});
	//选择内容显示以及类型更改
    $(".nav-pills li").click(function(){
        var type = $(this).find("a").attr("type");
        var tab = $(this).parent().parent(".panel-heading").siblings(".panel-body");
        if(type == "text"){
    	    tab.find(".content").addClass("hidden");
            tab.find(".text").removeClass("hidden");
            $("input[name=content_type]").val("text");
        }
    });
    //规则显示
    $(".rule_title").click(function(){
        var obj = $(this).siblings("ul.view");
        if(obj.hasClass("hidden")){
        	obj.removeClass("hidden");
        	$(this).addClass("dropup");
		}
		else{
			obj.addClass("hidden");
			$(this).removeClass("dropup");
		}
    });
    //修改规则显示
    $(".edit").click(function(){
        var obj = $(this).parent().siblings("form");
    	if(obj.hasClass("hidden")){
        	obj.removeClass("hidden");
		}
		else{
			obj.addClass("hidden");
		}
    });
})
</script>
{include file="pagefooter"}