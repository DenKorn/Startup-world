<?php

use yii\db\Migration;

class m170313_203144_notifications_mail_only_field_add extends Migration
{
    public function up()
    {
        $this->addColumn('forum_notifications','mail_only','TINYINT(1) DEFAULT 0');
    }

    public function down()
    {
        $this->dropColumn('forum_notifications','mail_only');
        return true;
    }
}
