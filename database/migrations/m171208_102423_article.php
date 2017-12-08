<?php

use yii\db\Schema;
use yii\db\Migration;

class m171208_102423_article extends Migration
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
            '{{%article}}',
            [
                'article_id'=> $this->primaryKey(8)->unsigned(),
                'cat_id'=> $this->smallInteger(5)->notNull()->defaultValue(0),
                'title'=> $this->string(150)->notNull()->defaultValue(''),
                'content'=> $this->text()->notNull(),
                'author'=> $this->string(30)->notNull()->defaultValue(''),
                'author_email'=> $this->string(60)->notNull()->defaultValue(''),
                'keywords'=> $this->string(255)->notNull()->defaultValue(''),
                'article_type'=> $this->smallInteger(1)->unsigned()->notNull()->defaultValue(2),
                'is_open'=> $this->smallInteger(1)->unsigned()->notNull()->defaultValue(1),
                'add_time'=> $this->integer(10)->unsigned()->notNull()->defaultValue('0'),
                'file_url'=> $this->string(255)->notNull()->defaultValue(''),
                'open_type'=> $this->smallInteger(1)->unsigned()->notNull()->defaultValue(0),
                'link'=> $this->string(255)->notNull()->defaultValue(''),
                'description'=> $this->string(255)->null()->defaultValue(null),
            ],$tableOptions
        );
        $this->createIndex('cat_id','{{%article}}',['cat_id'],false);

    }

    public function safeDown()
    {
        $this->dropIndex('cat_id', '{{%article}}');
        $this->dropTable('{{%article}}');
    }
}
