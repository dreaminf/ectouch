<?php

use yii\db\Schema;
use yii\db\Migration;

class m171208_102535_vote extends Migration
{

    public function safeUp()
    {
        $tableOptions = 'ENGINE=InnoDB';

        $this->createTable(
            '{{%vote}}',
            [
                'vote_id'=> $this->primaryKey(10)->unsigned(),
                'vote_name'=> $this->string(250)->notNull()->defaultValue(''),
                'start_time'=> $this->integer(10)->unsigned()->notNull()->defaultValue('0'),
                'end_time'=> $this->integer(10)->unsigned()->notNull()->defaultValue('0'),
                'can_multi'=> $this->smallInteger(1)->unsigned()->notNull()->defaultValue(0),
                'vote_count'=> $this->integer(10)->unsigned()->notNull()->defaultValue('0'),
            ],$tableOptions
        );

    }

    public function safeDown()
    {
        $this->dropTable('{{%vote}}');
    }
}
