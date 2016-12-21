<?php

namespace common\models;

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
        $tree->user_id = $attribs['user_id']; //TODO: дополнять инфой о пользователях это поле
        $tree->created_at = $attribs['created_at'];
        $tree->content = $attribs['content'];
        $tree->subjected = [];
        if($levels > 0) {
            $subjected = $nodeModel->getSubjectedMessages()->all();
            if($subjected) {
                foreach ($subjected as $subjModel) {
                    array_push($tree->subjected, $this->getNodeSubjects($subjModel, $levels-1));
                }
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
