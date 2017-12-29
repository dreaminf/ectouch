<?php

/**
 * 取得当前位置和页面标题
 *
 * @access  public
 * @param   integer $cat 分类编号（只有商品及分类、文章及分类用到）
 * @param   string $str 商品名、文章标题或其他附加的内容（无链接）
 * @return  array
 */
function assign_ur_here($cat = 0, $str = '')
{
    $filename = empty(PHP_SELF) ? 'index' : strtolower(PHP_SELF);

    // 初始化“页面标题”和“当前位置”
    $page_title = $GLOBALS['_CFG']['shop_title'];
    $ur_here = '<a href=".">' . $GLOBALS['_LANG']['home'] . '</a>';

    // 根据文件名分别处理中间的部分
    if ($filename != 'index') {
        // 处理有分类的
        if (in_array($filename, ['category', 'goods', 'article_cat', 'article', 'brand'])) {
            // 商品分类或商品
            if ('category' == $filename || 'goods' == $filename || 'brand' == $filename) {
                if ($cat > 0) {
                    $cat_arr = get_parent_cats($cat);

                    $key = 'cid';
                    $type = 'category';
                } else {
                    $cat_arr = [];
                }
            } // 文章分类或文章
            elseif ('article_cat' == $filename || 'article' == $filename) {
                if ($cat > 0) {
                    $cat_arr = get_article_parent_cats($cat);

                    $key = 'acid';
                    $type = 'article_cat';
                } else {
                    $cat_arr = [];
                }
            }

            // 循环分类
            if (!empty($cat_arr)) {
                krsort($cat_arr);
                foreach ($cat_arr as $val) {
                    $page_title = htmlspecialchars($val['cat_name']) . '_' . $page_title;
                    $args = [$key => $val['cat_id']];
                    $ur_here .= ' <code>&gt;</code> <a href="' . build_uri($type, $args, $val['cat_name']) . '">' .
                        htmlspecialchars($val['cat_name']) . '</a>';
                }
            }
        } // 处理无分类的
        else {
            // 团购
            if ('group_buy' == $filename) {
                $page_title = $GLOBALS['_LANG']['group_buy_goods'] . '_' . $page_title;
                $args = ['gbid' => '0'];
                $ur_here .= ' <code>&gt;</code> <a href="group_buy.php">' .
                    $GLOBALS['_LANG']['group_buy_goods'] . '</a>';
            } // 拍卖
            elseif ('auction' == $filename) {
                $page_title = $GLOBALS['_LANG']['auction'] . '_' . $page_title;
                $args = ['auid' => '0'];
                $ur_here .= ' <code>&gt;</code> <a href="auction.php">' .
                    $GLOBALS['_LANG']['auction'] . '</a>';
            } // 夺宝
            elseif ('snatch' == $filename) {
                $page_title = $GLOBALS['_LANG']['snatch'] . '_' . $page_title;
                $args = ['id' => '0'];
                $ur_here .= ' <code> &gt; </code><a href="snatch.php">' . $GLOBALS['_LANG']['snatch_list'] . '</a>';
            } // 批发
            elseif ('wholesale' == $filename) {
                $page_title = $GLOBALS['_LANG']['wholesale'] . '_' . $page_title;
                $args = ['wsid' => '0'];
                $ur_here .= ' <code>&gt;</code> <a href="wholesale.php">' .
                    $GLOBALS['_LANG']['wholesale'] . '</a>';
            } // 积分兑换
            elseif ('exchange' == $filename) {
                $page_title = $GLOBALS['_LANG']['exchange'] . '_' . $page_title;
                $args = ['wsid' => '0'];
                $ur_here .= ' <code>&gt;</code> <a href="exchange.php">' .
                    $GLOBALS['_LANG']['exchange'] . '</a>';
            }
            // 其他的在这里补充
        }
    }

    // 处理最后一部分
    if (!empty($str)) {
        $page_title = $str . '_' . $page_title;
        $ur_here .= ' <code>&gt;</code> ' . $str;
    }

    // 返回值
    return ['title' => $page_title, 'ur_here' => $ur_here];
}

/**
 * 根据提供的数组编译成页面标题
 *
 * @access  public
 * @param   string $type 类型
 * @param   array $arr 分类数组
 * @return  string
 */
function build_pagetitle($arr, $type = 'category')
{
    $str = '';

    foreach ($arr as $val) {
        $str .= htmlspecialchars($val['cat_name']) . '_';
    }

    return $str;
}

/**
 * 根据提供的数组编译成当前位置
 *
 * @access  public
 * @param   string $type 类型
 * @param   array $arr 分类数组
 * @return  void
 */
function build_urhere($arr, $type = 'category')
{
    krsort($arr);

    $str = '';
    foreach ($arr as $val) {
        switch ($type) {
            case 'category':
            case 'brand':
                $args = ['cid' => $val['cat_id']];
                break;
            case 'article_cat':
                $args = ['acid' => $val['cat_id']];
                break;
        }

        $str .= ' <code>&gt;</code> <a href="' . build_uri($type, $args) . '">' . htmlspecialchars($val['cat_name']) . '</a>';
    }

    return $str;
}

/**
 * 获得指定页面的动态内容
 *
 * @access  public
 * @param   string $tmp 模板名称
 * @return  void
 */
