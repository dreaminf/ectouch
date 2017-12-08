<?php

use yii\db\Schema;
use yii\db\Migration;

class m171208_102533_virtual_card extends Migration
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
            '{{%virtual_card}}',
            [
                'card_id'=> $this->primaryKey(8),
                'goods_id'=> $this->integer(8)->unsigned()->notNull()->defaultValue('0'),
                'card_sn'=> $this->string(60)->notNull()->defaultValue(''),
                'card_password'=> $this->string(60)->notNull()->defaultValue(''),
                'add_date'=> $this->integer(11)->notNull()->defaultValue(0),
                'end_date'=> $this->integer(11)->notNull()->defaultValue(0),
                'is_saled'=> $this->smallInteger(1)->notNull()->defaultValue(0),
                'order_sn'=> $this->string(20)->notNull()->defaultValue(''),
                'crc32'=> $this->string(12)->notNull()->defaultValue('0'),
            ],$tableOptions
        );
        $this->createIndex('goods_id','{{%virtual_card}}',['goods_id'],false);
        $this->createIndex('car_sn','{{%virtual_card}}',['card_sn'],false);
        $this->createIndex('is_saled','{{%virtual_card}}',['is_saled'],false);

    }

    public function safeDown()
    {
        $this->dropIndex('goods_id', '{{%virtual_card}}');
        $this->dropIndex('car_sn', '{{%virtual_card}}');
        $this->dropIndex('is_saled', '{{%virtual_card}}');
        $this->dropTable('{{%virtual_card}}');
    }
}
