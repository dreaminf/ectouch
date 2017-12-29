<?php

/**
 * 创建像这样的查询: "IN('a','b')";
 *
 * @access   public
 * @param    mix $item_list 列表数组或字符串
 * @param    string $field_name 字段名称
 *
 * @return   void
 */
function db_create_in($item_list, $field_name = '')
{
    if (empty($item_list)) {
        return $field_name . " IN ('') ";
    } else {
        if (!is_array($item_list)) {
            $item_list = explode(',', $item_list);
        }
        $item_list = array_unique($item_list);
        $item_list_tmp = '';
        foreach ($item_list as $item) {
            if ($item !== '') {
                $item_list_tmp .= $item_list_tmp ? ",'$item'" : "'$item'";
            }
        }
        if (empty($item_list_tmp)) {
            return $field_name . " IN ('') ";
        } else {
            return $field_name . ' IN (' . $item_list_tmp . ') ';
        }
    }
}

/**
 * 验证输入的手机号是否合法
 *
 * @param $mobile
 * @return bool
 */
function is_mobile($mobile)
{
    $chars = "/^1(3[0-9]|4[0-9]|5[0-35-9]|6[6]|7[01345678]|8[0-9]|9[89])\d{8}\$/";
    if (preg_match($chars, $mobile)) {
        return true;
    } else {
        return false;
    }
}

/**
 * 验证输入的邮件地址是否合法
 *
 * @access  public
 * @param   string $email 需要验证的邮件地址
 *
 * @return bool
 */
function is_email($user_email)
{
    $chars = "/^([a-z0-9+_]|\\-|\\.)+@(([a-z0-9_]|\\-)+\\.)+[a-z]{2,6}\$/i";
    if (strpos($user_email, '@') !== false && strpos($user_email, '.') !== false) {
        if (preg_match($chars, $user_email)) {
            return true;
        } else {
            return false;
        }
    } else {
        return false;
    }
}

/**
 * 检查是否为一个合法的时间格式
 *
 * @access  public
 * @param   string $time
 * @return  void
 */
function is_time($time)
{
    $pattern = '/[\d]{4}-[\d]{1,2}-[\d]{1,2}\s[\d]{1,2}:[\d]{1,2}:[\d]{1,2}/';

    return preg_match($pattern, $time);
}


/**
 * 格式化商品价格
 *
 * @access  public
 * @param   float $price 商品价格
 * @return  string
 */
function price_format($price, $change_price = true)
{
    if ($price === '') {
        $price = 0;
    }
    if ($change_price && defined('ECS_ADMIN') === false) {
        switch ($GLOBALS['_CFG']['price_format']) {
            case 0:
                $price = number_format($price, 2, '.', '');
                break;
            case 1: // 保留不为 0 的尾数
                $price = preg_replace('/(.*)(\\.)([0-9]*?)0+$/', '\1\2\3', number_format($price, 2, '.', ''));

                if (substr($price, -1) == '.') {
                    $price = substr($price, 0, -1);
                }
                break;
            case 2: // 不四舍五入，保留1位
                $price = substr(number_format($price, 2, '.', ''), 0, -1);
                break;
            case 3: // 直接取整
                $price = intval($price);
                break;
            case 4: // 四舍五入，保留 1 位
                $price = number_format($price, 1, '.', '');
                break;
            case 5: // 先四舍五入，不保留小数
                $price = round($price);
                break;
        }
    } else {
        $price = number_format($price, 2, '.', '');
    }

    return sprintf($GLOBALS['_CFG']['currency_format'], $price);
}

/**
 *  清除指定后缀的模板缓存或编译文件
 *
 * @access  public
 * @param  bool $is_cache 是否清除缓存还是清出编译文件
 * @param  string $ext 需要删除的文件名，不包含后缀
 *
 * @return int        返回清除的文件个数
 */
