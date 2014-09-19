<div class="modal-header">
	<button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">&times;</span><span class="sr-only">Close</span></button>
	<h4 class="modal-title" id="myModalLabel">编辑微信OAuth</h4>
</div>
<div class="modal-body">
	<form action="{url('oauth_edit')}" method="post" class="form-horizontal" role="form">
      <table id="general-table" class="table table-hover table-bordered table-striped">
        <tr>
          <td width="200">规则名称:</td>
          <td><div class="col-md-6">
              <input type="text" name="name" class="form-control input-sm" value="{$rs['name']}" />
            </div></td>
        </tr>
        <tr>
          <td width="200">回调地址:</td>
          <td><div class="col-md-6">
              <input type="text" name="data[redirect_uri]" class="form-control input-sm" value="{$rs['config']['redirect_uri']}" />
            </div></td>
        </tr>
          <td width="200"></td>
          <td><div class="col-md-4">
                <input type="hidden" name="data[enable]" value="{$rs['config']['enable']}" />
                <input type="hidden" name="data[count]" value="{$rs['config']['count']}" />
                <input type="hidden" name="data[contents]" value="{$rs['config']['contents']}" />
				<input type="submit" value="{$lang['button_submit']}" class="btn btn-primary" />
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