{include file="pageheader"}
<div class="container-fluid" style="padding:0">
  <div class="row" style="margin:0">
    <div class="col-md-2 col-sm-2 col-lg-1" style="padding-right:0;">{include file="wechat_left_menu"}</div>
    <div class="col-md-10 col-sm-10 col-lg-11" style="padding-right:0;">
      <div class="panel panel-default">
        <div class="panel-heading">图文回复添加</div>
      	<form action="{url('article_edit')}" method="post" enctype="multipart/form-data" class="form-horizontal" role="form">
            <table id="general-table" class="table table-hover table-bordered table-striped">
            
            <tr>
                <td width="200">关键词:</td>
                <td><div class="col-md-4">
                    <input type='text' name='data[keywords]' value="{$article['keywords']}" class="form-control input-sm" />
                    <span class="help-block">多个关键词请用半角逗号","格开：例如：ECTouch,ecmoban,模版堂</span>
                  </div></td>
              </tr>
             <tr>
                <td width="200">标题:</td>
                <td><div class="col-md-4">
                    <input type='text' name='data[title]' value="{$article['title']}" class="form-control input-sm" />
                  </div></td>
              </tr>
              <tr>
                <td width="200">作者（选填）:</td>
                <td><div class="col-md-4">
                    <input type='text' name='data[author]' value="{$article['author']}" class="form-control input-sm" />
                  </div></td>
              </tr>
              <tr>
                <td width="200">封面:</td>
                <td><div class="col-md-4">
                    <input type="text" value="{$article['file']}" name="file_path" class="form-control input-sm">
                    <input type='file' name="pic" />
                    <span class="help-block">大图片建议尺寸：900像素 * 500像素</span>
                    </div>
                    <div clas="col-md-1">
                    <a href="javascript:;" class="glyphicon glyphicon-picture ectouch-fs16"  data-toggle="modal" data-target=".bs-edit-modal-lg" title="预览"></a>
                  </div></td>
              </tr>
              <tr>
                <td width="200">封面图片显示在正文中:</td>
                <td><div class="col-md-4 btn-group" data-toggle="buttons">
                    <label class="btn btn-primary {if $article['is_show'] == 1}active{/if}">
                      <input type="radio" name="data[is_show]" value="1" {if $article['is_show'] == 1}checked{/if} />{$lang['yes']}
                    </label>
                    <label class="btn btn-primary {if $article['is_show'] == 0}active{/if}">
                      <input type="radio" name="data[is_show]" value="0" {if $article['is_show'] == 0}checked{/if} />{$lang['no']}
                    </label>
                  </div></td>
              </tr>
              <tr>
                <td width="200">摘要:</td>
                <td><div class="col-md-4">
                    <textarea class="form-control" name="data[digest]">{$article['digest']}</textarea>
                  </div></td>
              </tr>
              <tr>
                <td width="200">正文:</td>
                <td><div class="col-md-9">
                    <script id="container" name="content" type="text/plain" style="width:810px; height:360px;">{html_out($article['content'])}</script>
                  </div></td>
              </tr>
              <tr>
                <td width="200">原文链接:</td>
                <td><div class="col-md-4">
                    <input type='text' name='data[link]' value="{$article['link']}" class="form-control input-sm" />
                  </div></td>
              </tr>
              <tr>
                <td width="200">排序:</td>
                <td><div class="col-md-2">
                    <input type='text' name='data[sort]' value="{$article['sort']}" class="form-control input-sm" />
                  </div></td>
              </tr>
              <tr>
                <td width="200"></td>
                <td><div class="col-md-4">
                    <input type="hidden" name="id" value="{$article['id']}" />
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
        </div>
      <div class="modal-body">
        <img src="{$article['file']}" class="img-responsive" />
      </div>
    </div>
  </div>
</div>
<script type="text/javascript" src="__PUBLIC__/ueditor/ueditor.config.js"></script>
<script type="text/javascript" src="__PUBLIC__/ueditor/ueditor.all.min.js"></script>
<script type="text/javascript">
var ue = UE.getEditor('container');
$(function(){
	//模态框被隐藏之后清除数据
	$(".bs-edit-modal-lg").on("hidden.bs.modal", function() {
	    $(this).removeData("bs.modal");
	});
})
</script>
{include file="pagefooter"}