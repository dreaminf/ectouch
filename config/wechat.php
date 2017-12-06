<?php

return [

    'SHOP_URL' => 'http://dev.ectouch.cn',

    // 注册协议地址
    'TERMS_URL' => 'http://localhost/article.php?cat_id=-1',
    'ABOUT_URL' => 'http://localhost/article.php?cat_id=-2',

    // Token授权加密key
    'TOKEN_SECRET' => '1161a348ddb044ae8e02f5337ae12570',
    'TOKEN_ALG' => 'HS256',
    'TOKEN_TTL' => '43200',
    'TOKEN_REFRESH' => false,
    'TOKEN_REFRESH_TTL' => '1440',
    'TOKEN_VER' => '1.0.0',

    // 短信验证信息模版
    'SMS_TEMPLATE' => '#CODE#，短信验证码有效期30分钟，请尽快进行验证。',
    
        // 微信小程序
    'WX_MINI_APPID' => 'wx',
    'WX_MINI_SECRET' => 'wx',

];
