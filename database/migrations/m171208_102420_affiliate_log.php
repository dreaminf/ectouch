<?php

use yii\db\Schema;
use yii\db\Migration;

class m171208_102420_affiliate_log extends Migration
{

    public function safeUp()
    {
        $tableOptions = 'ENGINE=InnoDB';

        $this->createTable(
            '{{%affiliate_log}}',
            [
                'log_id'=> $this->primaryKey(10),
                'order_id'=> $this->integer(10)->notNull(),
                'time'=> $this->integer(10)->notNull(),
                'user_id'=> $this->integer(10)->notNull(),
                'user_name'=> $this->string(60)->null()->defaultValue(null),
                'money'=> $this->decimal(10, 2)->notNull()->defaultValue('0.00'),
                'point'=> $this->integer(10)->notNull()->defaultValue(0),
                'separate_type'=> $this->smallInteger(1)->notNull()->defaultValue(0),
            ],$tableOptions
        );

    }

    public function safeDown()
    {
        $this->dropTable('{{%affiliate_log}}');
    }
}
