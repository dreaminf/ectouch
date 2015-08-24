<?php

/**
 * ECTouch Open Source Project
 * ============================================================================
 * Copyright (c) 2012-2014 http://ectouch.cn All rights reserved.
 * ----------------------------------------------------------------------------
 * File name：post_mail.php
 * ----------------------------------------------------------------------------
 * Function description：Common mailing plug-in's language file
 * ----------------------------------------------------------------------------
 * Licensed ( http://www.ectouch.cn/docs/license.txt )
 * ----------------------------------------------------------------------------
 */

/* 访问控制 */
if (! defined('IN_ECTOUCH')) {
    die('Deny Access');
}

$_LANG['post_mail']          = 'Common mailing';
$_LANG['post_mail_desc']     = 'Common mailing\'s description';
$_LANG['pack_fee']           = 'Packing money:';

$_LANG['item_fee']           = 'Single commodity costs:';
$_LANG['base_fee']          = 'Cost less than 1000g:';
$_LANG['step_fee']          = 'Less than 5000g, cost of every 1000g:';
$_LANG['step_fee1']          = 'More than 5001g, cost of every 1000g:';