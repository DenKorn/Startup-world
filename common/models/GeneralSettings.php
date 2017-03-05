<?php

namespace common\models;

use Yii;
use yii\db\Exception;

/**
 * This is the model class for table "general_settings".
 *
 * @property string $id
 * @property string $name
 * @property string $value
 * @property integer $enabled
 */
class GeneralSettings extends \yii\db\ActiveRecord
{
    /**
     * Метод парсит строку "body" настроек из БД и возвращает полученный обьект
     *
     * @param string $name
     * @return \stdClass
     */
    public static function getSettingsObjByName($name)
    {
        $record = self::findOne(['name' => $name]);
        if ($record)
            return json_decode($record->body);
         else
            throw new Exception('Setting with name "'.$name.'" not found in database');
    }

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'general_settings';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['name', 'value'], 'required'],
            [['enabled'], 'integer'],
            [['name'], 'string', 'max' => 35],
            [['value'], 'string', 'max' => 20],
            [['name'], 'unique'],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'name' => 'Name',
            'value' => 'Value',
            'enabled' => 'Enabled',
        ];
    }
}
