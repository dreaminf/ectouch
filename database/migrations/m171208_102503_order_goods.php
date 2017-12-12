<?php

use yii\db\Schema;
use yii\db\Migration;

class m171208_102503_order_goods extends Migration
{

    public function safeUp()
    {
        $tableOptions = 'ENGINE=InnoDB';

        $this->createTable(
            '{{%order_goods}}',
            [
                'rec_id'=> $this->primaryKey(10)->unsigned(),
                'order_id'=> $this->integer(10)->unsigned()->notNull()->defaultValue('0'),
                'goods_id'=> $this->integer(10)->unsigned()->notNull()->defaultValue('0'),
                'goods_name'=> $this->string(120)->notNull()->defaultValue(''),
                'goods_sn'=> $this->string(60)->notNull()->defaultValue(''),
                'product_id'=> $this->integer(10)->unsigned()->notNull()->defaultValue('0'),
                'goods_number'=> $this->integer(10)->unsigned()->notNull()->defaultValue(1),
                'market_price'=> $this->decimal(10, 2)->notNull()->defaultValue('0.00'),
                'goods_price'=> $this->decimal(10, 2)->notNull()->defaultValue('0.00'),
                'goods_attr'=> $this->text()->notNull(),
                'send_number'=> $this->integer(10)->unsigned()->notNull()->defaultValue(0),
                'is_real'=> $this->smallInteger(1)->unsigned()->notNull()->defaultValue(0),
                'extension_code'=> $this->string(30)->notNull()->defaultValue(''),
                'parent_id'=> $this->integer(10)->unsigned()->notNull()->defaultValue('0'),
                'is_gift'=> $this->integer(10)->unsigned()->notNull()->defaultValue(0),
                'goods_attr_id'=> $this->string(255)->notNull()->defaultValue(''),
            ],$tableOptions
        );
        $this->createIndex('order_id','{{%order_goods}}',['order_id'],false);
        $this->createIndex('goods_id','{{%order_goods}}',['goods_id'],false);

    }

    public function safeDown()
    {
        $this->dropIndex('order_id', '{{%order_goods}}');
        $this->dropIndex('goods_id', '{{%order_goods}}');
        $this->dropTable('{{%order_goods}}');
    }
}
