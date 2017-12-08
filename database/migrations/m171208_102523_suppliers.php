<?php

use yii\db\Schema;
use yii\db\Migration;

class m171208_102523_suppliers extends Migration
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
            '{{%suppliers}}',
            [
                'suppliers_id'=> $this->primaryKey(5)->unsigned(),
                'suppliers_name'=> $this->string(255)->null()->defaultValue(null),
                'suppliers_desc'=> $this->text()->null()->defaultValue(null),
                'is_check'=> $this->smallInteger(1)->unsigned()->notNull()->defaultValue(1),
            ],$tableOptions
        );

    }

    public function safeDown()
    {
        $this->dropTable('{{%suppliers}}');
    }
}
