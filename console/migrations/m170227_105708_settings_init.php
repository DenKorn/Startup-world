<?php

use yii\db\Migration;

class m170227_105708_settings_init extends Migration
{
    public function up()
    {
        $tableOptions = null;
        if ($this->db->driverName === 'mysql') {
            $tableOptions = 'CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE=InnoDB';
        }

        // Поле body содержит в себе JSON-обьект с настройками вида "name : value", для группировки
        // часто используемых настроек в один обьект с целью минимизации количества запросов к БД.
        // Обьекты берутся из подпапки initial_settings.

        $this->createTable('general_settings',[
            'id' => $this->primaryKey()->unsigned()->notNull(),
            'name' => $this->string(35)->notNull()->unique(),
            'body' => $this->string(300)->notNull()->defaultValue('{}'),
            'enabled' => $this->boolean()->defaultValue('1')
        ], $tableOptions);

        $objectsPath = __DIR__ .'\\initial_settings\\';

        $this->batchInsert('general_settings', ['name', 'body'], [
            ['MESSAGES_LIMITS', file_get_contents($objectsPath.'messages_limits.json')],
            ['USER_NOTIFICATIONS', file_get_contents($objectsPath.'user_notifications.json')],
            ['TIME', file_get_contents($objectsPath.'time.json')],
            //todo дописать импорт всех остальных настроек сюда
        ]);
    }

    public function down()
    {
        $this->dropTable('general_settings');
        return true;
    }

}
