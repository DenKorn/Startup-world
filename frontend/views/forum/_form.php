<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;

/* @var $this 4yii\web\View */
/* @var $themeModel common\models\ForumRoots */
/* @var $msgModel common\models\ForumMessages */
/* @var $form yii\widgets\ActiveForm */
?>

<div class="forum-roots-form">

    <?php $form = ActiveForm::begin();
          $commonTemplate = "{input}\n\n{hint}\n\n{error}";
    ?>
    Заголовок темы
    <?= $form->field($themeModel, 'title',
        ['template' => $commonTemplate])->textInput()
        ->hint('Длина заголовка до 150 символов.') ?>
    Тело вашего главного сообщения
    <?= $form->field($themeModel->rootMessage, 'content',
        ['template' => $commonTemplate])->textInput()
        ->hint('Длина сообщения до 1000 символов.') ?>

    <div class="form-group">
        <?= Html::submitButton($themeModel->isNewRecord ? 'Создать тему' : 'Обновить тему', ['class' => $themeModel->isNewRecord ? 'btn btn-success' : 'btn btn-primary']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>
