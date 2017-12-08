<?php

use yii\db\Schema;
use yii\db\Migration;

class m171208_102451_goods_article extends Migration
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
            '{{%goods_article}}',
            [
                'goods_id'=> $this->integer(8)->unsigned()->notNull(),
                'article_id'=> $this->integer(8)->unsigned()->notNull(),
                'admin_id'=> $this->smallInteger(3)->unsigned()->notNull(),
            ],$tableOptions
        );
        $this->addPrimaryKey('pk_on_ecs_goods_article','{{%goods_article}}',['goods_id','article_id','admin_id']);

    }

    public function safeDown()
    {
    $this->dropPrimaryKey('pk_on_ecs_goods_article','{{%goods_article}}');
        $this->dropTable('{{%goods_article}}');
    }
}
