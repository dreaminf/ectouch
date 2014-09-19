<!DOCTYPE html>
<html lang="zh-cn">
<head>
<meta charset="utf-8">
<meta http-equiv="X-UA-Compatible" content="IE=edge">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>{$lang['cp_home']}</title>
<link href="__PUBLIC__/bootstrap/css/bootstrap.min.css" rel="stylesheet">
<script src="__PUBLIC__/js/jquery.min.js" type="text/javascript"></script>
<script src="__PUBLIC__/artDialog/jquery.artDialog.js?skin=aero"></script>
<script src="__PUBLIC__/artDialog/plugins/iframeTools.js"></script>
<script src="__PUBLIC__/bootstrap/js/bootstrap.min.js"></script>
<script src="__ASSETS__/js/common.js"></script>
<!--[if lt IE 9]>
<script src="__PUBLIC__/bootstrap/js/html5shiv.min.js"></script>
<script src="__PUBLIC__/bootstrap/js/respond.min.js"></script>
<![endif]-->
<style type="text/css">
body {padding:5px; font-size:12px; margin-bottom:0px; font-family:'微软雅黑'}
.ectouch-table {margin:0;}
.ectouch-table tr:first-child td {border-top:none;}
.ectouch-line {line-height: 1.8;}
.ectouch-mb5, .breadcrumb {margin-bottom:5px}
.ectouch-fs16 {font-size:16px}
.ectouch-fs18 {font-size:18px}
.btn-primary {background-color:#1b9ad5}
.panel-title {font-size:14px}
.pagination {margin-top:0}
.pagination .glyphicon {top:0}
.ectouch-mb{margin-bottom:5px;}
.clear{clear:both;}
.borderno{border:0;}
.article{border:1px solid #ddd;padding:5px 5px 0 5px;}
.cover{h+eight:160px; position:relative;margin-bottom:5px;overflow:hidden;}
.article .cover img{width:100%; height:auto;}
.article span{height:40px; line-height:40px; display:block; z-index:5; position:absolute;width:100%;bottom:0px; color:#FFF; padding:0 10px; background-color:rgba(0,0,0,0.6)}
.article_list{padding:5px;border:1px solid #ddd;border-top:0;overflow:hidden;}
.radio label{width:100%;position:relative;padding:0;}
.radio .news_mask{position:absolute;left:0;top:0;background-color:#000;opacity:0.5;width:100%;height:100%;z-index:10;}
</style>
</head>

<body>
<div class="panel panel-default">
    {if $type == 'news'}
    <div class="panel-body">
    <div class="col-md-4 col-sm-4 col-md-offset-2 col-sm-offset-2">
    {loop $list $key $val}
    {if $key%2 == 0}
        <div class="radio">
            <label>
            <input type="radio" name="id" value="{$val['id']}" file_type="{$type}" file_name="{$val['file_name']}" class="hidden" no_list={$no_list} />
            {if $val['article_id']}
                <div>
                {loop $val['articles'] $k $v}
                    {if $k == 0}
                    <div class="article">
                        <p>{date('Y年m月d日', $v['add_time'])}</p>
                        <div class="cover"><img src="{$v['file']}" /><span>{$v['title']}</span></div>
                    </div>
                        {else}
                    <div class="article_list">
                        <span>{$v['title']}</span>
                        <img src="{$v['file']}" width="78" height="78" class="pull-right" />
                    </div>
                    {/if}
                {/loop}
                </div>
                {else}
                <div>
                    <div class="article">
                        <h4>{$val['title']}</h4>
                        <p>{date('Y年m月d日', $val['add_time'])}</p>
                        <div class="cover"><img src="{$val['file']}" /></div>
                        <p>{$val['content']}</p>
                    </div>
                </div>
            {/if}
            <div class="news_mask hidden"></div>
        </label>
      </div>
    {/if}
    {/loop}
    </div>
    <div class="col-md-4 col-sm-4">
        {loop $list $key $val}
            {if ($key+1)%2 == 0}
            <div class="radio">
            <label>
            <input type="radio" name="id" value="{$val['id']}" file_type="{$type}" file_name="{$val['file_name']}" class="hidden" no_list={$no_list} />
            {if $val['article_id']}
                <div>
                {loop $val['articles'] $k $v}
                    {if $k == 0}
                    <div class="article">
                        <p>{date('Y年m月d日', $v['add_time'])}</p>
                        <div class="cover"><img src="{$v['file']}" /><span>{$v['title']}</span></div>
                    </div>
                        {else}
                    <div class="article_list">
                        <span>{$v['title']}</span>
                        <img src="{$v['file']}" width="78" height="78" class="pull-right" />
                    </div>
                    {/if}
                {/loop}
                </div>
                {else}
                <div>
                    <div class="article">
                        <h4>{$val['title']}</h4>
                        <p>{date('Y年m月d日', $val['add_time'])}</p>
                        <div class="cover"><img src="{$val['file']}" /></div>
                        <p>{$val['content']}</p>
                    </div>
                </div>
            {/if}
            <div class="news_mask hidden"></div>
        </label>
      </div>
            {/if}
        {/loop}
    </div>
    </div>
    {else}
    <ul class="list-group">
    {loop $list $key $val}
    <li class="list-group-item">
        <div class="form-group">
            <div class="col-md-6 col-sm-6 radio">
              <label><input type='radio' name='id' value="{$val['id']}" data="{$val['file']}" file_type="{$type}" file_name="{$val['file_name']}" />{$val['file_name']}</label>
            </div>
            <div class="col-md-3 col-sm-3">{$val['size']}</div>
            <div class="col-md-3 col-sm-3">{date('Y-m-d H:i:s', $val['add_time'])}</div>
        </div>
        <div class="clear">
        {if $val['type'] == 'voice'}
            <img src="__PUBLIC__/images/voice.png" class="img-rounded" width="100" height="70" />
        {elseif $val['type'] == 'video'}
            <img src="__PUBLIC__/images/video.png" class="img-rounded" width="100" height="70" />
        {else}
            <img src="{$val['file']}" class="img-rounded" width="100" height="70" />
        {/if}
        </div>
    </li>
    {/loop}
    </ul>
    {/if}
    <div class="panel-footer">
        {include file="pageview"}
		<input type="submit" value="{$lang['button_submit']}" class="btn btn-primary" name="file_submit" />
      	<input type="reset" value="{$lang['button_reset']}" class="btn btn-default" name="reset" onClick="window.parent.$('.iframe').colorbox.close();"  />
    </div>
</div>
<script type="text/javascript">
$(function(){
	//选择素材
	$("input[name=file_submit]").click(function(){
		var obj = $("input[name=id]:checked");
	    var id = obj.val();
	    var file = obj.attr("data");
	    var type = obj.attr("file_type");
	    var file_name = obj.attr("file_name");
	    if(type == 'voice'){
	    	window.parent.$(".content").html("<input type='hidden' name='media_id' value='"+id+"'><img src='__PUBLIC__/images/voice.png' class='img-rounded' /><span class='help-block'>"+file_name+"</span>");		     
		}
	    else if(type == 'video'){
	    	window.parent.$(".content").html("<input type='hidden' name='media_id' value='"+id+"'><img src='__PUBLIC__/images/video.png' class='img-rounded' /><span class='help-block'>"+file_name+"</span>");		     
		}
	    else if(type == 'news'){
		    var no_list = obj.attr("no_list");
	        var html = obj.siblings("div").html();
	        if(no_list){
	        	html += '<input type="hidden" name="cfg_value[media_id]" value="'+id+'">';
		    }
	        else{
	        	html += '<input type="hidden" name="media_id" value="'+id+'">';
		    }
	        window.parent.$(".content").html(html);
		}
	    else{
		    window.parent.$(".content").html("<input type='hidden' name='media_id' value='"+id+"'><img src='"+file+"' class='img-rounded' />");
		}
	    window.parent.$(".iframe").colorbox.close();
	});
	$("input[name=id]").click(function(){
	    if($(this).is(":checked")){
		    $(".news_mask").addClass("hidden");
	        $(this).siblings(".news_mask").removeClass("hidden");
		}
	});
})
</script>
{include file="pagefooter"}