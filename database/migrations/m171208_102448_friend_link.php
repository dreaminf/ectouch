<?php

use yii\db\Schema;
use yii\db\Migration;

class m171208_102448_friend_link extends Migration
{

    public function safeUp()
    {
        $tableOptions = 'ENGINE=InnoDB';

        $this->createTable(
            '{{%friend_link}}',
            [
                'link_id'=> $this->primaryKey(10)->unsigned(),
                'link_name'=> $this->string(255)->notNull()->defaultValue(''),
                'link_url'=> $this->string(255)->notNull()->defaultValue(''),
                'link_logo'=> $this->string(255)->notNull()->defaultValue(''),
                'show_order'=> $this->smallInteger(3)->unsigned()->notNull()->defaultValue(50),
            ],$tableOptions
        );
        $this->createIndex('show_order','{{%friend_link}}',['show_order'],false);

    }

    public function safeDown()
    {
        $this->dropIndex('show_order', '{{%friend_link}}');
        $this->dropTable('{{%friend_link}}');
    }
}
