<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;

/* @var $this yii\web\View */
/* @var $model common\models\ForumRootsSearch */
/* @var $form yii\widgets\ActiveForm */
?>

<div class="forum-roots-search">

    <?php $form = ActiveForm::begin([
        'action' => ['index'],
        'method' => 'get',
    ]); ?>

        <div class="col-md-11">
            <?= $form->field($model, 'title', ['template' => "{input}\n\n{hint}\n\n{error}"]) ?>
        </div>
        <div class="col-md-1 center-block">
            <br>
            <?= Html::submitButton('<i class="material-icons">search</i>', ['class' => 'btn btn-fab btn-fab-mini']) ?>
        </div>


    <?php ActiveForm::end(); ?>

</div>
