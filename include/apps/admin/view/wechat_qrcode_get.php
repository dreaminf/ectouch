<div class="modal-header">
	<button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">&times;</span><span class="sr-only">Close</span></button>
	<h4 class="modal-title" id="myModalLabel">{$lang['qrcode']}</h4>
</div>
<div class="modal-body text-center">
	<img src="{$qrcode_url}" alt="{$lang['qrcode']}" />
</div>
<script type="text/javascript">
$(function(){
	//模态框被隐藏之后清除数据
	$(".bs-edit-modal-lg").on("hidden.bs.modal", function() {
	    $(this).removeData("bs.modal");
	});
})
</script>