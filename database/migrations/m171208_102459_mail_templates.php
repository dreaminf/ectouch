<?php

use yii\db\Schema;
use yii\db\Migration;

class m171208_102459_mail_templates extends Migration
{

    public function safeUp()
    {
        $tableOptions = 'ENGINE=InnoDB';

        $this->createTable(
            '{{%mail_templates}}',
            [
                'template_id'=> $this->primaryKey(10)->unsigned(),
                'template_code'=> $this->string(30)->notNull()->defaultValue(''),
                'is_html'=> $this->smallInteger(1)->unsigned()->notNull()->defaultValue(0),
                'template_subject'=> $this->string(200)->notNull()->defaultValue(''),
                'template_content'=> $this->text()->notNull(),
                'last_modify'=> $this->integer(10)->unsigned()->notNull()->defaultValue('0'),
                'last_send'=> $this->integer(10)->unsigned()->notNull()->defaultValue('0'),
                'type'=> $this->string(10)->notNull(),
            ],$tableOptions
        );
        $this->createIndex('template_code','{{%mail_templates}}',['template_code'],true);
        $this->createIndex('type','{{%mail_templates}}',['type'],false);

    }

    public function safeDown()
    {
        $this->dropIndex('template_code', '{{%mail_templates}}');
        $this->dropIndex('type', '{{%mail_templates}}');
        $this->dropTable('{{%mail_templates}}');
    }
}
