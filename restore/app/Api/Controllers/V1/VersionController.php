<?php

namespace app\api\controllers\v1;

use app\api\models\v1\Version;

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
