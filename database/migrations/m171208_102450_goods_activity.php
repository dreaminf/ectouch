<?php

use yii\db\Schema;
use yii\db\Migration;

class m171208_102450_goods_activity extends Migration
{

    public function safeUp()
    {
        $tableOptions = 'ENGINE=InnoDB';

        $this->createTable(
            '{{%goods_activity}}',
            [
                'act_id'=> $this->primaryKey(10)->unsigned(),
                'act_name'=> $this->string(255)->notNull(),
                'act_desc'=> $this->text()->notNull(),
                'act_type'=> $this->smallInteger(3)->unsigned()->notNull(),
                'goods_id'=> $this->integer(10)->unsigned()->notNull(),
                'product_id'=> $this->integer(10)->unsigned()->notNull()->defaultValue('0'),
                'goods_name'=> $this->string(255)->notNull(),
                'start_time'=> $this->integer(10)->unsigned()->notNull(),
                'end_time'=> $this->integer(10)->unsigned()->notNull(),
                'is_finished'=> $this->smallInteger(3)->unsigned()->notNull(),
                'ext_info'=> $this->text()->notNull(),
            ],$tableOptions
        );
        $this->createIndex('act_name','{{%goods_activity}}',['act_name','act_type','goods_id'],false);

    }

    public function safeDown()
    {
        $this->dropIndex('act_name', '{{%goods_activity}}');
        $this->dropTable('{{%goods_activity}}');
    }
}