function assign_dynamic($tmp)
{
    $sql = 'SELECT id, number, type FROM ' . $GLOBALS['ecs']->table('template') .
        " WHERE filename = '$tmp' AND type > 0 AND remarks ='' AND theme='" . $GLOBALS['_CFG']['template'] . "'";
    $res = $GLOBALS['db']->getAll($sql);

    foreach ($res as $row) {
        switch ($row['type']) {
            case 1:
                // 分类下的商品
                $GLOBALS['smarty']->assign('goods_cat_' . $row['id'], assign_cat_goods($row['id'], $row['number']));
                break;
            case 2:
                // 品牌的商品
                $brand_goods = assign_brand_goods($row['id'], $row['number']);

                $GLOBALS['smarty']->assign('brand_goods_' . $row['id'], $brand_goods['goods']);
                $GLOBALS['smarty']->assign('goods_brand_' . $row['id'], $brand_goods['brand']);
                break;
            case 3:
                // 文章列表
                $cat_articles = assign_articles($row['id'], $row['number']);

                $GLOBALS['smarty']->assign('articles_cat_' . $row['id'], $cat_articles['cat']);
                $GLOBALS['smarty']->assign('articles_' . $row['id'], $cat_articles['arr']);
                break;
        }
    }
}

/**
 * 创建分页信息
 *
 * @access  public
 * @param   string $app 程序名称，如category
 * @param   string $cat 分类ID
 * @param   string $record_count 记录总数
 * @param   string $size 每页记录数
 * @param   string $sort 排序类型
 * @param   string $order 排序顺序
 * @param   string $page 当前页
 * @param   string $keywords 查询关键字
 * @param   string $brand 品牌
 * @param   string $price_min 最小价格
 * @param   string $price_max 最高价格
 * @return  void
 */
function assign_pager($app, $cat, $record_count, $size, $sort, $order, $page = 1,
                      $keywords = '', $brand = 0, $price_min = 0, $price_max = 0, $display_type = 'list', $filter_attr = '', $url_format = '', $sch_array = '')
{
    $sch = ['keywords' => $keywords,
        'sort' => $sort,
        'order' => $order,
        'cat' => $cat,
        'brand' => $brand,
        'price_min' => $price_min,
        'price_max' => $price_max,
        'filter_attr' => $filter_attr,
        'display' => $display_type
    ];

    $page = intval($page);
    if ($page < 1) {
        $page = 1;
    }

    $page_count = $record_count > 0 ? intval(ceil($record_count / $size)) : 1;

    $pager['page'] = $page;
    $pager['size'] = $size;
    $pager['sort'] = $sort;
    $pager['order'] = $order;
    $pager['record_count'] = $record_count;
    $pager['page_count'] = $page_count;
    $pager['display'] = $display_type;

    switch ($app) {
        case 'category':
            $uri_args = ['cid' => $cat, 'bid' => $brand, 'price_min' => $price_min, 'price_max' => $price_max, 'filter_attr' => $filter_attr, 'sort' => $sort, 'order' => $order, 'display' => $display_type];
            break;
        case 'article_cat':
            $uri_args = ['acid' => $cat, 'sort' => $sort, 'order' => $order];
            break;
        case 'brand':
            $uri_args = ['cid' => $cat, 'bid' => $brand, 'sort' => $sort, 'order' => $order, 'display' => $display_type];
            break;
        case 'search':
            $uri_args = ['cid' => $cat, 'bid' => $brand, 'sort' => $sort, 'order' => $order];
            break;
        case 'exchange':
            $uri_args = ['cid' => $cat, 'integral_min' => $price_min, 'integral_max' => $price_max, 'sort' => $sort, 'order' => $order, 'display' => $display_type];
            break;
    }
    // 分页样式
    $pager['styleid'] = isset($GLOBALS['_CFG']['page_style']) ? intval($GLOBALS['_CFG']['page_style']) : 0;

    $page_prev = ($page > 1) ? $page - 1 : 1;
    $page_next = ($page < $page_count) ? $page + 1 : $page_count;
    if ($pager['styleid'] == 0) {
        if (!empty($url_format)) {
            $pager['page_first'] = $url_format . 1;
            $pager['page_prev'] = $url_format . $page_prev;
            $pager['page_next'] = $url_format . $page_next;
            $pager['page_last'] = $url_format . $page_count;
        } else {
            $pager['page_first'] = build_uri($app, $uri_args, '', 1, $keywords);
            $pager['page_prev'] = build_uri($app, $uri_args, '', $page_prev, $keywords);
            $pager['page_next'] = build_uri($app, $uri_args, '', $page_next, $keywords);
            $pager['page_last'] = build_uri($app, $uri_args, '', $page_count, $keywords);
        }
        $pager['array'] = [];

        for ($i = 1; $i <= $page_count; $i++) {
            $pager['array'][$i] = $i;
        }
    } else {
        $_pagenum = 10;     // 显示的页码
        $_offset = 2;       // 当前页偏移值
        $_from = $_to = 0;  // 开始页, 结束页
        if ($_pagenum > $page_count) {
            $_from = 1;
            $_to = $page_count;
        } else {
            $_from = $page - $_offset;
            $_to = $_from + $_pagenum - 1;
            if ($_from < 1) {
                $_to = $page + 1 - $_from;
                $_from = 1;
                if ($_to - $_from < $_pagenum) {
                    $_to = $_pagenum;
                }
            } elseif ($_to > $page_count) {
                $_from = $page_count - $_pagenum + 1;
                $_to = $page_count;
            }
        }
        if (!empty($url_format)) {
            $pager['page_first'] = ($page - $_offset > 1 && $_pagenum < $page_count) ? $url_format . 1 : '';
            $pager['page_prev'] = ($page > 1) ? $url_format . $page_prev : '';
            $pager['page_next'] = ($page < $page_count) ? $url_format . $page_next : '';
            $pager['page_last'] = ($_to < $page_count) ? $url_format . $page_count : '';
            $pager['page_kbd'] = ($_pagenum < $page_count) ? true : false;
            $pager['page_number'] = [];
            for ($i = $_from; $i <= $_to; ++$i) {
                $pager['page_number'][$i] = $url_format . $i;
            }
        } else {
            $pager['page_first'] = ($page - $_offset > 1 && $_pagenum < $page_count) ? build_uri($app, $uri_args, '', 1, $keywords) : '';
            $pager['page_prev'] = ($page > 1) ? build_uri($app, $uri_args, '', $page_prev, $keywords) : '';
            $pager['page_next'] = ($page < $page_count) ? build_uri($app, $uri_args, '', $page_next, $keywords) : '';
            $pager['page_last'] = ($_to < $page_count) ? build_uri($app, $uri_args, '', $page_count, $keywords) : '';
            $pager['page_kbd'] = ($_pagenum < $page_count) ? true : false;
            $pager['page_number'] = [];
            for ($i = $_from; $i <= $_to; ++$i) {
                $pager['page_number'][$i] = build_uri($app, $uri_args, '', $i, $keywords);
            }
        }
    }
    if (!empty($sch_array)) {
        $pager['search'] = $sch_array;
    } else {
        $pager['search']['category'] = $cat;
        foreach ($sch as $key => $row) {
            $pager['search'][$key] = $row;
        }
    }

    $GLOBALS['smarty']->assign('pager', $pager);
}

