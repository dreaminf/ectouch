<?php

use yii\db\Schema;
use yii\db\Migration;

class m171208_102515_searchengine extends Migration
{

    public function safeUp()
    {
        $tableOptions = 'ENGINE=InnoDB';

        $this->createTable(
            '{{%searchengine}}',
            [
                'date'=> $this->date()->notNull(),
                'searchengine'=> $this->string(20)->notNull(),
                'count'=> $this->integer(10)->unsigned()->notNull()->defaultValue('0'),
            ],$tableOptions
        );
        $this->addPrimaryKey('pk_on_ecs_searchengine','{{%searchengine}}',['date','searchengine']);

    }

    public function safeDown()
    {
    $this->dropPrimaryKey('pk_on_ecs_searchengine','{{%searchengine}}');
        $this->dropTable('{{%searchengine}}');
    }
}
