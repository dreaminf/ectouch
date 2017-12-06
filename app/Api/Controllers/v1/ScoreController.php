<?php

namespace App\Api\Controllers\v1;

use App\Api\Models\V1\Member;
use App\Api\Models\V1\Features;
use App\Api\Models\V1\AccountLog;

class ScoreController extends BaseController
{

    /**
     * POST ecapi.score.get
     */
    public function actionView()
    {
        if ($res = Features::check('score')) {
            return $this->json($res);
        }

        $model = Member::getUserPayPoints();
        return $this->json($model);
    }

    /**
     * POST ecapi.score.history.list
     */
    public function actionHistory()
    {
        if ($res = Features::check('score')) {
            return $this->json($res);
        }

        $rules = [
            // 'page' => 'required|integer|min:1',
            // 'per_page' => 'required|integer|min:1',

            [['page', 'per_page'], 'required'],
            [['page', 'per_page'], 'integer', 'min' => 1]
        ];

        if ($error = $this->validateInput($rules)) {
            return $error;
        }

        $model = AccountLog::getPayPointsList($this->validated);

        return $this->json($model);
    }
}
