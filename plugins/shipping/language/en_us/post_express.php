<?php

/**
 * ECTouch Open Source Project
 * ============================================================================
 * Copyright (c) 2012-2014 http://ectouch.cn All rights reserved.
 * ----------------------------------------------------------------------------
 * File name：post_express.php
 * ----------------------------------------------------------------------------
 * Function description：Postal shipping packing plug-in's language file
 * ----------------------------------------------------------------------------
 * Licensed ( http://www.ectouch.cn/docs/license.txt )
 * ----------------------------------------------------------------------------
 */

/* 访问控制 */
if (! defined('IN_ECTOUCH')) {
    die('Deny Access');
}

$_LANG['post_express']          = 'Postal shipping packing';
$_LANG['post_express_desc']     = 'Postal shipping packing description.';
$_LANG['base_fee']              = 'Cost less than 1000g:';
$_LANG['item_fee']              = 'Single commodity costs:';
$_LANG['step_fee']             = 'Less than 5000g, cost of every 500g:';
$_LANG['step_fee1']             = 'More than 5001g, cost of every 500g:';