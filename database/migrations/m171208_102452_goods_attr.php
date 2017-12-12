<?php

use yii\db\Schema;
use yii\db\Migration;

class m171208_102452_goods_attr extends Migration
{

    public function safeUp()
    {
        $tableOptions = 'ENGINE=InnoDB';

        $this->createTable(
            '{{%goods_attr}}',
            [
                'goods_attr_id'=> $this->primaryKey(10)->unsigned(),
                'goods_id'=> $this->integer(10)->unsigned()->notNull()->defaultValue('0'),
                'attr_id'=> $this->integer(10)->unsigned()->notNull()->defaultValue(0),
                'attr_value'=> $this->text()->notNull(),
                'attr_price'=> $this->string(255)->notNull()->defaultValue(''),
            ],$tableOptions
        );
        $this->createIndex('goods_id','{{%goods_attr}}',['goods_id'],false);
        $this->createIndex('attr_id','{{%goods_attr}}',['attr_id'],false);

    }

    public function safeDown()
    {
        $this->dropIndex('goods_id', '{{%goods_attr}}');
        $this->dropIndex('attr_id', '{{%goods_attr}}');
        $this->dropTable('{{%goods_attr}}');
    }
}