/**
 *  生成给pager.lbi赋值的数组
 *
 * @access  public
 * @param   string $url 分页的链接地址(必须是带有参数的地址，若不是可以伪造一个无用参数)
 * @param   array $param 链接参数 key为参数名，value为参数值
 * @param   int $record 记录总数量
 * @param   int $page 当前页数
 * @param   int $size 每页大小
 *
 * @return  array       $pager
 */
function get_pager($url, $param, $record_count, $page = 1, $size = 10)
{
    $size = intval($size);
    if ($size < 1) {
        $size = 10;
    }

    $page = intval($page);
    if ($page < 1) {
        $page = 1;
    }

    $record_count = intval($record_count);

    $page_count = $record_count > 0 ? intval(ceil($record_count / $size)) : 1;
    if ($page > $page_count) {
        $page = $page_count;
    }
    // 分页样式
    $pager['styleid'] = isset($GLOBALS['_CFG']['page_style']) ? intval($GLOBALS['_CFG']['page_style']) : 0;

    $page_prev = ($page > 1) ? $page - 1 : 1;
    $page_next = ($page < $page_count) ? $page + 1 : $page_count;

    // 将参数合成url字串
    $param_url = '?';
    foreach ($param as $key => $value) {
        $param_url .= $key . '=' . $value . '&';
    }

    $pager['url'] = $url;
    $pager['start'] = ($page - 1) * $size;
    $pager['page'] = $page;
    $pager['size'] = $size;
    $pager['record_count'] = $record_count;
    $pager['page_count'] = $page_count;

    if ($pager['styleid'] == 0) {
        $pager['page_first'] = $url . $param_url . 'page=1';
        $pager['page_prev'] = $url . $param_url . 'page=' . $page_prev;
        $pager['page_next'] = $url . $param_url . 'page=' . $page_next;
        $pager['page_last'] = $url . $param_url . 'page=' . $page_count;
        $pager['array'] = [];
        for ($i = 1; $i <= $page_count; $i++) {
            $pager['array'][$i] = $i;
        }
    } else {
        $_pagenum = 10;     // 显示的页码
        $_offset = 2;       // 当前页偏移值
        $_from = $_to = 0;  // 开始页, 结束页
        if ($_pagenum > $page_count) {
            $_from = 1;
            $_to = $page_count;
        } else {
            $_from = $page - $_offset;
            $_to = $_from + $_pagenum - 1;
            if ($_from < 1) {
                $_to = $page + 1 - $_from;
                $_from = 1;
                if ($_to - $_from < $_pagenum) {
                    $_to = $_pagenum;
                }
            } elseif ($_to > $page_count) {
                $_from = $page_count - $_pagenum + 1;
                $_to = $page_count;
            }
        }
        $url_format = $url . $param_url . 'page=';
        $pager['page_first'] = ($page - $_offset > 1 && $_pagenum < $page_count) ? $url_format . 1 : '';
        $pager['page_prev'] = ($page > 1) ? $url_format . $page_prev : '';
        $pager['page_next'] = ($page < $page_count) ? $url_format . $page_next : '';
        $pager['page_last'] = ($_to < $page_count) ? $url_format . $page_count : '';
        $pager['page_kbd'] = ($_pagenum < $page_count) ? true : false;
        $pager['page_number'] = [];
        for ($i = $_from; $i <= $_to; ++$i) {
            $pager['page_number'][$i] = $url_format . $i;
        }
    }
    $pager['search'] = $param;

    return $pager;
}

