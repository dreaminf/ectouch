<?php

use yii\db\Schema;
use yii\db\Migration;

class m171208_102447_feedback extends Migration
{

    public function safeUp()
    {
        $tableOptions = 'ENGINE=InnoDB';

        $this->createTable(
            '{{%feedback}}',
            [
                'msg_id'=> $this->primaryKey(10)->unsigned(),
                'parent_id'=> $this->integer(10)->unsigned()->notNull()->defaultValue('0'),
                'user_id'=> $this->integer(10)->unsigned()->notNull()->defaultValue('0'),
                'user_name'=> $this->string(60)->notNull()->defaultValue(''),
                'user_email'=> $this->string(60)->notNull()->defaultValue(''),
                'msg_title'=> $this->string(200)->notNull()->defaultValue(''),
                'msg_type'=> $this->smallInteger(1)->unsigned()->notNull()->defaultValue(0),
                'msg_status'=> $this->smallInteger(1)->unsigned()->notNull()->defaultValue(0),
                'msg_content'=> $this->text()->notNull(),
                'msg_time'=> $this->integer(10)->unsigned()->notNull()->defaultValue('0'),
                'message_img'=> $this->string(255)->notNull()->defaultValue('0'),
                'order_id'=> $this->integer(10)->unsigned()->notNull()->defaultValue('0'),
                'msg_area'=> $this->smallInteger(1)->unsigned()->notNull()->defaultValue(0),
            ],$tableOptions
        );
        $this->createIndex('user_id','{{%feedback}}',['user_id'],false);

    }

    public function safeDown()
    {
        $this->dropIndex('user_id', '{{%feedback}}');
        $this->dropTable('{{%feedback}}');
    }
}
