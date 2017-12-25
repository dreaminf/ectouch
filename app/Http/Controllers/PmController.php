<?php

namespace App\Http\Controllers;

/**
 * Class PmController
 * @package App\Http\Controllers
 */
class PmController extends BaseController
{
    public function actionIndex()
    {
        if (empty(session('user_id')) || $GLOBALS['_CFG']['integrate_code'] == 'ecshop') {
            return redirect('./');
        }

        uc_call("uc_pm_location", [session('user_id')]);
    }
}
