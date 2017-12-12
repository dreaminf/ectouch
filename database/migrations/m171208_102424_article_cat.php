<?php

use yii\db\Schema;
use yii\db\Migration;

class m171208_102424_article_cat extends Migration
{

    public function safeUp()
    {
        $tableOptions = 'ENGINE=InnoDB';

        $this->createTable(
            '{{%article_cat}}',
            [
                'cat_id'=> $this->primaryKey(10),
                'cat_name'=> $this->string(255)->notNull()->defaultValue(''),
                'cat_type'=> $this->smallInteger(1)->unsigned()->notNull()->defaultValue(1),
                'keywords'=> $this->string(255)->notNull()->defaultValue(''),
                'cat_desc'=> $this->string(255)->notNull()->defaultValue(''),
                'sort_order'=> $this->smallInteger(3)->unsigned()->notNull()->defaultValue(50),
                'show_in_nav'=> $this->smallInteger(1)->unsigned()->notNull()->defaultValue(0),
                'parent_id'=> $this->integer(10)->unsigned()->notNull()->defaultValue(0),
            ],$tableOptions
        );
        $this->createIndex('cat_type','{{%article_cat}}',['cat_type'],false);
        $this->createIndex('sort_order','{{%article_cat}}',['sort_order'],false);
        $this->createIndex('parent_id','{{%article_cat}}',['parent_id'],false);

    }

    public function safeDown()
    {
        $this->dropIndex('cat_type', '{{%article_cat}}');
        $this->dropIndex('sort_order', '{{%article_cat}}');
        $this->dropIndex('parent_id', '{{%article_cat}}');
        $this->dropTable('{{%article_cat}}');
    }
}
