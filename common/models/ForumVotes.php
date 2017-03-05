<?php

namespace common\models;

use Yii;
use yii\db\ActiveQuery;
use yii\db\Query;

/**
 * This is the model class for table "forum_votes".
 *
 * @property integer $user_id
 * @property string $msg_id
 * @property string $value
 * @property string $setting_date
 *
 * @property ForumMessages $msg
 * @property User $user
 */
class ForumVotes extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'forum_votes';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['user_id', 'msg_id', 'value'], 'required'],
            [['user_id', 'msg_id', 'value'], 'integer'],
            [['setting_date'], 'safe'],
            [['msg_id'], 'exist', 'skipOnError' => true, 'targetClass' => ForumMessages::className(), 'targetAttribute' => ['msg_id' => 'id']],
            [['user_id'], 'exist', 'skipOnError' => true, 'targetClass' => User::className(), 'targetAttribute' => ['user_id' => 'id']],
        ];
    }

    /**
     * Считает рейтинг конкретного сообщения
     *
     * @param $msg_id int
     * @return int
     */
    public static function getMessageSummaryRating($msg_id)
    {
        $querySumRes = (new Query())->select(['sum(value) as rating'])->from(self::tableName())->where(['msg_id' => $msg_id])->one()['rating'];
        return $querySumRes ? $querySumRes : 0;
    }

    /**
     * Получает величину выбора оценки авторизованного пользователя, на кокретном сообщении с id = $msg_id
     * Возможные значения: -1 - дизлайк, 0 - не оценивал, 1 - лайк
     *
     * @param $msg_id int|string
     * @return int|null
     */
    public static function getAuthorizedUserChoiseForMessage($msg_id)
    {
        if(!Yii::$app->user->isGuest) {
            $queryRes = (new Query())->select('value')->from(self::tableName())->where(['user_id' => Yii::$app->user->id, 'msg_id' => $msg_id])->one()['value'];
            return $queryRes ? $queryRes : 0;
        } else
            return null;
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'user_id' => 'User ID',
            'msg_id' => 'Msg ID',
            'value' => 'Value',
            'setting_date' => 'Setting Date',
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getMsg()
    {
        return $this->hasOne(ForumMessages::className(), ['id' => 'msg_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getUser()
    {
        return $this->hasOne(User::className(), ['id' => 'user_id']);
    }
}
