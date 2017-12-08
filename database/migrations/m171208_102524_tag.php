<?php

use yii\db\Schema;
use yii\db\Migration;

class m171208_102524_tag extends Migration
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
            '{{%tag}}',
            [
                'tag_id'=> $this->primaryKey(8),
                'user_id'=> $this->integer(8)->unsigned()->notNull()->defaultValue('0'),
                'goods_id'=> $this->integer(8)->unsigned()->notNull()->defaultValue('0'),
                'tag_words'=> $this->string(255)->notNull()->defaultValue(''),
            ],$tableOptions
        );
        $this->createIndex('user_id','{{%tag}}',['user_id'],false);
        $this->createIndex('goods_id','{{%tag}}',['goods_id'],false);

    }

    public function safeDown()
    {
        $this->dropIndex('user_id', '{{%tag}}');
        $this->dropIndex('goods_id', '{{%tag}}');
        $this->dropTable('{{%tag}}');
    }
}
