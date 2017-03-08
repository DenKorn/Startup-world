<?php
/**
 * @var $this yii\web\View
 * @var $userInfo \common\models\User
 * @var $isOnline boolean
 * @var $stats array
 * @var $isOwnProfile boolean
 */

$this->title = $isOwnProfile ? "Мой профиль (@$userInfo->username)" : "Профиль - @$userInfo->username";
?>

<div class="row col-md-offset-2 col-md-8 col-lg-offset-0 col-lg-12">
    <div class="panel panel-default">
        <div class="panel-body">
            <h3 style="display: inline"><div style="display:inline-block; width:3.3%"></div><?= $this->title ?></h3>
            <?= $isOnline ?
                '<span style="font-size: 1.3em; color: green">online</span>' :
                '<span style="font-size: 1.3em; color: grey">offline</span>' ?>
            <a href="javascript:void(0)" class="btn btn-raised btn-warning btn-sm" style="margin: 0px 5px 0px;">Уведомление</a>
            <a href="javascript:void(0)" class="btn btn-raised btn-danger btn-sm" style="margin: 0px 5px 0px;">Бан</a>
            <?= $isOnline ? '' : "<span style='float: right'>Был в сети: $userInfo->last_activity</span>"?>
        </div>
    </div>
</div>

<div class="row col-md-offset-2 col-md-8 col-lg-offset-0 col-lg-12">
    <div class="panel panel-default">
        <div class="panel-body">

            <div class="list-group">
                <div class="list-group-item">
                    <div class="row-picture">
                        <img class="circle" src="http://lorempixel.com/56/56/people/1" alt="icon">
                    </div>
                    <div class="row-content">
                        <h4 class="list-group-item-heading">Tile with avatar</h4>

                        <p class="list-group-item-text">Donec id elit non mi porta gravida at eget metus</p>
                    </div>
                </div>
                <div class="list-group-separator"></div>
                <div class="list-group-item">
                    <div class="row-picture">
                        <img class="circle" src="http://lorempixel.com/56/56/people/6" alt="icon">
                    </div>
                    <div class="row-content">
                        <h4 class="list-group-item-heading">Tile with another avatar</h4>

                        <p class="list-group-item-text">Maecenas sed diam eget risus varius blandit.</p>
                    </div>
                </div>
                <div class="list-group-separator"></div>
                <div class="list-group-item">
                    <div class="row-action-primary checkbox">
                        <label><input type="checkbox"></label>
                    </div>
                    <div class="row-content">
                        <h4 class="list-group-item-heading">Tile with a checkbox in it</h4>

                        <p class="list-group-item-text">Donec id elit non mi risus varius blandit.</p>
                    </div>
                </div>
                <div class="list-group-separator"></div>
            </div>

        </div>
    </div>
</div>

<?php //Пользователям доступен просмотр лишь своей статистики
if($isOwnProfile): ?>

<div class="row col-md-offset-2 col-md-8 col-lg-offset-0 col-lg-12">
    <div class="panel panel-default">
        <div class="panel-body">
            <h3 style="display: inline"><div style="display:inline-block; width:3.3%"></div><?= $isOwnProfile ? 'Моя статистика' : 'Статистика пользователя' ?></h3>
            <br><br>
            <table class="table table-striped table-hover">
                <tbody>
                <tr>
                    <td>1</td>
                    <td>Написал сообщений</td>
                    <td><?= $stats['created_messages_count'] ?></td>
                </tr>
                <tr>
                    <td>2</td>
                    <td>Создал тем</td>
                    <td><?= $stats['created_themes_count'] ?></td>
                </tr>
                <tr>
                    <td>2</td>
                    <td>Мой средний рейтинг</td>
                    <td><?= $stats['average_user_rating'] ?></td>
                </tr>
                <tr>
                    <td>4</td>
                    <td>Оценил сообщений</td>
                    <td><?= $stats['user_rates_count'] ?></td>
                </tr>
                <tr>
                    <td>5</td>
                    <td>Рейтинг моих оценок</td>
                    <td><?= $stats['summary_user_rate'] ?></td>
                </tr>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php endif; ?>
