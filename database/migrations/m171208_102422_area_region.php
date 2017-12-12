<?php

use yii\db\Schema;
use yii\db\Migration;

class m171208_102422_area_region extends Migration
{

    public function safeUp()
    {
        $tableOptions = 'ENGINE=InnoDB';

        $this->createTable(
            '{{%area_region}}',
            [
                'shipping_area_id'=> $this->integer(10)->unsigned()->notNull(),
                'region_id'=> $this->integer(10)->unsigned()->notNull(),
            ],$tableOptions
        );
        $this->addPrimaryKey('pk_on_ecs_area_region','{{%area_region}}',['shipping_area_id','region_id']);

    }

    public function safeDown()
    {
    $this->dropPrimaryKey('pk_on_ecs_area_region','{{%area_region}}');
        $this->dropTable('{{%area_region}}');
    }
}
