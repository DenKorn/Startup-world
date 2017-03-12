<?php
/**
 * @var $this yii\web\View
 * @var $userInfo \common\models\User
 * @var $isOnline boolean
 * @var $isBanned boolean
 * @var $isAbleToBanOrWrite boolean
 * @var $isAdmin boolean
 * @var $banReason string
 * @var $stats array
 * @var $targetUserRoles array
 * @var $isOwnProfile boolean
 */

$roleName = array_key_exists('admin',$targetUserRoles) ? "администратор" : (array_key_exists('moderator',$targetUserRoles) ? "модератор" : null);

$this->title = ($isOwnProfile ? "Мой п" : "П" )."рофиль - @$userInfo->username".($roleName ? " ($roleName)" : "");

if($isOwnProfile || $isAbleToBanOrWrite) {
    $this->registerJsFile(
        '@web/js/profile-controller.js',
        ['depends' => [\yii\web\JqueryAsset::className()]]
    );

    $script = <<< JS
        window.PROFILE_TARGET_ID = $userInfo->id;
JS;
    $this->registerJs($script,$this::POS_HEAD);
}

echo $this->render('profile_header',[
    'isOnline' => $isOnline,
    'isAbleToBanOrWrite' => $isAbleToBanOrWrite,
    'isBanned' => $isBanned,
    'userInfo' => $userInfo,
    'isOwnProfile' => $isOwnProfile
]);

if($isBanned) {
    echo $isOwnProfile ? $this->render('user_blocked_dialog_own', ['isBanned' => $isBanned, 'ban_reason' => $banReason])
        : $this->render('user_blocked_dialog', ['showedForModerator' => $isAbleToBanOrWrite]);
}

if(!$isBanned || $isAbleToBanOrWrite) {
    echo $this->render('profile_settings', [
        'isOwnProfile' => $isOwnProfile,
        'isAdmin' => $isAdmin,
        'userInfo' => $userInfo,
    ]);

    if($isOwnProfile) {
        echo $this->render('stats', [
            'stats' => $stats,
            'isOwnProfile' => $isOwnProfile
        ]);
    }
}

if($isAdmin && !$isOwnProfile) {
    echo $this->render('choose_user_role', [
        'targetUserRoles' => $targetUserRoles
    ]);
}