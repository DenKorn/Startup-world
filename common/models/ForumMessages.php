<?php

namespace common\models;

use DateTime;
use DateTimeZone;
use stdClass;
use Yii;
use yii\base\Object;
use yii\db\Query;

/**
 * This is the model class for table "forum_messages".
 *
 * @property integer $id
 * @property integer $parent_message_id
 * @property string $created_at
 * @property integer $user_id
 * @property integer $root_theme_id
 * @property integer $last_calculated_rating
 * @property string $content
 */
class ForumMessages extends \yii\db\ActiveRecord
{

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'forum_messages';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['user_id', 'content'], 'required'],
            [['parent_message_id', 'user_id', 'root_theme_id'], 'integer'],
            [['created_at'], 'safe'],
            [['content'], 'string'],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'parent_message_id' => 'Parent Message ID',
            'created_at' => 'Created At',
            'user_id' => 'User ID',
            'root_theme_id' => 'Root theme ID',
            'content' => 'Content',
        ];
    }


    /**
     * Выдаёт количество тем, созданных конкретным пользователем.
     *
     * @param $user_id
     * @return array
     */
    public static function getCreatedRootsByUserCount($user_id)
    {
        return (new Query())->select('count(*) as created_roost_count')
            ->from(self::tableName())
            ->where(['user_id' => $user_id])
            ->andWhere(['not',['root_theme_id' => null]])
            ->one()['created_roost_count'];
    }

    /**
     * Функция выдает общее количество сообщений, написанных некоторым пользователем, а также их суммарный рейтинг.
     * Поиск двух значений в одной функции сопряжен с необходимостью оптимизировать вывод статистики.
     *
     * @param $user_id int
     * @return array
     */
    public static function getUserMsgCountAndRating($user_id)
    {
        return (new Query())->select("COUNT(*) as msg_summary_count, SUM(last_calculated_rating) as msg_summary_rating")
            ->from(self::tableName())
            ->where(['user_id' => $user_id])
            ->one();
    }

    /**
     * Выдаёт специфичное для сообщений форматирование, ограничивающее точность отображаемого времени по мере увеличения его давности
     * В сообщении, оставленном более суток назад, будет отображаться не время, а день и месяц. В сообщении, оставленном более года назад,
     * будет отображаться лишь название месяца и год, когда оставили сообщение
     * todo сделать функцию мультиязычной с хранением возможных названий месяцев в БД
     *
     * @param $created_at string
     * @return string
     */
    public static function formatCreationTime($created_at)
    {
        $MONTHS_INCL = ['января','февраля','марта','апреля','мая','июня','июля','августа','сентября','октября','ноября','декабря'];
        $MONTHS = ['январь','февраль','март','апрель','май','июнь','июль','август','сентябрь','октябрь','ноябрь','декабрь'];

        $TIME_CONFIG = GeneralSettings::getSettingsObjByName('TIME');
        $time_now = new DateTime(null, new DateTimeZone($TIME_CONFIG->server_timezone));
        $time_created_at = new DateTime($created_at);
        $interval = ($time_now->getTimestamp() - $time_created_at->getTimestamp()) / 3600; //абсолютное количество времени в часах

        if($interval < 24) {
            $time_formatted = $time_created_at->format('H:i');
        } elseif($interval < 8760) { //количество часов в году
            $time_formatted = (+$time_created_at->format('d')).' '.$MONTHS_INCL[$time_created_at->format('m')-1];
        } else {
            $time_formatted = $MONTHS[$time_created_at->format('m')-1].' '.(+$time_created_at->format('Y'));
        }

        return $time_formatted;
    }

    /**
     * Синтаксический сахар над функцией formatCreationTime. Сделан для упрощенного разового вызова извне модели.
     *
     * @param $msg_id int|string
     * @return string
     */
    public static function getFormattedMsgCreationTime($msg_id)
    {
        return self::formatCreationTime(self::findOne(['root_theme_id' => $msg_id])->created_at);
    }

    /**
     * Получает id темы сообщения, в которой оно оставлено
     * Реализовано дико костыльно, лишь бы работало
     *
     * @param $msg_id int
     * @return int
     */
    public static function getTopParent($msg_id)
    {
        $messageRecord = self::findOne(['id' => $msg_id]);
        while ($messageRecord->parent_message_id) {
            $messageRecord = self::findOne(['id' => $messageRecord->parent_message_id]);
        }
        return $messageRecord->root_theme_id;
    }

    /**
     * Функция рекурсивно строит обьект сообщений для его отправки в загружаемую переписку
     *
     * @param $nodeModel ForumMessages
     * @param $levels integer
     * @return stdClass
     */
    protected function getNodeSubjects($nodeModel, $levels) {
        $tree = new stdClass();
        $attribs = $nodeModel->getAttributes();

        $tree->msg_id = $attribs['id'];
        $tree->user_id = $attribs['user_id'];
        $user = User::findOne($tree->user_id);
        $tree->user_login = $user ? $user->username : null;

        //TODO добавить инфу о роли пользователя (user_role)
        $tree->user_role = 0; //0 - обычный пользователь, 1 - модератор, 2 - администратор

        $tree->rating = ForumVotes::getMessageSummaryRating($attribs['id']);

        //Если пользователь уже голосовал за конкретное сообщение- нужно получить его выбор:
        $tree->voting_choise = ForumVotes::getAuthorizedUserChoiseForMessage($attribs['id']);


        $MSG_LIMITS = GeneralSettings::getSettingsObjByName('MESSAGES_LIMITS');
        $TIME_CONFIG = GeneralSettings::getSettingsObjByName('TIME');
        $time_now = new DateTime(null, new DateTimeZone($TIME_CONFIG->server_timezone));

        $time_created_at = new DateTime($attribs['created_at']);
        $interval = ($time_now->getTimestamp() - $time_created_at->getTimestamp()) / 3600;
        //todo добавить учитывание полномочий для модератора
        $tree->editable = $interval < $MSG_LIMITS->still_editable_during_hours && $tree->user_id == Yii::$app->user->id;

        $tree->parent_msg_id = $attribs['parent_message_id'];
        $tree->created_at = self::formatCreationTime($attribs['created_at']);
        $tree->content = $attribs['content'];
        $tree->subjected = [];
        $subjected = $nodeModel->getSubjectedMessages()->all();
        $tree->subjected_count = $subjected ? count($subjected) : 0;

        if($levels > 0 && $subjected) {
            foreach ($subjected as $subjModel) {
                array_push($tree->subjected, $this->getNodeSubjects($subjModel, $levels-1));
            }
        }

        return $tree;
    }

    /**
     * Создаёт рекурсивно обьект сообщения с заданным $root_id, и все подчиненные ему обьекты
     * до уровня $levels
     *
     * @param $root_id integer
     * @param int $levels
     * @return stdClass
     */
    public static function getTreeStructById($root_id, $levels = 1)
    {
        $rootModel = self::findOne($root_id);
        $tree = self::getNodeSubjects($rootModel,$levels);
        return $tree;
    }

    /**
     * Создает рекурсивно обьект сообщения из данной модели, и подчиненных сообщений
     * вплоть до уровня $levels
     *
     * @param int $levels
     * @return null|stdClass
     */
    public function getTreeStruct($levels = 1)
    {
        $tree = null;
        if(!$this->isNewRecord) {
           $tree = $this->getNodeSubjects($this,$levels);
        }
        return $tree;
    }

    /**
     * Получает подчиненные данной модели сообщения
     *
     * @return \yii\db\ActiveQuery
     */
    public function getSubjectedMessages()
    {
        return $this->hasMany(ForumMessages::className(),['parent_message_id' => 'id']);
    }
}
