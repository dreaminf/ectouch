<?php

use yii\db\Schema;
use yii\db\Migration;

class m171208_102445_exchange_goods extends Migration
{

    public function safeUp()
    {
        $tableOptions = 'ENGINE=InnoDB';

        $this->createTable(
            '{{%exchange_goods}}',
            [
                'goods_id'=> $this->primaryKey(10)->unsigned(),
                'exchange_integral'=> $this->integer(10)->unsigned()->notNull()->defaultValue('0'),
                'is_exchange'=> $this->smallInteger(1)->unsigned()->notNull()->defaultValue(0),
                'is_hot'=> $this->smallInteger(1)->unsigned()->notNull()->defaultValue(0),
            ],$tableOptions
        );

    }

    public function safeDown()
    {
        $this->dropTable('{{%exchange_goods}}');
    }
}
