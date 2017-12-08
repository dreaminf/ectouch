<?php

use yii\db\Schema;
use yii\db\Migration;

class m171208_102441_delivery_order extends Migration
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
            '{{%delivery_order}}',
            [
                'delivery_id'=> $this->primaryKey(8)->unsigned(),
                'delivery_sn'=> $this->string(20)->notNull(),
                'order_sn'=> $this->string(20)->notNull(),
                'order_id'=> $this->integer(8)->unsigned()->notNull()->defaultValue('0'),
                'invoice_no'=> $this->string(50)->null()->defaultValue(null),
                'add_time'=> $this->integer(10)->unsigned()->null()->defaultValue('0'),
                'shipping_id'=> $this->smallInteger(3)->unsigned()->null()->defaultValue(0),
                'shipping_name'=> $this->string(120)->null()->defaultValue(null),
                'user_id'=> $this->integer(8)->unsigned()->null()->defaultValue('0'),
                'action_user'=> $this->string(30)->null()->defaultValue(null),
                'consignee'=> $this->string(60)->null()->defaultValue(null),
                'address'=> $this->string(250)->null()->defaultValue(null),
                'country'=> $this->smallInteger(5)->unsigned()->null()->defaultValue(0),
                'province'=> $this->smallInteger(5)->unsigned()->null()->defaultValue(0),
                'city'=> $this->smallInteger(5)->unsigned()->null()->defaultValue(0),
                'district'=> $this->smallInteger(5)->unsigned()->null()->defaultValue(0),
                'sign_building'=> $this->string(120)->null()->defaultValue(null),
                'email'=> $this->string(60)->null()->defaultValue(null),
                'zipcode'=> $this->string(60)->null()->defaultValue(null),
                'tel'=> $this->string(60)->null()->defaultValue(null),
                'mobile'=> $this->string(60)->null()->defaultValue(null),
                'best_time'=> $this->string(120)->null()->defaultValue(null),
                'postscript'=> $this->string(255)->null()->defaultValue(null),
                'how_oos'=> $this->string(120)->null()->defaultValue(null),
                'insure_fee'=> $this->decimal(10, 2)->unsigned()->null()->defaultValue('0.00'),
                'shipping_fee'=> $this->decimal(10, 2)->unsigned()->null()->defaultValue('0.00'),
                'update_time'=> $this->integer(10)->unsigned()->null()->defaultValue('0'),
                'suppliers_id'=> $this->smallInteger(5)->null()->defaultValue(0),
                'status'=> $this->smallInteger(1)->unsigned()->notNull()->defaultValue(0),
                'agency_id'=> $this->smallInteger(5)->unsigned()->null()->defaultValue(0),
            ],$tableOptions
        );
        $this->createIndex('user_id','{{%delivery_order}}',['user_id'],false);
        $this->createIndex('order_id','{{%delivery_order}}',['order_id'],false);

    }

    public function safeDown()
    {
        $this->dropIndex('user_id', '{{%delivery_order}}');
        $this->dropIndex('order_id', '{{%delivery_order}}');
        $this->dropTable('{{%delivery_order}}');
    }
}