function clear_tpl_files($is_cache = true, $ext = '')
{
    $dirs = [];

    if (isset($GLOBALS['shop_id']) && $GLOBALS['shop_id'] > 0) {
        $tmp_dir = DATA_DIR;
    } else {
        $tmp_dir = 'temp';
    }
    if ($is_cache) {
        $cache_dir = storage_path('framework/' . $tmp_dir . '/caches/');
        $dirs[] = storage_path('framework/' . $tmp_dir . '/query_caches/');
        $dirs[] = storage_path('framework/' . $tmp_dir . '/static_caches/');
        for ($i = 0; $i < 16; $i++) {
            $hash_dir = $cache_dir . dechex($i);
            $dirs[] = $hash_dir . '/';
        }
    } else {
        $dirs[] = storage_path('framework/' . $tmp_dir . '/compiled/');
        $dirs[] = storage_path('framework/' . $tmp_dir . '/compiled/admin/');
    }

    $str_len = strlen($ext);
    $count = 0;

    foreach ($dirs as $dir) {
        if (!is_dir($dir)) {
            mkdir($dir, 0755, true);
        }
        $folder = scandir($dir);

        if ($folder === false) {
            continue;
        }

        foreach ($folder as $file) {
            if ($file == '.' || $file == '..' || $file == 'index.htm' || $file == 'index.html' || $file == '.gitignore') {
                continue;
            }
            if (is_file($dir . $file)) {
                // 如果有文件名则判断是否匹配
                $pos = ($is_cache) ? strrpos($file, '_') : strrpos($file, '.');

                if ($str_len > 0 && $pos !== false) {
                    $ext_str = substr($file, 0, $pos);

                    if ($ext_str == $ext) {
                        if (@unlink($dir . $file)) {
                            $count++;
                        }
                    }
                } else {
                    if (@unlink($dir . $file)) {
                        $count++;
                    }
                }
            }
        }
    }

    return $count;
}

/**
 * 清除模版编译文件
 *
 * @access  public
 * @param   mix $ext 模版文件名， 不包含后缀
 * @return  void
 */
function clear_compiled_files($ext = '')
{
    return clear_tpl_files(false, $ext);
}

/**
 * 清除缓存文件
 *
 * @access  public
 * @param   mix $ext 模版文件名， 不包含后缀
 * @return  void
 */
function clear_cache_files($ext = '')
{
    return clear_tpl_files(true, $ext);
}

/**
 * 清除模版编译和缓存文件
 *
 * @access  public
 * @param   mix $ext 模版文件名后缀
 * @return  void
 */
function clear_all_files($ext = '')
{
    return clear_tpl_files(false, $ext) + clear_tpl_files(true, $ext);
}

/**
 * 页面上调用的js文件
 *
 * @access  public
 * @param   string $files
 * @return  void
 */
function smarty_insert_scripts($args)
{
    static $scripts = [];

    $arr = explode(',', str_replace(' ', '', $args['files']));

    $str = '';
    foreach ($arr as $val) {
        if (in_array($val, $scripts) == false) {
            $scripts[] = $val;
            if ($val{0} == '.') {
                $str .= '<script type="text/javascript" src="' . $val . '"></script>';
            } else {
                $str .= '<script type="text/javascript" src="js/' . $val . '"></script>';
            }
        }
    }

    return $str;
}

/**
 * 创建分页的列表
 *
 * @access  public
 * @param   integer $count
 * @return  string
 */
function smarty_create_pages($params)
{
    extract($params);

    $str = '';
    $len = 10;

    if (empty($page)) {
        $page = 1;
    }

    if (!empty($count)) {
        $step = 1;
        $str .= "<option value='1'>1</option>";

        for ($i = 2; $i < $count; $i += $step) {
            $step = ($i >= $page + $len - 1 || $i <= $page - $len + 1) ? $len : 1;
            $str .= "<option value='$i'";
            $str .= $page == $i ? " selected='true'" : '';
            $str .= ">$i</option>";
        }

        if ($count > 1) {
            $str .= "<option value='$count'";
            $str .= $page == $count ? " selected='true'" : '';
            $str .= ">$count</option>";
        }
    }

    return $str;
}

/**
 * 重写 URL 地址
 *
 * @access  public
 * @param   string $app 执行程序
 * @param   array $params 参数数组
 * @param   string $append 附加字串
 * @param   integer $page 页数
 * @param   string $keywords 搜索关键词字符串
 * @return  void
 */
