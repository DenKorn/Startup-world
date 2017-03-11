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


    <div class="label-floating">
        <div class="input-group">
            <?= $form->field($model, 'title', ['template' => "{input}\n\n{hint}\n\n{error}"]) ?>
            <span class="input-group-btn">
     <?= Html::submitButton('<i class="material-icons">search</i>', ['class' => 'btn btn-fab btn-fab-mini']) ?>
    </span>
        </div>
    </div>

    <?php ActiveForm::end(); ?>

</div>
