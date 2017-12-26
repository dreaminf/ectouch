<?php

namespace App\Api\Controllers\V2;

use Illuminate\Http\Request;
use App\Api\Controllers\Controller;
use App\Api\Models\V2\AreaCode;

class AreaCodeController extends Controller
{

    /**
     * POST ecapi.areacode.list
     */
    public function index(Request $request)
    {
        $model = AreaCode::getList();

        return $this->json($model);
    }

}
