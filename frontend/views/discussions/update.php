<?php

use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $model common\models\ForumMessages */

$this->title = 'Update Forum Messages: ' . $model->id;
$this->params['breadcrumbs'][] = ['label' => 'Forum Messages', 'url' => ['index']];
$this->params['breadcrumbs'][] = ['label' => $model->id, 'url' => ['view', 'id' => $model->id]];
$this->params['breadcrumbs'][] = 'Update';
?>
<div class="forum-messages-update">

    <h1><?= Html::encode($this->title) ?></h1>

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>
