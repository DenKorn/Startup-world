<?php

use yii\helpers\Html;
use yii\grid\GridView;

/* @var $this yii\web\View */
/* @var $searchModel common\models\ForumRootsSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = 'Темы форума';
?>

    <div class="row col-md-offset-2 col-md-8">
        <div class="panel panel-default">
            <div class="panel-body text-center">
                <h1><?= Html::encode($this->title) ?></h1>
                <p>Выберите интересную для вас тему ниже, воспользовавшись поиском. Вы также можете создать свою тему и сделать этот форум ещё информативнее!</p>
            </div>
        </div>
    </div>

    <div class="row col-md-offset-2 col-md-8">
        <div class="panel panel-default center-block">
            <div class="panel-body">
                <?php  echo $this->render('_search', ['model' => $searchModel]); ?>
            </div>
        </div>
    </div>

<div class="row col-md-offset-2 col-md-8">
    <div class="panel panel-default center-block">
        <div class="panel-body ">
            <div class="list-group">
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
        </div>
    </div>
</div>

