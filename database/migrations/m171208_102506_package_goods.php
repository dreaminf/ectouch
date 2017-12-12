<?php

use yii\db\Schema;
use yii\db\Migration;

class m171208_102506_package_goods extends Migration
{

    public function safeUp()
    {
        $tableOptions = 'ENGINE=InnoDB';

        $this->createTable(
            '{{%package_goods}}',
            [
                'package_id'=> $this->integer(10)->unsigned()->notNull(),
                'goods_id'=> $this->integer(10)->unsigned()->notNull(),
                'product_id'=> $this->integer(10)->unsigned()->notNull(),
                'goods_number'=> $this->integer(10)->unsigned()->notNull()->defaultValue(1),
                'admin_id'=> $this->smallInteger(3)->unsigned()->notNull(),
            ],$tableOptions
        );
        $this->addPrimaryKey('pk_on_ecs_package_goods','{{%package_goods}}',['package_id','goods_id','product_id','admin_id']);

    }

    public function safeDown()
    {
    $this->dropPrimaryKey('pk_on_ecs_package_goods','{{%package_goods}}');
        $this->dropTable('{{%package_goods}}');
    }
}
