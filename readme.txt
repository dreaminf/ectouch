+=====================================+
ECTouch开源移动商城系统（又名：ECTouch）
+=====================================+

ECTouch（ECTouch移动商城系统）是一个基于 CodeIgniter 框架开发的 PHP 移动商城系统，她轻量小巧、简单易用、强大高效。

采用 CodeIgniter MVC 框架开发，易于二次开发和扩展，代码与模板分离，用户可以很方便地进行定制。

前台 UI 采用当前流行的 Bootstrap 和 jQuery，最大可能地发掘用户体验，增强用户对论坛的粘性和好感。

整体架构从缓存、数据库设计、代码等多个角度入手进行优化，支持百万级数据，开启缓存和 gzip 后,打开网站的速度如同静态页一样流畅。

安装包仅几百K大小, 比一般的商城系统都要小巧轻便, 向 “臃肿” 两字说拜拜吧！尽管目前还不完善，但我会尽心地把它开发下去，并持续更新。希望大家能多提宝贵意见，也欢迎有能力者贡献代码。


ECTouch开源移动商城系统
http://www.ectouch.cn


+=======================+
ECTouch移动商城系统的环境需求
+=======================+

1. 可用的 WWW 服务器，如 Apache、IIS、Nginx, 推荐使用 Apache
2. PHP 5.2.4 以上, 建议使用 PHP 5.3 以上的版本
3. MySQL 5.0 以上, 服务器需要支持 MySQLi 或 PDO_MySQL
4. GD 图形库支持或 ImageMagick 支持, 推荐使用 ImageMagick, 在处理大文件的时候表现较好


+=======================+
ECTouch移动商城系统下载
+=======================+

可到官方直接下载或到下面的托管地址

Github
https://github.com/ectouch/ectouch/


+=======================+
ECTouch移动商城系统的安装
+=======================+

1. 上传目录中的文件到服务器
2. 设置目录属性（Windows 服务器可忽略这一步）
	以下这些目录需要可读写权限
	./
	./data
	./data/caches
	./data/config.php
	./uploads 目录及其子目录

3. 进入首页就自动提示安装，如重新安装请删除data目录中的 intall.lock 文件
   或访问 http://您的域名/install/ 进行安装
4. 参照页面提示，进行安装，直至安装完毕


+=======================+
ECTouch移动商城系统的升级
+=======================+

每一个版本都会提供升级包，具体见官方论坛
a. 上传升级包覆盖原文件
b. 执行 http://域名/index.php/upgrade



+=======================+
ECTouch Rewrite 开启方法
+=======================+

Apache:

<IfModule mod_rewrite.c>
RewriteEngine on
RewriteCond %{REQUEST_FILENAME} !-f
RewriteCond %{REQUEST_FILENAME} !-d
RewriteCond $1 !^(index\.php|images|robots\.txt)
RewriteRule ^(.*)$ /index.php/$1 [L]
</IfModule>

Nginx:

location / {
  if (!-e $request_filename) {
    rewrite ^/(.*)$ /index.php last;
  }
}

后续会增加更多规则

+=======================+
ECTouch 软件的技术支持
+=======================+

当您在安装、升级、日常使用当中遇到疑难，请您到以下站点获取技术支持。

ECTouch官方网站：http://www.ectouch.cn
官方邮箱wanganlin@ecmoban.com
