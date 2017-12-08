<?php

use yii\db\Schema;
use yii\db\Migration;

class m171208_102501_nav extends Migration
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
            '{{%nav}}',
            [
                'id'=> $this->primaryKey(8),
                'ctype'=> $this->string(10)->null()->defaultValue(null),
                'cid'=> $this->smallInteger(5)->unsigned()->null()->defaultValue(null),
                'name'=> $this->string(255)->notNull(),
                'ifshow'=> $this->smallInteger(1)->notNull(),
                'vieworder'=> $this->smallInteger(1)->notNull(),
                'opennew'=> $this->smallInteger(1)->notNull(),
                'url'=> $this->string(255)->notNull(),
                'type'=> $this->string(10)->notNull(),
            ],$tableOptions
        );
        $this->createIndex('type','{{%nav}}',['type'],false);
        $this->createIndex('ifshow','{{%nav}}',['ifshow'],false);

    }

    public function safeDown()
    {
        $this->dropIndex('type', '{{%nav}}');
        $this->dropIndex('ifshow', '{{%nav}}');
        $this->dropTable('{{%nav}}');
    }
}
