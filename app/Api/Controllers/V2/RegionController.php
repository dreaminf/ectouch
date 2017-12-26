<?php

namespace App\Api\Controllers\V2;

use Illuminate\Http\Request;
use App\Api\Controllers\Controller;
use App\Api\Models\V2\Region;

class RegionController extends Controller
{

    public function index(Request $request)
    {
        $response = Region::getList();

        return $this->json($response);
    }
}
