<?php

namespace app\notifications;

/**
 * 分销商审核
 * Class DrpAccountChecked
 * @package app\notifications
 */
class DrpAccountChecked
{

    /**
     * 设置发送通道
     * @param $via
     * @return $this
     */
    public function setVia($via)
    {
        if (!is_array($via)) {
            $this->via = [$via];
        }

        return $this;
    }

}