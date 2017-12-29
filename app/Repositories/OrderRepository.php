<?php

namespace App\Repositories;

class OrderRepository
{
    /**
     * 去除虚拟卡中重复数据
     *
     *
     */
    function deleteRepeat($array)
    {
        $_card_sn_record = [];
        foreach ($array as $_k => $_v) {
            foreach ($_v['info'] as $__k => $__v) {
                if (in_array($__v['card_sn'], $_card_sn_record)) {
                    unset($array[$_k]['info'][$__k]);
                } else {
                    array_push($_card_sn_record, $__v['card_sn']);
                }
            }
        }
        return $array;
    }
}