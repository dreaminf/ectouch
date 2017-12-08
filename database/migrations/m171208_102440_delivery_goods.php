<?php

use yii\db\Schema;
use yii\db\Migration;

class m171208_102440_delivery_goods extends Migration
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
            '{{%delivery_goods}}',
            [
                'rec_id'=> $this->primaryKey(8)->unsigned(),
                'delivery_id'=> $this->integer(8)->unsigned()->notNull()->defaultValue('0'),
                'goods_id'=> $this->integer(8)->unsigned()->notNull()->defaultValue('0'),
                'product_id'=> $this->integer(8)->unsigned()->null()->defaultValue('0'),
                'product_sn'=> $this->string(60)->null()->defaultValue(null),
                'goods_name'=> $this->string(120)->null()->defaultValue(null),
                'brand_name'=> $this->string(60)->null()->defaultValue(null),
                'goods_sn'=> $this->string(60)->null()->defaultValue(null),
                'is_real'=> $this->smallInteger(1)->unsigned()->null()->defaultValue(0),
                'extension_code'=> $this->string(30)->null()->defaultValue(null),
                'parent_id'=> $this->integer(8)->unsigned()->null()->defaultValue('0'),
                'send_number'=> $this->smallInteger(5)->unsigned()->null()->defaultValue(0),
                'goods_attr'=> $this->text()->null()->defaultValue(null),
            ],$tableOptions
        );
        $this->createIndex('delivery_id','{{%delivery_goods}}',['delivery_id','goods_id'],false);
        $this->createIndex('goods_id','{{%delivery_goods}}',['goods_id'],false);

    }

    public function safeDown()
    {
        $this->dropIndex('delivery_id', '{{%delivery_goods}}');
        $this->dropIndex('goods_id', '{{%delivery_goods}}');
        $this->dropTable('{{%delivery_goods}}');
    }
}
