<?php

namespace App\Api\Controllers\V2;

use Illuminate\Http\Request;
use App\Api\Controllers\Controller;
use App\Api\Models\V2\BonusType;
use App\Api\Models\V2\Features;

class CashGiftController extends Controller
{

    /**
     * POST ecapi.cashgift.list
     */
    public function index(Request $request)
    {
        $rules = [
            'page' => 'required|integer|min:1',
            'per_page' => 'required|integer|min:1',
            'status' => 'required|integer',
        ];

        if ($res = Features::check('cashgift')) {
            return $this->json($res);
        }

        if ($error = $this->validateInput($rules)) {
            return $error;
        }

        $model = BonusType::getListByUser($this->validated);

        return $this->json($model);
    }

    /**
     * POST ecapi.cashgift.available
     */
    public function available(Request $request)
    {
        $rules = [
            'page' => 'required|integer|min:1',
            'per_page' => 'required|integer|min:1',
            'total_price' => 'required|numeric|min:0',
        ];

        if ($res = Features::check('cashgift')) {
            return $this->json($res);
        }

        if ($error = $this->validateInput($rules)) {
            return $error;
        }

        $model = BonusType::getAvailableListByUser($this->validated);

        return $this->json($model);
    }

}
