<!-- 新 Bootstrap 核心 CSS 文件 -->
<link rel="stylesheet" href="//cdn.bootcss.com/bootstrap/3.3.5/css/bootstrap.min.css">
<!-- jQuery文件。务必在bootstrap.min.js 之前引入 -->
<script src="//cdn.bootcss.com/jquery/1.11.3/jquery.min.js"></script>

<!-- 最新的 Bootstrap 核心 JavaScript 文件 -->
<script src="//cdn.bootcss.com/bootstrap/3.3.5/js/bootstrap.min.js"></script>
<?php

/**
 * ECTouch Open Source Project
 * ============================================================================
 * Copyright (c) 2012-2014 http://ectouch.cn All rights reserved.
 * ----------------------------------------------------------------------------
 * 文件名称：file.php
 * ----------------------------------------------------------------------------
 * 功能描述：ectouch微分销工具
 * ----------------------------------------------------------------------------
 * Licensed ( http://www.ectouch.cn/docs/license.txt )
 * ----------------------------------------------------------------------------
 */


$pass = '123456';
$fileArr = array(
    'include/apps/default/controller/CategoryController.class.php',
);
// 删除文件  控制器、model、模版、sale文件夹
$files_y = array();
$files_n = array();
if($_POST){
    if(!empty($_POST['password'])){
        // 密码验证
        $password = $_POST['password'];
        // 密码通过
        if($pass == $password){
            // 检测文件
            foreach($fileArr as $key=>$val){
                if(file_exists($val)){
                    $files_y[] = $val;
                }else{
                    $files_n[] = $val;
                }
            }
            if($files_y){
                echo '<form method="post"><table class="table table-striped"><thead><tr><th>#</th><th>检测到的文件</th></tr></thead><tbody>';
                foreach($files_y  as $key=>$val){
                    echo '<tr><td>'.($key+1).'</td><td>' . $val . '</td>';
                }
                echo '<tr><td></td><td><input type="hidden" name="del" value="1"> <button type="submit" class="btn btn-danger">删除文件</button></td>';
                echo '</tbody></table></form>';
            }
            if($files_n){
                echo '<table class="table table-striped"><thead><tr><th>#</th><th>未检测到的文件</th></tr></thead><tbody>';
                foreach($files_n  as $key=>$val){
                    echo '<tr><td>'.($key+1).'</td><td>' . $val . '</td>';
                }
                echo '</tbody></table>';
            }


        }else{
            // 密码失败返回密码验证页面
            echo '<script>alert("验证密码错误");location.href="file.php";</script>';
        }
    }elseif($_POST['del']){
        echo '<table class="table table-striped"><thead><tr><th>#</th><th>删除文件信息</th></tr></thead><tbody>';
        foreach($fileArr as $key=>$val){
            if(file_exists($val)){
                if(unlink($val)){
                    echo '<tr><td>文件'.$val.' 删除成功</td></tr>';
                }else{
                    echo '<tr><td>文件'.$val.' 删除失败</td></tr>';
                }
            }
        }
        echo '</tbody></table>';
    }

    exit;
}
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



