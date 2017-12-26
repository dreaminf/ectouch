<?php
//

namespace App\Api\Controllers\V2;

use App\Api\Controllers\Controller;
use Illuminate\Http\Request;

use App\Models\V2\ShopConfig;

class SiteController extends Controller
{
    //POST  ecapi.site.get
    public function index()
    {
       return $this->json(ShopConfig::getSiteInfo());
    }
}