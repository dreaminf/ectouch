<?php

use yii\db\Schema;
use yii\db\Migration;

class m171208_102432_brand extends Migration
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
            '{{%brand}}',
            [
                'brand_id'=> $this->primaryKey(5)->unsigned(),
                'brand_name'=> $this->string(60)->notNull()->defaultValue(''),
                'brand_logo'=> $this->string(80)->notNull()->defaultValue(''),
                'brand_desc'=> $this->text()->notNull(),
                'site_url'=> $this->string(255)->notNull()->defaultValue(''),
                'sort_order'=> $this->smallInteger(3)->unsigned()->notNull()->defaultValue(50),
                'is_show'=> $this->smallInteger(1)->unsigned()->notNull()->defaultValue(1),
            ],$tableOptions
        );
        $this->createIndex('is_show','{{%brand}}',['is_show'],false);

    }

    public function safeDown()
    {
        $this->dropIndex('is_show', '{{%brand}}');
        $this->dropTable('{{%brand}}');
    }
}
