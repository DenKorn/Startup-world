<?php

use yii\helpers\Html;


/* @var $this yii\web\View */
/* @var $model common\models\ForumMessages */

$this->title = 'Create Forum Messages';
$this->params['breadcrumbs'][] = ['label' => 'Forum Messages', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="forum-messages-create">

    <h1><?= Html::encode($this->title) ?></h1>

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>
