<?php

namespace App\Api\Controllers\V2;

use App\Api\Controllers\Controller;
use Illuminate\Http\Request;
use App\Api\Models\V2\Version;

class VersionController extends Controller
{
    /**
     * POST ecapi.version.check
     */
    public function check(Request $request)
    {
        $data = Version::checkVersion();

        return $this->json($data);
    }

}
