<?php
/**
 * 说明：分布式OSS存储、数据缓存及session配置
 * 作者：模板堂
 * 时间：2016-05
 */
$GLOBALS['ecmobanConfig'] = array(
    /* 上传设置 */
    'UPLOAD_OPTIONS' => array(
        'FILE_UPLOAD_TYPE' => 'Local', // 文件上传方式，支持：Local|Alioss
        'UPLOAD_TYPE_CONFIG' => array(),
        'UPLOAD_DRIVER_CONF' => array(
            'OSS_ACCESS_ID' => '', //您从OSS获得的AccessKeyId
            'OSS_ACCESS_KEY' => '', //您从OSS获得的AccessKeySecret
            'OSS_ENDPOINT' => 'oss-cn-hangzhou.aliyuncs.com', //您选定的OSS数据中心访问域名
            'OSS_BUCKET' => 'cn-ectouch', //空间名称
        )
    ),
    /* 缓存设置 */
    'CACHE_OPTIONS' => array(
        'DATA_CACHE_TIME'        => 0, // 数据缓存有效期 0表示永久缓存
        'DATA_CACHE_COMPRESS'    => false, // 数据缓存是否压缩缓存
        'DATA_CACHE_CHECK'       => false, // 数据缓存是否校验缓存
        'DATA_CACHE_PREFIX'      => '', // 缓存前缀
        'DATA_CACHE_TYPE'        => 'File', // 数据缓存类型,支持:File|Db|Apc|Memcache|Shmop|Sqlite|Xcache|Apachenote|Eaccelerator
        'DATA_CACHE_PATH'        => './temp/', // 缓存路径设置 (仅对File方式缓存有效)
        'DATA_CACHE_KEY'         => '', // 缓存文件KEY (仅对File方式缓存有效)
        'DATA_CACHE_SUBDIR'      => false, // 使用子目录缓存 (自动根据缓存标识的哈希创建子目录)
        'DATA_PATH_LEVEL'        => 1, // 子目录缓存级别
    ),
    /* SESSION设置 */
    'SESSION_OPTIONS' => array(
        'SESSION_AUTO_START'     => true, // 是否自动开启Session
        'SESSION_OPTIONS'        => array(), // session 配置数组 支持type name id path expire domain 等参数
        'SESSION_TYPE'           => '', // session hander类型 默认无需设置 除非扩展了session hander驱动
        'SESSION_PREFIX'         => '', // session 前缀
    )
);


/* 上传文件 */
function ecmoban_upload_file($files, $path = 'images/upload')
{
    $config = $GLOBALS['ecmobanConfig']['UPLOAD_OPTIONS'];
    $config['UPLOAD_TYPE_CONFIG'] = array_merge($config['UPLOAD_TYPE_CONFIG'], array('rootPath' => rtrim($path, '/') . '/'));
    $upload = new \ecmoban\Upload\Upload($config['UPLOAD_TYPE_CONFIG'], $config['FILE_UPLOAD_TYPE'], $config['UPLOAD_DRIVER_CONF']);
    if($info = $upload->uploadOne($files)){
        return str_replace('../', '', $info['url']);
    }else{
        return false;
    }
}