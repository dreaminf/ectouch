<?php

use yii\db\Schema;
use yii\db\Migration;

class m171208_102458_link_goods extends Migration
{

    public function safeUp()
    {
        $tableOptions = 'ENGINE=InnoDB';

        $this->createTable(
            '{{%link_goods}}',
            [
                'goods_id'=> $this->integer(10)->unsigned()->notNull(),
                'link_goods_id'=> $this->integer(10)->unsigned()->notNull(),
                'is_double'=> $this->smallInteger(1)->unsigned()->notNull()->defaultValue(0),
                'admin_id'=> $this->smallInteger(3)->unsigned()->notNull(),
            ],$tableOptions
        );
        $this->addPrimaryKey('pk_on_ecs_link_goods','{{%link_goods}}',['goods_id','link_goods_id','admin_id']);

    }

    public function safeDown()
    {
    $this->dropPrimaryKey('pk_on_ecs_link_goods','{{%link_goods}}');
        $this->dropTable('{{%link_goods}}');
    }
}
