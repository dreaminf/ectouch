{include file="pageheader"}
{if $empower}


    <style type="text/css">
        <!--
        body{ background:#fff; text-align:center; font-size:12px;}
        a { color:#333; text-decoration:none;}
        #cert{background:#fff url(http://www.phpcms.cn/statics/images/license/license_bg.jpg) no-repeat 0 0; width:632px; height:396px; margin:50px auto; position:relative;}
        #cert table{ position:absolute; left:160px; top:130px;}
        #cert table th{ height:30px; line-height:30px; text-align:right; padding-right:10px; font-weight:normal; color:#000;}
        #cert table td{ text-align:left; color:#333;}
        -->
    </style>

<div id="cert">
    <table cellpadding="0" cellspacing="0">
        <tr>
            <th>授权类型：</th>
            <td>{$empower['type']}</td>
        </tr>
        <tr>
            <th>授权域名：</th>
            <td><a target="blank" href="http://www.phpcms.cn">{$empower['domain']}</a></td>
        </tr>
        <tr>
            <th>网站名称：</th>
            <td>{$empower['shop_name']}</td>
        </tr>
        <tr>
            <th>授权持有人：</th>
            <td>{$empower['name']}</td>
        </tr>
        <tr>
            <th>授权起始时间：</th>
            <td>{$empower['license_time']}</td>
        </tr>
        <tr>
            <th>服务起止日期：</th>
            <td>{$empower['service_time']}</td>
        </tr>
    </table>
</div>
{else}
<div class="panel panel-default">
  <div class="panel-heading">
    <h3 class="panel-title">{$ur_here}</h3>
  </div>
  <div class="panel-body">
    <form action="{url('license')}" method="post" enctype="multipart/form-data" class="form-horizontal" role="form">
      <table id="general-table" class="table table-hover ectouch-table">
        <tr>
          <td width="200">授权序列号:</td>
          <td><div class="col-md-4">
              <input type='text' name='license' maxlength="20" id="a" class="form-control input-sm" />
            </div></td>
        </tr>
        <tr>
          <td></td>
          <td><div class="col-md-4">
              <input type="submit" value="{$lang['button_submit']}" class="btn btn-primary" />
              <input type="reset" value="{$lang['button_reset']}" class="btn btn-default" />
            </div></td>
        </tr>
      </table>
    </form>
  </div>
</div>
{/if}
{include file="pagefooter"}