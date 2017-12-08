<?php

use yii\db\Schema;
use yii\db\Migration;

class m171208_102446_favourable_activity extends Migration
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
            '{{%favourable_activity}}',
            [
                'act_id'=> $this->primaryKey(5)->unsigned(),
                'act_name'=> $this->string(255)->notNull(),
                'start_time'=> $this->integer(10)->unsigned()->notNull(),
                'end_time'=> $this->integer(10)->unsigned()->notNull(),
                'user_rank'=> $this->string(255)->notNull(),
                'act_range'=> $this->smallInteger(3)->unsigned()->notNull(),
                'act_range_ext'=> $this->string(255)->notNull(),
                'min_amount'=> $this->decimal(10, 2)->unsigned()->notNull(),
                'max_amount'=> $this->decimal(10, 2)->unsigned()->notNull(),
                'act_type'=> $this->smallInteger(3)->unsigned()->notNull(),
                'act_type_ext'=> $this->decimal(10, 2)->unsigned()->notNull(),
                'gift'=> $this->text()->notNull(),
                'sort_order'=> $this->smallInteger(3)->unsigned()->notNull()->defaultValue(50),
            ],$tableOptions
        );
        $this->createIndex('act_name','{{%favourable_activity}}',['act_name'],false);

    }

    public function safeDown()
    {
        $this->dropIndex('act_name', '{{%favourable_activity}}');
        $this->dropTable('{{%favourable_activity}}');
    }
}
