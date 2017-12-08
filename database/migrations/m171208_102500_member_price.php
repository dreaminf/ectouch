<?php

use yii\db\Schema;
use yii\db\Migration;

class m171208_102500_member_price extends Migration
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
            '{{%member_price}}',
            [
                'price_id'=> $this->primaryKey(8)->unsigned(),
                'goods_id'=> $this->integer(8)->unsigned()->notNull()->defaultValue('0'),
                'user_rank'=> $this->smallInteger(3)->notNull()->defaultValue(0),
                'user_price'=> $this->decimal(10, 2)->notNull()->defaultValue('0.00'),
            ],$tableOptions
        );
        $this->createIndex('goods_id','{{%member_price}}',['goods_id','user_rank'],false);

    }

    public function safeDown()
    {
        $this->dropIndex('goods_id', '{{%member_price}}');
        $this->dropTable('{{%member_price}}');
    }
}
