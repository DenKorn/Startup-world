<?php
use common\models\ForumMessages;
use yii\helpers\Html;
use yii\helpers\Url;
//todo добавить отображение тегов, рейтинг темы (суммарное количество голосов в сообщениях), общее количество сообщений в теме
//todo добавить ссылки на профили пользователей
$time_formatted = ForumMessages::getFormattedMsgCreationTime($model->id);
$cutted_msg = mb_substr($model->getRootMessage()->one()->content, 0, 250, 'UTF-8') . '...';
?>

<div class="list-group-item">
    <div class="row-action-primary">
        <i class="material-icons">person</i>
    </div>
    <div class="row-content">
        <div class="action-secondary"><?= $time_formatted ?></div>
        <h4 class="list-group-item-heading"><?= Html::a($model->title,Url::toRoute(['/discussions','id' => $model->id])) ?></h4>
        <p class="list-group-item-text"><?= $cutted_msg ?></p>
    </div>
</div>
<div class="list-group-separator"></div>