function build_uri($app, $params, $append = '', $page = 0, $keywords = '', $size = 0)
{
    static $rewrite = null;

    if ($rewrite === null) {
        $rewrite = intval($GLOBALS['_CFG']['rewrite']);
    }

    $args = ['cid' => 0,
        'gid' => 0,
        'bid' => 0,
        'acid' => 0,
        'aid' => 0,
        'sid' => 0,
        'gbid' => 0,
        'auid' => 0,
        'sort' => '',
        'order' => '',
    ];

    extract(array_merge($args, $params));

    $uri = '';
    switch ($app) {
        case 'category':
            if (empty($cid)) {
                return false;
            } else {
                if ($rewrite) {
                    $uri = 'category-' . $cid;
                    if (isset($bid)) {
                        $uri .= '-b' . $bid;
                    }
                    if (isset($price_min)) {
                        $uri .= '-min' . $price_min;
                    }
                    if (isset($price_max)) {
                        $uri .= '-max' . $price_max;
                    }
                    if (isset($filter_attr)) {
                        $uri .= '-attr' . $filter_attr;
                    }
                    if (!empty($page)) {
                        $uri .= '-' . $page;
                    }
                    if (!empty($sort)) {
                        $uri .= '-' . $sort;
                    }
                    if (!empty($order)) {
                        $uri .= '-' . $order;
                    }
                } else {
                    $uri = 'category.php?id=' . $cid;
                    if (!empty($bid)) {
                        $uri .= '&amp;brand=' . $bid;
                    }
                    if (isset($price_min)) {
                        $uri .= '&amp;price_min=' . $price_min;
                    }
                    if (isset($price_max)) {
                        $uri .= '&amp;price_max=' . $price_max;
                    }
                    if (!empty($filter_attr)) {
                        $uri .= '&amp;filter_attr=' . $filter_attr;
                    }

                    if (!empty($page)) {
                        $uri .= '&amp;page=' . $page;
                    }
                    if (!empty($sort)) {
                        $uri .= '&amp;sort=' . $sort;
                    }
                    if (!empty($order)) {
                        $uri .= '&amp;order=' . $order;
                    }
                }
            }

            break;
        case 'goods':
            if (empty($gid)) {
                return false;
            } else {
                $uri = $rewrite ? 'goods-' . $gid : 'goods.php?id=' . $gid;
            }

            break;
        case 'brand':
            if (empty($bid)) {
                return false;
            } else {
                if ($rewrite) {
                    $uri = 'brand-' . $bid;
                    if (isset($cid)) {
                        $uri .= '-c' . $cid;
                    }
                    if (!empty($page)) {
                        $uri .= '-' . $page;
                    }
                    if (!empty($sort)) {
                        $uri .= '-' . $sort;
                    }
                    if (!empty($order)) {
                        $uri .= '-' . $order;
                    }
                } else {
                    $uri = 'brand.php?id=' . $bid;
                    if (!empty($cid)) {
                        $uri .= '&amp;cat=' . $cid;
                    }
                    if (!empty($page)) {
                        $uri .= '&amp;page=' . $page;
                    }
                    if (!empty($sort)) {
                        $uri .= '&amp;sort=' . $sort;
                    }
                    if (!empty($order)) {
                        $uri .= '&amp;order=' . $order;
                    }
                }
            }

            break;
        case 'article_cat':
            if (empty($acid)) {
                return false;
            } else {
                if ($rewrite) {
                    $uri = 'article_cat-' . $acid;
                    if (!empty($page)) {
                        $uri .= '-' . $page;
                    }
                    if (!empty($sort)) {
                        $uri .= '-' . $sort;
                    }
                    if (!empty($order)) {
                        $uri .= '-' . $order;
                    }
                    if (!empty($keywords)) {
                        $uri .= '-' . $keywords;
                    }
                } else {
                    $uri = 'article_cat.php?id=' . $acid;
                    if (!empty($page)) {
                        $uri .= '&amp;page=' . $page;
                    }
                    if (!empty($sort)) {
                        $uri .= '&amp;sort=' . $sort;
                    }
                    if (!empty($order)) {
                        $uri .= '&amp;order=' . $order;
                    }
                    if (!empty($keywords)) {
                        $uri .= '&amp;keywords=' . $keywords;
                    }
                }
            }

            break;
        case 'article':
            if (empty($aid)) {
                return false;
            } else {
                $uri = $rewrite ? 'article-' . $aid : 'article.php?id=' . $aid;
            }

            break;
        case 'group_buy':
            if (empty($gbid)) {
                return false;
            } else {
                $uri = $rewrite ? 'group_buy-' . $gbid : 'group_buy.php?act=view&amp;id=' . $gbid;
            }

            break;
        case 'auction':
            if (empty($auid)) {
                return false;
            } else {
                $uri = $rewrite ? 'auction-' . $auid : 'auction.php?act=view&amp;id=' . $auid;
            }

            break;
        case 'snatch':
            if (empty($sid)) {
                return false;
            } else {
                $uri = $rewrite ? 'snatch-' . $sid : 'snatch.php?id=' . $sid;
            }

            break;
        case 'search':
            break;
        case 'exchange':
            if ($rewrite) {
                $uri = 'exchange-' . $cid;
                if (isset($price_min)) {
                    $uri .= '-min' . $price_min;
                }
                if (isset($price_max)) {
                    $uri .= '-max' . $price_max;
                }
                if (!empty($page)) {
                    $uri .= '-' . $page;
                }
                if (!empty($sort)) {
                    $uri .= '-' . $sort;
                }
                if (!empty($order)) {
                    $uri .= '-' . $order;
                }
            } else {
                $uri = 'exchange.php?cat_id=' . $cid;
                if (isset($price_min)) {
                    $uri .= '&amp;integral_min=' . $price_min;
                }
                if (isset($price_max)) {
                    $uri .= '&amp;integral_max=' . $price_max;
                }

                if (!empty($page)) {
                    $uri .= '&amp;page=' . $page;
                }
                if (!empty($sort)) {
                    $uri .= '&amp;sort=' . $sort;
                }
                if (!empty($order)) {
                    $uri .= '&amp;order=' . $order;
                }
            }

            break;
        case 'exchange_goods':
            if (empty($gid)) {
                return false;
            } else {
                $uri = $rewrite ? 'exchange-id' . $gid : 'exchange.php?act=view&id=' . $gid;
            }

            break;
        default:
            return false;
            break;
    }

    if ($rewrite) {
        if ($rewrite == 2 && !empty($append)) {
            $uri .= '-' . urlencode(preg_replace('/[\.|\/|\?|&|\+|\\\|\'|"|,]+/', '', $append));
        }

        $uri .= '.html';
    }
    if (($rewrite == 2) && (strpos(strtolower(CHARSET), 'utf') !== 0)) {
        $uri = urlencode($uri);
    }
    return $uri;
}