/**
 * 获得浏览器名称和版本
 *
 * @access  public
 * @return  string
 */
function get_user_browser()
{
    if (empty($_SERVER['HTTP_USER_AGENT'])) {
        return '';
    }

    $agent = $_SERVER['HTTP_USER_AGENT'];
    $browser = '';
    $browser_ver = '';

    if (preg_match('/MSIE\s([^\s|;]+)/i', $agent, $regs)) {
        $browser = 'Internet Explorer';
        $browser_ver = $regs[1];
    } elseif (preg_match('/FireFox\/([^\s]+)/i', $agent, $regs)) {
        $browser = 'FireFox';
        $browser_ver = $regs[1];
    } elseif (preg_match('/Maxthon/i', $agent, $regs)) {
        $browser = '(Internet Explorer ' . $browser_ver . ') Maxthon';
        $browser_ver = '';
    } elseif (preg_match('/Opera[\s|\/]([^\s]+)/i', $agent, $regs)) {
        $browser = 'Opera';
        $browser_ver = $regs[1];
    } elseif (preg_match('/OmniWeb\/(v*)([^\s|;]+)/i', $agent, $regs)) {
        $browser = 'OmniWeb';
        $browser_ver = $regs[2];
    } elseif (preg_match('/Netscape([\d]*)\/([^\s]+)/i', $agent, $regs)) {
        $browser = 'Netscape';
        $browser_ver = $regs[2];
    } elseif (preg_match('/safari\/([^\s]+)/i', $agent, $regs)) {
        $browser = 'Safari';
        $browser_ver = $regs[1];
    } elseif (preg_match('/NetCaptor\s([^\s|;]+)/i', $agent, $regs)) {
        $browser = '(Internet Explorer ' . $browser_ver . ') NetCaptor';
        $browser_ver = $regs[1];
    } elseif (preg_match('/Lynx\/([^\s]+)/i', $agent, $regs)) {
        $browser = 'Lynx';
        $browser_ver = $regs[1];
    }

    if (!empty($browser)) {
        return addslashes($browser . ' ' . $browser_ver);
    } else {
        return 'Unknow browser';
    }
}

/**
 * 判断是否为搜索引擎蜘蛛
 *
 * @access  public
 * @return  string
 */
function is_spider($record = true)
{
    static $spider = null;

    if ($spider !== null) {
        return $spider;
    }

    if (empty($_SERVER['HTTP_USER_AGENT'])) {
        $spider = '';

        return '';
    }

    $searchengine_bot = [
        'googlebot',
        'mediapartners-google',
        'baiduspider+',
        'msnbot',
        'yodaobot',
        'yahoo! slurp;',
        'yahoo! slurp china;',
        'iaskspider',
        'sogou web spider',
        'sogou push spider'
    ];

    $searchengine_name = [
        'GOOGLE',
        'GOOGLE ADSENSE',
        'BAIDU',
        'MSN',
        'YODAO',
        'YAHOO',
        'Yahoo China',
        'IASK',
        'SOGOU',
        'SOGOU'
    ];

    $spider = strtolower($_SERVER['HTTP_USER_AGENT']);

    foreach ($searchengine_bot as $key => $value) {
        if (strpos($spider, $value) !== false) {
            $spider = $searchengine_name[$key];

            if ($record === true) {
                $GLOBALS['db']->autoReplace($GLOBALS['ecs']->table('searchengine'), ['date' => local_date('Y-m-d'), 'searchengine' => $spider, 'count' => 1], ['count' => 1]);
            }

            return $spider;
        }
    }

    $spider = '';

    return '';
}

/**
 * 获得客户端的操作系统
 *
 * @access  private
 * @return  void
 */
function get_os()
{
    if (empty($_SERVER['HTTP_USER_AGENT'])) {
        return 'Unknown';
    }

    $agent = new Jenssegers\Agent\Agent();

    return $agent->platform();
}

/**
 * 统计访问信息
 *
 * @access  public
 * @return  void
 */
