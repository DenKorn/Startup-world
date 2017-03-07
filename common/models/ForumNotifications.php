<?php

namespace common\models;

use yii\db\Expression;

/**
 * This is the model class for table "forum_notifications".
 *
 * @property integer $id
 * @property integer $recipient_id
 * @property string $type
 * @property string $message
 * @property string $sended_at
 *
 * @property User $recipient
 */
class ForumNotifications extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'forum_notifications';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['recipient_id', 'type', 'message'], 'required'],
            [['recipient_id'], 'integer'],
            [['sended_at'], 'safe'],
            [['type'], 'string', 'max' => 15],
            [['message'], 'string', 'max' => 255],
            [['recipient_id'], 'exist', 'skipOnError' => true, 'targetClass' => User::className(), 'targetAttribute' => ['recipient_id' => 'id']],
        ];
    }

    public static function createNotification($user_id, $message, $type, $from_message = null)
    {
        $newRecord = new ForumNotifications(['recipient_id' => $user_id, 'message' => $message, 'type' => $type, 'from_message' => $from_message]);
        $newRecord->save();
    }

    /**
     * Получает список оповещений определенного типа для определенного пользователя.
     * Поддерживает возможность удалять записи после извлечения из базы данных
     *
     * @param $user_id int
     * @param $msg_age integer
     * @param $msg_type string
     * @param $removeThen boolean
     * @return static[]
     */
    public static function getNotificationsForUser($user_id, $msg_age = 3, $msg_type = 'alert', $removeThen = false)
    {
        $notifyRecords = self::find()->where(['recipient_id' => $user_id, 'type' => $msg_type])->
            andWhere(['>=', 'sended_at', new Expression("DATE_SUB(CURRENT_TIMESTAMP, INTERVAL $msg_age HOUR )")])->all();

        $messages = array_map(function($record)
        {
            $message = $record->message;
            return $message;
        }, $notifyRecords);

        if($removeThen) {
            foreach ($notifyRecords as $record) {
                $record->delete();
            }
        }

        return $messages;
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'recipient_id' => 'Recipient ID',
            'type' => 'Type',
            'message' => 'Message',
            'sended_at' => 'Sended At',
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getRecipient()
    {
        return $this->hasOne(User::className(), ['id' => 'recipient_id']);
    }
}
