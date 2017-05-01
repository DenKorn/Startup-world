<?php

/* @var $this yii\web\View */
/* @var $form yii\bootstrap\ActiveForm */
/* @var $model \common\models\LoginForm */

use yii\helpers\Html;
use yii\bootstrap\ActiveForm;

$this->title = 'Обратная связь';
$this->params['breadcrumbs'][] = $this->title;
?>

<div class="row col-md-offset-1 col-md-10">
    <div class="panel panel-default center-block">
        <div class="panel-body">
            <h3 class="text-center">Обратная связь с администрацией</h3>
            <div class="col-lg-6 col-lg-offset-3">
                <form action="/site/contact-us" method="post">
                    <div class="form-group">
                        <label class="control-label" for="inputDefault">Ваше имя</label>
                        <input type="text" class="form-control" id="inputDefault">
                    </div>

                    <div class="form-group">
                        <label for="inputEmail" class="control-label">Email</label>
                        <input type="email" class="form-control" id="inputEmail">
                    </div>

                    <div class="form-group">
                        <label for="textArea" class="control-label">Ваше сообщение</label>
                        <textarea class="form-control" rows="3" id="textArea"></textarea>
                    </div>

                    <div class="form-group">
                        <div class="col-md-10 col-md-offset-2">
                            <button type="submit" class="btn btn-primary">Отправить</button>
                            <button type="reset" class="btn btn-default">Сброс</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>