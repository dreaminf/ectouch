<?php

namespace app\http\controllers;

/**
 * Class PmController
 * @package app\http\controllers
 */
class PmController extends Controller
{
    public function actionIndex()
    {
        if (empty(session('user_id')) || $GLOBALS['_CFG']['integrate_code'] == 'ecshop') {
            return $this->redirect('./');
        }

        uc_call("uc_pm_location", [session('user_id')]);
    }
}
