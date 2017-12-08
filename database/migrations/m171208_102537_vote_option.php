<?php

use yii\db\Schema;
use yii\db\Migration;

class m171208_102537_vote_option extends Migration
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
            '{{%vote_option}}',
            [
                'option_id'=> $this->primaryKey(5)->unsigned(),
                'vote_id'=> $this->smallInteger(5)->unsigned()->notNull()->defaultValue(0),
                'option_name'=> $this->string(250)->notNull()->defaultValue(''),
                'option_count'=> $this->integer(8)->unsigned()->notNull()->defaultValue('0'),
                'option_order'=> $this->smallInteger(3)->unsigned()->notNull()->defaultValue(100),
            ],$tableOptions
        );
        $this->createIndex('vote_id','{{%vote_option}}',['vote_id'],false);

    }

    public function safeDown()
    {
        $this->dropIndex('vote_id', '{{%vote_option}}');
        $this->dropTable('{{%vote_option}}');
    }
}
