<?php

use yii\db\Migration;

class m170307_014747_few_mods_for_notifications extends Migration
{
    public function up()
    {
        $this->addColumn('forum_messages','last_calculated_rating','INT NOT NULL DEFAULT 0');

        $this->addColumn('forum_notifications','from_message','INT UNSIGNED DEFAULT NULL');
        $this->addForeignKey('fk_notifications_messages','forum_notifications','from_message','forum_messages','id','CASCADE','NO ACTION');
    }

    public function down()
    {
        $this->dropForeignKey('fk_notifications_messages','forum_notifications');
        $this->dropColumn('forum_notifications','from_message');

        $this->dropColumn('forum_messages','last_calculated_rating');

        return true;
    }
}
