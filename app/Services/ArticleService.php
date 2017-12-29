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


    /**
     * 获得指定文章分类下所有底层分类的ID
     *
     * @access  public
     * @param   integer $cat 指定的分类ID
     *
     * @return void
     */
    function get_article_children($cat = 0)
    {
        return db_create_in(array_unique(array_merge([$cat], array_keys(article_cat_list($cat, 0, false)))), 'cat_id');
    }

    /**
     * 获得指定分类同级的所有分类以及该分类下的子分类
     *
     * @access  public
     * @param   integer $cat_id 分类编号
     * @return  array
     */
    function article_categories_tree($cat_id = 0)
    {
        if ($cat_id > 0) {
            $sql = 'SELECT parent_id FROM ' . $GLOBALS['ecs']->table('article_cat') . " WHERE cat_id = '$cat_id'";
            $parent_id = $GLOBALS['db']->getOne($sql);
        } else {
            $parent_id = 0;
        }

        /*
         判断当前分类中全是是否是底级分类，
         如果是取出底级分类上级分类，
         如果不是取当前分类及其下的子分类
        */
        $sql = 'SELECT count(*) FROM ' . $GLOBALS['ecs']->table('article_cat') . " WHERE parent_id = '$parent_id'";
        if ($GLOBALS['db']->getOne($sql)) {
            // 获取当前分类及其子分类
            $sql = 'SELECT a.cat_id, a.cat_name, a.sort_order AS parent_order, a.cat_id, ' .
                'b.cat_id AS child_id, b.cat_name AS child_name, b.sort_order AS child_order ' .
                'FROM ' . $GLOBALS['ecs']->table('article_cat') . ' AS a ' .
                'LEFT JOIN ' . $GLOBALS['ecs']->table('article_cat') . ' AS b ON b.parent_id = a.cat_id ' .
                "WHERE a.parent_id = '$parent_id' AND a.cat_type=1 ORDER BY parent_order ASC, a.cat_id ASC, child_order ASC";
        } else {
            // 获取当前分类及其父分类
            $sql = 'SELECT a.cat_id, a.cat_name, b.cat_id AS child_id, b.cat_name AS child_name, b.sort_order ' .
                'FROM ' . $GLOBALS['ecs']->table('article_cat') . ' AS a ' .
                'LEFT JOIN ' . $GLOBALS['ecs']->table('article_cat') . ' AS b ON b.parent_id = a.cat_id ' .
                "WHERE b.parent_id = '$parent_id' AND b.cat_type = 1 ORDER BY sort_order ASC";
        }
        $res = $GLOBALS['db']->getAll($sql);

        $cat_arr = [];
        foreach ($res as $row) {
            $cat_arr[$row['cat_id']]['id'] = $row['cat_id'];
            $cat_arr[$row['cat_id']]['name'] = $row['cat_name'];
            $cat_arr[$row['cat_id']]['url'] = build_uri('article_cat', ['acid' => $row['cat_id']], $row['cat_name']);

            if ($row['child_id'] != null) {
                $cat_arr[$row['cat_id']]['children'][$row['child_id']]['id'] = $row['child_id'];
                $cat_arr[$row['cat_id']]['children'][$row['child_id']]['name'] = $row['child_name'];
                $cat_arr[$row['cat_id']]['children'][$row['child_id']]['url'] = build_uri('article_cat', ['acid' => $row['child_id']], $row['child_name']);
            }
        }

        return $cat_arr;
    }

    /**
     * 获得指定文章分类的所有上级分类
     *
     * @access  public
     * @param   integer $cat 分类编号
     * @return  array
     */
    function get_article_parent_cats($cat)
    {
        if ($cat == 0) {
            return [];
        }

        $arr = $GLOBALS['db']->getAll('SELECT cat_id, cat_name, parent_id FROM ' . $GLOBALS['ecs']->table('article_cat'));

        if (empty($arr)) {
            return [];
        }

        $index = 0;
        $cats = [];

        while (1) {
            foreach ($arr as $row) {
                if ($cat == $row['cat_id']) {
                    $cat = $row['parent_id'];

                    $cats[$index]['cat_id'] = $row['cat_id'];
                    $cats[$index]['cat_name'] = $row['cat_name'];

                    $index++;
                    break;
                }
            }

            if ($index == 0 || $cat == 0) {
                break;
            }
        }

        return $cats;
    }


    /**
     * 分配文章列表给smarty
     *
     * @access  public
     * @param   integer $id 文章分类的编号
     * @param   integer $num 文章数量
     * @return  array
     */
    function assign_articles($id, $num)
    {
        $sql = 'SELECT cat_name FROM ' . $GLOBALS['ecs']->table('article_cat') . " WHERE cat_id = '" . $id . "'";

        $cat['id'] = $id;
        $cat['name'] = $GLOBALS['db']->getOne($sql);
        $cat['url'] = build_uri('article_cat', ['acid' => $id], $cat['name']);

        $articles['cat'] = $cat;
        $articles['arr'] = get_cat_articles($id, 1, $num);

        return $articles;
    }

}