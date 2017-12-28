<?php

namespace App\Services;

use App\Repositorys\ArticleRepository;
use App\Contracts\Services\ArticleInterface;

class ArticleService implements ArticleInterface
{
    private $article;

    public function __construct(ArticleRepository $article)
    {
        $this->article = $article;
    }

    /**
     * @param $condition
     * @return mixed
     */
    public function getList($condition = [])
    {
        return $this->article->all($condition);
    }

    /**
     * @param $data
     * @return mixed
     */
    public function createArticle($data)
    {

    }

    /**
     * @param $id
     * @return mixed
     */
    public function getDetail($id)
    {
        return $this->article->show($id);

    }

    /**
     * @param $data
     * @return mixed
     */
    public function updateArticle($data)
    {

    }

    /**
     * @param $id
     * @return mixed
     */
    public function deleteArticle($id)
    {

    }

    public function getCatArticles($cat_id, $page = 1, $size = 20, $requirement = '')
    {
        //取出所有非0的文章
        if ($cat_id == '-1') {
            $cat_str = 'cat_id > 0';
        } else {
            $cat_str = get_article_children($cat_id);
        }
        //增加搜索条件，如果有搜索内容就进行搜索
        if ($requirement != '') {
            $sql = 'SELECT article_id, title, author, add_time, file_url, open_type' .
                ' FROM ' . $GLOBALS['ecs']->table('article') .
                ' WHERE is_open = 1 AND title like \'%' . $requirement . '%\' ' .
                ' ORDER BY article_type DESC, article_id DESC';
        } else {
            $sql = 'SELECT article_id, title, author, add_time, file_url, open_type' .
                ' FROM ' . $GLOBALS['ecs']->table('article') .
                ' WHERE is_open = 1 AND ' . $cat_str .
                ' ORDER BY article_type DESC, article_id DESC';
        }

        $res = $GLOBALS['db']->selectLimit($sql, $size, ($page - 1) * $size);

        $arr = [];
        if ($res) {
            foreach ($res as $row) {
                $article_id = $row['article_id'];

                $arr[$article_id]['id'] = $article_id;
                $arr[$article_id]['title'] = $row['title'];
                $arr[$article_id]['short_title'] = $GLOBALS['_CFG']['article_title_length'] > 0 ? sub_str($row['title'], $GLOBALS['_CFG']['article_title_length']) : $row['title'];
                $arr[$article_id]['author'] = empty($row['author']) || $row['author'] == '_SHOPHELP' ? $GLOBALS['_CFG']['shop_name'] : $row['author'];
                $arr[$article_id]['url'] = $row['open_type'] != 1 ? build_uri('article', ['aid' => $article_id], $row['title']) : trim($row['file_url']);
                $arr[$article_id]['add_time'] = date($GLOBALS['_CFG']['date_format'], $row['add_time']);
            }
        }

        return $arr;
    }

    public function getArticleCount($cat_id, $requirement = '')
    {
        if ($requirement != '') {
            $count = $GLOBALS['db']->getOne('SELECT COUNT(*) FROM ' . $GLOBALS['ecs']->table('article') . ' WHERE ' . get_article_children($cat_id) . ' AND  title like \'%' . $requirement . '%\'  AND is_open = 1');
        } else {
            $count = $GLOBALS['db']->getOne("SELECT COUNT(*) FROM " . $GLOBALS['ecs']->table('article') . " WHERE " . get_article_children($cat_id) . " AND is_open = 1");
        }
        return $count;
    }
}