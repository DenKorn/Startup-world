<?php

use yii\helpers\Html;
use yii\grid\GridView;

/* @var $this yii\web\View */
/* @var $searchModel common\models\ForumRootsSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = 'Темы форума';
?>
<div class="forum-roots-index">
    <h1><?= Html::encode($this->title) ?></h1>
    <?php  echo $this->render('_search', ['model' => $searchModel]); ?>
    <?= \yii\widgets\ListView::widget([
        'dataProvider' => $dataProvider,
        'itemView' => '_list',
        'emptyText' => 'Список пуст',
        'emptyTextOptions' => [
            'tag' => 'p'
        ],
        'pager' => [
            'firstPageLabel' => 'first',
            'lastPageLabel' => 'last',
            'prevPageLabel' => '<span class="glyphicon glyphicon-chevron-left"></span>',
            'nextPageLabel' => '<span class="glyphicon glyphicon-chevron-right"></span>',
            'maxButtonCount' => 8,
        ],
        'layout' => "{items}\n{pager}",
    ]); ?>
</div>