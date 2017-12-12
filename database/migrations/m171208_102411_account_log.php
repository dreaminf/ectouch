<?php

use yii\db\Schema;
use yii\db\Migration;

class m171208_102411_account_log extends Migration
{

    public function safeUp()
    {
        $tableOptions = 'ENGINE=InnoDB';

        $this->createTable(
            '{{%account_log}}',
            [
                'log_id'=> $this->primaryKey(10)->unsigned(),
                'user_id'=> $this->integer(10)->unsigned()->notNull(),
                'user_money'=> $this->decimal(10, 2)->notNull(),
                'frozen_money'=> $this->decimal(10, 2)->notNull(),
                'rank_points'=> $this->integer(10)->notNull(),
                'pay_points'=> $this->integer(10)->notNull(),
                'change_time'=> $this->integer(10)->unsigned()->notNull(),
                'change_desc'=> $this->string(255)->notNull(),
                'change_type'=> $this->smallInteger(3)->unsigned()->notNull(),
            ],$tableOptions
        );
        $this->createIndex('user_id','{{%account_log}}',['user_id'],false);

    }

    public function safeDown()
    {
        $this->dropIndex('user_id', '{{%account_log}}');
        $this->dropTable('{{%account_log}}');
    }
}
