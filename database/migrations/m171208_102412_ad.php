<?php

use yii\db\Schema;
use yii\db\Migration;

class m171208_102412_ad extends Migration
{

    public function safeUp()
    {
        $tableOptions = 'ENGINE=InnoDB';

        $this->createTable(
            '{{%ad}}',
            [
                'ad_id'=> $this->primaryKey(10)->unsigned(),
                'position_id'=> $this->integer(10)->unsigned()->notNull()->defaultValue(0),
                'media_type'=> $this->smallInteger(3)->unsigned()->notNull()->defaultValue(0),
                'ad_name'=> $this->string(60)->notNull()->defaultValue(''),
                'ad_link'=> $this->string(255)->notNull()->defaultValue(''),
                'ad_code'=> $this->text()->notNull(),
                'start_time'=> $this->integer(10)->notNull()->defaultValue(0),
                'end_time'=> $this->integer(10)->notNull()->defaultValue(0),
                'link_man'=> $this->string(60)->notNull()->defaultValue(''),
                'link_email'=> $this->string(60)->notNull()->defaultValue(''),
                'link_phone'=> $this->string(60)->notNull()->defaultValue(''),
                'click_count'=> $this->integer(10)->unsigned()->notNull()->defaultValue('0'),
                'enabled'=> $this->smallInteger(3)->unsigned()->notNull()->defaultValue(1),
            ],$tableOptions
        );
        $this->createIndex('position_id','{{%ad}}',['position_id'],false);
        $this->createIndex('enabled','{{%ad}}',['enabled'],false);

    }

    public function safeDown()
    {
        $this->dropIndex('position_id', '{{%ad}}');
        $this->dropIndex('enabled', '{{%ad}}');
        $this->dropTable('{{%ad}}');
    }
}
