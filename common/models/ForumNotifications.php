<?php

namespace common\models;

use Yii;

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
