<?php

use yii\helpers\Html;


/* @var $this yii\web\View */
/* @var $msgModel common\models\ForumRoots */
/* @var $themeModel common\models\ForumRoots */
/* @var $isBanned boolean */

$this->title = 'Создать новую тему';
?>

<div class="row col-md-offset-2 col-md-8">
    <div class="panel panel-default">
        <div class="panel-body text-center">
            <h2><?= Html::encode($this->title) ?></h2>
            <?php if($isBanned): ?>
                <p><strong>Вы заблокированы и не можете создавать новые темы...
                        Советуем подумать над своим поведением, пока наши админы не помиловали вашу грешную душу!</strong></p>
            <?php else: ?>
                <p>Грамотный заголовок темы и хорошее описание - ключ к оживленному общению в вашей теме.
                    <strong>ВНИМАНИЕ! После создания темы вы не можете удалить её или изменить главное сообщение!</strong></p>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php if(!$isBanned): ?>
<div class="row col-md-offset-2 col-md-8">
    <div class="panel panel-default">
        <div class="panel-body text-center">
            <?= $this->render('_form', ['msgModel'=>$msgModel, 'themeModel'=>$themeModel]) ?>
        </div>
    </div>
</div>
<?php endif; ?>