function visit_stats()
{
    if (isset($GLOBALS['_CFG']['visit_stats']) && $GLOBALS['_CFG']['visit_stats'] == 'off') {
        return;
    }
    $time = gmtime();
    // 检查客户端是否存在访问统计的cookie
    $visit_times = request()->cookie('visit_times');
    $visit_times = !empty($visit_times) ? intval($visit_times) + 1 : 1;
    \Cookie::queue('visit_times', $visit_times, 1440 * 365);

    $browser = get_user_browser();
    $os = get_os();
    $ip = real_ip();
    $area = ecs_geoip($ip);

    // 语言
    if (!empty($_SERVER['HTTP_ACCEPT_LANGUAGE'])) {
        $pos = strpos($_SERVER['HTTP_ACCEPT_LANGUAGE'], ';');
        $lang = addslashes(($pos !== false) ? substr($_SERVER['HTTP_ACCEPT_LANGUAGE'], 0, $pos) : $_SERVER['HTTP_ACCEPT_LANGUAGE']);
    } else {
        $lang = '';
    }

    // 来源
    if (!empty($_SERVER['HTTP_REFERER']) && strlen($_SERVER['HTTP_REFERER']) > 9) {
        $pos = strpos($_SERVER['HTTP_REFERER'], '/', 9);
        if ($pos !== false) {
            $domain = substr($_SERVER['HTTP_REFERER'], 0, $pos);
            $path = substr($_SERVER['HTTP_REFERER'], $pos);

            // 来源关键字
            if (!empty($domain) && !empty($path)) {
                save_searchengine_keyword($domain, $path);
            }
        } else {
            $domain = $path = '';
        }
    } else {
        $domain = $path = '';
    }

    $sql = 'INSERT INTO ' . $GLOBALS['ecs']->table('stats') . ' ( ' .
        'ip_address, visit_times, browser, system, language, area, ' .
        'referer_domain, referer_path, access_url, access_time' .
        ') VALUES (' .
        "'$ip', '$visit_times', '$browser', '$os', '$lang', '$area', " .
        "'" . htmlspecialchars(addslashes($domain)) . "', '" . htmlspecialchars(addslashes($path)) . "', '" . htmlspecialchars(addslashes(PHP_SELF)) . "', '" . $time . "')";
    $GLOBALS['db']->query($sql);
}

/**
 * 保存搜索引擎关键字
 *
 * @access  public
 * @return  void
 */
function save_searchengine_keyword($domain, $path)
{
    if (strpos($domain, 'google.com.tw') !== false && preg_match('/q=([^&]*)/i', $path, $regs)) {
        $searchengine = 'GOOGLE TAIWAN';
        $keywords = urldecode($regs[1]); // google taiwan
    }
    if (strpos($domain, 'google.cn') !== false && preg_match('/q=([^&]*)/i', $path, $regs)) {
        $searchengine = 'GOOGLE CHINA';
        $keywords = urldecode($regs[1]); // google china
    }
    if (strpos($domain, 'google.com') !== false && preg_match('/q=([^&]*)/i', $path, $regs)) {
        $searchengine = 'GOOGLE';
        $keywords = urldecode($regs[1]); // google
    } elseif (strpos($domain, 'baidu.') !== false && preg_match('/wd=([^&]*)/i', $path, $regs)) {
        $searchengine = 'BAIDU';
        $keywords = urldecode($regs[1]); // baidu
    } elseif (strpos($domain, 'baidu.') !== false && preg_match('/word=([^&]*)/i', $path, $regs)) {
        $searchengine = 'BAIDU';
        $keywords = urldecode($regs[1]); // baidu
    } elseif (strpos($domain, '114.vnet.cn') !== false && preg_match('/kw=([^&]*)/i', $path, $regs)) {
        $searchengine = 'CT114';
        $keywords = urldecode($regs[1]); // ct114
    } elseif (strpos($domain, 'iask.com') !== false && preg_match('/k=([^&]*)/i', $path, $regs)) {
        $searchengine = 'IASK';
        $keywords = urldecode($regs[1]); // iask
    } elseif (strpos($domain, 'soso.com') !== false && preg_match('/w=([^&]*)/i', $path, $regs)) {
        $searchengine = 'SOSO';
        $keywords = urldecode($regs[1]); // soso
    } elseif (strpos($domain, 'sogou.com') !== false && preg_match('/query=([^&]*)/i', $path, $regs)) {
        $searchengine = 'SOGOU';
        $keywords = urldecode($regs[1]); // sogou
    } elseif (strpos($domain, 'so.163.com') !== false && preg_match('/q=([^&]*)/i', $path, $regs)) {
        $searchengine = 'NETEASE';
        $keywords = urldecode($regs[1]); // netease
    } elseif (strpos($domain, 'yodao.com') !== false && preg_match('/q=([^&]*)/i', $path, $regs)) {
        $searchengine = 'YODAO';
        $keywords = urldecode($regs[1]); // yodao
    } elseif (strpos($domain, 'zhongsou.com') !== false && preg_match('/word=([^&]*)/i', $path, $regs)) {
        $searchengine = 'ZHONGSOU';
        $keywords = urldecode($regs[1]); // zhongsou
    } elseif (strpos($domain, 'search.tom.com') !== false && preg_match('/w=([^&]*)/i', $path, $regs)) {
        $searchengine = 'TOM';
        $keywords = urldecode($regs[1]); // tom
    } elseif (strpos($domain, 'live.com') !== false && preg_match('/q=([^&]*)/i', $path, $regs)) {
        $searchengine = 'MSLIVE';
        $keywords = urldecode($regs[1]); // MSLIVE
    } elseif (strpos($domain, 'tw.search.yahoo.com') !== false && preg_match('/p=([^&]*)/i', $path, $regs)) {
        $searchengine = 'YAHOO TAIWAN';
        $keywords = urldecode($regs[1]); // yahoo taiwan
    } elseif (strpos($domain, 'cn.yahoo.') !== false && preg_match('/p=([^&]*)/i', $path, $regs)) {
        $searchengine = 'YAHOO CHINA';
        $keywords = urldecode($regs[1]); // yahoo china
    } elseif (strpos($domain, 'yahoo.') !== false && preg_match('/p=([^&]*)/i', $path, $regs)) {
        $searchengine = 'YAHOO';
        $keywords = urldecode($regs[1]); // yahoo
    } elseif (strpos($domain, 'msn.com.tw') !== false && preg_match('/q=([^&]*)/i', $path, $regs)) {
        $searchengine = 'MSN TAIWAN';
        $keywords = urldecode($regs[1]); // msn taiwan
    } elseif (strpos($domain, 'msn.com.cn') !== false && preg_match('/q=([^&]*)/i', $path, $regs)) {
        $searchengine = 'MSN CHINA';
        $keywords = urldecode($regs[1]); // msn china
    } elseif (strpos($domain, 'msn.com') !== false && preg_match('/q=([^&]*)/i', $path, $regs)) {
        $searchengine = 'MSN';
        $keywords = urldecode($regs[1]); // msn
    }

    if (!empty($keywords)) {
        $gb_search = ['YAHOO CHINA', 'TOM', 'ZHONGSOU', 'NETEASE', 'SOGOU', 'SOSO', 'IASK', 'CT114', 'BAIDU'];
        if (CHARSET == 'utf-8' && in_array($searchengine, $gb_search)) {
            $keywords = ecs_iconv('GBK', 'UTF8', $keywords);
        }
        if (CHARSET == 'gbk' && !in_array($searchengine, $gb_search)) {
            $keywords = ecs_iconv('UTF8', 'GBK', $keywords);
        }

        $GLOBALS['db']->autoReplace($GLOBALS['ecs']->table('keywords'), ['date' => local_date('Y-m-d'), 'searchengine' => $searchengine, 'keyword' => htmlspecialchars(addslashes($keywords)), 'count' => 1], ['count' => 1]);
    }
}

