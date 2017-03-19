<?php

use yii\db\Migration;

class m161227_171156_db_extending_1 extends Migration
{
    public function up()
    {
        echo "'user' table extending:\n";

        $this->addColumn('user','user_mail','VARCHAR(200) NOT NULL');
        $this->addColumn('user','real_name','VARCHAR(60) NULL');
        $this->addColumn('user','real_surname','VARCHAR(60) NULL');
        $this->addColumn('user','last_activity','TIMESTAMP  NOT NULL DEFAULT CURRENT_TIMESTAMP');

        echo "adding new tables :\n";

        $this->execute('
        USE `startup_forum` ;

CREATE TABLE IF NOT EXISTS `startup_forum`.`forum_activity` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `user_id` INT NULL,
  `activity_code` INT UNSIGNED NOT NULL DEFAULT 0,
  `activity_object` VARCHAR(255) NULL,
  PRIMARY KEY (`id`))
ENGINE = InnoDB;


CREATE TABLE IF NOT EXISTS `startup_forum`.`forum_votes` (
  `user_id` INT NOT NULL,
  `msg_id` INT UNSIGNED NOT NULL,
  `value` INT(4) NOT NULL DEFAULT \'0\',
  `setting_date` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`user_id`, `msg_id`))
ENGINE = InnoDB;

CREATE TABLE IF NOT EXISTS `startup_forum`.`forum_ban_list` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `user_id` INT NOT NULL,
  `ban_time` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `reason` VARCHAR(200) NOT NULL DEFAULT \'\',
  PRIMARY KEY (`id`))
ENGINE = InnoDB;

CREATE TABLE IF NOT EXISTS `startup_forum`.`forum_activity_types` (
  `activity_code` INT UNSIGNED NOT NULL,
  `activity_description` VARCHAR(300) NOT NULL,
  PRIMARY KEY (`activity_code`))
ENGINE = InnoDB;
        
        ');

        $this->addForeignKey('fk_activities_to_activity_types','forum_activity','activity_code','forum_activity_types','activity_code','CASCADE');
        $this->addForeignKey('fk_activities_to_users','forum_activity','user_id','user','id','SET NULL');
        $this->addForeignKey('fk_votes_to_users','forum_votes','user_id','user','id','CASCADE');
        $this->addForeignKey('fk_votes_to_messages','forum_votes','msg_id','forum_messages','id','CASCADE');
        $this->addForeignKey('fk_ban_list_to_user','forum_ban_list','user_id','user','id','CASCADE');

    }

    public function down()
    {
        $this->dropForeignKey('fk_ban_list_to_user','forum_ban_list');
        $this->dropForeignKey('fk_votes_to_users','forum_votes');
        $this->dropForeignKey('fk_activities_to_users','forum_activity');
        $this->dropForeignKey('fk_activities_to_activity_types','forum_activity');

        $this->dropTable('forum_activity');
        $this->dropTable('forum_activity_types');
        $this->dropTable('forum_ban_list');
        $this->dropTable('forum_votes');

        $this->dropColumn('user','user_mail');
        $this->dropColumn('user','real_name');
        $this->dropColumn('user','real_surname');
        $this->dropColumn('user','last_activity');

        return true;
    }
}
