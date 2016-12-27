<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;

/* @var $this 4yii\web\View */
/* @var $themeModel common\models\ForumRoots */
/* @var $msgModel common\models\ForumMessages */
/* @var $form yii\widgets\ActiveForm */
//todo модифицировать отображение для поддержки обновления содержимого темы (Update операция)
?>

<div class="forum-roots-form">

    <?php $form = ActiveForm::begin();
          $commonTemplate = "{input}\n\n{hint}\n\n{error}";
    ?>
    <h3>Заголовок темы</h3>
        <?= $form->field($themeModel, 'title',
        ['template' => $commonTemplate])->textInput()
        ->hint('Длина заголовка до 150 символов.') ?>

    <h3>Тело вашего сообщения</h3>
    <?= $form->field($themeModel->rootMessage, 'content',
        ['template' => $commonTemplate])->textInput()
        ->hint('Длина сообщения до 1500 символов.') ?>

    <div class="form-group">
        <?= Html::submitButton($themeModel->isNewRecord ? 'Создать тему' : 'Обновить тему', ['class' => $themeModel->isNewRecord ? 'btn btn-success' : 'btn btn-primary']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>
