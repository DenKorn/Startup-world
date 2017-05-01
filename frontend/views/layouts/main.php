<?php

/* @var $this \yii\web\View */
/* @var $content string */

use common\models\User;
use frontend\assets\MaterialAsset;
use yii\helpers\Html;
use yii\bootstrap\Nav;
use yii\bootstrap\NavBar;
use common\widgets\Alert;
use yii\helpers\Url;

$siteBundle = MaterialAsset::register($this);
?>
<?php $this->beginPage() ?>
<!DOCTYPE html>
<html lang="<?= Yii::$app->language ?>">
<head>
    <meta charset="<?= Yii::$app->charset ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <?= Html::csrfMetaTags() ?>
    <title><?= Html::encode($this->title) ?></title>
	<link href="/favicon.ico" rel="icon" type="image/x-icon" />
    <?php $this->head() ?>
</head>
<body>
<?php $this->beginBody() ?>

<div class="wrap">
    <?php
    NavBar::begin([
        'brandLabel' =>  Html::img("@web/img/forum_logo.png",['class' => 'img-responsive block-center','style'=>"width:160px; margin-top:-5px"]),// 'Startup world',
        'brandUrl' => Yii::$app->homeUrl,
        'options' => [
            'class' => 'navbar navbar-default navbar-fixed-top', // прежнее: navbar-inverse navbar-fixed-top
        ],
    ]);
    $menuItems = [];
    if(Yii::$app->user->can('admin')) {
        $menuItems[] = ['label' => 'Админ. панель', 'url' => ['/admin']];
    }
    $menuItems[] = ['label' => '[Табулирование]', 'url' => ['/test-playground/index']];
    $menuItems[] = ['label' => 'Главная', 'url' => ['/site/index']];
    $menuItems[] = ['label' => 'Темы форума', 'url' => ['/forum']];
    $menuItems[] = ['label' => 'Обратная связь', 'url' => ['/site/contact-us']];

    if (Yii::$app->user->isGuest) {
        $menuItems[] = ['label' => 'Войти', 'url' => ['/site/login']];
    } else {
       if (! \common\models\ForumBanList::findOne(['user_id' => Yii::$app->user->id])) {
           $menuItems[] = ['label' => 'Создать тему', 'url' => ['/forum/create-theme']];
       }

       $menuItems[] = ['label' => 'Профиль', 'url' => ['/profile']];
       $menuItems[] = '<li>'
            . Html::beginForm(['/site/logout'], 'post')
            . Html::submitButton(
                'Выйти (' . Yii::$app->user->identity->username . ')',
                ['class' => 'btn btn-link logout forum-logout-btn-fix']
            )
            . Html::endForm()
            . '</li>';
    }
    echo Nav::widget([
        'options' => ['class' => 'navbar-nav navbar-right'],
        'items' => $menuItems,
    ]);
    NavBar::end();
    ?>

    <div class="container">
        <?= Alert::widget() ?>
        <?= $content ?>
    </div>
    <div class="btn-scroll-up">
        <a href="javascript:void(0)" class="btn btn-default btn-fab"><i class="material-icons">arrow_upward</i></a>
    </div>
</div>

<div class="forum-alerts"></div>

<footer class="footer">
    <div class="container">
        <p class="text-center">&copy; Startup World <?= date('Y') ?></p>
    </div>
</footer>

<?php
$apiBaseUrl = Url::home(true);
$clientModel = User::findOne(Yii::$app->user->id);

$id = 'null';
$login = 'null';
$role = 'null';
$banned = 'false';

if($clientModel) {
    $id = $clientModel->id;
    $login = $clientModel->username;
    $role = null; //$clientModel->role;

    if(\common\models\ForumBanList::findOne(['user_id' => $clientModel->id])) {
     $banned = 'true';
    }
}

$script = <<< JS
window.API_BASE_LINK = "$apiBaseUrl";
window.CLIENT_ID = $id;
window.CLIENT_LOGIN = '$login';
window.CLIENT_ROLE = '$role';
window.CLIENT_BANNED = $banned;
JS;

$this->registerJs($script,$this::POS_HEAD);

$requestInterval = \common\models\GeneralSettings::getSettingsObjByName('USER_NOTIFICATIONS')->notification_request_interval_in_seconds;

$notifications_init_script = <<< NISCRIPT
userNotifications.init($requestInterval);
NISCRIPT;

$this->registerJs($notifications_init_script, $this::POS_READY);
?>

<?php $this->endBody() ?>
</body>
</html>
<?php $this->endPage() ?>
