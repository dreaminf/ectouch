<?php

use yii\db\Schema;
use yii\db\Migration;

class m171208_102502_order_action extends Migration
{

    public function safeUp()
    {
        $tableOptions = 'ENGINE=InnoDB';

        $this->createTable(
            '{{%order_action}}',
            [
                'action_id'=> $this->primaryKey(10)->unsigned(),
                'order_id'=> $this->integer(10)->unsigned()->notNull()->defaultValue('0'),
                'action_user'=> $this->string(30)->notNull()->defaultValue(''),
                'order_status'=> $this->smallInteger(1)->unsigned()->notNull()->defaultValue(0),
                'shipping_status'=> $this->smallInteger(1)->unsigned()->notNull()->defaultValue(0),
                'pay_status'=> $this->smallInteger(1)->unsigned()->notNull()->defaultValue(0),
                'action_place'=> $this->smallInteger(1)->unsigned()->notNull()->defaultValue(0),
                'action_note'=> $this->string(255)->notNull()->defaultValue(''),
                'log_time'=> $this->integer(10)->unsigned()->notNull()->defaultValue('0'),
            ],$tableOptions
        );
        $this->createIndex('order_id','{{%order_action}}',['order_id'],false);

    }

    public function safeDown()
    {
        $this->dropIndex('order_id', '{{%order_action}}');
        $this->dropTable('{{%order_action}}');
    }
}
