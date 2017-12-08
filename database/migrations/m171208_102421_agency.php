<?php

use yii\db\Schema;
use yii\db\Migration;

class m171208_102421_agency extends Migration
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
            '{{%agency}}',
            [
                'agency_id'=> $this->primaryKey(5)->unsigned(),
                'agency_name'=> $this->string(255)->notNull(),
                'agency_desc'=> $this->text()->notNull(),
            ],$tableOptions
        );
        $this->createIndex('agency_name','{{%agency}}',['agency_name'],false);

    }

    public function safeDown()
    {
        $this->dropIndex('agency_name', '{{%agency}}');
        $this->dropTable('{{%agency}}');
    }
}
