<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;

?>

<div class="panel panel-default col-lg-offset-2 col-lg-8">
    <div class="panel-heading"><h3 class="text-center"><strong>Форма регистрации</strong></h3></div>
    <div class="panel-body">

        <div class="registration-form">

            <?php $form = ActiveForm::begin();
            $commonTemplate = "{input}\n\n{hint}\n\n{error}";
            ?>
            <h4>Логин</h4>
            <h4>Пароль</h4>
            <h4>Подтвердить пароль</h4>
            <h4>Электронная почта</h4>

            <?= 0/*$form->field($themeModel, 'title',
        ['template' => $commonTemplate])->textInput()
        ->hint('Длина заголовка до 150 символов.') */?>

            <h3>Тело вашего сообщения</h3>
            <?=0/* $form->field($themeModel->rootMessage, 'content',
        ['template' => $commonTemplate])->textInput()
        ->hint('Длина сообщения до 1500 символов.') */?>

            <div class="form-group text-center text-capitalize">
                <?= Html::submitButton('Зарегистрироваться', ['class' =>  'btn btn-primary']) ?>
            </div>

            <?php ActiveForm::end(); ?>

        </div>


    </div>
</div>