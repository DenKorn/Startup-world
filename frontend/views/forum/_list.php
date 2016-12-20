<?php
use yii\helpers\Html;
use yii\helpers\HtmlPurifier;
?>

<div class="news-item">
    <h2><?= Html::a($model->title,'discussions?id='.$model->id) ?></h2>
</div>