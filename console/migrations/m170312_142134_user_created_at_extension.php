<?php

use yii\db\Migration;

class m170312_142134_user_created_at_extension extends Migration
{
    public function up()
    {
        $this->addColumn('user', 'registered_at', 'timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP');
    }

    public function down()
    {
        $this->dropColumn('user','registered_at');

        return true;
    }
}
