<?php

use yii\helpers\Html;


/* @var $this yii\web\View */
/* @var $msgModel common\models\ForumRoots */
/* @var $themeModel common\models\ForumRoots */

$this->title = 'Создать новую тему';
?>

<div class="forum-roots-create">

    <h1><?= Html::encode($this->title) ?></h1>

    <?= $this->render('_form', ['msgModel'=>$msgModel, 'themeModel'=>$themeModel]) ?>

</div>
