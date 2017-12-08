<?php

use yii\db\Schema;
use yii\db\Migration;

class m171208_102415_admin_action extends Migration
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
            '{{%admin_action}}',
            [
                'action_id'=> $this->primaryKey(3)->unsigned(),
                'parent_id'=> $this->smallInteger(3)->unsigned()->notNull()->defaultValue(0),
                'action_code'=> $this->string(20)->notNull()->defaultValue(''),
                'relevance'=> $this->string(20)->notNull()->defaultValue(''),
            ],$tableOptions
        );
        $this->createIndex('parent_id','{{%admin_action}}',['parent_id'],false);

    }

    public function safeDown()
    {
        $this->dropIndex('parent_id', '{{%admin_action}}');
        $this->dropTable('{{%admin_action}}');
    }
}
