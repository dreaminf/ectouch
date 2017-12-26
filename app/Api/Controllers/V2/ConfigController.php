<?php
//

namespace App\Api\Controllers\V2;

use Illuminate\Http\Request;
use App\Api\Controllers\Controller;

use App\Models\V2\Configs;

class ConfigController extends Controller {

    public function index()
    {
        $data = Configs::getList();
        return $this->json($data);
    }
   
}
