<?php

use yii\db\Schema;
use yii\db\Migration;

class m171208_102518_shipping extends Migration
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
            '{{%shipping}}',
            [
                'shipping_id'=> $this->primaryKey(3)->unsigned(),
                'shipping_code'=> $this->string(20)->notNull()->defaultValue(''),
                'shipping_name'=> $this->string(120)->notNull()->defaultValue(''),
                'shipping_desc'=> $this->string(255)->notNull()->defaultValue(''),
                'insure'=> $this->string(10)->notNull()->defaultValue('0'),
                'support_cod'=> $this->smallInteger(1)->unsigned()->notNull()->defaultValue(0),
                'enabled'=> $this->smallInteger(1)->unsigned()->notNull()->defaultValue(0),
                'shipping_print'=> $this->text()->notNull(),
                'print_bg'=> $this->string(255)->null()->defaultValue(null),
                'config_lable'=> $this->text()->null()->defaultValue(null),
                'print_model'=> $this->smallInteger(1)->null()->defaultValue(0),
                'shipping_order'=> $this->smallInteger(3)->unsigned()->notNull()->defaultValue(0),
            ],$tableOptions
        );
        $this->createIndex('shipping_code','{{%shipping}}',['shipping_code','enabled'],false);

    }

    public function safeDown()
    {
        $this->dropIndex('shipping_code', '{{%shipping}}');
        $this->dropTable('{{%shipping}}');
    }
}
