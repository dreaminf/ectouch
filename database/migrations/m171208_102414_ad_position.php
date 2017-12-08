<?php

use yii\db\Schema;
use yii\db\Migration;

class m171208_102414_ad_position extends Migration
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
            '{{%ad_position}}',
            [
                'position_id'=> $this->primaryKey(3)->unsigned(),
                'position_name'=> $this->string(60)->notNull()->defaultValue(''),
                'ad_width'=> $this->smallInteger(5)->unsigned()->notNull()->defaultValue(0),
                'ad_height'=> $this->smallInteger(5)->unsigned()->notNull()->defaultValue(0),
                'position_desc'=> $this->string(255)->notNull()->defaultValue(''),
                'position_style'=> $this->text()->notNull(),
            ],$tableOptions
        );

    }

    public function safeDown()
    {
        $this->dropTable('{{%ad_position}}');
    }
}
