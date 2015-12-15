<?php
/**
 *
 */

class Ultron_Ext_Log_Echo extends Ultron_Ext_Log_Abstract
{
    protected function _handler($text)
    {
        echo $text;
    }
}