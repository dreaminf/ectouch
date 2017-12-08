<?php

use yii\db\Schema;
use yii\db\Migration;

class m171208_102527_user_account extends Migration
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
            '{{%user_account}}',
            [
                'id'=> $this->primaryKey(8)->unsigned(),
                'user_id'=> $this->integer(8)->unsigned()->notNull()->defaultValue('0'),
                'admin_user'=> $this->string(255)->notNull(),
                'amount'=> $this->decimal(10, 2)->notNull(),
                'add_time'=> $this->integer(10)->notNull()->defaultValue(0),
                'paid_time'=> $this->integer(10)->notNull()->defaultValue(0),
                'admin_note'=> $this->string(255)->notNull(),
                'user_note'=> $this->string(255)->notNull(),
                'process_type'=> $this->smallInteger(1)->notNull()->defaultValue(0),
                'payment'=> $this->string(90)->notNull(),
                'is_paid'=> $this->smallInteger(1)->notNull()->defaultValue(0),
            ],$tableOptions
        );
        $this->createIndex('user_id','{{%user_account}}',['user_id'],false);
        $this->createIndex('is_paid','{{%user_account}}',['is_paid'],false);

    }

    public function safeDown()
    {
        $this->dropIndex('user_id', '{{%user_account}}');
        $this->dropIndex('is_paid', '{{%user_account}}');
        $this->dropTable('{{%user_account}}');
    }
}
