<?php

use yii\db\Schema;
use yii\db\Migration;

class m171208_102426_auction_log extends Migration
{

    public function safeUp()
    {
        $tableOptions = 'ENGINE=InnoDB';

        $this->createTable(
            '{{%auction_log}}',
            [
                'log_id'=> $this->primaryKey(10)->unsigned(),
                'act_id'=> $this->integer(10)->unsigned()->notNull(),
                'bid_user'=> $this->integer(10)->unsigned()->notNull(),
                'bid_price'=> $this->decimal(10, 2)->unsigned()->notNull(),
                'bid_time'=> $this->integer(10)->unsigned()->notNull(),
            ],$tableOptions
        );
        $this->createIndex('act_id','{{%auction_log}}',['act_id'],false);

    }

    public function safeDown()
    {
        $this->dropIndex('act_id', '{{%auction_log}}');
        $this->dropTable('{{%auction_log}}');
    }
}
