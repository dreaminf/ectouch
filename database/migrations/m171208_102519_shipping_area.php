<?php

use yii\db\Schema;
use yii\db\Migration;

class m171208_102519_shipping_area extends Migration
{

    public function safeUp()
    {
        $tableOptions = 'ENGINE=InnoDB';

        $this->createTable(
            '{{%shipping_area}}',
            [
                'shipping_area_id'=> $this->primaryKey(10)->unsigned(),
                'shipping_area_name'=> $this->string(150)->notNull()->defaultValue(''),
                'shipping_id'=> $this->smallInteger(3)->unsigned()->notNull()->defaultValue(0),
                'configure'=> $this->text()->notNull(),
            ],$tableOptions
        );
        $this->createIndex('shipping_id','{{%shipping_area}}',['shipping_id'],false);

    }

    public function safeDown()
    {
        $this->dropIndex('shipping_id', '{{%shipping_area}}');
        $this->dropTable('{{%shipping_area}}');
    }
}
