<?php

use yii\helpers\Html;


/* @var $this yii\web\View */
/* @var $msgModel common\models\ForumRoots */
/* @var $themeModel common\models\ForumRoots */

$this->title = 'Создать новую тему';
?>

<div class="row col-md-offset-2 col-md-8">
    <div class="panel panel-default">
        <div class="panel-body text-center">
            <h2><?= Html::encode($this->title) ?></h2>
            <p>Грамотный заголовок темы и хорошее описание - ключ к оживленному общению в вашей теме.
                <strong>ВНИМАНИЕ! После создания темы вы не можете удалить её или изменить главное сообщение!</strong></p>
        </div>
    </div>
</div>

<div class="row col-md-offset-2 col-md-8">
    <div class="panel panel-default">
        <div class="panel-body text-center">
            <?= $this->render('_form', ['msgModel'=>$msgModel, 'themeModel'=>$themeModel]) ?>
        </div>
    </div>
</div>




