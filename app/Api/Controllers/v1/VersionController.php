<?php

namespace App\Api\Controllers\v1;

use App\Api\Models\V1\Version;

class VersionController extends BaseController
{
    /**
     * POST ecapi.version.check
     */
    public function actionCheck()
    {
        $data = Version::checkVersion();
        return $this->json($data);
    }

}
