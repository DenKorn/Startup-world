<?php
use yii\helpers\Html;
?>

<div class="news-item">
    <h3><?= Html::a($model->title,'discussions?id='.$model->id) ?></h3>
    <h4><?= Html::a($model->getRootMessage()->one()->content,'') ?></h4>
</div>