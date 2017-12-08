<?php

use yii\db\Schema;
use yii\db\Migration;

class m171208_102536_vote_log extends Migration
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
            '{{%vote_log}}',
            [
                'log_id'=> $this->primaryKey(8)->unsigned(),
                'vote_id'=> $this->smallInteger(5)->unsigned()->notNull()->defaultValue(0),
                'ip_address'=> $this->string(15)->notNull()->defaultValue(''),
                'vote_time'=> $this->integer(10)->unsigned()->notNull()->defaultValue('0'),
            ],$tableOptions
        );
        $this->createIndex('vote_id','{{%vote_log}}',['vote_id'],false);

    }

    public function safeDown()
    {
        $this->dropIndex('vote_id', '{{%vote_log}}');
        $this->dropTable('{{%vote_log}}');
    }
}
