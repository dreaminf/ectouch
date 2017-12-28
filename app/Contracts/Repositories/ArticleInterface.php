<?php

namespace App\Contracts\Repositories;

interface ArticleInterface
{
    public function articleCatList();

    public function articleCatOptions();
}