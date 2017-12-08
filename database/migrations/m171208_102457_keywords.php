<?php

use yii\db\Schema;
use yii\db\Migration;

class m171208_102457_keywords extends Migration
{

    public function init()
    {
        $this->db = 'db';
        parent::init();
    }

    public function safeUp()
    {
        $tableOptions = 'ENGINE=InnoDB';

        $this->createTable(
            '{{%keywords}}',
            [
                'date'=> $this->date()->notNull(),
                'searchengine'=> $this->string(20)->notNull(),
                'keyword'=> $this->string(90)->notNull(),
                'count'=> $this->integer(8)->unsigned()->notNull()->defaultValue('0'),
            ],$tableOptions
        );
        $this->addPrimaryKey('pk_on_ecs_keywords','{{%keywords}}',['date','searchengine','keyword']);

    }

    public function safeDown()
    {
    $this->dropPrimaryKey('pk_on_ecs_keywords','{{%keywords}}');
        $this->dropTable('{{%keywords}}');
    }
}
