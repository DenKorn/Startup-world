<?php

use yii\db\Migration;

class m161219_001215_forum_init extends Migration
{
    public function up()
    {
        $commonOption = 'ENGINE = InnoDB';

        //в parent_message_id может быть NULL только для корневых сообщений в темах
        $this->createTable('forum_messages',[
            'id' => $this->primaryKey()->unsigned()->notNull(),
            'parent_message_id' => $this->integer()->unsigned(),
            'created_at' => $this->timestamp()->defaultExpression('CURRENT_TIMESTAMP'),
            'user_id' => $this->integer()->null(),
            'root_theme_id' => $this->integer()->unsigned()->null(), //поле для внешнего ключа на список тем
            'content' => $this->text()->notNull()
        ],$commonOption);

        $this->addForeignKey('fk_messages_inner_key','forum_messages','parent_message_id','forum_messages','id','CASCADE','NO ACTION');

        $this->createTable('forum_roots',[
            'id' => $this->primaryKey()->unsigned()->notNull(),
            'title' => $this->string(150)->notNull(),
        ],$commonOption);

        $this->addForeignKey('fk_roots_messages','forum_messages','root_theme_id','forum_roots','id','CASCADE');
        $this->addForeignKey('fk_messages_to_users','forum_messages','user_id','user','id','SET NULL');

        //test data
        $this->execute("INSERT INTO `forum_roots` (title) VALUE ('test title of first')");

        $this->execute("INSERT INTO `forum_messages` (parent_message_id,user_id,root_theme_id,content) VALUES 
(NULL,1,1,'root message')");

        $this->execute("INSERT INTO `forum_messages` (parent_message_id,user_id,content) VALUES 
(1,1 ,'first submessage'),
(1,1 ,'second submessage'),
(3,1 ,'subsub))'),
(3,1 ,'second subsub')");

    }

    public function down()
    {
        $this->dropForeignKey('fk_messages_to_users','forum_messages');
        $this->dropForeignKey('fk_roots_messages','forum_messages');
        $this->dropForeignKey('fk_messages_inner_key','forum_messages');
        $this->dropTable('forum_roots');
        $this->dropTable('forum_messages');

        return true;
    }

}
