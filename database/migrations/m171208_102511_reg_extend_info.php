<?php

use yii\db\Schema;
use yii\db\Migration;

class m171208_102511_reg_extend_info extends Migration
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
            '{{%reg_extend_info}}',
            [
                'Id'=> $this->primaryKey(10)->unsigned(),
                'user_id'=> $this->integer(8)->unsigned()->notNull(),
                'reg_field_id'=> $this->integer(10)->unsigned()->notNull(),
                'content'=> $this->text()->notNull(),
            ],$tableOptions
        );

    }

    public function safeDown()
    {
        $this->dropTable('{{%reg_extend_info}}');
    }
}
