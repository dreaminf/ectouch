<?php
/**
 *
 */

class Ultron_Ext_Log_File extends Ultron_Ext_Log_Abstract
{
    protected function _handler($text)
    {
        $dir = dirname($this->_options['file']);
        is_dir($dir) || mkdir($dir, $this->_options['mode'], true);
        return file_put_contents($this->_options['file'], $text . "\n", FILE_APPEND | LOCK_EX);
    }
}