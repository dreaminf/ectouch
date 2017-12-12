<?php

use yii\db\Schema;
use yii\db\Migration;

class m171208_102517_sessions_data extends Migration
{

    public function safeUp()
    {
        $tableOptions = 'ENGINE=InnoDB';

        $this->createTable(
            '{{%sessions_data}}',
            [
                'sesskey'=> $this->string(32)->notNull(),
                'expiry'=> $this->integer(10)->unsigned()->notNull()->defaultValue('0'),
                'data'=> $this->text()->notNull(),
            ],$tableOptions
        );
        $this->createIndex('expiry','{{%sessions_data}}',['expiry'],false);
        $this->addPrimaryKey('pk_on_ecs_sessions_data','{{%sessions_data}}',['sesskey']);

    }

    public function safeDown()
    {
    $this->dropPrimaryKey('pk_on_ecs_sessions_data','{{%sessions_data}}');
        $this->dropIndex('expiry', '{{%sessions_data}}');
        $this->dropTable('{{%sessions_data}}');
    }
}
