<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

class Article_model extends Base_model
{

    public function add_article()
    {

    }

    public function edit_article()
    {

    }

    public function delete_article()
    {

    }

    /**
     * 获得指定的文章的详细信息
     * @param $article_id
     */
    public function get_article($article_id)
    {
        /* 获得文章的信息 */
        $sql = "SELECT a.*, IFNULL(AVG(r.comment_rank), 0) AS comment_rank " .
            "FROM " . $GLOBALS['ecs']->table('article') . " AS a " .
            "LEFT JOIN " . $GLOBALS['ecs']->table('comment') . " AS r ON r.id_value = a.article_id AND comment_type = 1 " .
            "WHERE a.is_open = 1 AND a.article_id = '$article_id' GROUP BY a.article_id";
        $row = $GLOBALS['db']->getRow($sql);

        if ($row !== false) {
            $row['comment_rank'] = ceil($row['comment_rank']);                              // 用户评论级别取整
            $row['add_time'] = local_date($GLOBALS['_CFG']['date_format'], $row['add_time']); // 修正添加时间显示

            /* 作者信息如果为空，则用网站名称替换 */
            if (empty($row['author']) || $row['author'] == '_SHOPHELP') {
                $row['author'] = $GLOBALS['_CFG']['shop_name'];
            }
        }

        return $row;
    }

    /**
     * 获得文章关联的商品
     *
     * @access  public
     * @param   integer $id
     * @return  array
     */
    function article_related_goods($id)
    {
        $sql = 'SELECT g.goods_id, g.goods_name, g.goods_thumb, g.goods_img, g.shop_price AS org_price, ' .
            "IFNULL(mp.user_price, g.shop_price * '$_SESSION[discount]') AS shop_price, " .
            'g.market_price, g.promote_price, g.promote_start_date, g.promote_end_date ' .
            'FROM ' . $GLOBALS['ecs']->table('goods_article') . ' ga ' .
            'LEFT JOIN ' . $GLOBALS['ecs']->table('goods') . ' AS g ON g.goods_id = ga.goods_id ' .
            "LEFT JOIN " . $GLOBALS['ecs']->table('member_price') . " AS mp " .
            "ON mp.goods_id = g.goods_id AND mp.user_rank = '$_SESSION[user_rank]' " .
            "WHERE ga.article_id = '$id' AND g.is_on_sale = 1 AND g.is_alone_sale = 1 AND g.is_delete = 0";
        $res = $GLOBALS['db']->query($sql);

        $arr = array();
        while ($row = $GLOBALS['db']->fetchRow($res)) {
            $arr[$row['goods_id']]['goods_id'] = $row['goods_id'];
            $arr[$row['goods_id']]['goods_name'] = $row['goods_name'];
            $arr[$row['goods_id']]['short_name'] = $GLOBALS['_CFG']['goods_name_length'] > 0 ?
                sub_str($row['goods_name'], $GLOBALS['_CFG']['goods_name_length']) : $row['goods_name'];
            $arr[$row['goods_id']]['goods_thumb'] = get_image_path($row['goods_id'], $row['goods_thumb'], true);
            $arr[$row['goods_id']]['goods_img'] = get_image_path($row['goods_id'], $row['goods_img']);
            $arr[$row['goods_id']]['market_price'] = price_format($row['market_price']);
            $arr[$row['goods_id']]['shop_price'] = price_format($row['shop_price']);
            $arr[$row['goods_id']]['url'] = build_uri('goods', array('gid' => $row['goods_id']), $row['goods_name']);

            if ($row['promote_price'] > 0) {
                $arr[$row['goods_id']]['promote_price'] = bargain_price($row['promote_price'], $row['promote_start_date'], $row['promote_end_date']);
                $arr[$row['goods_id']]['formated_promote_price'] = price_format($arr[$row['goods_id']]['promote_price']);
            } else {
                $arr[$row['goods_id']]['promote_price'] = 0;
            }
        }

        return $arr;
    }

}