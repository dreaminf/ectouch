{include file="pageheader"}
<div class="panel panel-default">
	<div class="panel-heading">{$lang['wechat_add'].$lang['wechat_num']}</div>
	<table border="0" cellpadding="0" cellspacing="0" class="table table-hover table-bordered table-striped">
		<form method="post" action="{url('wechat/append')}" class="form-horizontal" role="form">
		<tr>
			<td width="200">{$lang['wechat_name']}</td>
			<td><div class="col-sm-4">
				<input type="text" name="data[name]" class="form-control">
			</div>
			<p class="help-block"></p>
			</td>
		</tr>
		<tr>
			<td>{$lang['wechat_id']}</td>
			<td><div class="col-sm-4">
				<input type="text" name="data[orgid]" class="form-control">
			</div>
			<p class="help-block">{$lang['wechat_help2']}</p>
			</td>
		</tr>
		<tr>
			<td>{$lang['wechat_number']}</td>
			<td><div class="col-sm-4">
				<input type="text" name="data[weixin]" class="form-control"> 
				</div>
				<p class="help-block">{$lang['wechat_help1']}</p>
				</td>
		</tr>
		<tr>
			<td>{$lang['token']}</td>
			<td><div class="col-sm-4">
				<input type="text" name="data[token]" class="form-control">
				</div>
				<p class="help-block">{$lang['wechat_help3']}</p>
				</td>
		</tr>
		<tr>
			<td>{$lang['appid']}</td>
			<td><div class="col-sm-4">
				<input type="text" name="data[appid]" class="form-control"> 
				</div>
				<p class="help-block">{$lang['wechat_help4']}</p>
				</td>
		</tr>
		<tr>
			<td>{$lang['appsecret']}</td>
			<td><div class="col-sm-4">
				<input type="text" name="data[appsecret]" class="form-control"> 
				</div>
				<p class="help-block">{$lang['wechat_help4']}</p>
				</td>
		</tr>
		<tr>
			<td>{$lang['wechat_type']}</td>
			<td><div class="col-sm-2">
				<select name="data[type]" class="form-control">
					<option value="1">{$lang['wechat_type1']}</option>
					<option value="2">{$lang['wechat_type2']}</option>
					<option value="3">{$lang['wechat_type3']}</option>
				</select>
				</div>
				<p class="help-block">{$lang['wechat_help5']}</p>
			</td>
		</tr>
		<tr>
			<td>{$lang['sort_order']}</td>
			<td><div class="col-sm-4">
				<input type="text" name="data[sort]" value="10" class="form-control">
				</div></td>
		</tr>
		<tr>
			<td>{$lang['wechat_status']}</td>
			<td><div class="col-sm-2">
				<div  class="btn-group" data-toggle="buttons">
					<label class="btn btn-primary active">
						<input type="radio" name="data[status]" value="1" checked> {$lang['wechat_open']}
					</label>
					<label class="btn btn-primary">
						<input type="radio" name="data[status]" value="0"> {$lang['wechat_close']}
					</label>
				</div>
			</div></td>
		</tr>
		<tr>
			<td></td>
			<td><div class="col-sm-4">
				<input type="submit" name="submit" value="{$lang['button_save']}" class="btn btn-primary" />
				</div></td>
		</tr>
		</form>
	</table>
{include file="pagefooter"}