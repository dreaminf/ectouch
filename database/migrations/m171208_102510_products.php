<?php

use yii\db\Schema;
use yii\db\Migration;

class m171208_102510_products extends Migration
{

    public function safeUp()
    {
        $tableOptions = 'ENGINE=InnoDB';

        $this->createTable(
            '{{%products}}',
            [
                'product_id'=> $this->primaryKey(10)->unsigned(),
                'goods_id'=> $this->integer(10)->unsigned()->notNull()->defaultValue('0'),
                'goods_attr'=> $this->string(50)->null()->defaultValue(null),
                'product_sn'=> $this->string(60)->null()->defaultValue(null),
                'product_number'=> $this->integer(10)->unsigned()->null()->defaultValue(0),
            ],$tableOptions
        );

    }

    public function safeDown()
    {
        $this->dropTable('{{%products}}');
    }
}
