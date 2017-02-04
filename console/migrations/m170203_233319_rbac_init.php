<?php

use yii\db\Migration;

class m170203_233319_rbac_init extends Migration
{
    public function up()
    {
        $this->execute(
            file_get_contents(
                Yii::getAlias('@yii/rbac/migrations/schema-mysql.sql')
            )
        );
    }

    public function down()
    {
        $this->dropTable('auth_assignment');
        $this->dropTable('auth_item_child');
        $this->dropTable('auth_item');
        $this->dropTable('auth_rule');
        return true;
    }

}
