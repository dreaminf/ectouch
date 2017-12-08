<?php

use yii\db\Schema;
use yii\db\Migration;

class m171208_102433_card extends Migration
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
            '{{%card}}',
            [
                'card_id'=> $this->primaryKey(3)->unsigned(),
                'card_name'=> $this->string(120)->notNull()->defaultValue(''),
                'card_img'=> $this->string(255)->notNull()->defaultValue(''),
                'card_fee'=> $this->decimal(6, 2)->unsigned()->notNull()->defaultValue('0.00'),
                'free_money'=> $this->decimal(6, 2)->unsigned()->notNull()->defaultValue('0.00'),
                'card_desc'=> $this->string(255)->notNull()->defaultValue(''),
            ],$tableOptions
        );

    }

    public function safeDown()
    {
        $this->dropTable('{{%card}}');
    }
}
