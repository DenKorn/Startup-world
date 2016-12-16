<?php

use yii\db\Migration;

class m161129_002010_db_exp extends Migration
{
    public function up()
    {
        $tableOptions = null;
        if ($this->db->driverName === 'mysql') {
            $tableOptions = 'CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE=InnoDB';
        }

        $this->createTable('common_info', [
            'id' => $this->primaryKey(),
            'info_header' => $this->char(50)->notNull(),
            'info_content' => $this->text()->null()
        ],$tableOptions);

        $this->createTable('projects', [
            'id' => $this->primaryKey(),
            'title' => $this->char(50)->null(),
            'description' => $this->text(),
            'sort_order' => $this->integer()->unsigned()->null(),
            'complete_date' => $this->dateTime()->null(),
            'link' => $this->char(200),
            'has_image' => $this->boolean()->defaultValue(true),
        ], $tableOptions);

        $this->createTable('contacts', [
            'id' => $this->primaryKey(),
            'title' => $this->char(50)->notNull(),
            'description' => $this->text()->null(),
        ], $tableOptions);

        $this->createTable('achievements', [
            'id' => $this->primaryKey(),
            'title' => $this->char(50)->notNull(),
            'content' => $this->text()->null(),
            'time' => $this->dateTime()->null()
        ], $tableOptions);

        $this->createTable('activity', [
            'date' => 'timestamp default current_timestamp'
        ]);
    }

    public function down()
    {
        $this->dropTable('achievements');
        $this->dropTable('contacts');
        $this->dropTable('projects');
        $this->dropTable('common_info');
        return true;
    }
}
