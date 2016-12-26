<?php

/* @var $this yii\web\View */
/* @var $name string */
/* @var $message string */
/* @var $exception Exception */

use yii\helpers\Html;

$this->title = $name;
?>
<div class="site-error">

    <h1><?= Html::encode($this->title) ?></h1>

    <div class="alert alert-danger">
        <?= nl2br(Html::encode($message)) ?>
    </div>

    <p>
        Вышеуказанная проблема произошла при попытке сервера обработать Ваш запрос.
    </p>
    <p>
        Если вы считаете, что это сервер неправильно работает - свяжитесь с нами.
        Заранее благодарим за помощь в улучшении форума!
    </p>

</div>
