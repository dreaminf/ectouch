<?php

use yii\db\Schema;
use yii\db\Migration;

class m171208_102507_pay_log extends Migration
{

    public function safeUp()
    {
        $tableOptions = 'ENGINE=InnoDB';

        $this->createTable(
            '{{%pay_log}}',
            [
                'log_id'=> $this->primaryKey(10)->unsigned(),
                'order_id'=> $this->integer(10)->unsigned()->notNull()->defaultValue('0'),
                'order_amount'=> $this->decimal(10, 2)->unsigned()->notNull(),
                'order_type'=> $this->smallInteger(1)->unsigned()->notNull()->defaultValue(0),
                'is_paid'=> $this->smallInteger(1)->unsigned()->notNull()->defaultValue(0),
            ],$tableOptions
        );

    }

    public function safeDown()
    {
        $this->dropTable('{{%pay_log}}');
    }
}
