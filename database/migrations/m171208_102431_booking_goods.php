<?php

use yii\db\Schema;
use yii\db\Migration;

class m171208_102431_booking_goods extends Migration
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
            '{{%booking_goods}}',
            [
                'rec_id'=> $this->primaryKey(8)->unsigned(),
                'user_id'=> $this->integer(8)->unsigned()->notNull()->defaultValue('0'),
                'email'=> $this->string(60)->notNull()->defaultValue(''),
                'link_man'=> $this->string(60)->notNull()->defaultValue(''),
                'tel'=> $this->string(60)->notNull()->defaultValue(''),
                'goods_id'=> $this->integer(8)->unsigned()->notNull()->defaultValue('0'),
                'goods_desc'=> $this->string(255)->notNull()->defaultValue(''),
                'goods_number'=> $this->smallInteger(5)->unsigned()->notNull()->defaultValue(0),
                'booking_time'=> $this->integer(10)->unsigned()->notNull()->defaultValue('0'),
                'is_dispose'=> $this->smallInteger(1)->unsigned()->notNull()->defaultValue(0),
                'dispose_user'=> $this->string(30)->notNull()->defaultValue(''),
                'dispose_time'=> $this->integer(10)->unsigned()->notNull()->defaultValue('0'),
                'dispose_note'=> $this->string(255)->notNull()->defaultValue(''),
            ],$tableOptions
        );
        $this->createIndex('user_id','{{%booking_goods}}',['user_id'],false);

    }

    public function safeDown()
    {
        $this->dropIndex('user_id', '{{%booking_goods}}');
        $this->dropTable('{{%booking_goods}}');
    }
}
