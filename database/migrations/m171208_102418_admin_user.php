<?php

use yii\db\Schema;
use yii\db\Migration;

class m171208_102418_admin_user extends Migration
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
            '{{%admin_user}}',
            [
                'user_id'=> $this->primaryKey(5)->unsigned(),
                'user_name'=> $this->string(60)->notNull()->defaultValue(''),
                'email'=> $this->string(60)->notNull()->defaultValue(''),
                'password'=> $this->string(32)->notNull()->defaultValue(''),
                'ec_salt'=> $this->string(10)->null()->defaultValue(null),
                'add_time'=> $this->integer(11)->notNull()->defaultValue(0),
                'last_login'=> $this->integer(11)->notNull()->defaultValue(0),
                'last_ip'=> $this->string(15)->notNull()->defaultValue(''),
                'action_list'=> $this->text()->notNull(),
                'nav_list'=> $this->text()->notNull(),
                'lang_type'=> $this->string(50)->notNull()->defaultValue(''),
                'agency_id'=> $this->smallInteger(5)->unsigned()->notNull(),
                'suppliers_id'=> $this->smallInteger(5)->unsigned()->null()->defaultValue(0),
                'todolist'=> $this->text()->null()->defaultValue(null),
                'role_id'=> $this->smallInteger(5)->null()->defaultValue(null),
            ],$tableOptions
        );
        $this->createIndex('user_name','{{%admin_user}}',['user_name'],false);
        $this->createIndex('agency_id','{{%admin_user}}',['agency_id'],false);

    }

    public function safeDown()
    {
        $this->dropIndex('user_name', '{{%admin_user}}');
        $this->dropIndex('agency_id', '{{%admin_user}}');
        $this->dropTable('{{%admin_user}}');
    }
}
