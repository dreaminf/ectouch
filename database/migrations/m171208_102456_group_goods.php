<?php

use yii\db\Schema;
use yii\db\Migration;

class m171208_102456_group_goods extends Migration
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
            '{{%group_goods}}',
            [
                'parent_id'=> $this->integer(8)->unsigned()->notNull(),
                'goods_id'=> $this->integer(8)->unsigned()->notNull(),
                'goods_price'=> $this->decimal(10, 2)->unsigned()->notNull()->defaultValue('0.00'),
                'admin_id'=> $this->smallInteger(3)->unsigned()->notNull(),
            ],$tableOptions
        );
        $this->addPrimaryKey('pk_on_ecs_group_goods','{{%group_goods}}',['parent_id','goods_id','admin_id']);

    }

    public function safeDown()
    {
    $this->dropPrimaryKey('pk_on_ecs_group_goods','{{%group_goods}}');
        $this->dropTable('{{%group_goods}}');
    }
}
