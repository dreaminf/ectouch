{include file="pageheader"}
<style>
.article{border:1px solid #ddd;padding:5px 5px 0 5px;}
.cover{height:160px; position:relative;margin-bottom:5px;overflow:hidden;}
.article .cover img{width:100%; height:auto;}
.article span{height:40px; line-height:40px; display:block; z-index:5; position:absolute;width:100%;bottom:0px; color:#FFF; padding:0 10px; background-color:rgba(0,0,0,0.6)}
.article_list{padding:5px;border:1px solid #ddd;border-top:0;overflow:hidden;}
.radio label{width:100%;position:relative;padding:0;}
.radio .news_mask{position:absolute;left:0;top:0;background-color:#000;opacity:0.5;width:100%;height:100%;z-index:10;}
</style>
<div class="container-fluid" style="padding:0">
  <div class="row" style="margin:0">
    <div class="col-md-2 col-sm-2 col-lg-1" style="padding-right:0;">{include file="wechat_left_menu"}</div>
    <div class="col-md-10 col-sm-10 col-lg-11" style="padding-right:0;">
      <div class="panel panel-default">
        <div class="panel-heading"><a href="{url('mass_message')}" class="btn btn-primary">群发信息</a><a href="{url('mass_list')}" class="btn btn-default">发送记录</a></div>
        <div class="panel-body bg-danger">
            请注意：
            <span class="help-block">1.该接口暂时仅提供给已微信认证的服务号</span>
            <span class="help-block">2.用户每月只能接收4条群发消息，多于4条的群发将对该用户发送失败。</span>
            <span class="help-block">3.群发图文消息的标题上限为64个字节,群发内容字数上限为1200个字符、或600个汉字。</span>
            <span class="help-block">4.在返回成功时，意味着群发任务提交成功，并不意味着此时群发已经结束，所以，仍有可能在后续的发送过程中出现异常情况导致用户未收到消息，如消息有时会进行审核、服务器不稳定等。此外，群发任务一般需要较长的时间才能全部发送完毕，请耐心等待。</span>
        </div>
        <form action="{url('mass_message')}" method="post" enctype="multipart/form-data" class="form-horizontal" role="form">
        <table id="general-table" class="table table-hover ectouch-table">
        <tr>
            <td width="200">选择分组:</td>
            <td><div class="col-md-2">
                <select name="group_id" class="form-control input-sm">
                    {loop $groups $val}
                    <option value="{$val['group_id']}">{$val['name']}</option>
                    {/loop}
                </select>
              </div></td>
          </tr>
          <tr>
            <td width="200">选择图文信息:</td>
            <td><div class="col-md-4">
                <a class="btn btn-primary fancybox fancybox.iframe" href="{url('auto_reply', array('type'=>'news'))}">选择图文信息</a>
                <span class="help-block">一个图文消息支持1到10条图文，多于10条会发送失败</span>
              </div></td>
          </tr>
          <tr>
            <td width="200">图文信息</td>
            <td><div class="col-md-3 content"></div></td>
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
    </div>
  </div>
</div>
{include file="pagefooter"}