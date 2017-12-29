<?php

namespace App\Services;


class ShopService
{
    /**
     * 载入配置信息
     *
     * @access  public
     * @return  array
     */
    function load_config()
    {
        $arr = [];

        $data = read_static_cache('shop_config');
        if ($data === false) {
            $sql = 'SELECT code, value FROM ' . $GLOBALS['ecs']->table('shop_config') . ' WHERE parent_id > 0';
            $res = $GLOBALS['db']->getAll($sql);

            foreach ($res as $row) {
                $arr[$row['code']] = $row['value'];
            }

            // 对数值型设置处理
            $arr['watermark_alpha'] = intval($arr['watermark_alpha']);
            $arr['market_price_rate'] = floatval($arr['market_price_rate']);
            $arr['integral_scale'] = floatval($arr['integral_scale']);
            //$arr['integral_percent']     = floatval($arr['integral_percent']);
            $arr['cache_time'] = intval($arr['cache_time']);
            $arr['thumb_width'] = intval($arr['thumb_width']);
            $arr['thumb_height'] = intval($arr['thumb_height']);
            $arr['image_width'] = intval($arr['image_width']);
            $arr['image_height'] = intval($arr['image_height']);
            $arr['best_number'] = !empty($arr['best_number']) && intval($arr['best_number']) > 0 ? intval($arr['best_number']) : 3;
            $arr['new_number'] = !empty($arr['new_number']) && intval($arr['new_number']) > 0 ? intval($arr['new_number']) : 3;
            $arr['hot_number'] = !empty($arr['hot_number']) && intval($arr['hot_number']) > 0 ? intval($arr['hot_number']) : 3;
            $arr['promote_number'] = !empty($arr['promote_number']) && intval($arr['promote_number']) > 0 ? intval($arr['promote_number']) : 3;
            $arr['top_number'] = intval($arr['top_number']) > 0 ? intval($arr['top_number']) : 10;
            $arr['history_number'] = intval($arr['history_number']) > 0 ? intval($arr['history_number']) : 5;
            $arr['comments_number'] = intval($arr['comments_number']) > 0 ? intval($arr['comments_number']) : 5;
            $arr['article_number'] = intval($arr['article_number']) > 0 ? intval($arr['article_number']) : 5;
            $arr['page_size'] = intval($arr['page_size']) > 0 ? intval($arr['page_size']) : 10;
            $arr['bought_goods'] = intval($arr['bought_goods']);
            $arr['goods_name_length'] = intval($arr['goods_name_length']);
            $arr['top10_time'] = intval($arr['top10_time']);
            $arr['goods_gallery_number'] = intval($arr['goods_gallery_number']) ? intval($arr['goods_gallery_number']) : 5;
            $arr['no_picture'] = !empty($arr['no_picture']) ? str_replace('../', './', $arr['no_picture']) : 'images/no_picture.gif'; // 修改默认商品图片的路径
            $arr['qq'] = !empty($arr['qq']) ? $arr['qq'] : '';
            $arr['ww'] = !empty($arr['ww']) ? $arr['ww'] : '';
            $arr['default_storage'] = isset($arr['default_storage']) ? intval($arr['default_storage']) : 1;
            $arr['min_goods_amount'] = isset($arr['min_goods_amount']) ? floatval($arr['min_goods_amount']) : 0;
            $arr['one_step_buy'] = empty($arr['one_step_buy']) ? 0 : 1;
            $arr['invoice_type'] = empty($arr['invoice_type']) ? ['type' => [], 'rate' => []] : unserialize($arr['invoice_type']);
            $arr['show_order_type'] = isset($arr['show_order_type']) ? $arr['show_order_type'] : 0;    // 显示方式默认为列表方式
            $arr['help_open'] = isset($arr['help_open']) ? $arr['help_open'] : 1;    // 显示方式默认为列表方式

            /**
             * 限定语言项
             */
            $lang_array = ['zh_cn', 'zh_tw', 'en_us'];
            if (empty($arr['lang']) || !in_array($arr['lang'], $lang_array)) {
                $arr['lang'] = 'zh_cn'; // 默认语言为简体中文
            }
            $arr['lang'] = str_replace('_', '-', $arr['lang']);

            if (empty($arr['integrate_code']) || $arr['integrate_code'] == 'ecshop') {
                $arr['integrate_code'] = 'passport';
            }
            write_static_cache('shop_config', $arr);
        } else {
            $arr = $data;
        }

        return $arr;
    }

