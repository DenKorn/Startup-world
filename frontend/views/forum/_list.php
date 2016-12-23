<?php
use yii\helpers\Html;
use yii\helpers\Url;

?>

<div class="news-item">
    <h3><?= Html::a($model->title,Url::toRoute(['/discussions','id' => $model->id])) ?></h3>
    <h4><?= Html::a($model->getRootMessage()->one()->content,'') ?></h4>
</div>