<?php

use yii\db\Schema;
use yii\db\Migration;

class m171208_102444_error_log extends Migration
{

    public function safeUp()
    {
        $tableOptions = 'ENGINE=InnoDB';

        $this->createTable(
            '{{%error_log}}',
            [
                'id'=> $this->primaryKey(10),
                'info'=> $this->string(255)->notNull(),
                'file'=> $this->string(100)->notNull(),
                'time'=> $this->integer(10)->notNull(),
            ],$tableOptions
        );
        $this->createIndex('time','{{%error_log}}',['time'],false);

    }

    public function safeDown()
    {
        $this->dropIndex('time', '{{%error_log}}');
        $this->dropTable('{{%error_log}}');
    }
}
