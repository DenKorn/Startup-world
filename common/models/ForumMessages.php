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

    protected function getNodeSubjects($nodeModel, $levels) {
        $tree = new stdClass();
        $attribs = $nodeModel->getAttributes();
        $tree->user_id = $attribs['user_id'];
        $user = User::findOne($tree->user_id);
        $tree->user_login = $user ? $user->username : null;
        //TODO добавить инфу о рейтинге сообщения (rating) и роли пользователя (user_role)
        $tree->user_role = 0; //0 - обычный пользователь, 1 - модератор, 2 - администратор
        $tree->rating = 15;
        $tree->voting_choise = 0; // -1 - дизлайк, 0 - не оценивал, 1 - лайк// todo Если пользователь уже голосовал - нужно получить его выбор
        $MAX_DAYS_SINCE_CREATED = 1;     //todo время, допустимое для редактирования сообщения подтягивать из таблицы настроек в БД

        $time_now = new DateTime();
        $time_created_at = new DateTime($attribs['created_at']);
        $interval = $time_now->diff($time_created_at,true)->days; //todo корректировку по часовым поясам

        $tree->editable = $interval < $MAX_DAYS_SINCE_CREATED && $tree->user_id == Yii::$app->user->id;
        $tree->msg_id = $attribs['id'];
        //$tree->parent_msg_id = $attribs['parent_message_id']; //пока не требуется
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

    public function getTreeStructById($root_id, $levels = 1)
    {
        $rootModel = static::findOne($root_id);
        $tree = $this->getNodeSubjects($rootModel,$levels);
        return $tree;
    }

    public function getTreeStruct($levels = 1)
    {
        $tree = null;
        if(!$this->isNewRecord) {
           $tree = $this->getNodeSubjects($this,$levels);
        }
        return $tree;
    }

    public function getSubjectedMessages()
    {
        return $this->hasMany(ForumMessages::className(),['parent_message_id' => 'id']);
    }
}
