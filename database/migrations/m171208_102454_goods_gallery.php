<?php

use yii\db\Schema;
use yii\db\Migration;

class m171208_102454_goods_gallery extends Migration
{

    public function safeUp()
    {
        $tableOptions = 'ENGINE=InnoDB';

        $this->createTable(
            '{{%goods_gallery}}',
            [
                'img_id'=> $this->primaryKey(10)->unsigned(),
                'goods_id'=> $this->integer(10)->unsigned()->notNull()->defaultValue('0'),
                'img_url'=> $this->string(255)->notNull()->defaultValue(''),
                'img_desc'=> $this->string(255)->notNull()->defaultValue(''),
                'thumb_url'=> $this->string(255)->notNull()->defaultValue(''),
                'img_original'=> $this->string(255)->notNull()->defaultValue(''),
            ],$tableOptions
        );
        $this->createIndex('goods_id','{{%goods_gallery}}',['goods_id'],false);

    }

    public function safeDown()
    {
        $this->dropIndex('goods_id', '{{%goods_gallery}}');
        $this->dropTable('{{%goods_gallery}}');
    }
}
