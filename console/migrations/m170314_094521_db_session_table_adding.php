<?php

use yii\db\Migration;

class m170314_094521_db_session_table_adding extends Migration
{
    public function up()
    {
        $this->execute('
          CREATE TABLE session (
          id CHAR(40) NOT NULL PRIMARY KEY,
          expire INTEGER,
          data BLOB
          )');
    }

    public function down()
    {
        $this->dropTable('session');
        return true;
    }
}
