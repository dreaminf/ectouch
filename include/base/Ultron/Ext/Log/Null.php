<?php
/**
 *
 */

class Ultron_Ext_Log_Null extends Ultron_Ext_Log_Abstract
{
    protected function _handler($text)
    {
        return;
    }
}