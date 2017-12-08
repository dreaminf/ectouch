<?php

use yii\db\Schema;
use yii\db\Migration;

class m171208_102512_reg_fields extends Migration
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
            '{{%reg_fields}}',
            [
                'id'=> $this->primaryKey(3)->unsigned(),
                'reg_field_name'=> $this->string(60)->notNull(),
                'dis_order'=> $this->smallInteger(3)->unsigned()->notNull()->defaultValue(100),
                'display'=> $this->smallInteger(1)->unsigned()->notNull()->defaultValue(1),
                'type'=> $this->smallInteger(1)->unsigned()->notNull()->defaultValue(0),
                'is_need'=> $this->smallInteger(1)->unsigned()->notNull()->defaultValue(1),
            ],$tableOptions
        );

    }

    public function safeDown()
    {
        $this->dropTable('{{%reg_fields}}');
    }
}
