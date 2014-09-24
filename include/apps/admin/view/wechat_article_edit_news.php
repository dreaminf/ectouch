{include file="pageheader"}
<style>
.article{border:1px solid #ddd;padding:5px 5px 0 5px;}
.cover{height:160px; position:relative;margin-bottom:5px;overflow:hidden;}
.article .cover img{width:100%; height:auto;}
.article span{height:40px; line-height:40px; display:block; z-index:5; position:absolute;width:100%;bottom:0px; color:#FFF; padding:0 10px; background-color:rgba(0,0,0,0.6)}
.article_list{padding:5px;border:1px solid #ddd;border-top:0;overflow:hidden;}
.checkbox label{width:100%;position:relative;padding:0;}
.checkbox .news_mask{position:absolute;left:0;top:0;background-color:#000;opacity:0.5;width:100%;height:100%;z-index:10;}
</style>
<div class="container-fluid" style="padding:0">
  <div class="row" style="margin:0">
    <div class="col-md-2 col-sm-2 col-lg-1" style="padding-right:0;">{include file="wechat_left_menu"}</div>
    <div class="col-md-10 col-sm-10 col-lg-11" style="padding-right:0;">
      <div class="panel panel-default">
        <div class="panel-heading">多图文编辑</div>
      	<form action="{url('article_edit_news')}" method="post" enctype="multipart/form-data" class="form-horizontal" role="form">
            <table id="general-table" class="table table-hover ectouch-table">  
              <tr>
                <td width="200">图文信息:</td>
                <td><div class="col-md-4">
                    <a class="btn btn-primary" data-toggle="modal" data-target=".bs-edit-modal-lg">选择图文信息</a>
                    <a class="btn btn-default" href="javascript:if(confirm('{$lang['confirm_delete']}')){window.location.href='{url('article_news_del', array('id'=>$id))}'};">清空重选</a>
                    <span class="help-block">一个图文消息支持1到10条图文，多于10条会发送失败</span>
                  </div></td>
              </tr>
              <tr class="content hidden">
                <td width="200"></td>
                <td><div class="col-md-3 ajax-data">
                    {if $articles}
                    {loop $articles $k $v}
                        {if $k == 0}
                        <div class="article">
                            <input type="hidden" name="article[]" value="{$v['id']}" />
                            <p>{date('Y年m月d日', $v['add_time'])}</p>
                            <div class="cover"><img src="{$v['file']}" /><span>{$v['title']}</span></div>
                        </div>
                            {else}
                        <div class="article_list">
                            <input type="hidden" name="article[]" value="{$v['id']}" />
                            <span>{$v['title']}</span>
                            <img src="{$v['file']}" width="78" height="78" class="pull-right" />
                        </div>
                        {/if}
                    {/loop}
                    {/if}
                  </div></td>
              </tr>
              <tr>
                <td width="200">排序</td>
                <td>
                    <div class="col-md-1">
                        <input type="text" name="sort" class="form-control input-sm" />
                    </div>
                </td>
              </tr>
              <tr>
                <td width="200"></td>
                <td><div class="col-md-4">
                    <input type="hidden" name="id" value="{$id}" />
      				<input type="submit" value="{$lang['button_submit']}" class="btn btn-primary" />
                    <input type="reset" value="{$lang['button_reset']}" class="btn btn-default" />
                  </div></td>
              </tr>
              </table>
      	</form>
      </div>
    </div>
  </div>
</div>
<div class="modal fade bs-edit-modal-lg" tabindex="-1" role="dialog" aria-labelledby="myLargeModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
        <div class="modal-header">
            <button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">&times;</span><span class="sr-only">Close</span></button>
            <h4 class="modal-title" id="myModalLabel">选择素材</h4>
        </div>
      <div class="modal-body">
        <div class="row">
        <div class="col-md-4 col-sm-4 col-md-offset-2 col-sm-offset-2">
            {loop $article $k $v}
            {if $k%2 == 0}
            <div class="checkbox">
            <label>
                <input type="checkbox" name="article[]" value="{$v['id']}" class="hidden artlist" />
                <div class="article">
                    <h4>{$v['title']}</h4>
                    <p>{date('Y年m月d日', $v['add_time'])}</p>
                    <div class="cover"><img src="{$v['file']}" /></div>
                    <p>{$v['content']}</p>
                </div>
                <div class="news_mask hidden"></div>
            </label>
            </div>
            {/if}
            {/loop}
        </div>
        <div class="col-md-4 col-sm-4">
            {loop $article $k $v}
            {if ($k+1)%2 == 0}
            <div class="checkbox">
            <label>
                <input type="checkbox" name="article[]" value="{$v['id']}" class="hidden artlist" />
                <div class="article">
                    <h4>{$v['title']}</h4>
                    <p>{date('Y年m月d日', $v['add_time'])}</p>
                    <div class="cover"><img src="{$v['file']}" /></div>
                    <p>{$v['content']}</p>
                </div>
                <div class="news_mask hidden"></div>
            </label>
            </div>
            {/if}
            {/loop}
        </div>
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-primary confrim"  data-dismiss="modal">确认</button>
        <button type="button" class="btn btn-default" data-dismiss="modal">取消</button>
      </div>
    </div>
  </div>
</div>
<script type="text/javascript">
$(function(){
	//选择图文
	$(".confrim").click(function(){
	    var article = [];
	    $("input[type=checkbox]:checked").each(function(){
	        article.push($(this).val());
		});
	    $.post('{url(get_article)}', {article:article}, function(data){
	        if(data.length > 0){
		        var str = '';
		        for(i=0;i<data.length;i++){
			        if(i == 0 && $(".ajax-data .article").length <= 0){
			            str += '<div class="article"><input type="hidden" name="article[]" value="'+data[i]['id']+'" /><p>'+data[i]['add_time']+'</p><div class="cover"><img src="'+data[i]['file']+'" /><span>'+data[i]['title']+'</span></div></div>';
			        }
			        else{
			            str += '<div class="article_list"><input type="hidden" name="article[]" value="'+data[i]['id']+'" /><span>'+data[i]['title']+'</span><img src="'+data[i]['file']+'" width="78" height="78" class="pull-right"></div>';
				    }
			    }
			    $(".content").removeClass("hidden");
			    $(".ajax-data").append(str);
		    }
	    }, 'json');
	});
	//遮罩
	$(".artlist").click(function(){
	    if($(this).is(":checked")){
	        $(this).siblings(".news_mask").removeClass("hidden");
		}
	});
	//模态框被隐藏之后清除数据
	$(".bs-edit-modal-lg").on("hidden.bs.modal", function() {
	    $(this).removeData("bs.modal");
	});
})
</script>
{include file="pagefooter"}