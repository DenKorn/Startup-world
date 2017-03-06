<?php

use yii\db\Migration;

class m170306_215211_notification_table_init extends Migration
{
    public function up()
    {
        $tableOptions = null;
        if ($this->db->driverName === 'mysql') {
            $tableOptions = 'CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE=InnoDB';
        }

        $this->createTable('forum_notifications', [
            'id' => $this->primaryKey()->notNull(),
            'recipient_id' => $this->integer()->notNull(),
            'type' => $this->string(15)->notNull(),
            'message' => $this->string(255)->notNull(),
            'sended_at' => $this->timestamp()->defaultExpression('CURRENT_TIMESTAMP'),
        ],
            $tableOptions);

        $this->addForeignKey('fk_notifications_users', 'forum_notifications', 'recipient_id', 'user', 'id', 'CASCADE', 'NO ACTION');
    }

    public function down()
    {
        $this->dropForeignKey('fk_notifications_users', 'forum_notifications');
        $this->dropTable('forum_notifications');
        return true;
    }
}