    /**
     * 分配帮助信息
     *
     * @access  public
     * @return  array
     */
    function get_shop_help()
    {
        $sql = 'SELECT c.cat_id, c.cat_name, c.sort_order, a.article_id, a.title, a.file_url, a.open_type ' .
            'FROM ' . $GLOBALS['ecs']->table('article') . ' AS a ' .
            'LEFT JOIN ' . $GLOBALS['ecs']->table('article_cat') . ' AS c ' .
            'ON a.cat_id = c.cat_id WHERE c.cat_type = 5 AND a.is_open = 1 ' .
            'ORDER BY c.sort_order ASC, a.article_id';
        $res = $GLOBALS['db']->getAll($sql);

        $arr = [];
        foreach ($res as $key => $row) {
            $arr[$row['cat_id']]['cat_id'] = build_uri('article_cat', ['acid' => $row['cat_id']], $row['cat_name']);
            $arr[$row['cat_id']]['cat_name'] = $row['cat_name'];
            $arr[$row['cat_id']]['article'][$key]['article_id'] = $row['article_id'];
            $arr[$row['cat_id']]['article'][$key]['title'] = $row['title'];
            $arr[$row['cat_id']]['article'][$key]['short_title'] = $GLOBALS['_CFG']['article_title_length'] > 0 ?
                sub_str($row['title'], $GLOBALS['_CFG']['article_title_length']) : $row['title'];
            $arr[$row['cat_id']]['article'][$key]['url'] = $row['open_type'] != 1 ?
                build_uri('article', ['aid' => $row['article_id']], $row['title']) : trim($row['file_url']);
        }

        return $arr;
    }


    function assign_template($ctype = '', $catlist = [])
    {
        $GLOBALS['smarty']->assign('image_width', $GLOBALS['_CFG']['image_width']);
        $GLOBALS['smarty']->assign('image_height', $GLOBALS['_CFG']['image_height']);
        $GLOBALS['smarty']->assign('points_name', $GLOBALS['_CFG']['integral_name']);
        $GLOBALS['smarty']->assign('qq', explode(',', $GLOBALS['_CFG']['qq']));
        $GLOBALS['smarty']->assign('ww', explode(',', $GLOBALS['_CFG']['ww']));
        $GLOBALS['smarty']->assign('ym', explode(',', $GLOBALS['_CFG']['ym']));
        $GLOBALS['smarty']->assign('msn', explode(',', $GLOBALS['_CFG']['msn']));
        $GLOBALS['smarty']->assign('skype', explode(',', $GLOBALS['_CFG']['skype']));
        $GLOBALS['smarty']->assign('stats_code', $GLOBALS['_CFG']['stats_code']);
        $GLOBALS['smarty']->assign('copyright', sprintf($GLOBALS['_LANG']['copyright'], date('Y'), $GLOBALS['_CFG']['shop_name']));
        $GLOBALS['smarty']->assign('shop_name', $GLOBALS['_CFG']['shop_name']);
        $GLOBALS['smarty']->assign('service_email', $GLOBALS['_CFG']['service_email']);
        $GLOBALS['smarty']->assign('service_phone', $GLOBALS['_CFG']['service_phone']);
        $GLOBALS['smarty']->assign('shop_address', $GLOBALS['_CFG']['shop_address']);
        $GLOBALS['smarty']->assign('licensed', license_info());
        $GLOBALS['smarty']->assign('ecs_version', VERSION);
        $GLOBALS['smarty']->assign('icp_number', $GLOBALS['_CFG']['icp_number']);
        $GLOBALS['smarty']->assign('username', session('user_name', ''));
        $GLOBALS['smarty']->assign('category_list', cat_list(0, 0, true, 2, false));
        $GLOBALS['smarty']->assign('catalog_list', cat_list(0, 0, false, 1, false));
        $GLOBALS['smarty']->assign('navigator_list', get_navigator($ctype, $catlist));  //自定义导航栏

        if (!empty($GLOBALS['_CFG']['search_keywords'])) {
            $searchkeywords = explode(',', trim($GLOBALS['_CFG']['search_keywords']));
        } else {
            $searchkeywords = [];
        }
        $GLOBALS['smarty']->assign('searchkeywords', $searchkeywords);
    }

