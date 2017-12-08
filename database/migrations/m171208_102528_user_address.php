<?php

use yii\db\Schema;
use yii\db\Migration;

class m171208_102528_user_address extends Migration
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
            '{{%user_address}}',
            [
                'address_id'=> $this->primaryKey(8)->unsigned(),
                'address_name'=> $this->string(50)->notNull()->defaultValue(''),
                'user_id'=> $this->integer(8)->unsigned()->notNull()->defaultValue('0'),
                'consignee'=> $this->string(60)->notNull()->defaultValue(''),
                'email'=> $this->string(60)->notNull()->defaultValue(''),
                'country'=> $this->smallInteger(5)->notNull()->defaultValue(0),
                'province'=> $this->smallInteger(5)->notNull()->defaultValue(0),
                'city'=> $this->smallInteger(5)->notNull()->defaultValue(0),
                'district'=> $this->smallInteger(5)->notNull()->defaultValue(0),
                'address'=> $this->string(120)->notNull()->defaultValue(''),
                'zipcode'=> $this->string(60)->notNull()->defaultValue(''),
                'tel'=> $this->string(60)->notNull()->defaultValue(''),
                'mobile'=> $this->string(60)->notNull()->defaultValue(''),
                'sign_building'=> $this->string(120)->notNull()->defaultValue(''),
                'best_time'=> $this->string(120)->notNull()->defaultValue(''),
            ],$tableOptions
        );
        $this->createIndex('user_id','{{%user_address}}',['user_id'],false);

    }

    public function safeDown()
    {
        $this->dropIndex('user_id', '{{%user_address}}');
        $this->dropTable('{{%user_address}}');
    }
}
