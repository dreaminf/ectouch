<div class="modal-header">
	<button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">&times;</span><span class="sr-only">Close</span></button>
	<h4 class="modal-title" id="myModalLabel">{$lang['menu_edit']}</h4>
</div>
<div class="modal-body">
	<form action="{url('wechat/menu_edit')}" method="post" enctype="multipart/form-data" class="form-horizontal" role="form">
      <table id="general-table" class="table table-hover table-bordered table-striped">
       <tr>
          <td width="200">{$lang['menu_parent']}:</td>
          <td><div class="col-md-4">
              <select name="data[pid]" class="form-control input-sm">
              	<option value="">{$lang['menu_select']}</option>
              	{loop $top_menu $m}
              		<option value="{$m['id']}" {if $info['pid'] == $m['id']}selected{/if}>{$m['name']}</option>
              	{/loop}
              </select>
            </div></td>
        </tr>
        <tr>
          <td width="200">{$lang['menu_name']}:</td>
          <td><div class="col-md-4">
              <input type="text" name="data[name]" class="form-control" value="{$info['name']}" />
            </div></td>
        </tr>
        <tr>
          <td width="200">{$lang['menu_type']}:</td>
          <td><div class="col-md-10">
          		<div class="btn-group" data-toggle="buttons">
	              	<label class="btn btn-primary {if $info['type'] == 'click' || empty($info['type'])}active{/if} click">
					  <input type="radio" name="data[type]" value="click" {if $info['type'] == 'click' || empty($info['type'])}checked{/if} />{$lang['menu_click']}
					</label>
					<label class="btn btn-primary {if $info['type'] == 'view'}active{/if} click">
					  <input type="radio" name="data[type]" value="view" {if $info['type'] == 'view'}checked{/if} />{$lang['menu_view']}
					</label>
				</div>
            </div></td>
        </tr>
        <tr id="click" {if $info['type'] == 'view'}class="hidden"{/if}>
          <td width="200">{$lang['menu_keyword']}:</td>
          <td><div class="col-md-4">
              <input type="text" name="data[key]" class="form-control" value="{$info['key']}" />
            </div></td>
        </tr>
        <tr id="view" {if $info['type'] == 'click'}class="hidden"{/if}>
          <td width="200">{$lang['menu_url']}:</td>
          <td><div class="col-md-10">
              <input type="text" name="data[url]" class="form-control" value="{$info['url']}" />
              <span class="help-block">{$lang['menu_help1']}</span>
            </div></td>
        </tr>
        <tr>
          <td width="200">{$lang['menu_show']}:</td>
          <td><div class="col-md-10">
          		<div class="btn-group" data-toggle="buttons">
	              	<label class="btn btn-primary {if $info['status'] == 1}active{/if}">
					  <input type="radio" name="data[status]" value="1" {if $info['status'] == 1}checked{/if} />{$lang['yes']}
					</label>
					<label class="btn btn-primary {if $info['status'] == 0}active{/if}">
					  <input type="radio" name="data[status]" value="0" {if $info['status'] == 0}checked{/if} />{$lang['no']}
					</label>
				</div>
            </div></td>
        </tr>
        <tr>
          <td width="200">{$lang['sort_order']}:</td>
          <td><div class="col-md-10">
              <input type="text" name="data[sort]" class="form-control" value="{$info['sort']}" />
            </div></td>
        </tr>
        <tr>
          <td width="200"></td>
          <td><div class="col-md-4">
          		<input type="hidden" name="id" value="{$info['id']}" />
				<input type="submit" value="{$lang['button_submit']}" class="btn btn-primary" />
              	<input type="reset" value="{$lang['button_reset']}" class="btn btn-default" />
            </div></td>
        </tr>
        </table>
	</form>
</div>
<script type="text/javascript">
$(function(){
	$(".click").click(function(){
		var val = $(this).find("input[type=radio]").val();
		if('click' == val && $("#click").hasClass("hidden")){
			$("#view").hide().addClass("hidden");
			$("#click").show().removeClass("hidden");
		}
		else if('view' == val && $("#view").hasClass("hidden")){
			$("#click").hide().addClass("hidden");
			$("#view").show().removeClass("hidden");
		}
	});
	//模态框被隐藏之后清除数据
	$(".bs-edit-modal-lg").on("hidden.bs.modal", function() {
	    $(this).removeData("bs.modal");
	});
})
</script>