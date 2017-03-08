<?php

namespace common\models;

use Yii;
use yii\db\Query;

/**
 * This is the model class for table "forum_roots".
 *
 * @property integer $id
 * @property string $title
 */
class ForumRoots extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'forum_roots';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['title'], 'required'],
            [['title'], 'string'],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'title' => 'Title',
        ];
    }

    public function getRootMessage()
    {
        return $this->isNewRecord ?  new ForumMessages() : $this->hasOne(ForumMessages::className(),['root_theme_id' => 'id']);
    }
}
