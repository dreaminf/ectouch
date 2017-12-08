<?php

use yii\db\Schema;
use yii\db\Migration;

class m171208_102525_template extends Migration
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
            '{{%template}}',
            [
                'filename'=> $this->string(30)->notNull()->defaultValue(''),
                'region'=> $this->string(40)->notNull()->defaultValue(''),
                'library'=> $this->string(40)->notNull()->defaultValue(''),
                'sort_order'=> $this->smallInteger(1)->unsigned()->notNull()->defaultValue(0),
                'id'=> $this->smallInteger(5)->unsigned()->notNull()->defaultValue(0),
                'number'=> $this->smallInteger(1)->unsigned()->notNull()->defaultValue(5),
                'type'=> $this->smallInteger(1)->unsigned()->notNull()->defaultValue(0),
                'theme'=> $this->string(60)->notNull()->defaultValue(''),
                'remarks'=> $this->string(30)->notNull()->defaultValue(''),
            ],$tableOptions
        );
        $this->createIndex('filename','{{%template}}',['filename','region'],false);
        $this->createIndex('theme','{{%template}}',['theme'],false);
        $this->createIndex('remarks','{{%template}}',['remarks'],false);

    }

    public function safeDown()
    {
        $this->dropIndex('filename', '{{%template}}');
        $this->dropIndex('theme', '{{%template}}');
        $this->dropIndex('remarks', '{{%template}}');
        $this->dropTable('{{%template}}');
    }
}