/**
 * 获取指定主题某个模板的主题的动态模块
 *
 * @access  public
 * @param   string $theme 模板主题
 * @param   string $tmp 模板名称
 *
 * @return array()
 */
function get_dyna_libs($theme, $tmp)
{
    $tmps = explode('.', $tmp);
    $ext = end($tmps);
    $tmp = basename($tmp, ".$ext");
    $sql = 'SELECT region, library, sort_order, id, number, type' .
        ' FROM ' . $GLOBALS['ecs']->table('template') .
        " WHERE theme = '$theme' AND filename = '" . $tmp . "' AND type > 0 AND remarks=''" .
        ' ORDER BY region, library, sort_order';
    $res = $GLOBALS['db']->getAll($sql);

    $dyna_libs = [];
    foreach ($res as $row) {
        $dyna_libs[$row['region']][$row['library']][] = [
            'id' => $row['id'],
            'number' => $row['number'],
            'type' => $row['type']
        ];
    }

    return $dyna_libs;
}

/**
 * 替换动态模块
 *
 * @access  public
 * @param   string $matches 匹配内容
 *
 * @return string        结果
 */
function dyna_libs_replace($matches)
{
    $key = '/' . $matches[1];

    if ($row = array_shift($GLOBALS['libs'][$key])) {
        $str = '';
        switch ($row['type']) {
            case 1:
                // 分类的商品
                $str = '{assign var="cat_goods" value=$cat_goods_' . $row['id'] . '}{assign var="goods_cat" value=$goods_cat_' . $row['id'] . '}';
                break;
            case 2:
                // 品牌的商品
                $str = '{assign var="brand_goods" value=$brand_goods_' . $row['id'] . '}{assign var="goods_brand" value=$goods_brand_' . $row['id'] . '}';
                break;
            case 3:
                // 文章列表
                $str = '{assign var="articles" value=$articles_' . $row['id'] . '}{assign var="articles_cat" value=$articles_cat_' . $row['id'] . '}';
                break;
            case 4:
                //广告位
                $str = '{assign var="ads_id" value=' . $row['id'] . '}{assign var="ads_num" value=' . $row['number'] . '}';
                break;
        }
        return $str . $matches[0];
    } else {
        return $matches[0];
    }
}

/**
 * 处理上传文件，并返回上传图片名(上传失败时返回图片名为空）
 *
 * @access  public
 * @param array $upload $_FILES 数组
 * @param array $type 图片所属类别，即data目录下的文件夹名
 *
 * @return string               上传图片名
 */
function upload_file($upload, $type)
{
    if (!empty($upload['tmp_name'])) {
        $ftype = check_file_type($upload['tmp_name'], $upload['name'], '|png|jpg|jpeg|gif|doc|xls|txt|zip|ppt|pdf|rar|docx|xlsx|pptx|');
        if (!empty($ftype)) {
            $name = date('Ymd');
            for ($i = 0; $i < 6; $i++) {
                $name .= chr(mt_rand(97, 122));
            }

            $name = session('user_id') . '_' . $name . '.' . $ftype;

            $target = ROOT_PATH . DATA_DIR . '/' . $type . '/' . $name;
            if (!move_upload_file($upload['tmp_name'], $target)) {
                $GLOBALS['err']->add($GLOBALS['_LANG']['upload_file_error'], 1);

                return false;
            } else {
                return $name;
            }
        } else {
            $GLOBALS['err']->add($GLOBALS['_LANG']['upload_file_type'], 1);

            return false;
        }
    } else {
        $GLOBALS['err']->add($GLOBALS['_LANG']['upload_file_error']);
        return false;
    }
}

