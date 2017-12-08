<?php

use yii\db\Schema;
use yii\db\Migration;

class m171208_102520_shop_config extends Migration
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
            '{{%shop_config}}',
            [
                'id'=> $this->primaryKey(5)->unsigned(),
                'parent_id'=> $this->smallInteger(5)->unsigned()->notNull()->defaultValue(0),
                'code'=> $this->string(30)->notNull()->defaultValue(''),
                'type'=> $this->string(10)->notNull()->defaultValue(''),
                'store_range'=> $this->string(255)->notNull()->defaultValue(''),
                'store_dir'=> $this->string(255)->notNull()->defaultValue(''),
                'value'=> $this->text()->notNull(),
                'sort_order'=> $this->smallInteger(3)->unsigned()->notNull()->defaultValue(1),
            ],$tableOptions
        );
        $this->createIndex('code','{{%shop_config}}',['code'],true);
        $this->createIndex('parent_id','{{%shop_config}}',['parent_id'],false);

    }

    public function safeDown()
    {
        $this->dropIndex('code', '{{%shop_config}}');
        $this->dropIndex('parent_id', '{{%shop_config}}');
        $this->dropTable('{{%shop_config}}');
    }
}
