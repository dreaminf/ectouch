<?php

namespace App\Api\Controllers\V2;

use Illuminate\Http\Request;
use App\Api\Controllers\Controller;
use App\Api\Models\V2\Member;
use App\Api\Models\V2\Features;
use App\Api\Models\V2\AccountLog;

class ScoreController extends Controller
{

    /**
     * POST ecapi.score.get
     */
    public function view(Request $request)
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
    public function history(Request $request)
    {
        if ($res = Features::check('score')) {
            return $this->json($res);
        }

        $rules = [
            'page' => 'required|integer|min:1',
            'per_page' => 'required|integer|min:1',
        ];

        if ($error = $this->validateInput($rules)) {
            return $error;
        }

        $model = AccountLog::getPayPointsList($this->validated);

        return $this->json($model);
    }
}
