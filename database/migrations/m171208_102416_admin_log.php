<?php

use yii\db\Schema;
use yii\db\Migration;

class m171208_102416_admin_log extends Migration
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
            '{{%admin_log}}',
            [
                'log_id'=> $this->primaryKey(10)->unsigned(),
                'log_time'=> $this->integer(10)->unsigned()->notNull()->defaultValue('0'),
                'user_id'=> $this->smallInteger(3)->unsigned()->notNull()->defaultValue(0),
                'log_info'=> $this->string(255)->notNull()->defaultValue(''),
                'ip_address'=> $this->string(15)->notNull()->defaultValue(''),
            ],$tableOptions
        );
        $this->createIndex('log_time','{{%admin_log}}',['log_time'],false);
        $this->createIndex('user_id','{{%admin_log}}',['user_id'],false);

    }

    public function safeDown()
    {
        $this->dropIndex('log_time', '{{%admin_log}}');
        $this->dropIndex('user_id', '{{%admin_log}}');
        $this->dropTable('{{%admin_log}}');
    }
}
