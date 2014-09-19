<div class="modal-header">
	<button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">&times;</span><span class="sr-only">Close</span></button>
	<h4 class="modal-title" id="myModalLabel">发送客服消息</h4>
</div>
<div class="modal-body">
	<form action="{url('send_custom_message')}" method="post" enctype="multipart/form-data" class="form-horizontal" role="form">
      <table id="general-table" class="table table-hover table-bordered table-striped">
        <tr>
          <td width="200">{$lang['message_content']}:</td>
          <td><div class="col-md-7">
              <textarea name="data[msg]" class="form-control" row="3"></textarea>
              <span class="help-block">{$lang['sub_help1']}</span>
              </div></td>
        </tr>
        <tr>
          <td width="200">{$lang['sub_nickname']}:</td>
          <td><div class="col-md-6">{$info['nickname']}</div></td>
        </tr>
        <tr>
          <td width="200">{$lang['sub_openid']}:</td>
          <td><div class="col-md-6">{$info['openid']}</div></td>
        </tr>
        <tr>
          <td width="200"></td>
          <td><div class="col-md-4">
                <input type="hidden" name="data[uid]" value="{$info['uid']}" />
                <input type="hidden" name="openid" value="{$info['openid']}" />
				        <input type="submit" value="{$lang['button_send']}" class="btn btn-primary" />
              	<input type="reset" value="{$lang['button_reset']}" class="btn btn-default" />
            </div></td>
        </tr>
        </table>
	</form>
</div>
<script type="text/javascript">
$(function(){
	//模态框被隐藏之后清除数据
	$(".bs-edit-modal-lg").on("hidden.bs.modal", function() {
	    $(this).removeData("bs.modal");
	});
})
</script>