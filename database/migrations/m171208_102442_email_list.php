<?php

use yii\db\Schema;
use yii\db\Migration;

class m171208_102442_email_list extends Migration
{

    public function safeUp()
    {
        $tableOptions = 'ENGINE=InnoDB';

        $this->createTable(
            '{{%email_list}}',
            [
                'id'=> $this->primaryKey(10),
                'email'=> $this->string(60)->notNull(),
                'stat'=> $this->smallInteger(1)->notNull()->defaultValue(0),
                'hash'=> $this->string(10)->notNull(),
            ],$tableOptions
        );

    }

    public function safeDown()
    {
        $this->dropTable('{{%email_list}}');
    }
}