/**
 * 显示一个提示信息
 *
 * @access  public
 * @param   string $content
 * @param   string $link
 * @param   string $href
 * @param   string $type 信息类型：warning, error, info
 * @param   string $auto_redirect 是否自动跳转
 * @return  void
 */
function show_message($content, $links = '', $hrefs = '', $type = 'info', $auto_redirect = true)
{
    assign_template();

    $msg['content'] = $content;
    if (is_array($links) && is_array($hrefs)) {
        if (!empty($links) && count($links) == count($hrefs)) {
            foreach ($links as $key => $val) {
                $msg['url_info'][$val] = $hrefs[$key];
            }
            $msg['back_url'] = $hrefs['0'];
        }
    } else {
        $link = empty($links) ? $GLOBALS['_LANG']['back_up_page'] : $links;
        $href = empty($hrefs) ? 'javascript:history.back()' : $hrefs;
        $msg['url_info'][$link] = $href;
        $msg['back_url'] = $href;
    }

    $msg['type'] = $type;
    $position = assign_ur_here(0, $GLOBALS['_LANG']['sys_msg']);
    $GLOBALS['smarty']->assign('page_title', $position['title']);   // 页面标题
    $GLOBALS['smarty']->assign('ur_here', $position['ur_here']); // 当前位置

    if (is_null($GLOBALS['smarty']->get_template_vars('helps'))) {
        $GLOBALS['smarty']->assign('helps', get_shop_help()); // 网店帮助
    }

    $GLOBALS['smarty']->assign('auto_redirect', $auto_redirect);
    $GLOBALS['smarty']->assign('message', $msg);
    return $GLOBALS['smarty']->display('message.dwt');
}

/**
 * 将一个形如+10, 10, -10, 10%的字串转换为相应数字，并返回操作符号
 *
 * @access  public
 * @param   string      str     要格式化的数据
 * @param   char        operate 操作符号，只能返回‘+’或‘*’;
 * @return  float       value   浮点数
 */
function parse_rate_value($str, &$operate)
{
    $operate = '+';
    $is_rate = false;

    $str = trim($str);
    if (empty($str)) {
        return 0;
    }
    if ($str[strlen($str) - 1] == '%') {
        $value = floatval($str);
        if ($value > 0) {
            $operate = '*';

            return $value / 100;
        } else {
            return 0;
        }
    } else {
        return floatval($str);
    }
}

/**
 * 重新计算购物车中的商品价格：目的是当用户登录时享受会员价格，当用户退出登录时不享受会员价格
 * 如果商品有促销，价格不变
 *
 * @access  public
 * @return  void
 */
function recalculate_price()
{
    // 取得有可能改变价格的商品：除配件和赠品之外的商品
    $sql = 'SELECT c.rec_id, c.goods_id, c.goods_attr_id, g.promote_price, g.promote_start_date, c.goods_number,' .
        "g.promote_end_date, IFNULL(mp.user_price, g.shop_price * '". session('discount') ."') AS member_price " .
        'FROM ' . $GLOBALS['ecs']->table('cart') . ' AS c ' .
        'LEFT JOIN ' . $GLOBALS['ecs']->table('goods') . ' AS g ON g.goods_id = c.goods_id ' .
        "LEFT JOIN " . $GLOBALS['ecs']->table('member_price') . " AS mp " .
        "ON mp.goods_id = g.goods_id AND mp.user_rank = '" . session('user_rank') . "' " .
        "WHERE session_id = '" . SESS_ID . "' AND c.parent_id = 0 AND c.is_gift = 0 AND c.goods_id > 0 " .
        "AND c.rec_type = '" . CART_GENERAL_GOODS . "' AND c.extension_code <> 'package_buy'";

    $res = $GLOBALS['db']->getAll($sql);

    foreach ($res as $row) {
        $attr_id = empty($row['goods_attr_id']) ? [] : explode(',', $row['goods_attr_id']);


        $goods_price = get_final_price($row['goods_id'], $row['goods_number'], true, $attr_id);


        $goods_sql = "UPDATE " . $GLOBALS['ecs']->table('cart') . " SET goods_price = '$goods_price' " .
            "WHERE goods_id = '" . $row['goods_id'] . "' AND session_id = '" . SESS_ID . "' AND rec_id = '" . $row['rec_id'] . "'";

        $GLOBALS['db']->query($goods_sql);
    }

    // 删除赠品，重新选择
    $GLOBALS['db']->query('DELETE FROM ' . $GLOBALS['ecs']->table('cart') .
        " WHERE session_id = '" . SESS_ID . "' AND is_gift > 0");
}

/**
 * 查询评论内容
 *
 * @access  public
 * @params  integer     $id
 * @params  integer     $type
 * @params  integer     $page
 * @return  array
 */
