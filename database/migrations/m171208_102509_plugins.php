<?php

use yii\db\Schema;
use yii\db\Migration;

class m171208_102509_plugins extends Migration
{

    public function safeUp()
    {
        $tableOptions = 'ENGINE=InnoDB';

        $this->createTable(
            '{{%plugins}}',
            [
                'code'=> $this->string(30)->notNull(),
                'version'=> $this->string(10)->notNull()->defaultValue(''),
                'library'=> $this->string(255)->notNull()->defaultValue(''),
                'assign'=> $this->smallInteger(1)->unsigned()->notNull()->defaultValue(0),
                'install_date'=> $this->integer(10)->unsigned()->notNull()->defaultValue('0'),
            ],$tableOptions
        );
        $this->addPrimaryKey('pk_on_ecs_plugins','{{%plugins}}',['code']);

    }

    public function safeDown()
    {
    $this->dropPrimaryKey('pk_on_ecs_plugins','{{%plugins}}');
        $this->dropTable('{{%plugins}}');
    }
}
