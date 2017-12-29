<?php

namespace App\Services;


class CommentService
{

    /**
     *  获取用户评论
     *
     * @access  public
     * @param   int $user_id 用户id
     * @param   int $page_size 列表最大数量
     * @param   int $start 列表起始页
     * @return  array
     */
    function get_comment_list($user_id, $page_size, $start)
    {
        $sql = "SELECT c.*, g.goods_name AS cmt_name, r.content AS reply_content, r.add_time AS reply_time " .
            " FROM " . $GLOBALS['ecs']->table('comment') . " AS c " .
            " LEFT JOIN " . $GLOBALS['ecs']->table('comment') . " AS r " .
            " ON r.parent_id = c.comment_id AND r.parent_id > 0 " .
            " LEFT JOIN " . $GLOBALS['ecs']->table('goods') . " AS g " .
            " ON c.comment_type=0 AND c.id_value = g.goods_id " .
            " WHERE c.user_id='$user_id'";
        $res = $GLOBALS['db']->selectLimit($sql, $page_size, $start);

        $comments = [];
        $to_article = [];
        foreach ($res as $row) {
            $row['formated_add_time'] = local_date($GLOBALS['_CFG']['time_format'], $row['add_time']);
            if ($row['reply_time']) {
                $row['formated_reply_time'] = local_date($GLOBALS['_CFG']['time_format'], $row['reply_time']);
            }
            if ($row['comment_type'] == 1) {
                $to_article[] = $row["id_value"];
            }
            $comments[] = $row;
        }

        if ($to_article) {
            $sql = "SELECT article_id , title FROM " . $GLOBALS['ecs']->table('article') . " WHERE " . db_create_in($to_article, 'article_id');
            $arr = $GLOBALS['db']->getAll($sql);
            $to_cmt_name = [];
            foreach ($arr as $row) {
                $to_cmt_name[$row['article_id']] = $row['title'];
            }

            foreach ($comments as $key => $row) {
                if ($row['comment_type'] == 1) {
                    $comments[$key]['cmt_name'] = isset($to_cmt_name[$row['id_value']]) ? $to_cmt_name[$row['id_value']] : '';
                }
            }
        }

        return $comments;
    }

}