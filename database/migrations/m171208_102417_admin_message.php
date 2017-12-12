<?php

use yii\db\Schema;
use yii\db\Migration;

class m171208_102417_admin_message extends Migration
{

    public function safeUp()
    {
        $tableOptions = 'ENGINE=InnoDB';

        $this->createTable(
            '{{%admin_message}}',
            [
                'message_id'=> $this->primaryKey(10)->unsigned(),
                'sender_id'=> $this->smallInteger(3)->unsigned()->notNull()->defaultValue(0),
                'receiver_id'=> $this->smallInteger(3)->unsigned()->notNull()->defaultValue(0),
                'sent_time'=> $this->integer(10)->unsigned()->notNull()->defaultValue('0'),
                'read_time'=> $this->integer(10)->unsigned()->notNull()->defaultValue('0'),
                'readed'=> $this->smallInteger(1)->unsigned()->notNull()->defaultValue(0),
                'deleted'=> $this->smallInteger(1)->unsigned()->notNull()->defaultValue(0),
                'title'=> $this->string(150)->notNull()->defaultValue(''),
                'message'=> $this->text()->notNull(),
            ],$tableOptions
        );
        $this->createIndex('sender_id','{{%admin_message}}',['sender_id','receiver_id'],false);
        $this->createIndex('receiver_id','{{%admin_message}}',['receiver_id'],false);

    }

    public function safeDown()
    {
        $this->dropIndex('sender_id', '{{%admin_message}}');
        $this->dropIndex('receiver_id', '{{%admin_message}}');
        $this->dropTable('{{%admin_message}}');
    }
}
