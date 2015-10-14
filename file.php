<?php
// 遍历文件夹下的一级文件
function listDir($dir,$stauts=0){
    if(is_dir($dir)){
        if ($dh = opendir($dir)){
            while (($file = readdir($dh)) !== false){
                if((is_dir($dir."/".$file)) && $file!="." && $file!=".."){
                    //echo "<b><font color='red'>文件名：</font></b>",$file,"<br><hr>";
                    //listDir($dir."/".$file."/");
                }else{
                    if($file!="." && $file!=".."){
                        if(substr($file,0,5) == 'sale_' || $file == 'sale.dwt'){
                            if($stauts==1){
                                if(unlink($dir.$file)){
                                    echo '<tr><td>文件'.$dir.$file.' 删除成功</td></tr>';
                                }else{
                                    echo '<tr><td>文件'.$dir.$file.' 删除失败</td></tr>';
                                }
                            }
                            $fileList[] = $dir.$file;
                        }
                    }
                }
            }
            closedir($dh);
            return empty($fileList) ? array() : $fileList ;
        }
    }
}
// 删除目录和目录下的文件
function deldir($dir) {
    //先删除目录下的文件：
    $dh=opendir($dir);
    while ($file=readdir($dh)) {
        if($file!="." && $file!="..") {
            $fullpath=$dir."/".$file;
            if(!is_dir($fullpath)) {
                if(unlink($fullpath)){
                    echo '<tr><td>文件'.$fullpath.' 删除成功</td></tr>';
                }else{
                    echo '<tr><td>文件'.$fullpath.' 删除失败</td></tr>';
                }
            } else {
                deldir($fullpath);
            }
        }
    }
    closedir($dh);
    //删除当前文件夹：
    if(rmdir($dir)){
        return true;
    }else{
        return false;
    }
}


// 删除文件  控制器、model、模版、sale文件夹
$pass = '123456';
$fileArr = array(
    'include/apps/default/controller/SaleController.class.php',
    'include/apps/default/mobile/SaleModel.class.php',
);
$themes = './themes/default/';
$dirArr = array(
    './themes/default/sale',
);

if($_POST){
    if(!empty($_POST['password'])){
        // 密码验证
        $password = $_POST['password'];
        // 密码通过
        if($pass != $password){
            // 密码失败返回密码验证页面
            echo '<script>alert("验证密码错误");location.href="file.php";</script>';
        }

        // 连接数据库
        $mysql_info = require_once("./data/config.php");
        if($mysql_info){
            $con = mysql_connect($mysql_info['DB_HOST'],$mysql_info['DB_USER'],$mysql_info['DB_PWD']);
            if (!$con){
                die('Could not connect: ' . mysql_error());
            }else{
                $db_selected = mysql_select_db($mysql_info['DB_NAME'], $con);
                if (!$db_selected){
                    die ("Can\'t use test_db : " . mysql_error());
                }
                $sql['ecs_drp_bank'] = 'drop table ecs_drp_bank';
                $sql['ecs_drp_config'] = 'drop table ecs_drp_config';
                $sql['ecs_drp_log'] = 'drop table ecs_drp_log';
                $sql['ecs_drp_profit'] = 'drop table ecs_drp_profit';
                $sql['ecs_drp_shop'] = 'drop table ecs_drp_shop';
                $sql['ecs_drp_visiter'] = 'drop table ecs_drp_visiter';
                if($sql){
                    foreach($sql as $key=>$val){
                        if(mysql_query($val, $con)){
                            $execute[] = '<tr><td>数据表'.$key.'删除成功</td></tr>';
                        }else{
                            $execute[] = '<tr><td>数据表'.$key.'删除失败</td></tr>';
                        }
                    }
                }

            }
            mysql_close($con);
        }


    }
}
?>
<!DOCTYPE html>
<html>
<head lang="en">
    <meta charset="UTF-8">
    <title>ECTouch微分销代码管理</title>
    <link rel="stylesheet" href="//cdn.bootcss.com/bootstrap/3.3.5/css/bootstrap.min.css">
</head>
<body>
<?php
if(empty($_POST)){
    ?>
    <h1>ECTouch微分销代码管理</h1>
    <form class="form-inline" method="post">
        <div class="form-group">
            <label for="inputPassword2" class="sr-only">Password</label>
            <input type="password" name="password" class="form-control" id="inputPassword2" placeholder="Password">
        </div>
        <button type="submit" class="btn btn-info">密码验证</button>
    </form>
<?php
}elseif(!empty($_POST['password'])){
    echo '<table class="table table-striped"><thead><tr><th>删除文件信息</th></tr></thead><tbody>';
    listDir($themes,1);
    if($dirArr){
        foreach($dirArr as $key=>$val){
            deldir($val);
        }
    }
    foreach($fileArr as $key=>$val){
        if(file_exists($val)){
            if(unlink($val)){
                echo '<tr><td>文件'.$val.' 删除成功</td></tr>';
            }else{
                echo '<tr><td>文件'.$val.' 删除失败</td></tr>';
            }
        }
    }
    if($execute){
        foreach($execute as $val){
            echo $val;
        }
    }
    echo '</tbody></table>';
} ?>
</body>
</html>