    /**
     * 保存推荐uid
     *
     * @access  public
     * @param   void
     *
     * @return void
     * @author xuanyan
     **/
    function set_affiliate()
    {
        $config = unserialize($GLOBALS['_CFG']['affiliate']);
        if (!empty($_GET['u']) && $config['on'] == 1) {
            if (!empty($config['config']['expire'])) {
                if ($config['config']['expire_unit'] == 'hour') {
                    $c = 1;
                } elseif ($config['config']['expire_unit'] == 'day') {
                    $c = 24;
                } elseif ($config['config']['expire_unit'] == 'week') {
                    $c = 24 * 7;
                } else {
                    $c = 1;
                }
                \Cookie::queue('affiliate_uid', intval($_GET['u']), 60 * $config['config']['expire'] * $c);
            } else {
                \Cookie::queue('affiliate_uid', intval($_GET['u']), 60 * 24); // 过期时间为 1 天
            }
        }
    }

    /**
     * 获取推荐uid
     *
     * @access  public
     * @param   void
     *
     * @return int
     * @author xuanyan
     **/
    function get_affiliate()
    {
        $affiliate_uid = request()->cookie('affiliate_uid');
        if (!empty($affiliate_uid)) {
            $uid = intval($affiliate_uid);
            if ($GLOBALS['db']->getOne('SELECT user_id FROM ' . $GLOBALS['ecs']->table('users') . "WHERE user_id = '$uid'")) {
                return $uid;
            } else {
                \Cookie::queue('affiliate_uid', null);
            }
        }

        return 0;
    }


    /**
     * 取得自定义导航栏列表
     * @param   string $type 位置，如top、bottom、middle
     * @return  array         列表
     */
    function get_navigator($ctype = '', $catlist = [])
    {
        $sql = 'SELECT * FROM ' . $GLOBALS['ecs']->table('nav') . '
            WHERE ifshow = \'1\' ORDER BY type, vieworder';
        $res = $GLOBALS['db']->query($sql);

        $cur_url = substr(strrchr($_SERVER['REQUEST_URI'], '/'), 1);

        if (intval($GLOBALS['_CFG']['rewrite'])) {
            if (strpos($cur_url, '-')) {
                preg_match('/([a-z]*)-([0-9]*)/', $cur_url, $matches);
                $cur_url = $matches[1] . '.php?id=' . $matches[2];
            }
        } else {
            $cur_url = substr(strrchr($_SERVER['REQUEST_URI'], '/'), 1);
        }

        $noindex = false;
        $active = 0;
        $navlist = [
            'top' => [],
            'middle' => [],
            'bottom' => []
        ];
        foreach ($res as $row) {
            $navlist[$row['type']][] = [
                'name' => $row['name'],
                'opennew' => $row['opennew'],
                'url' => $row['url'],
                'ctype' => $row['ctype'],
                'cid' => $row['cid'],
            ];
        }

        /*遍历自定义是否存在currentPage*/
        foreach ($navlist['middle'] as $k => $v) {
            $condition = empty($ctype) ? (strpos($cur_url, $v['url']) === 0) : (strpos($cur_url, $v['url']) === 0 && strlen($cur_url) == strlen($v['url']));
            if ($condition) {
                $navlist['middle'][$k]['active'] = 1;
                $noindex = true;
                $active += 1;
            }
        }

        if (!empty($ctype) && $active < 1) {
            foreach ($catlist as $key => $val) {
                foreach ($navlist['middle'] as $k => $v) {
                    if (!empty($v['ctype']) && $v['ctype'] == $ctype && $v['cid'] == $val && $active < 1) {
                        $navlist['middle'][$k]['active'] = 1;
                        $noindex = true;
                        $active += 1;
                    }
                }
            }
        }

        if ($noindex == false) {
            $navlist['config']['index'] = 1;
        }

        return $navlist;
    }


    /**
     * 处理序列化的支付、配送的配置参数
     * 返回一个以name为索引的数组
     *
     * @access  public
     * @param   string $cfg
     * @return  void
     */
    function unserialize_config($cfg)
    {
        if (is_string($cfg) && ($arr = unserialize($cfg)) !== false) {
            $config = [];

            foreach ($arr as $key => $val) {
                $config[$val['name']] = $val['value'];
            }

            return $config;
        } else {
            return false;
        }
    }

}