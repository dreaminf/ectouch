<?php

/**
 * Created by PhpStorm.
 * User: Admin
 * Date: 2015-02-27
 * Time: 14:48
 */
class Config_model extends Base_model
{

    public function get_shop_config()
    {
        $data = $this->db->get('shop_config')->result_array();

        $result = array();
        foreach ($data AS $row) {
            $result[$row['code']] = $row['value'];
        }

        // 对数值型设置处理
        $result['watermark_alpha'] = intval($result['watermark_alpha']);
        $result['market_price_rate'] = floatval($result['market_price_rate']);
        $result['integral_scale'] = floatval($result['integral_scale']);
        $result['cache_time'] = intval($result['cache_time']);
        $result['thumb_width'] = intval($result['thumb_width']);
        $result['thumb_height'] = intval($result['thumb_height']);
        $result['image_width'] = intval($result['image_width']);
        $result['image_height'] = intval($result['image_height']);
        $result['best_number'] = !empty($result['best_number']) && intval($result['best_number']) > 0 ? intval($result['best_number']) : 3;
        $result['new_number'] = !empty($result['new_number']) && intval($result['new_number']) > 0 ? intval($result['new_number']) : 3;
        $result['hot_number'] = !empty($result['hot_number']) && intval($result['hot_number']) > 0 ? intval($result['hot_number']) : 3;
        $result['promote_number'] = !empty($result['promote_number']) && intval($result['promote_number']) > 0 ? intval($result['promote_number']) : 3;
        $result['top_number'] = intval($result['top_number']) > 0 ? intval($result['top_number']) : 10;
        $result['history_number'] = intval($result['history_number']) > 0 ? intval($result['history_number']) : 5;
        $result['comments_number'] = intval($result['comments_number']) > 0 ? intval($result['comments_number']) : 5;
        $result['article_number'] = intval($result['article_number']) > 0 ? intval($result['article_number']) : 5;
        $result['page_size'] = intval($result['page_size']) > 0 ? intval($result['page_size']) : 10;
        $result['bought_goods'] = intval($result['bought_goods']);
        $result['goods_name_length'] = intval($result['goods_name_length']);
        $result['top10_time'] = intval($result['top10_time']);
        $result['goods_gallery_number'] = intval($result['goods_gallery_number']) ? intval($result['goods_gallery_number']) : 5;
        $result['no_picture'] = !empty($result['no_picture']) ? str_replace('../', './', $result['no_picture']) : 'images/no_picture.gif'; // 修改默认商品图片的路径
        $result['qq'] = !empty($result['qq']) ? $result['qq'] : '';
        $result['ww'] = !empty($result['ww']) ? $result['ww'] : '';
        $result['default_storage'] = isset($result['default_storage']) ? intval($result['default_storage']) : 1;
        $result['min_goods_amount'] = isset($result['min_goods_amount']) ? floatval($result['min_goods_amount']) : 0;
        $result['one_step_buy'] = empty($result['one_step_buy']) ? 0 : 1;
        $result['invoice_type'] = empty($result['invoice_type']) ? array('type' => array(), 'rate' => array()) : unserialize($result['invoice_type']);
        $result['show_order_type'] = isset($result['show_order_type']) ? $result['show_order_type'] : 0; // 显示方式默认为列表方式
        $result['help_open'] = isset($result['help_open']) ? $result['help_open'] : 1; // 显示方式默认为列表方式

        // 如果没有版本号
        if (!isset($result['ecs_version'])) {
            $result['ecs_version'] = 'v1.1';
        }

        //限定语言项
        $lang_array = array('zh_cn', 'zh_tw', 'en_us');
        if (empty($result['lang']) || !in_array($result['lang'], $lang_array)) {
            $result['lang'] = 'zh_cn'; // 默认语言为简体中文
        }

        // 默认的会员整合插件为 ecshop
        if (empty($result['integrate_code'])) {
            $result['integrate_code'] = 'ecshop';
        }

        $result['shop_url'] = 'http://192.168.1.92/ecshop/';

        return $result;
    }
}