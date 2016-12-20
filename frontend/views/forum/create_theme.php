<?php

use yii\helpers\Html;


/* @var $this yii\web\View */
/* @var $model common\models\ForumRoots */

$this->title = 'Create Forum Roots';
$this->params['breadcrumbs'][] = ['label' => 'Forum Roots', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="forum-roots-create">

    <h1><?= Html::encode($this->title) ?></h1>

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>
