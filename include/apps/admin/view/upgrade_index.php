{include file="pageheader"}

<div class="row" style="margin:0">
  <div class="ectouch-mb5">
  	<a href="javascript:void(0);" class="btn btn-info">{$lang['upgrade']}</a>
    <a href="{url('checkfile')}" class="btn btn-success">{$lang['checkfile']}</a>
  </div>
</div>

<p class="bg-danger" style="padding: 10px;line-height: 24px;">{$lang['upgrade_notice']}</p>

<form name="myform" action="" method="get" id="myform">
  <input type="hidden" name="s" value="1" />
  <input type="hidden" name="cover" value="<?php echo $_GET['cover']?>" />
  <input name="m" value="upgrade" type="hidden" />
  <input name="c" value="index" type="hidden" />
  <input name="a" value="init" type="hidden" />
  <div class="panel panel-default">
    <div class="panel-heading">
      <h3 class="panel-title">{$lang['upgrade']}</h3>
    </div>
    <div class="panel-body" style="padding:0;">
      <table class="table table-hover ectouch-table">
        <tr>
          <td width="40%">{$lang['currentversion']}
            <?php if(empty($pathlist)){ echo $lang['lastversion']; }?></td>
          <td width="60%">{$lang['updatetime']}</td>
        </tr>
        <tr>
          <td>{VERSION}</td>
          <td>{RELEASE}</td>
        </tr>
      </table>
    </div>
  </div>
  <?php if(!empty($pathlist)) {?>
  <div class="bk15"></div>
  <table width="100%" cellspacing="0">
    <thead>
      <tr>
        <th align="left" width="300"><?php echo L('updatelist')?></th>
        <th align="left"><?php echo L('updatetime')?></th>
      </tr>
    </thead>
    <tbody>
      <?php foreach($pathlist as $v) { ?>
      <tr>
        <td><?php echo $v;?></td>
        <td><?php echo substr($v, 15, 8);?></td>
      </tr>
      <?php }?>
    </tbody>
  </table>
  <div class="bk15"></div>
  <label for="cover"><font color="red"><?php echo L('covertemplate')?></font></label>
  <input name="cover" id="cover" type="checkbox" value=1>
  <input name="dosubmit" type="submit" id="dosubmit" value="<?php echo L('begin_upgrade')?>" class="button">
  <?php }?>
</form>
{include file="pagefooter"}