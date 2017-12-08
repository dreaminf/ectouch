<?php

use yii\db\Schema;
use yii\db\Migration;

class m171208_102453_goods_cat extends Migration
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
            '{{%goods_cat}}',
            [
                'goods_id'=> $this->integer(8)->unsigned()->notNull(),
                'cat_id'=> $this->smallInteger(5)->unsigned()->notNull(),
            ],$tableOptions
        );
        $this->addPrimaryKey('pk_on_ecs_goods_cat','{{%goods_cat}}',['goods_id','cat_id']);

    }

    public function safeDown()
    {
    $this->dropPrimaryKey('pk_on_ecs_goods_cat','{{%goods_cat}}');
        $this->dropTable('{{%goods_cat}}');
    }
}
