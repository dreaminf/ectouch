<?php

use yii\db\Schema;
use yii\db\Migration;

class m171208_102435_cat_recommend extends Migration
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
            '{{%cat_recommend}}',
            [
                'cat_id'=> $this->smallInteger(5)->notNull(),
                'recommend_type'=> $this->smallInteger(1)->notNull(),
            ],$tableOptions
        );
        $this->addPrimaryKey('pk_on_ecs_cat_recommend','{{%cat_recommend}}',['cat_id','recommend_type']);

    }

    public function safeDown()
    {
    $this->dropPrimaryKey('pk_on_ecs_cat_recommend','{{%cat_recommend}}');
        $this->dropTable('{{%cat_recommend}}');
    }
}
