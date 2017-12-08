<?php

use yii\db\Schema;
use yii\db\Migration;

class m171208_102455_goods_type extends Migration
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
            '{{%goods_type}}',
            [
                'cat_id'=> $this->primaryKey(5)->unsigned(),
                'cat_name'=> $this->string(60)->notNull()->defaultValue(''),
                'enabled'=> $this->smallInteger(1)->unsigned()->notNull()->defaultValue(1),
                'attr_group'=> $this->string(255)->notNull(),
            ],$tableOptions
        );

    }

    public function safeDown()
    {
        $this->dropTable('{{%goods_type}}');
    }
}
