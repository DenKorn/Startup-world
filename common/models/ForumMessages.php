<?php

namespace common\models;

use Yii;

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

    public function getSubjectedMessages()
    {
        return $this->hasMany(ForumMessages::className(),['parent_message_id' => 'id']);
    }
}
