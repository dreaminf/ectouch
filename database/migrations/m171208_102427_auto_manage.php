<?php

use yii\db\Schema;
use yii\db\Migration;

class m171208_102427_auto_manage extends Migration
{

    public function safeUp()
    {
        $tableOptions = 'ENGINE=InnoDB';

        $this->createTable(
            '{{%auto_manage}}',
            [
                'item_id'=> $this->integer(10)->notNull(),
                'type'=> $this->string(10)->notNull(),
                'starttime'=> $this->integer(10)->notNull(),
                'endtime'=> $this->integer(10)->notNull(),
            ],$tableOptions
        );
        $this->addPrimaryKey('pk_on_ecs_auto_manage','{{%auto_manage}}',['item_id','type']);

    }

    public function safeDown()
    {
    $this->dropPrimaryKey('pk_on_ecs_auto_manage','{{%auto_manage}}');
        $this->dropTable('{{%auto_manage}}');
    }
}
