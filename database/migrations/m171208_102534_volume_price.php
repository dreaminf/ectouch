<?php

use yii\db\Schema;
use yii\db\Migration;

class m171208_102534_volume_price extends Migration
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
            '{{%volume_price}}',
            [
                'price_type'=> $this->smallInteger(1)->unsigned()->notNull(),
                'goods_id'=> $this->integer(8)->unsigned()->notNull(),
                'volume_number'=> $this->smallInteger(5)->unsigned()->notNull(),
                'volume_price'=> $this->decimal(10, 2)->notNull()->defaultValue('0.00'),
            ],$tableOptions
        );
        $this->addPrimaryKey('pk_on_ecs_volume_price','{{%volume_price}}',['price_type','goods_id','volume_number']);

    }

    public function safeDown()
    {
    $this->dropPrimaryKey('pk_on_ecs_volume_price','{{%volume_price}}');
        $this->dropTable('{{%volume_price}}');
    }
}
