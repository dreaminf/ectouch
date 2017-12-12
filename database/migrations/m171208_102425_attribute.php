<?php

use yii\db\Schema;
use yii\db\Migration;

class m171208_102425_attribute extends Migration
{

    public function safeUp()
    {
        $tableOptions = 'ENGINE=InnoDB';

        $this->createTable(
            '{{%attribute}}',
            [
                'attr_id'=> $this->primaryKey(10)->unsigned(),
                'cat_id'=> $this->integer(10)->unsigned()->notNull()->defaultValue(0),
                'attr_name'=> $this->string(60)->notNull()->defaultValue(''),
                'attr_input_type'=> $this->smallInteger(1)->unsigned()->notNull()->defaultValue(1),
                'attr_type'=> $this->smallInteger(1)->unsigned()->notNull()->defaultValue(1),
                'attr_values'=> $this->text()->notNull(),
                'attr_index'=> $this->smallInteger(1)->unsigned()->notNull()->defaultValue(0),
                'sort_order'=> $this->smallInteger(3)->unsigned()->notNull()->defaultValue(0),
                'is_linked'=> $this->smallInteger(1)->unsigned()->notNull()->defaultValue(0),
                'attr_group'=> $this->smallInteger(1)->unsigned()->notNull()->defaultValue(0),
            ],$tableOptions
        );
        $this->createIndex('cat_id','{{%attribute}}',['cat_id'],false);

    }

    public function safeDown()
    {
        $this->dropIndex('cat_id', '{{%attribute}}');
        $this->dropTable('{{%attribute}}');
    }
}
