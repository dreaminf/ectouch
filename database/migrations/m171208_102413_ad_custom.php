<?php

use yii\db\Schema;
use yii\db\Migration;

class m171208_102413_ad_custom extends Migration
{

    public function safeUp()
    {
        $tableOptions = 'ENGINE=InnoDB';

        $this->createTable(
            '{{%ad_custom}}',
            [
                'ad_id'=> $this->primaryKey(10)->unsigned(),
                'ad_type'=> $this->smallInteger(1)->unsigned()->notNull()->defaultValue(1),
                'ad_name'=> $this->string(60)->null()->defaultValue(null),
                'add_time'=> $this->integer(10)->unsigned()->notNull()->defaultValue('0'),
                'content'=> $this->text()->null()->defaultValue(null),
                'url'=> $this->string(255)->null()->defaultValue(null),
                'ad_status'=> $this->smallInteger(3)->unsigned()->notNull()->defaultValue(0),
            ],$tableOptions
        );

    }

    public function safeDown()
    {
        $this->dropTable('{{%ad_custom}}');
    }
}
