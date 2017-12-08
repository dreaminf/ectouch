<?php

use yii\db\Schema;
use yii\db\Migration;

class m171208_102437_collect_goods extends Migration
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
            '{{%collect_goods}}',
            [
                'rec_id'=> $this->primaryKey(8)->unsigned(),
                'user_id'=> $this->integer(8)->unsigned()->notNull()->defaultValue('0'),
                'goods_id'=> $this->integer(8)->unsigned()->notNull()->defaultValue('0'),
                'add_time'=> $this->integer(11)->unsigned()->notNull()->defaultValue('0'),
                'is_attention'=> $this->smallInteger(1)->notNull()->defaultValue(0),
            ],$tableOptions
        );
        $this->createIndex('user_id','{{%collect_goods}}',['user_id'],false);
        $this->createIndex('goods_id','{{%collect_goods}}',['goods_id'],false);
        $this->createIndex('is_attention','{{%collect_goods}}',['is_attention'],false);

    }

    public function safeDown()
    {
        $this->dropIndex('user_id', '{{%collect_goods}}');
        $this->dropIndex('goods_id', '{{%collect_goods}}');
        $this->dropIndex('is_attention', '{{%collect_goods}}');
        $this->dropTable('{{%collect_goods}}');
    }
}
