<?php
use common\models\ForumMessages;
use yii\helpers\Html;
use yii\helpers\Url;
//todo добавить отображение тегов, рейтинг темы (суммарное количество голосов в сообщениях), общее количество сообщений в теме
//todo добавить ссылки на профили пользователей
?>

<div class="list-group-item">
    <div class="row-action-primary">
        <i class="material-icons">person</i>
    </div>
    <div class="row-content">
        <div class="action-secondary"><?=ForumMessages::findOne(['root_theme_id' => $model->id])->created_at?></div>
        <h4 class="list-group-item-heading"><?= Html::a($model->title,Url::toRoute(['/discussions','id' => $model->id])) ?></h4>
        <p class="list-group-item-text"><?=$model->getRootMessage()->one()->content?></p>
    </div>
</div>
<div class="list-group-separator"></div>
