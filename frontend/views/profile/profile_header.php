<?php
/**
 * @var $isOnline boolean
 * @var $isAbleToBanOrWrite boolean
 * @var $isBanned boolean
 * @var $userInfo \common\models\User
 */
?>

<div class="row col-md-offset-1 col-md-10 col-lg-offset-0 col-lg-12">
    <div class="panel panel-default">
        <div class="panel-body">
            <h3 style="display: inline"><div style="display:inline-block; width:3.3%"></div><?= $this->title ?></h3>
            <?= $isOnline ?
                '<span style="font-size: 1.3em; color: green">online</span>' :
                '<span style="font-size: 1.3em; color: grey">offline</span>' ?>
            <?php if($isAbleToBanOrWrite): ?>
                <a href="javascript:void(0)" class="btn btn-raised btn-warning btn-sm" style="margin: 0px 5px 0px;">Уведомление</a>
                <?php if($isBanned): ?>
                    <a href="javascript:void(0)" class="btn btn-raised btn-primary btn-sm" style="margin: 0px 5px 0px;">Разблокировать</a>
                <?php else: ?>
                    <a href="javascript:void(0)" class="btn btn-raised btn-danger btn-sm" style="margin: 0px 5px 0px;">Заблокировать</a>
                <?php endif; ?>

                <?= $isOnline ? '' : "<span style='float: right'>Был в сети: $userInfo->last_activity</span>"?>
            <?php endif;?>
        </div>
    </div>
</div>
