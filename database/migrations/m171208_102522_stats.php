<?php

use yii\db\Schema;
use yii\db\Migration;

class m171208_102522_stats extends Migration
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
            '{{%stats}}',
            [
                'access_time'=> $this->integer(10)->unsigned()->notNull()->defaultValue('0'),
                'ip_address'=> $this->string(15)->notNull()->defaultValue(''),
                'visit_times'=> $this->smallInteger(5)->unsigned()->notNull()->defaultValue(1),
                'browser'=> $this->string(60)->notNull()->defaultValue(''),
                'system'=> $this->string(20)->notNull()->defaultValue(''),
                'language'=> $this->string(20)->notNull()->defaultValue(''),
                'area'=> $this->string(30)->notNull()->defaultValue(''),
                'referer_domain'=> $this->string(100)->notNull()->defaultValue(''),
                'referer_path'=> $this->string(200)->notNull()->defaultValue(''),
                'access_url'=> $this->string(255)->notNull()->defaultValue(''),
            ],$tableOptions
        );
        $this->createIndex('access_time','{{%stats}}',['access_time'],false);

    }

    public function safeDown()
    {
        $this->dropIndex('access_time', '{{%stats}}');
        $this->dropTable('{{%stats}}');
    }
}
