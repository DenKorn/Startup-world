<?php

use yii\db\Migration;

class m170312_142134_user_created_at_extension extends Migration
{
    public function up()
    {
        $this->addColumn('user', 'registered_at', 'datetime');

        $this->execute('CREATE TRIGGER user_OnInsert BEFORE INSERT ON `user`
    FOR EACH ROW SET NEW.registered_at = IFNULL(NEW.registered_at, NOW());');
    }

    public function down()
    {
        $this->execute('DROP TRIGGER IF EXISTS user_OnInsert');

        $this->dropColumn('user','registered_at');
        return true;
    }
}
