<div class="modal-header">
	<button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">&times;</span><span class="sr-only">Close</span></button>
	<h4 class="modal-title" id="myModalLabel">{$lang['group_edit']}</h4>
</div>
<div class="modal-body">
	<form action="{url('wechat/groups_edit')}" method="post" enctype="multipart/form-data" class="form-horizontal" role="form">
      <table id="general-table" class="table table-hover ectouch-table">
       <tr>
          <td width="200">{$lang['group_name']}:</td>
          <td><div class="col-md-4">
              <input type='text' name='name' maxlength="20" value="{$group['name']}" class="form-control input-sm" />
            </div></td>
        </tr>
        <tr>
          <td width="200"></td>
          <td><div class="col-md-4">
              	<input type="hidden" name="id" value="{$group['id']}" />
              	<input type="hidden" name="group_id" value="{$group['group_id']}" />
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