/**
 * 格式化重量：小于1千克用克表示，否则用千克表示
 * @param   float $weight 重量
 * @return  string  格式化后的重量
 */
function formated_weight($weight)
{
    $weight = round(floatval($weight), 3);
    if ($weight > 0) {
        if ($weight < 1) {
            // 小于1千克，用克表示
            return intval($weight * 1000) . $GLOBALS['_LANG']['gram'];
        } else {
            // 大于1千克，用千克表示
            return $weight . $GLOBALS['_LANG']['kilogram'];
        }
    } else {
        return 0;
    }
}

/**
 * 重新获得商品图片与商品相册的地址
 *
 * @param string $image
 * @param string $path
 *
 * @return string
 */
function get_image_path($image = '', $path = '')
{
    if (strtolower(substr($image, 0, 4)) == 'http') {
        return $image;
    } else {
        if (empty($image)) {
            return asset($GLOBALS['_CFG']['no_picture']);
        } else {
            $static = config('app.static');
            $image = (empty($path) ? '' : rtrim($path, '/') . '/') . $image;

            return empty($static) ? asset($image) : rtrim($static, '/') . '/' . ltrim($image, '/');
        }
    }
}

function get_data_path($url = '', $path = '')
{
    return get_image_path($url, 'data/' . $path);
}
