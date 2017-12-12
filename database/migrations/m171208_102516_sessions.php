<?php

use yii\db\Schema;
use yii\db\Migration;

class m171208_102516_sessions extends Migration
{

    public function safeUp()
    {
        $tableOptions = 'ENGINE=InnoDB';

        $this->createTable(
            '{{%sessions}}',
            [
                'sesskey'=> $this->char(32)->notNull(),
                'expiry'=> $this->integer(10)->unsigned()->notNull()->defaultValue('0'),
                'userid'=> $this->integer(10)->unsigned()->notNull()->defaultValue('0'),
                'adminid'=> $this->integer(10)->unsigned()->notNull()->defaultValue('0'),
                'ip'=> $this->char(15)->notNull()->defaultValue(''),
                'user_name'=> $this->string(60)->notNull(),
                'user_rank'=> $this->smallInteger(3)->notNull(),
                'discount'=> $this->decimal(3, 2)->notNull(),
                'email'=> $this->string(60)->notNull(),
                'data'=> $this->char(255)->notNull()->defaultValue(''),
            ],$tableOptions
        );
        $this->createIndex('expiry','{{%sessions}}',['expiry'],false);
        $this->addPrimaryKey('pk_on_ecs_sessions','{{%sessions}}',['sesskey']);

    }

    public function safeDown()
    {
    $this->dropPrimaryKey('pk_on_ecs_sessions','{{%sessions}}');
        $this->dropIndex('expiry', '{{%sessions}}');
        $this->dropTable('{{%sessions}}');
    }
}
