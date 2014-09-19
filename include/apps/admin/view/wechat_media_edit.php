<div class="modal-header">
	<button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">&times;</span><span class="sr-only">Close</span></button>
	<h4 class="modal-title" id="myModalLabel">编辑</h4>
</div>
<div class="modal-body">
	<form action="{url('media_edit')}" method="post" class="form-horizontal" role="form">
      <table id="general-table" class="table table-hover table-bordered table-striped">
       <tr>
          <td width="200">名称:</td>
          <td><div class="col-md-4">
              <input type='text' name='file_name' value="{$pic['file_name']}" class="form-control input-sm" />
            </div></td>
        </tr>
        <tr>
          <td width="200"></td>
          <td><div class="col-md-4">
              	<input type="hidden" name="id" value="{$pic['id']}" />
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