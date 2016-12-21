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

    <?= $form->field($model, 'title', ['template' => "{input}\n\n{hint}\n\n{error}"]) ?>

    <div class="form-group">
        <?= Html::submitButton('Поиск по темам', ['class' => 'btn btn-primary']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>
