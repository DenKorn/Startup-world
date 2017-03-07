<?php

namespace common\models;

use Yii;
use yii\db\ActiveQuery;
use yii\db\Query;
use yii\helpers\Url;

/**
 * This is the model class for table "forum_votes".
 *
 * @property integer $user_id
 * @property string $msg_id
 * @property string $value
 * @property string $setting_date
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
     * Также записывает в поле сообщения last_calculated_rating посчитанное значение рейтинга, кешируя его
     *
     * @param $msg_id int
     * @return int
     */
    public static function getMessageSummaryRating($msg_id)
    {
        $querySumRes = (new Query())->select(['sum(value) as rating'])->from(self::tableName())->where(['msg_id' => $msg_id])->one()['rating'];
        $ratingValue = $querySumRes ? $querySumRes : 0;

        $targetMessage = ForumMessages::findOne(['id' => $msg_id]);
        if($targetMessage->last_calculated_rating != $ratingValue) {
            $targetMessage->last_calculated_rating = $ratingValue;
            $targetMessage->save();

            if(6 >= $ratingValue && $ratingValue >= -6) {
                $methodLink = Url::home(true).'/discussions/search-for-message?id='.$msg_id;
                $msgLink = "<br><a href='$methodLink'>ПЕРЕЙТИ</a>";
                switch ($ratingValue) {
                    case 6:
                        ForumNotifications::createNotification($targetMessage->user_id,
                            'Рейтинг сообщения +6! Вас почитает даже админ!'.$msgLink, 'alert',$msg_id);
                        break;
                    case 3:
                        ForumNotifications::createNotification($targetMessage->user_id,
                            'Так держать, +3 рейтинга на вашем сообщении'.$msgLink, 'alert',$msg_id);
                        break;
                    case -3:
                        ForumNotifications::createNotification($targetMessage->user_id,
                            'Вы здесь не в почете, рейтинг -3:'.$msgLink, 'alert',$msg_id);
                        break;
                    case -6:
                        ForumNotifications::createNotification($targetMessage->user_id,
                            'Лучше выходи скорей, пока не забанили (рейтинг -6)'.$msgLink, 'warning',$msg_id);
                        break;
                }
            }
        }

        return $ratingValue;
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
