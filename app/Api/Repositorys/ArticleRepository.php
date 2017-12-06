<?php
namespace app\repositorys;

use App\Api\Models\V1\ArticleCategory;
use App\Api\Models\V1\Article;
use yii\data\Pagination;
use yii\helpers\Url;
use Yii;

class ArticleRepository extends FoundationRepository
{
    /**
     * @param array $attributes
     * @return array
     */
    public static function getList(array $attributes)
    {
        extract($attributes);

        if (ArticleCategory::find()->where(['parent_id' => $id])->count() > 0) {
            $model = ArticleCategory::find()->where(['parent_id' => $id]);
        } else {
            $model = Article::find()->where(['cat_id' => $id]);
        }

        $total = $model->count();
        $pages = new Pagination(['totalCount' => $total, 'pageSize' => $per_page]);
        $data = $model->orderBy('cat_id', 'DESC')->offset($pages->offset)->limit($pages->limit)->asArray()->all();

        return self::formatBody(['articles' => $data, 'paged' => self::formatPaged($page, $per_page, $total)]);
    }

    /**
     * @param $id
     * @return array
     */
    public static function getArticle($id)
    {
        $data = [];
        if ($model = Article::findOne($id)) {
            $data['id'] = $model->article_id;
            $data['title'] = $model->title;
            $data['content'] = $model->content;
            $data['add_time'] = $model->add_time;
            $data['category'] = $model->getCategory()->one();
        }

        return self::formatBody(['article' => $data]);
    }
}