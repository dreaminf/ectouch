<?php

use yii\db\Schema;
use yii\db\Migration;

class m171208_102505_pack extends Migration
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
            '{{%pack}}',
            [
                'pack_id'=> $this->primaryKey(3)->unsigned(),
                'pack_name'=> $this->string(120)->notNull()->defaultValue(''),
                'pack_img'=> $this->string(255)->notNull()->defaultValue(''),
                'pack_fee'=> $this->decimal(6, 2)->unsigned()->notNull()->defaultValue('0.00'),
                'free_money'=> $this->smallInteger(5)->unsigned()->notNull()->defaultValue(0),
                'pack_desc'=> $this->string(255)->notNull()->defaultValue(''),
            ],$tableOptions
        );

    }

    public function safeDown()
    {
        $this->dropTable('{{%pack}}');
    }
}
