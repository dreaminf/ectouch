<?php

use yii\db\Schema;
use yii\db\Migration;

class m171208_102531_user_rank extends Migration
{

    public function safeUp()
    {
        $tableOptions = 'ENGINE=InnoDB';

        $this->createTable(
            '{{%user_rank}}',
            [
                'rank_id'=> $this->primaryKey(10)->unsigned(),
                'rank_name'=> $this->string(30)->notNull()->defaultValue(''),
                'min_points'=> $this->integer(10)->unsigned()->notNull()->defaultValue('0'),
                'max_points'=> $this->integer(10)->unsigned()->notNull()->defaultValue('0'),
                'discount'=> $this->smallInteger(3)->unsigned()->notNull()->defaultValue(0),
                'show_price'=> $this->smallInteger(1)->unsigned()->notNull()->defaultValue(1),
                'special_rank'=> $this->smallInteger(1)->unsigned()->notNull()->defaultValue(0),
            ],$tableOptions
        );

    }

    public function safeDown()
    {
        $this->dropTable('{{%user_rank}}');
    }
}
