<?php
/**
 * @var $isOnline boolean
 * @var $isAbleToBanOrWrite boolean
 * @var $isBanned boolean
 * @var $userInfo \common\models\User
 */
//todo добавить для описания времени последнего визита своё особое форматирование, чтобы надпись смотрелась органично
?>

<div class="row">
    <div class="col-md-offset-1 col-md-10 col-lg-offset-0 col-lg-12">
        <div class="panel panel-default">
            <div class="panel-heading text-center">
                <h4 style="display: inline">
                    <?= $this->title ?>
                </h4>
                    <span style="font-size: 1.3em; color: green"><?= $isOnline ? 'online' : 'offline' ?></span>
                <?php if ($isAbleToBanOrWrite && !$isOwnProfile): ?>
                    <a href="javascript:void(0)" class="btn btn-raised btn-warning btn-xs" style="margin: 0px 5px 0px;"
                       onclick="profileController.notifyUser()">Уведомление</a>
                    <?php if ($isBanned): ?>
                        <a href="javascript:void(0)" class="btn btn-raised btn-primary btn-xs"
                           style="margin: 0px 5px 0px;"
                           onclick="profileController.setBanStatus(false)">Разблокировать</a>
                    <?php else: ?>
                        <a href="javascript:void(0)" class="btn btn-raised btn-danger btn-xs"
                           style="margin: 0px 5px 0px;" onclick="profileController.setBanStatus(true)">Заблокировать</a>
                    <?php endif; ?>
                    <?= $isOnline ? '' : "<div style=''>был в сети: ".\common\models\ForumMessages::formatCreationTime($userInfo->last_activity)."</div>" ?>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>
