<?php

use yii\db\Schema;
use yii\db\Migration;

class m171208_102528_user_address extends Migration
{

    public function safeUp()
    {
        $tableOptions = 'ENGINE=InnoDB';

        $this->createTable(
            '{{%user_address}}',
            [
                'address_id'=> $this->primaryKey(10)->unsigned(),
                'address_name'=> $this->string(50)->notNull()->defaultValue(''),
                'user_id'=> $this->integer(10)->unsigned()->notNull()->defaultValue('0'),
                'consignee'=> $this->string(60)->notNull()->defaultValue(''),
                'email'=> $this->string(60)->notNull()->defaultValue(''),
                'country'=> $this->integer(10)->notNull()->defaultValue(0),
                'province'=> $this->integer(10)->notNull()->defaultValue(0),
                'city'=> $this->integer(10)->notNull()->defaultValue(0),
                'district'=> $this->integer(10)->notNull()->defaultValue(0),
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
