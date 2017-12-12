<?php

use yii\db\Schema;
use yii\db\Migration;

class m171208_102419_adsense extends Migration
{

    public function safeUp()
    {
        $tableOptions = 'ENGINE=InnoDB';

        $this->createTable(
            '{{%adsense}}',
            [
                'from_ad'=> $this->integer(10)->notNull()->defaultValue(0),
                'referer'=> $this->string(255)->notNull()->defaultValue(''),
                'clicks'=> $this->integer(10)->unsigned()->notNull()->defaultValue('0'),
            ],$tableOptions
        );
        $this->createIndex('from_ad','{{%adsense}}',['from_ad'],false);

    }

    public function safeDown()
    {
        $this->dropIndex('from_ad', '{{%adsense}}');
        $this->dropTable('{{%adsense}}');
    }
}
