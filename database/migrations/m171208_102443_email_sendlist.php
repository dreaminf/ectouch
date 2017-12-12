<?php

use yii\db\Schema;
use yii\db\Migration;

class m171208_102443_email_sendlist extends Migration
{

    public function safeUp()
    {
        $tableOptions = 'ENGINE=InnoDB';

        $this->createTable(
            '{{%email_sendlist}}',
            [
                'id'=> $this->primaryKey(10),
                'email'=> $this->string(100)->notNull(),
                'template_id'=> $this->integer(10)->notNull(),
                'email_content'=> $this->text()->notNull(),
                'error'=> $this->smallInteger(1)->notNull()->defaultValue(0),
                'pri'=> $this->smallInteger(10)->notNull(),
                'last_send'=> $this->integer(10)->notNull(),
            ],$tableOptions
        );

    }

    public function safeDown()
    {
        $this->dropTable('{{%email_sendlist}}');
    }
}
