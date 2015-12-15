<?php
/**
 *
 */

class Ultron_Ext_Db_Pdo_Mysql extends Ultron_Ext_Db_Pdo_Abstract
{
    protected function _dsn($params)
    {
        return "mysql:host={$params['host']};port={$params['port']};dbname={$params['database']}";
    }
}