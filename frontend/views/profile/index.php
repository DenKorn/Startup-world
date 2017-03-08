<?php
/**
 * @var $this yii\web\View
 * @var $userInfo \common\models\User
 * @var $isOnline boolean
 * @var $isBanned boolean
 * @var $isAbleToBanOrWrite boolean
 * @var $isAdmin boolean
 * @var $roleName string
 * @var $stats array
 * @var $isOwnProfile boolean
 */

$this->title = ($isOwnProfile ? "Мой п" : "П" )."рофиль - @$userInfo->username".($roleName ? "($roleName)" : "");

echo $this->render('profile_header',[
    'isOnline' => $isOnline,
    'isAbleToBanOrWrite' => $isAbleToBanOrWrite,
    'isBanned' => $isBanned,
    'userInfo' => $userInfo,
]);

if($isBanned) {
    echo $isOwnProfile ? $this->render('user_blocked_dialog_own') : $this->render('user_blocked_dialog');
} else {
    echo $this->render('profile_settings', [
        'isOwnProfile' => $isOwnProfile,
        'isAdmin' => $isAdmin,
        'userInfo' => $userInfo
    ]);

    if($isOwnProfile) {
        echo $this->render('stats',[
            'stats' => $stats,
            'isOwnProfile' => $isOwnProfile
        ]);
    }
}

