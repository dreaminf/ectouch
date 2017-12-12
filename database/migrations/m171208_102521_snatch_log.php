<?php

use yii\db\Schema;
use yii\db\Migration;

class m171208_102521_snatch_log extends Migration
{

    public function safeUp()
    {
        $tableOptions = 'ENGINE=InnoDB';

        $this->createTable(
            '{{%snatch_log}}',
            [
                'log_id'=> $this->primaryKey(10)->unsigned(),
                'snatch_id'=> $this->smallInteger(3)->unsigned()->notNull()->defaultValue(0),
                'user_id'=> $this->integer(10)->unsigned()->notNull()->defaultValue('0'),
                'bid_price'=> $this->decimal(10, 2)->notNull()->defaultValue('0.00'),
                'bid_time'=> $this->integer(10)->unsigned()->notNull()->defaultValue('0'),
            ],$tableOptions
        );
        $this->createIndex('snatch_id','{{%snatch_log}}',['snatch_id'],false);

    }

    public function safeDown()
    {
        $this->dropIndex('snatch_id', '{{%snatch_log}}');
        $this->dropTable('{{%snatch_log}}');
    }
}
