<?php

namespace App\Api\Controllers\V2;

use Illuminate\Http\Request;
use App\Api\Controllers\Controller;
use App\Api\Models\V2\Banner;

class BannerController extends Controller
{

    /**
     * POST ecapi.banner.list
     */
    public function index(Request $request)
    {
        $model = Banner::getList();

        return $this->json($model);
    }
}
