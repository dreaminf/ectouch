<div class="modal-header">
	<button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">&times;</span><span class="sr-only">Close</span></button>
	<h4 class="modal-title" id="myModalLabel">{$lang['add'].$lang['qrcode']}</h4>
</div>
<div class="modal-body">
	<form action="{url('qrcode_edit')}" method="post" enctype="multipart/form-data" class="form-horizontal" role="form">
      <table id="general-table" class="table table-hover table-bordered table-striped">
       <tr>
          <td width="200">{$lang['qrcode_type']}:</td>
          <td><div class="col-md-4">
              <select class="form-control input-sm" name="data[type]">
              	<option value="0">{$lang['qrcode_short']}</option>
              	<option value="1">{$lang['qrcode_forever']}</option>
              </select>
            </div></td>
        </tr>
        <tr>
          <td width="200">{$lang['qrcode_valid_time']}:</td>
          <td><div class="col-md-6">
              <input type="text" name="data[expire_seconds]" class="form-control input-sm" />
               <span class="help-block">{$lang['qrcode_help1']}</span>
            </div></td>
        </tr>
        <tr>
          <td width="200">{$lang['qrcode_function']}:</td>
          <td><div class="col-md-6">
              <input type="text" name="data[function]" class="form-control input-sm" />
            </div></td>
        </tr>
        <tr>
          <td width="200">{$lang['qrcode_scene_value']}:</td>
          <td><div class="col-md-6">
              <input type="text" name="data[scene_id]" class="form-control input-sm" />
              <span class="help-block">{$lang['qrcode_help2']}</span>
            </div></td>
        </tr>
        <tr>
          <td width="200">{$lang['wechat_status']}:</td>
          <td><div class="col-md-10">
          		<div class="btn-group" data-toggle="buttons">
	              	<label class="btn btn-primary active">
        					  <input type="radio" name="data[status]" value="1" checked />{$lang['enabled']}
        					</label>
        					<label class="btn btn-primary">
        					  <input type="radio" name="data[status]" value="0" />{$lang['disabled']}
        					</label>
				</div>
            </div></td>
        </tr>
        <tr>
          <td width="200">{$lang['sort_order']}:</td>
          <td><div class="col-md-2">
              <input type="text" name="data[sort]" class="form-control input-sm" />
            </div></td>
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
<script type="text/javascript">
$(function(){
	//模态框被隐藏之后清除数据
	$(".bs-edit-modal-lg").on("hidden.bs.modal", function() {
	    $(this).removeData("bs.modal");
	});
})
</script>