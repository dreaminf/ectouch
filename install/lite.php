<?php
header("Content-type:text/html;charset=utf-8");
defined('ROOT_PATH') or define('ROOT_PATH', str_replace('\\', '/', dirname(dirname(__FILE__))) . '/');

// 修改的文件
$edit_file = array(
  'admin/goods.php',
  'admin/includes/inc_menu.php',
  'admin/templates/goods_info.htm',
  'include/language/zh_cn/admin/common.php',
  'include/apps/default/controller/CommonController.class.php',
);
// 删除的文件
$del_file = array(
  'admin/drp.php',
  'admin/templates/drp_*',
  'include/apps/default/controller/SaleController.class.php',
  'include/apps/default/controller/MY_*',
  'include/apps/default/model/SaleModel.class.php',
  'include/apps/default/model/MY_*',
  'themes/default/sale/*',
  'themes/default/sale_*',
  'themes/default/library/sale_*',
  'include/language/zh_cn/admin/drp.php',
  'include/apps/default/language/zh_cn/sale.php',
);
// 更新数据库
$update_db = array(
  'DROP TABLE ecs_drp_config;',
  'DROP TABLE ecs_drp_log;',
  'DROP TABLE ecs_drp_profit;',
  'DROP TABLE ecs_drp_shop;',
  'DROP TABLE ecs_drp_bank;',
  'DROP TABLE ecs_drp_visiter;',
  'DROP TABLE ecs_drp_goods;',
  'DROP TABLE ecs_drp_order_goods;',
  'DROP TABLE ecs_drp_order_info;',
  'DROP TABLE ecs_drp_apply;',
  'ALTER TABLE `ecs_users` DROP COLUMN `apply_sale`;',
  'DELETE FROM `ecs_wechat_template`;',
);

// 修改文件
foreach($edit_file as $vo){
    replace($vo);
}
// 删除文件
foreach($del_file as $vo){
    delete($vo);
}
// 更新数据库
update($update_db);

/**
 * private function
 */
function replace($file = ''){
  $str = file_get_contents(ROOT_PATH . $file);
  preg_match_all("/\/\*DRP_START\*\/.+\/\*DRP_END\*\//isU", $str, $arr);
  for($i=0, $j=count($arr[0]); $i<$j; $i++){
    $str = str_replace($arr[0][$i], '', $str);
  }
  file_put_contents(ROOT_PATH . $file, $str);
}

function delete($file = ''){
  $suffix = substr($file, -2);
  if($suffix == '/*'){
      del_dir(ROOT_PATH . substr($file, 0, -1));
  }else if($suffix == '_*'){
      del_pre(ROOT_PATH . substr($file, 0, -1));
  }else{
      @unlink(ROOT_PATH . $file);
  }
}

function del_dir($dir){
  if (!is_dir($dir)){
    return false;
  }
  $handle = opendir($dir);
  while (($file = readdir($handle)) !== false){
    if ($file != "." && $file != ".."){
      is_dir("$dir/$file")? del_dir("$dir/$file") : @unlink("$dir/$file");
    }
  }
  if (readdir($handle) == false){
    closedir($handle);
    @rmdir($dir);
  }
}
	
function del_pre($files) {
    $dir = dirname($files);
    //打开目录
    $res = @dir($dir);
    //列出目录中的文件
    while (($file = $res->read()) !== false) {
      if ($file != "." and $file != ".."){
          $prefix = basename($files);
          $FP = stripos($file, $prefix);
          if($FP === 0){
            @unlink($dir . '/' . $file);
          }
      }
    }
    $res->close();
}

function update($sqls){
  $db = require ROOT_PATH . 'data/config.php';
  //设置数据库连接信息。数据库服务器地址，数据库用户名，数据密码
  mysql_connect($db['DB_HOST'], $db['DB_USER'], $db['DB_PWD']);
  //设置查询的数据库名称
  mysql_select_db($db['DB_NAME']);
  /**
  $rs = mysql_query('show tables');
  while($arr = mysql_fetch_array($rs)){
    //设置要批量删除的数据库表前缀，如：prefix_
    $TF = strpos($arr[0], $db['DB_PREFIX'] . 'drp_');
    if($TF === 0){
      $FT = mysql_query("drop table $arr[0]");
    } 
  }
  */
  foreach($sqls as $sql){
    mysql_query($sql);
  }
}

