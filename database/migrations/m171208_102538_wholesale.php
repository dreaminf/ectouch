<?php

use yii\db\Schema;
use yii\db\Migration;

class m171208_102538_wholesale extends Migration
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
            '{{%wholesale}}',
            [
                'act_id'=> $this->primaryKey(8)->unsigned(),
                'goods_id'=> $this->integer(8)->unsigned()->notNull(),
                'goods_name'=> $this->string(255)->notNull(),
                'rank_ids'=> $this->string(255)->notNull(),
                'prices'=> $this->text()->notNull(),
                'enabled'=> $this->smallInteger(3)->unsigned()->notNull(),
            ],$tableOptions
        );
        $this->createIndex('goods_id','{{%wholesale}}',['goods_id'],false);

    }

    public function safeDown()
    {
        $this->dropIndex('goods_id', '{{%wholesale}}');
        $this->dropTable('{{%wholesale}}');
    }
}