function assign_comment($id, $type, $page = 1)
{
    // 取得评论列表
    $count = $GLOBALS['db']->getOne('SELECT COUNT(*) FROM ' . $GLOBALS['ecs']->table('comment') .
        " WHERE id_value = '$id' AND comment_type = '$type' AND status = 1 AND parent_id = 0");
    $size = !empty($GLOBALS['_CFG']['comments_number']) ? $GLOBALS['_CFG']['comments_number'] : 5;

    $page_count = ($count > 0) ? intval(ceil($count / $size)) : 1;

    $sql = 'SELECT * FROM ' . $GLOBALS['ecs']->table('comment') .
        " WHERE id_value = '$id' AND comment_type = '$type' AND status = 1 AND parent_id = 0" .
        ' ORDER BY comment_id DESC';
    $res = $GLOBALS['db']->selectLimit($sql, $size, ($page - 1) * $size);

    $arr = [];
    $ids = '';
    foreach ($res as $row) {
        $ids .= $ids ? ",$row[comment_id]" : $row['comment_id'];
        $arr[$row['comment_id']]['id'] = $row['comment_id'];
        $arr[$row['comment_id']]['email'] = $row['email'];
        $arr[$row['comment_id']]['username'] = $row['user_name'];
        $arr[$row['comment_id']]['content'] = str_replace('\r\n', '<br />', htmlspecialchars($row['content']));
        $arr[$row['comment_id']]['content'] = nl2br(str_replace('\n', '<br />', $arr[$row['comment_id']]['content']));
        $arr[$row['comment_id']]['rank'] = $row['comment_rank'];
        $arr[$row['comment_id']]['add_time'] = local_date($GLOBALS['_CFG']['time_format'], $row['add_time']);
    }
    // 取得已有回复的评论
    if ($ids) {
        $sql = 'SELECT * FROM ' . $GLOBALS['ecs']->table('comment') .
            " WHERE parent_id IN( $ids )";
        $res = $GLOBALS['db']->query($sql);
        foreach ($res as $row) {
            $arr[$row['parent_id']]['re_content'] = nl2br(str_replace('\n', '<br />', htmlspecialchars($row['content'])));
            $arr[$row['parent_id']]['re_add_time'] = local_date($GLOBALS['_CFG']['time_format'], $row['add_time']);
            $arr[$row['parent_id']]['re_email'] = $row['email'];
            $arr[$row['parent_id']]['re_username'] = $row['user_name'];
        }
    }
    // 分页样式
    //$pager['styleid'] = isset($GLOBALS['_CFG']['page_style'])? intval($GLOBALS['_CFG']['page_style']) : 0;
    $pager['page'] = $page;
    $pager['size'] = $size;
    $pager['record_count'] = $count;
    $pager['page_count'] = $page_count;
    $pager['page_first'] = "javascript:gotoPage(1,$id,$type)";
    $pager['page_prev'] = $page > 1 ? "javascript:gotoPage(" . ($page - 1) . ",$id,$type)" : 'javascript:;';
    $pager['page_next'] = $page < $page_count ? 'javascript:gotoPage(' . ($page + 1) . ",$id,$type)" : 'javascript:;';
    $pager['page_last'] = $page < $page_count ? 'javascript:gotoPage(' . $page_count . ",$id,$type)" : 'javascript:;';

    $cmt = ['comments' => $arr, 'pager' => $pager];

    return $cmt;
}

/**
 * 取得某模板某库设置的数量
 * @param   string $template 模板名，如index
 * @param   string $library 库名，如recommend_best
 * @param   int $def_num 默认数量：如果没有设置模板，显示的数量
 * @return  int         数量
 */
function get_library_number($library, $template = null)
{
    global $page_libs;

    if (empty($template)) {
        $template = basename(PHP_SELF);
        $template = substr($template, 0, strrpos($template, '.'));
    }
    $template = addslashes($template);

    static $lib_list = [];

    // 如果没有该模板的信息，取得该模板的信息
    if (!isset($lib_list[$template])) {
        $lib_list[$template] = [];
        $sql = "SELECT library, number FROM " . $GLOBALS['ecs']->table('template') .
            " WHERE theme = '" . $GLOBALS['_CFG']['template'] . "'" .
            " AND filename = '$template' AND remarks='' ";
        $res = $GLOBALS['db']->query($sql);
        foreach ($res as $row) {
            $lib = basename(strtolower(substr($row['library'], 0, strpos($row['library'], '.'))));
            $lib_list[$template][$lib] = $row['number'];
        }
    }

    $num = 0;
    if (isset($lib_list[$template][$library])) {
        $num = intval($lib_list[$template][$library]);
    } else {
        // 模板设置文件查找默认值
        load_helper('template', 'admin');
        static $static_page_libs = null;
        if ($static_page_libs == null) {
            $static_page_libs = $page_libs;
        }
        $lib = '/library/' . $library . '.lbi';

        $num = isset($static_page_libs[$template][$lib]) ? $static_page_libs[$template][$lib] : 3;
    }

    return $num;
}

/**
 * 获得当前环境的 HTTP 协议方式
 * @return string
 */
function protocol()
{
    return (isset($_SERVER['HTTPS']) && (strtolower($_SERVER['HTTPS']) != 'off')) ? 'https://' : 'http://';
}

/**
 * 获取HOST
 * @return mixed
 */
function url_host()
{
    if (isset($_SERVER['HTTP_X_FORWARDED_HOST'])) {
        $host = $_SERVER['HTTP_X_FORWARDED_HOST'];
    } elseif (isset($_SERVER['HTTP_HOST'])) {
        $host = $_SERVER['HTTP_HOST'];
    }
    return $host;
}

/**
 * 获取url
 * @return string
 */
function url_domain()
{
    $root = __ROOT__;

    if (substr($root, -1) != '/') {
        $root .= '/';
    }

    return $root;
}
