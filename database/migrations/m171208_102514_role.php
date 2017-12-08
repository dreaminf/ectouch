<?php

use yii\db\Schema;
use yii\db\Migration;

class m171208_102514_role extends Migration
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
            '{{%role}}',
            [
                'role_id'=> $this->primaryKey(5)->unsigned(),
                'role_name'=> $this->string(60)->notNull()->defaultValue(''),
                'action_list'=> $this->text()->notNull(),
                'role_describe'=> $this->text()->null()->defaultValue(null),
            ],$tableOptions
        );
        $this->createIndex('user_name','{{%role}}',['role_name'],false);

    }

    public function safeDown()
    {
        $this->dropIndex('user_name', '{{%role}}');
        $this->dropTable('{{%role}}');
    }
}
