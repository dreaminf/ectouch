<?php

use yii\db\Schema;
use yii\db\Migration;

class m171208_102530_user_feed extends Migration
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
            '{{%user_feed}}',
            [
                'feed_id'=> $this->primaryKey(8)->unsigned(),
                'user_id'=> $this->integer(8)->unsigned()->notNull()->defaultValue('0'),
                'value_id'=> $this->integer(8)->unsigned()->notNull()->defaultValue('0'),
                'goods_id'=> $this->integer(8)->unsigned()->notNull()->defaultValue('0'),
                'feed_type'=> $this->smallInteger(1)->unsigned()->notNull()->defaultValue(0),
                'is_feed'=> $this->smallInteger(1)->unsigned()->notNull()->defaultValue(0),
            ],$tableOptions
        );

    }

    public function safeDown()
    {
        $this->dropTable('{{%user_feed}}');
    }
}
