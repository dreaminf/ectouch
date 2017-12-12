<?php

use yii\db\Schema;
use yii\db\Migration;

class m171208_102438_comment extends Migration
{

    public function safeUp()
    {
        $tableOptions = 'ENGINE=InnoDB';

        $this->createTable(
            '{{%comment}}',
            [
                'comment_id'=> $this->primaryKey(10)->unsigned(),
                'comment_type'=> $this->smallInteger(3)->unsigned()->notNull()->defaultValue(0),
                'id_value'=> $this->integer(10)->unsigned()->notNull()->defaultValue('0'),
                'email'=> $this->string(60)->notNull()->defaultValue(''),
                'user_name'=> $this->string(60)->notNull()->defaultValue(''),
                'content'=> $this->text()->notNull(),
                'comment_rank'=> $this->smallInteger(1)->unsigned()->notNull()->defaultValue(0),
                'add_time'=> $this->integer(10)->unsigned()->notNull()->defaultValue('0'),
                'ip_address'=> $this->string(15)->notNull()->defaultValue(''),
                'status'=> $this->smallInteger(3)->unsigned()->notNull()->defaultValue(0),
                'parent_id'=> $this->integer(10)->unsigned()->notNull()->defaultValue('0'),
                'user_id'=> $this->integer(10)->unsigned()->notNull()->defaultValue('0'),
            ],$tableOptions
        );
        $this->createIndex('parent_id','{{%comment}}',['parent_id'],false);
        $this->createIndex('id_value','{{%comment}}',['id_value'],false);

    }

    public function safeDown()
    {
        $this->dropIndex('parent_id', '{{%comment}}');
        $this->dropIndex('id_value', '{{%comment}}');
        $this->dropTable('{{%comment}}');
    }
}
