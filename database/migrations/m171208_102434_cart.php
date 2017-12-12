<?php

use yii\db\Schema;
use yii\db\Migration;

class m171208_102434_cart extends Migration
{

    public function safeUp()
    {
        $tableOptions = 'ENGINE=InnoDB';

        $this->createTable(
            '{{%cart}}',
            [
                'rec_id'=> $this->primaryKey(10)->unsigned(),
                'user_id'=> $this->integer(10)->unsigned()->notNull()->defaultValue('0'),
                'session_id'=> $this->char(32)->notNull()->defaultValue(''),
                'goods_id'=> $this->integer(10)->unsigned()->notNull()->defaultValue('0'),
                'goods_sn'=> $this->string(60)->notNull()->defaultValue(''),
                'product_id'=> $this->integer(10)->unsigned()->notNull()->defaultValue('0'),
                'goods_name'=> $this->string(120)->notNull()->defaultValue(''),
                'market_price'=> $this->decimal(10, 2)->unsigned()->notNull()->defaultValue('0.00'),
                'goods_price'=> $this->decimal(10, 2)->notNull()->defaultValue('0.00'),
                'goods_number'=> $this->integer(10)->unsigned()->notNull()->defaultValue(0),
                'goods_attr'=> $this->text()->notNull(),
                'is_real'=> $this->smallInteger(1)->unsigned()->notNull()->defaultValue(0),
                'extension_code'=> $this->string(30)->notNull()->defaultValue(''),
                'parent_id'=> $this->integer(10)->unsigned()->notNull()->defaultValue('0'),
                'rec_type'=> $this->smallInteger(1)->unsigned()->notNull()->defaultValue(0),
                'is_gift'=> $this->integer(10)->unsigned()->notNull()->defaultValue(0),
                'is_shipping'=> $this->smallInteger(1)->unsigned()->notNull()->defaultValue(0),
                'can_handsel'=> $this->smallInteger(3)->unsigned()->notNull()->defaultValue(0),
                'goods_attr_id'=> $this->string(255)->notNull()->defaultValue(''),
            ],$tableOptions
        );
        $this->createIndex('session_id','{{%cart}}',['session_id'],false);

    }

    public function safeDown()
    {
        $this->dropIndex('session_id', '{{%cart}}');
        $this->dropTable('{{%cart}}');
    }
}
