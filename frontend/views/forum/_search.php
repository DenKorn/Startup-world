<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;

/* @var $this yii\web\View */
/* @var $model common\models\ForumRootsSearch */
/* @var $form yii\widgets\ActiveForm */
?>

<div class="form-group label-floating">
    <label class="control-label" for="addon2">Floating label w/right addon</label>
    <div class="input-group">
        <input type="text" id="addon2" class="form-control">
        <span class="input-group-btn">
      <button type="button" class="btn btn-fab btn-fab-mini">
        <i class="material-icons">search</i>
      </button>
    </span>
    </div>
</div>

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
