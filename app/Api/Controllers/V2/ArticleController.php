<?php

namespace App\Api\Controllers\V2;

use Illuminate\Http\Request;
use App\Api\Controllers\Controller;
use App\Api\Models\V2\Article;
use App\Api\Models\V2\ArticleCategory;

class ArticleController extends Controller
{

    /**
     * POST ecapi.article.list
     */
    public function index(Request $request)
    {
        $rules = [
            'id' => 'required|integer',
            'page' => 'required|integer|min:1',
            'per_page' => 'required|integer|min:1',
        ];

        if ($error = $this->validateInput($rules)) {
            return $error;
        }

        $model = ArticleCategory::getList($this->validated);

        return $this->json($model);
    }

    /**
     * GET article.{id:[0-9]+}
     */
    public function show($id)
    {
        return Article::getArticle($id);
    }
}
