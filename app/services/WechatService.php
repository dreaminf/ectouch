<?php

namespace app\services;

use app\repositorys\ArticleRepository;

class WechatService
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
    public function all($condition = [])
    {
        return $this->article->all($condition);
    }

    /**
     * @param $data
     * @return mixed
     */
    public function create($data)
    {

    }

    /**
     * @param $id
     * @return mixed
     */
    public function detail($id)
    {
        return $this->article->show($id);

    }

    /**
     * @param $data
     * @return mixed
     */
    public function update($data)
    {

    }

    /**
     * @param $id
     * @return mixed
     */
    public function delete($id)
    {

    }
}