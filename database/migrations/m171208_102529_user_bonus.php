<?php

use yii\db\Schema;
use yii\db\Migration;

class m171208_102529_user_bonus extends Migration
{

    public function safeUp()
    {
        $tableOptions = 'ENGINE=InnoDB';

        $this->createTable(
            '{{%user_bonus}}',
            [
                'bonus_id'=> $this->primaryKey(10)->unsigned(),
                'bonus_type_id'=> $this->smallInteger(3)->unsigned()->notNull()->defaultValue(0),
                'bonus_sn'=> $this->bigInteger(20)->unsigned()->notNull()->defaultValue('0'),
                'user_id'=> $this->integer(10)->unsigned()->notNull()->defaultValue('0'),
                'used_time'=> $this->integer(10)->unsigned()->notNull()->defaultValue('0'),
                'order_id'=> $this->integer(10)->unsigned()->notNull()->defaultValue('0'),
                'emailed'=> $this->smallInteger(3)->unsigned()->notNull()->defaultValue(0),
            ],$tableOptions
        );
        $this->createIndex('user_id','{{%user_bonus}}',['user_id'],false);

    }

    public function safeDown()
    {
        $this->dropIndex('user_id', '{{%user_bonus}}');
        $this->dropTable('{{%user_bonus}}');
    }
}
