{include file="pageheader"}
<div class="container-fluid" style="padding:0">
  <div class="row" style="margin:0">
    <div class="col-md-2 col-sm-2 col-lg-1" style="padding-right:0;">{include file="wechat_left_menu"}</div>
    <div class="col-md-10 col-sm-10 col-lg-11" style="padding-right:0;">
      <div class="panel panel-default">
        <div class="panel-heading"><a href="{url('mass_message')}" class="btn btn-primary">群发信息</a><a href="{url('mass_list')}" class="btn btn-default">发送记录</a></div>
        <div class="panel-body bg-danger">
            请注意：
            <span class="help-block">1.该接口暂时仅提供给已微信认证的服务号</span>
            <span class="help-block">2.用户每月只能接收4条群发消息，多于4条的群发将对该用户发送失败。</span>
            <span class="help-block">3.群发图文消息的标题上限为64个字节,群发内容字数上限为1200个字符、或600个汉字。</span>
            <span class="help-block">4.在返回成功时，意味着群发任务提交成功，并不意味着此时群发已经结束，所以，仍有可能在后续的发送过程中出现异常情况导致用户未收到消息，如消息有时会进行审核、服务器不稳定等。此外，群发任务一般需要较长的时间才能全部发送完毕，请耐心等待。</span>
        </div>
      	<form action="{url('mass_message')}" method="post" enctype="multipart/form-data" class="form-horizontal" role="form">
            <table id="general-table" class="table table-hover table-bordered table-striped">  
            <tr>
                <td width="200">选择分组:</td>
                <td><div class="col-md-2">
                    <select name="group_id" class="form-control input-sm">
                        {loop $groups $val}
                        <option value="{$val['group_id']}">{$val['name']}</option>
                        {/loop}
                    </select>
                  </div></td>
              </tr>
              <tr>
                <td width="200">图文信息:</td>
                <td><div class="col-md-4">
                    <a class="btn btn-primary" data-toggle="modal" data-target=".bs-edit-modal-lg">选择图文信息</a>
                    <span class="help-block">一个图文消息支持1到10条图文，多于10条会发送失败</span>
                  </div></td>
              </tr>
              <tr>
                <td width="200"></td>
                <td><div class="col-md-3 ajax-data"></div></td>
              </tr>
              <tr>
                <td width="200"></td>
                <td><div class="col-md-4">
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
<style>
.article{border:1px solid #ddd;padding:5px 5px 0 5px;}
.cover{height:160px; position:relative;margin-bottom:5px;overflow:hidden;}
.article .cover img{width:100%; height:auto;}
.article span{height:40px; line-height:40px; display:block; z-index:5; position:absolute;width:100%;bottom:0px; color:#FFF; padding:0 10px; background-color:rgba(0,0,0,0.6)}
.article_list{padding:5px;border:1px solid #ddd;border-top:0;overflow:hidden;}
.radio label{width:100%;position:relative;padding:0;}
.radio .news_mask{position:absolute;left:0;top:0;background-color:#000;opacity:0.5;width:100%;height:100%;z-index:10;}
</style>
<div class="modal fade bs-edit-modal-lg" tabindex="-1" role="dialog" aria-labelledby="myLargeModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
        <div class="modal-header">
            <button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">&times;</span><span class="sr-only">Close</span></button>
            <h4 class="modal-title" id="myModalLabel">选择素材</h4>
        </div>
      <div class="modal-body" style="overflow:hidden;">
      <div class="col-md-5 col-sm-5 col-sm-offset-1 col-md-offset-1">
      {loop $article $key $val}
        {if $key%2 == 0}
            <div class="radio">
                <label>
                    <input type="radio" name="article_id" value="{$val['id']}" class="hidden" >
                    <div>
                    {if $val['article_id']}
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
                    {else}
                    <div class="article">
                        <h4>{$val['title']}</h4>
                        <p>{date('Y年m月d日', $val['add_time'])}</p>
                        <div class="cover"><img src="{$val['file']}" /></div>
                        <p>{$val['content']}</p>
                    </div>
                    {/if}
                    </div>
                    <div class="news_mask hidden"></div>
                </label>
            </div>
        {/if}
      {/loop}
      </div>
      <div class="col-md-5 col-sm-5">
        {loop $article $key $val}
        {if ($key+1)%2 == 0}
            <div class="radio">
                <label>
                    <input type="radio" name="article_id" value="{$val['id']}" class="hidden" >
                    <div>
                    {if $val['article_id']}
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
                    {else}
                    <div class="article">
                        <h4>{$val['title']}</h4>
                        <p>{date('Y年m月d日', $val['add_time'])}</p>
                        <div class="cover"><img src="{$val['file']}" /></div>
                        <p>{$val['content']}</p>
                    </div>
                    {/if}
                    </div>
                    <div class="news_mask hidden"></div>
                </label>
            </div>
        {/if}
        {/loop}
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
	    var html = $("input[name=article_id]:checked").siblings("div").html();
	    var id = $("input[name=article_id]:checked").val();
	    if(html){
		    html += '<input type="hidden" name="article_id" value="'+id+'">';
	        $(".ajax-data").html(html);
		}
	});
	//选择遮罩
	$("input[name=article_id]").click(function(){
	    if($(this).is(":checked")){
		    $(".news_mask").addClass("hidden");
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