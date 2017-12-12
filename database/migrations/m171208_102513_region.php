<?php

use yii\db\Schema;
use yii\db\Migration;

class m171208_102513_region extends Migration
{

    public function safeUp()
    {
        $tableOptions = 'ENGINE=InnoDB';

        $this->createTable(
            '{{%region}}',
            [
                'region_id'=> $this->primaryKey(10)->unsigned(),
                'parent_id'=> $this->integer(10)->unsigned()->notNull()->defaultValue(0),
                'region_name'=> $this->string(120)->notNull()->defaultValue(''),
                'region_type'=> $this->smallInteger(1)->notNull()->defaultValue(2),
                'agency_id'=> $this->integer(10)->unsigned()->notNull()->defaultValue(0),
            ],$tableOptions
        );
        $this->createIndex('parent_id','{{%region}}',['parent_id'],false);
        $this->createIndex('region_type','{{%region}}',['region_type'],false);
        $this->createIndex('agency_id','{{%region}}',['agency_id'],false);

    }

    public function safeDown()
    {
        $this->dropIndex('parent_id', '{{%region}}');
        $this->dropIndex('region_type', '{{%region}}');
        $this->dropIndex('agency_id', '{{%region}}');
        $this->dropTable('{{%region}}');
    }
}
