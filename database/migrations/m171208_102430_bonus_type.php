<?php

use yii\db\Schema;
use yii\db\Migration;

class m171208_102430_bonus_type extends Migration
{

    public function safeUp()
    {
        $tableOptions = 'ENGINE=InnoDB';

        $this->createTable(
            '{{%bonus_type}}',
            [
                'type_id'=> $this->primaryKey(10)->unsigned(),
                'type_name'=> $this->string(60)->notNull()->defaultValue(''),
                'type_money'=> $this->decimal(10, 2)->notNull()->defaultValue('0.00'),
                'send_type'=> $this->smallInteger(3)->unsigned()->notNull()->defaultValue(0),
                'min_amount'=> $this->decimal(10, 2)->unsigned()->notNull()->defaultValue('0.00'),
                'max_amount'=> $this->decimal(10, 2)->unsigned()->notNull()->defaultValue('0.00'),
                'send_start_date'=> $this->integer(10)->notNull()->defaultValue(0),
                'send_end_date'=> $this->integer(10)->notNull()->defaultValue(0),
                'use_start_date'=> $this->integer(10)->notNull()->defaultValue(0),
                'use_end_date'=> $this->integer(10)->notNull()->defaultValue(0),
                'min_goods_amount'=> $this->decimal(10, 2)->unsigned()->notNull()->defaultValue('0.00'),
            ],$tableOptions
        );

    }

    public function safeDown()
    {
        $this->dropTable('{{%bonus_type}}');
    }
}
