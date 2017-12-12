<?php

use yii\db\Schema;
use yii\db\Migration;

class m171208_102532_users extends Migration
{

    public function safeUp()
    {
        $tableOptions = 'ENGINE=InnoDB';

        $this->createTable(
            '{{%users}}',
            [
                'user_id'=> $this->primaryKey(10)->unsigned(),
                'email'=> $this->string(60)->notNull()->defaultValue(''),
                'user_name'=> $this->string(60)->notNull()->defaultValue(''),
                'password'=> $this->string(32)->notNull()->defaultValue(''),
                'question'=> $this->string(255)->notNull()->defaultValue(''),
                'answer'=> $this->string(255)->notNull()->defaultValue(''),
                'sex'=> $this->smallInteger(1)->unsigned()->notNull()->defaultValue(0),
                'birthday'=> $this->date()->notNull()->defaultValue('1000-01-01'),
                'user_money'=> $this->decimal(10, 2)->notNull()->defaultValue('0.00'),
                'frozen_money'=> $this->decimal(10, 2)->notNull()->defaultValue('0.00'),
                'pay_points'=> $this->integer(10)->unsigned()->notNull()->defaultValue('0'),
                'rank_points'=> $this->integer(10)->unsigned()->notNull()->defaultValue('0'),
                'address_id'=> $this->integer(10)->unsigned()->notNull()->defaultValue('0'),
                'reg_time'=> $this->integer(10)->unsigned()->notNull()->defaultValue('0'),
                'last_login'=> $this->integer(10)->unsigned()->notNull()->defaultValue('0'),
                'last_time'=> $this->datetime()->notNull()->defaultValue('1000-01-01 00:00:00'),
                'last_ip'=> $this->string(15)->notNull()->defaultValue(''),
                'visit_count'=> $this->integer(10)->unsigned()->notNull()->defaultValue(0),
                'user_rank'=> $this->smallInteger(3)->unsigned()->notNull()->defaultValue(0),
                'is_special'=> $this->smallInteger(3)->unsigned()->notNull()->defaultValue(0),
                'ec_salt'=> $this->string(10)->null()->defaultValue(null),
                'salt'=> $this->string(10)->notNull()->defaultValue('0'),
                'parent_id'=> $this->integer(10)->notNull()->defaultValue(0),
                'flag'=> $this->smallInteger(3)->unsigned()->notNull()->defaultValue(0),
                'alias'=> $this->string(60)->notNull(),
                'msn'=> $this->string(60)->notNull(),
                'qq'=> $this->string(20)->notNull(),
                'office_phone'=> $this->string(20)->notNull(),
                'home_phone'=> $this->string(20)->notNull(),
                'mobile_phone'=> $this->string(20)->notNull(),
                'is_validated'=> $this->smallInteger(3)->unsigned()->notNull()->defaultValue(0),
                'credit_line'=> $this->decimal(10, 2)->unsigned()->notNull(),
                'passwd_question'=> $this->string(50)->null()->defaultValue(null),
                'passwd_answer'=> $this->string(255)->null()->defaultValue(null),
            ],$tableOptions
        );
        $this->createIndex('user_name','{{%users}}',['user_name'],true);
        $this->createIndex('email','{{%users}}',['email'],false);
        $this->createIndex('parent_id','{{%users}}',['parent_id'],false);
        $this->createIndex('flag','{{%users}}',['flag'],false);

    }

    public function safeDown()
    {
        $this->dropIndex('user_name', '{{%users}}');
        $this->dropIndex('email', '{{%users}}');
        $this->dropIndex('parent_id', '{{%users}}');
        $this->dropIndex('flag', '{{%users}}');
        $this->dropTable('{{%users}}');
    }
}
