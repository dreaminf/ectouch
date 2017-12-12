<?php

use yii\db\Schema;
use yii\db\Migration;

class m171208_102439_crons extends Migration
{

    public function safeUp()
    {
        $tableOptions = 'ENGINE=InnoDB';

        $this->createTable(
            '{{%crons}}',
            [
                'cron_id'=> $this->primaryKey(10)->unsigned(),
                'cron_code'=> $this->string(20)->notNull(),
                'cron_name'=> $this->string(120)->notNull(),
                'cron_desc'=> $this->text()->null()->defaultValue(null),
                'cron_order'=> $this->smallInteger(3)->unsigned()->notNull()->defaultValue(0),
                'cron_config'=> $this->text()->notNull(),
                'thistime'=> $this->integer(10)->notNull()->defaultValue(0),
                'nextime'=> $this->integer(10)->notNull(),
                'day'=> $this->smallInteger(3)->notNull(),
                'week'=> $this->string(1)->notNull(),
                'hour'=> $this->string(2)->notNull(),
                'minute'=> $this->string(255)->notNull(),
                'enable'=> $this->smallInteger(1)->notNull()->defaultValue(1),
                'run_once'=> $this->smallInteger(1)->notNull()->defaultValue(0),
                'allow_ip'=> $this->string(100)->notNull()->defaultValue(''),
                'alow_files'=> $this->string(255)->notNull(),
            ],$tableOptions
        );
        $this->createIndex('nextime','{{%crons}}',['nextime'],false);
        $this->createIndex('enable','{{%crons}}',['enable'],false);
        $this->createIndex('cron_code','{{%crons}}',['cron_code'],false);

    }

    public function safeDown()
    {
        $this->dropIndex('nextime', '{{%crons}}');
        $this->dropIndex('enable', '{{%crons}}');
        $this->dropIndex('cron_code', '{{%crons}}');
        $this->dropTable('{{%crons}}');
    }
}
