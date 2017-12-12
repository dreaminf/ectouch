<?php

use yii\db\Schema;
use yii\db\Migration;

class m171208_102526_topic extends Migration
{

    public function safeUp()
    {
        $tableOptions = 'ENGINE=InnoDB';

        $this->createTable(
            '{{%topic}}',
            [
                'topic_id'=> $this->integer(10)->unsigned()->notNull(),
                'title'=> $this->string(255)->notNull()->defaultValue(''''),
                'intro'=> $this->text()->notNull(),
                'start_time'=> $this->integer(10)->notNull()->defaultValue(0),
                'end_time'=> $this->integer(10)->notNull()->defaultValue(0),
                'data'=> $this->text()->notNull(),
                'template'=> $this->string(255)->notNull()->defaultValue(''''),
                'css'=> $this->text()->notNull(),
                'topic_img'=> $this->string(255)->null()->defaultValue(null),
                'title_pic'=> $this->string(255)->null()->defaultValue(null),
                'base_style'=> $this->char(6)->null()->defaultValue(null),
                'htmls'=> $this->text()->null()->defaultValue(null),
                'keywords'=> $this->string(255)->null()->defaultValue(null),
                'description'=> $this->string(255)->null()->defaultValue(null),
            ],$tableOptions
        );
        $this->createIndex('topic_id','{{%topic}}',['topic_id'],false);

    }

    public function safeDown()
    {
        $this->dropIndex('topic_id', '{{%topic}}');
        $this->dropTable('{{%topic}}');
    }
}
