<?php

namespace common\models;

use DateTime;
use stdClass;
use Yii;
use yii\base\Object;

/**
 * This is the model class for table "forum_messages".
 *
 * @property integer $id
 * @property integer $parent_message_id
 * @property string $created_at
 * @property integer $user_id
 * @property integer $root_theme_id
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
        $time_now = new DateTime();
        $time_created_at = new DateTime($attribs['created_at']);
        $interval = ($time_now->getTimestamp() - $time_created_at->getTimestamp()) / 3600;
        //todo добавить учитывание полномочий для модератора
        $tree->editable = $interval < $MSG_LIMITS->still_editable_during_hours && $tree->user_id == Yii::$app->user->id;

        $tree->parent_msg_id = $attribs['parent_message_id'];
        $tree->created_at = $attribs['created_at'];
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
