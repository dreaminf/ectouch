<?php

use yii\db\Schema;
use yii\db\Migration;

class m171208_102508_payment extends Migration
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
            '{{%payment}}',
            [
                'pay_id'=> $this->primaryKey(3)->unsigned(),
                'pay_code'=> $this->string(20)->notNull()->defaultValue(''),
                'pay_name'=> $this->string(120)->notNull()->defaultValue(''),
                'pay_fee'=> $this->string(10)->notNull()->defaultValue('0'),
                'pay_desc'=> $this->text()->notNull(),
                'pay_order'=> $this->smallInteger(3)->unsigned()->notNull()->defaultValue(0),
                'pay_config'=> $this->text()->notNull(),
                'enabled'=> $this->smallInteger(1)->unsigned()->notNull()->defaultValue(0),
                'is_cod'=> $this->smallInteger(1)->unsigned()->notNull()->defaultValue(0),
                'is_online'=> $this->smallInteger(1)->unsigned()->notNull()->defaultValue(0),
            ],$tableOptions
        );
        $this->createIndex('pay_code','{{%payment}}',['pay_code'],true);

    }

    public function safeDown()
    {
        $this->dropIndex('pay_code', '{{%payment}}');
        $this->dropTable('{{%payment}}');
    }
}
